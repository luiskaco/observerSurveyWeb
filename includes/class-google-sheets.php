<?php
namespace Observatorio\Survey;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Google_Sheets {

    const OPTION_KEY = 'obs_survey_google_sheets_config';
    const TOKEN_TRANSIENT = 'obs_survey_gsheet_token';

    /**
     * Obtiene la configuración guardada
     */
    public static function get_config() {
        $defaults = array(
            'enabled'        => false,
            'spreadsheet_id' => '',
            'sheet_name'     => 'Respuestas',
            'credentials'    => '', // JSON raw o vacío si usa archivo local
        );

        $saved = get_option( self::OPTION_KEY, array() );
        return wp_parse_args( $saved, $defaults );
    }

    /**
     * Guarda la configuración
     */
    public static function save_config( $data ) {
        $config = self::get_config();

        $config['enabled']        = ! empty( $data['enabled'] );
        $config['spreadsheet_id'] = self::extract_spreadsheet_id( sanitize_text_field( $data['spreadsheet_id'] ?? '' ) );
        $config['sheet_name']     = sanitize_text_field( $data['sheet_name'] ?? 'Respuestas' );
        
        if ( isset( $data['credentials'] ) ) {
            $trimmed = trim( $data['credentials'] );
            if ( ! empty( $trimmed ) ) {
                $config['credentials'] = $trimmed;
            } else {
                $config['credentials'] = '';
            }
        }

        update_option( self::OPTION_KEY, $config );
        delete_transient( self::TOKEN_TRANSIENT ); // Invalidar token anterior
        return $config;
    }

    /**
     * Extrae el ID del Spreadsheet si el usuario pegó la URL completa
     */
    public static function extract_spreadsheet_id( $input ) {
        $input = trim( $input );
        if ( preg_match( '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $input, $matches ) ) {
            return $matches[1];
        }
        return $input;
    }

    /**
     * Carga las credenciales del Service Account (de DB o de archivo local)
     */
    public static function get_credentials() {
        $config = self::get_config();

        // 1. Si hay credenciales JSON en la configuración de la BD
        if ( ! empty( $config['credentials'] ) ) {
            $decoded = json_decode( $config['credentials'], true );
            if ( ! empty( $decoded['client_email'] ) && ! empty( $decoded['private_key'] ) ) {
                return $decoded;
            }
        }

        // 2. Si existe archivo físico en el plugin
        $file_path = OBS_SURVEY_PLUGIN_DIR . 'credentials/service-account.json';
        if ( file_exists( $file_path ) ) {
            $raw = file_get_contents( $file_path );
            $decoded = json_decode( $raw, true );
            if ( ! empty( $decoded['client_email'] ) && ! empty( $decoded['private_key'] ) ) {
                return $decoded;
            }
        }

        return false;
    }

    /**
     * Genera un Access Token de Google mediante JWT firmado con OpenSSL
     */
    public static function get_access_token( $force_refresh = false ) {
        if ( ! $force_refresh ) {
            $cached = get_transient( self::TOKEN_TRANSIENT );
            if ( $cached ) {
                return $cached;
            }
        }

        $creds = self::get_credentials();
        if ( ! $creds ) {
            return new \WP_Error( 'no_credentials', __( 'No se encontraron credenciales válidas de Google Service Account.', 'observatorio-survey' ) );
        }

        $client_email = $creds['client_email'] ?? '';
        $private_key  = $creds['private_key'] ?? '';

        if ( empty( $client_email ) || empty( $private_key ) ) {
            return new \WP_Error( 'invalid_credentials', __( 'Las credenciales no contienen client_email o private_key válidos.', 'observatorio-survey' ) );
        }

        // Construir JWT Header
        $header = json_encode( array(
            'alg' => 'RS256',
            'typ' => 'JWT',
        ) );

        // Construir JWT Claim
        $now = time();
        $claim = json_encode( array(
            'iss'   => $client_email,
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ) );

        $base64_header = self::base64_url_encode( $header );
        $base64_claim  = self::base64_url_encode( $claim );
        $signature_input = $base64_header . '.' . $base64_claim;

        // Firmar con RSA-SHA256
        $signature = '';
        $binary_key = openssl_pkey_get_private( $private_key );
        if ( ! $binary_key ) {
            return new \WP_Error( 'openssl_key_error', __( 'Error al leer la clave privada RSA.', 'observatorio-survey' ) );
        }

        $signed = openssl_sign( $signature_input, $signature, $binary_key, OPENSSL_ALGO_SHA256 );
        if ( ! $signed ) {
            return new \WP_Error( 'openssl_sign_error', __( 'Error al firmar el token JWT con OpenSSL.', 'observatorio-survey' ) );
        }

        $base64_signature = self::base64_url_encode( $signature );
        $jwt = $signature_input . '.' . $base64_signature;

        // Solicitar token a Google OAuth2
        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'timeout' => 15,
            'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
            'body'    => array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== $code || empty( $body['access_token'] ) ) {
            $err_msg = $body['error_description'] ?? ( $body['error'] ?? 'Error desconocido al autenticar con Google.' );
            return new \WP_Error( 'oauth_failed', $err_msg );
        }

        $access_token = $body['access_token'];
        $expires_in   = (int) ( $body['expires_in'] ?? 3600 );
        
        // Cachear 5 minutos antes de expirar
        set_transient( self::TOKEN_TRANSIENT, $access_token, max( 300, $expires_in - 300 ) );

        return $access_token;
    }

    /**
     * Sincroniza un registro de encuesta a Google Sheets
     */
    public static function append_submission( $submission, $responses = array(), $is_manual = false ) {
        $config = self::get_config();

        if ( empty( $config['spreadsheet_id'] ) ) {
            if ( $is_manual ) {
                return new \WP_Error( 'no_spreadsheet_id', __( 'Debes ingresar el ID o URL de la hoja de Google Sheets en los ajustes.', 'observatorio-survey' ) );
            }
            return false;
        }

        if ( ! $is_manual && empty( $config['enabled'] ) ) {
            return false; // Sincronización automática deshabilitada
        }

        $token = self::get_access_token();
        if ( is_wp_error( $token ) ) {
            error_log( '[Observatorio Google Sheets] Auth Error: ' . $token->get_error_message() );
            return $token;
        }

        $spreadsheet_id = $config['spreadsheet_id'];
        $sheet_name     = ! empty( $config['sheet_name'] ) ? $config['sheet_name'] : 'Respuestas';

        // Asegurar que existan los encabezados en la primera fila
        $header_check = self::ensure_headers( $spreadsheet_id, $sheet_name, $token );
        if ( is_wp_error( $header_check ) ) {
            return $header_check;
        }

        // Construir la fila con todas las columnas
        $row_values = self::format_submission_row( $submission, $responses );

        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!A:A:append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS',
            urlencode( $spreadsheet_id ),
            urlencode( $sheet_name )
        );

        $response = wp_remote_post( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( array(
                'values' => array( $row_values ),
            ) ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( '[Observatorio Google Sheets] Append Error: ' . $response->get_error_message() );
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            $body_raw  = wp_remote_retrieve_body( $response );
            $body_json = json_decode( $body_raw, true );
            $err_msg   = $body_json['error']['message'] ?? ( ! empty( $body_raw ) ? $body_raw : 'Error desconocido de Google Sheets' );
            $creds     = self::get_credentials();

            if ( 403 === $code ) {
                $err_msg = 'Permiso denegado por Google. Comparte tu hoja con permiso de EDITOR a: ' . ( $creds['client_email'] ?? '' );
            } elseif ( 404 === $code ) {
                $err_msg = 'No se encontró la hoja de cálculo con el ID configurado.';
            }

            error_log( '[Observatorio Google Sheets] HTTP ' . $code . ': ' . $err_msg );
            return new \WP_Error( 'sheet_append_failed', $err_msg );
        }

        // Marcar en BD como sincronizado
        if ( is_array( $submission ) && isset( $submission['id'] ) ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'obs_survey_submissions';
            $wpdb->update(
                $table_name,
                array(
                    'synced_to_sheets' => 1,
                    'synced_at'        => current_time( 'mysql' ),
                ),
                array( 'id' => $submission['id'] ),
                array( '%d', '%s' ),
                array( '%d' )
            );
        }

        return true;
    }

    /**
     * Asegura que los encabezados de columnas existan en la fila 1
     */
    public static function ensure_headers( $spreadsheet_id, $sheet_name, $token ) {
        $cache_key = 'obs_headers_ensured_' . md5( $spreadsheet_id . '_' . $sheet_name );
        if ( get_transient( $cache_key ) ) {
            return;
        }

        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!A1:Z1',
            urlencode( $spreadsheet_id ),
            urlencode( $sheet_name )
        );

        $response = wp_remote_get( $url, array(
            'timeout' => 10,
            'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        ) );

        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $data['values'][0] ) ) {
                set_transient( $cache_key, 1, 86400 );
                return; // Ya tiene encabezados
            }
        }

        // Escribir encabezados en fila 1
        $headers = self::get_headers_list();
        $write_url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!A1?valueInputOption=USER_ENTERED',
            urlencode( $spreadsheet_id ),
            urlencode( $sheet_name )
        );

        wp_remote_request( $write_url, array(
            'method'  => 'PUT',
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( array(
                'values' => array( $headers ),
            ) ),
        ) );

        set_transient( $cache_key, 1, 86400 );
    }

    /**
     * Prueba la conexión con Google Sheets y retorna diagnóstico
     */
    public static function test_connection( $override_spreadsheet_id = null, $override_sheet_name = null ) {
        $config = self::get_config();
        $spreadsheet_id = $override_spreadsheet_id ? self::extract_spreadsheet_id( $override_spreadsheet_id ) : $config['spreadsheet_id'];
        $sheet_name     = $override_sheet_name ?: $config['sheet_name'];

        if ( empty( $spreadsheet_id ) ) {
            return array(
                'success' => false,
                'message' => 'Falta ingresar el ID o URL de la hoja de Google Sheets.',
            );
        }

        $creds = self::get_credentials();
        if ( ! $creds ) {
            return array(
                'success' => false,
                'message' => 'No se encontraron las credenciales de la Cuenta de Servicio (JSON).',
            );
        }

        $token = self::get_access_token( true );
        if ( is_wp_error( $token ) ) {
            return array(
                'success' => false,
                'message' => 'Error de autenticación con Google: ' . $token->get_error_message(),
                'email'   => $creds['client_email'] ?? '',
            );
        }

        // Consultar metadatos del Spreadsheet
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s?fields=properties.title,sheets.properties.title',
            urlencode( $spreadsheet_id )
        );

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => 'Error al conectar con la API de Google: ' . $response->get_error_message(),
                'email'   => $creds['client_email'] ?? '',
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== $code ) {
            $msg = $body['error']['message'] ?? 'Error desconocido al acceder a la hoja.';
            if ( 404 === $code ) {
                $msg = 'No se encontró la hoja de cálculo con ese ID. Verifica el ID ingresado.';
            } elseif ( 403 === $code ) {
                $msg = 'Permiso denegado. Asegúrate de compartir la hoja de cálculo con permiso de EDITOR a: ' . ( $creds['client_email'] ?? '' );
            }
            return array(
                'success' => false,
                'message' => $msg,
                'email'   => $creds['client_email'] ?? '',
            );
        }

        $title = $body['properties']['title'] ?? 'Sin título';
        $sheets = array();
        if ( ! empty( $body['sheets'] ) ) {
            foreach ( $body['sheets'] as $s ) {
                if ( isset( $s['properties']['title'] ) ) {
                    $sheets[] = $s['properties']['title'];
                }
            }
        }

        // Verificar si la pestaña existe
        $sheet_found = in_array( $sheet_name, $sheets, true );

        return array(
            'success'           => true,
            'message'           => '¡Conexión exitosa con Google Sheets!',
            'spreadsheet_title' => $title,
            'sheets'            => $sheets,
            'target_sheet'      => $sheet_name,
            'sheet_found'       => $sheet_found,
            'email'             => $creds['client_email'] ?? '',
        );
    }

    /**
     * Sincroniza en lote registros pendientes
     */
    public static function sync_all_pending( $limit = 50 ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'obs_survey_submissions';

        $pending = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table_name WHERE synced_to_sheets = 0 ORDER BY id ASC LIMIT %d", $limit ),
            ARRAY_A
        );

        if ( empty( $pending ) ) {
            return array( 'synced' => 0, 'total' => 0 );
        }

        $synced_count = 0;
        foreach ( $pending as $row ) {
            $responses = json_decode( $row['responses_json'], true ) ?: array();
            $result = self::append_submission( $row, $responses );
            if ( true === $result ) {
                $synced_count++;
            }
        }

        return array(
            'synced' => $synced_count,
            'total'  => count( $pending ),
        );
    }

    /**
     * Devuelve la lista completa de encabezados de columnas
     */
    public static function get_headers_list() {
        $headers = array(
            'ID',
            'Fecha Registro',
            'Nombres',
            'Apellidos',
            'Edad',
            'Celular',
            'Correo',
            'Región',
            'Es Paciente Actual',
            'Sistema de Salud',
            'Edad al Diagnóstico',
            'Consentimiento',
        );

        $questions_map = self::get_questions_map();
        foreach ( $questions_map as $key => $label ) {
            $headers[] = $label;
        }

        return $headers;
    }

    /**
     * Formatea un registro de envío para una fila de Google Sheets
     */
    public static function format_submission_row( $submission, $responses = array() ) {
        if ( is_object( $submission ) ) {
            $submission = (array) $submission;
        }

        if ( empty( $responses ) && ! empty( $submission['responses_json'] ) ) {
            $responses = json_decode( $submission['responses_json'], true ) ?: array();
        }

        $row = array(
            $submission['id'] ?? '',
            $submission['created_at'] ?? current_time( 'mysql' ),
            $submission['first_name'] ?? '',
            $submission['last_name'] ?? '',
            $submission['age'] ?? '',
            $submission['phone'] ?? '',
            $submission['email'] ?? '',
            $submission['region'] ?? '',
            $submission['is_current_patient'] ?? '',
            $submission['health_system'] ?? '',
            $submission['age_diagnosis'] ?? '',
            ! empty( $submission['consent_accepted'] ) ? 'Sí' : 'No',
        );

        $questions_map = self::get_questions_map();
        foreach ( $questions_map as $key => $label ) {
            $val = $responses[ $key ] ?? '';
            if ( is_array( $val ) ) {
                $val = implode( ', ', $val );
            }
            $row[] = (string) $val;
        }

        return $row;
    }

    /**
     * Mapeo de preguntas para las columnas
     */
    public static function get_questions_map() {
        return array(
            'p1_1'  => 'P1.1 Familiar primer grado con cáncer',
            'p1_2'  => 'P1.2 Edad primera menstruación',
            'p1_3'  => 'P1.3 Tuvo hijos',
            'p1_4'  => 'P1.4 Edad primer parto',
            'p1_5'  => 'P1.5 Amamantó a sus hijos',
            'p1_6'  => 'P1.6 Tiempo total de lactancia',
            'p1_7'  => 'P1.7 Estado menopausia al diagnóstico',
            'p1_8'  => 'P1.8 Terapia reemplazo hormonal',
            'p1_9'  => 'P1.9 Peso aproximado al diagnóstico (kg)',
            'p1_10' => 'P1.10 Estatura aproximada (m)',
            'p1_11' => 'P1.11 Fumaba al momento del diagnóstico',
            'p1_12' => 'P1.12 Consumo de alcohol',
            'p1_13' => 'P1.13 Actividad física previa',
            'p2_1'  => 'P2.1 Cómo descubrió los primeros indicios',
            'p2_2'  => 'P2.2 Primer síntoma o señal que notó',
            'p2_3'  => 'P2.3 Tiempo entre síntomas y primera consulta',
            'p2_4'  => 'P2.4 Primer lugar donde buscó atención',
            'p2_5'  => 'P2.5 Mamografías previas al diagnóstico',
            'p3_1'  => 'P3.1 Lugar donde recibió el diagnóstico definitivo',
            'p3_2'  => 'P3.2 Exámenes para confirmar diagnóstico',
            'p3_3'  => 'P3.3 Tiempo entre sospecha y biopsia',
            'p3_4'  => 'P3.4 Tiempo entre biopsia y resultado final',
            'p3_5'  => 'P3.5 Etapa clínica al diagnóstico',
            'p3_6'  => 'P3.6 Tipo de cáncer de mama informado',
            'p3_7'  => 'P3.7 Le explicaron con claridad el diagnóstico',
            'p3_8'  => 'P3.8 Tuvo que cambiar de sistema de salud',
            'p4_1'  => 'P4.1 Tratamientos recibidos',
            'p4_2'  => 'P4.2 Tiempo entre diagnóstico y primer tratamiento',
            'p4_3'  => 'P4.3 Retrasos o interrupciones en tratamiento',
            'p4_4'  => 'P4.4 Causa principal de interrupciones',
            'p4_5'  => 'P4.5 Tuvo que comprar medicamentos con dinero propio',
            'p4_6'  => 'P4.6 Tuvo que trasladarse de ciudad para tratamiento',
            'p4_7'  => 'P4.7 Apoyo psicológico durante tratamiento',
            'p5_1'  => 'P5.1 Estado actual del tratamiento',
            'p5_2'  => 'P5.2 Frecuencia de controles médicos actuales',
            'p5_3'  => 'P5.3 Secuelas físicas o emocionales persistentes',
            'p6_1'  => 'P6.1 Mayor barrera o dificultad en el proceso',
            'p6_2'  => 'P6.2 Calificación general de la atención recibida',
            'p6_3'  => 'P6.3 Sugerencias o comentarios finales',
        );
    }

    private static function base64_url_encode( $data ) {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    }
}
