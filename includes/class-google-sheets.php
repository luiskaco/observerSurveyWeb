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
        $raw_sheet_name = ! empty( $config['sheet_name'] ) ? $config['sheet_name'] : 'Respuestas';
        $sheet_name     = self::resolve_sheet_name( $spreadsheet_id, $raw_sheet_name, $token );

        // Asegurar que existan los encabezados en la primera fila
        $header_check = self::ensure_headers( $spreadsheet_id, $sheet_name, $token );
        if ( is_wp_error( $header_check ) ) {
            return $header_check;
        }

        // Construir la fila con todas las columnas
        $row_values = self::format_submission_row( $submission, $responses );
        $range      = "'" . str_replace( "'", "''", $sheet_name ) . "'!A:A";

        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS',
            urlencode( $spreadsheet_id ),
            rawurlencode( $range )
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
     * Resuelve y asegura la existencia de la pestaña en Google Sheets
     */
    public static function resolve_sheet_name( $spreadsheet_id, $requested_name, $token ) {
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s?fields=sheets.properties.title',
            urlencode( $spreadsheet_id )
        );

        $response = wp_remote_get( $url, array(
            'timeout' => 10,
            'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return $requested_name ?: 'Respuestas';
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $available = array();
        if ( ! empty( $body['sheets'] ) ) {
            foreach ( $body['sheets'] as $s ) {
                if ( isset( $s['properties']['title'] ) ) {
                    $available[] = $s['properties']['title'];
                }
            }
        }

        if ( empty( $available ) ) {
            return $requested_name ?: 'Respuestas';
        }

        // 1. Si existe la pestaña solicitada
        foreach ( $available as $title ) {
            if ( strcasecmp( $title, $requested_name ) === 0 ) {
                return $title;
            }
        }

        // 2. Si no existe, intentar crearla automáticamente
        $create_title = ! empty( $requested_name ) ? $requested_name : 'Respuestas';
        $batch_url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s:batchUpdate',
            urlencode( $spreadsheet_id )
        );

        $create_res = wp_remote_post( $batch_url, array(
            'timeout' => 10,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( array(
                'requests' => array(
                    array(
                        'addSheet' => array(
                            'properties' => array(
                                'title' => $create_title,
                            ),
                        ),
                    ),
                ),
            ) ),
        ) );

        if ( ! is_wp_error( $create_res ) && 200 === wp_remote_retrieve_response_code( $create_res ) ) {
            return $create_title;
        }

        // 3. Fallback a la primera pestaña existente
        return $available[0];
    }

    /**
     * Asegura que los encabezados de columnas existan en la fila 1
     */
    public static function ensure_headers( $spreadsheet_id, $sheet_name, $token ) {
        $cache_key = 'obs_headers_ensured_' . md5( $spreadsheet_id . '_' . $sheet_name );
        if ( get_transient( $cache_key ) ) {
            return true;
        }

        $range1 = "'" . str_replace( "'", "''", $sheet_name ) . "'!A1:Z1";
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
            urlencode( $spreadsheet_id ),
            rawurlencode( $range1 )
        );

        $response = wp_remote_get( $url, array(
            'timeout' => 10,
            'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        ) );

        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $data['values'][0] ) ) {
                set_transient( $cache_key, 1, 86400 );
                return true; // Ya tiene encabezados
            }
        }

        // Escribir encabezados en fila 1
        $headers = self::get_headers_list();
        $range_write = "'" . str_replace( "'", "''", $sheet_name ) . "'!A1";
        $write_url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s?valueInputOption=USER_ENTERED',
            urlencode( $spreadsheet_id ),
            rawurlencode( $range_write )
        );

        $res = wp_remote_request( $write_url, array(
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
        return true;
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
     * Mapeo de preguntas reales para las columnas de Google Sheets
     */
    public static function get_questions_map() {
        return array(
            'q1_diag_place'                           => '1. Lugar de diagnóstico',
            'q2_derivada_lima'                        => '2. Derivada a Lima',
            'q3_etapa_derivada'                       => '3. Etapa de derivación a Lima',
            'q4_tiempo_atencion_lima'                 => '4. Tiempo de espera atención en Lima',
            'q5_deteccion_inicial'                    => '5. Detección inicial de la señal',
            'q6_tiempo_hasta_buscar_atencion'         => '6. Tiempo hasta buscar atención médica',
            'q7_tiempo_primera_consulta'              => '7. Tiempo hasta primera consulta médica',
            'q8_tipo_establecimiento_primera_consulta'=> '8. Tipo de establecimiento primera consulta',
            'q9_solicitaron_examenes'                 => '9. Solicitud de exámenes de mama',
            'q10_examenes_solicitados'                => '10. Exámenes solicitados',
            'q11_tiempo_hasta_realizar_examen'        => '11. Tiempo hasta realizar el examen',
            'q12_tiempo_hasta_resultado_examen'       => '12. Tiempo hasta recibir resultado del examen',
            'q13_dificultad_examenes'                 => '13. Dificultad para realizar exámenes',
            'q14_principal_dificultad_examenes'       => '14. Principal dificultad en exámenes',
            'q15_derivaron_otro_establecimiento'      => '15. Derivación a otro establecimiento/especialista',
            'q16_tiempo_atencion_derivacion'          => '16. Tiempo de espera tras derivación',
            'q17_cambio_sistema'                      => '17. Cambio de sistema de atención en derivación',
            'q18_indicaron_biopsia'                   => '18. Indicación de biopsia',
            'q19_tiempo_hasta_realizar_biopsia'       => '19. Tiempo hasta realizar la biopsia',
            'q20_tiempo_resultado_biopsia'            => '20. Tiempo hasta resultado de biopsia',
            'q21_establecimiento_biopsia'             => '21. Establecimiento de la biopsia',
            'q22_informaron_subtipo'                  => '22. Información sobre subtipo de cáncer',
            'q23_subtipo_indicado'                    => '23. Subtipo de cáncer informado',
            'q24_estadio_detectado'                   => '24. Estadio detectado',
            'q25_tiempo_estudios_estadio'             => '25. Tiempo de estudios para determinar estadio',
            'q26_estadio_diferente'                   => '26. Estadio diferente al inicio de tratamiento',
            'q27_estadio_inicio_tratamiento'          => '27. Estadio informado al inicio de tratamiento',
            'q28_tratamiento_inicial'                 => '28. Tratamiento indicado inicialmente',
            'q29_tiempo_confirmacion_a_indicacion'    => '29. Tiempo de confirmación a indicación de tratamiento',
            'q30_tiempo_indicacion_a_inicio'          => '30. Tiempo de indicación a inicio de tratamiento',
            'q31_lugar_inicio_tratamiento'            => '31. Lugar donde inició el tratamiento',
            'q32_etapa_mayor_espera'                  => '32. Etapa con mayor tiempo de espera',
            'q33_principal_dificultad_recorrido'      => '33. Principal dificultad en el recorrido',
            'q34_acudio_privado_rapidez'              => '34. Acudió a sector privado por rapidez',
            'q35_demora_retraso_tratamiento'          => '35. Retraso en inicio de tratamiento por demoras',
            'q36_tiempo_total_recorrido'              => '36. Tiempo total desde primera señal hasta tratamiento',
            'q37_informacion_clara_siguiente_paso'    => '37. Información clara sobre el siguiente paso',
            'q38_tratamiento_innovador_no_cubierto'   => '38. Indicación de tratamiento innovador no cubierto',
        );
    }

    private static function base64_url_encode( $data ) {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    }
}
