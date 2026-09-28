<?php
namespace Observatorio\Survey;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Rest_Controller {
    public function register_routes() {
        register_rest_route( 'observatorio/v1', '/survey/submit', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_submission' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'observatorio/v1', '/survey/step-save', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_step_save' ),
            'permission_callback' => '__return_true',
        ) );
    }

    /**
     * Guarda el progreso de un paso individual en segundo plano (Autosave Progresivo)
     */
    public function handle_step_save( $request ) {
        $params = $request->get_json_params();

        // 1. Honeypot check
        if ( ! empty( $params['hp_field'] ) ) {
            return new \WP_REST_Response( array(
                'status'  => 'error',
                'message' => 'Spam detected.',
            ), 400 );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'obs_survey_submissions';
        $now = current_time( 'mysql' );

        $submission_id = ! empty( $params['submission_id'] ) ? absint( $params['submission_id'] ) : 0;
        $step          = ! empty( $params['step'] ) ? max( 1, min( 6, absint( $params['step'] ) ) ) : 1;
        $survey_type   = ! empty( $params['survey_type'] ) ? sanitize_key( $params['survey_type'] ) : 'cancer_mama_journey';

        $first_name = isset( $params['first_name'] ) ? self::format_title_case( sanitize_text_field( $params['first_name'] ) ) : '';
        $last_name  = isset( $params['last_name'] ) ? self::format_title_case( sanitize_text_field( $params['last_name'] ) ) : '';
        $age        = isset( $params['age'] ) ? absint( $params['age'] ) : 0;
        $phone_raw  = isset( $params['phone'] ) ? sanitize_text_field( $params['phone'] ) : '';
        $phone      = self::format_phone( $phone_raw );
        $email      = isset( $params['email'] ) ? sanitize_email( $params['email'] ) : '';
        $region     = isset( $params['region'] ) ? sanitize_text_field( $params['region'] ) : '';
        $is_patient = isset( $params['is_current_patient'] ) ? sanitize_text_field( $params['is_current_patient'] ) : '';
        $health_sys = isset( $params['health_system'] ) ? sanitize_text_field( $params['health_system'] ) : '';
        $age_diag   = isset( $params['age_diagnosis'] ) ? sanitize_text_field( $params['age_diagnosis'] ) : '';

        // Sanitizar respuestas
        $raw_responses       = isset( $params['responses'] ) && is_array( $params['responses'] ) ? $params['responses'] : array();
        $sanitized_responses = $this->sanitize_array( $raw_responses );

        $ip_address = $this->get_client_ip();
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( substr( $_SERVER['HTTP_USER_AGENT'], 0, 250 ) ) : '';

        $existing = null;
        if ( $submission_id > 0 ) {
            $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $submission_id ), ARRAY_A );
        }

        if ( $existing ) {
            // Si ya está completada, no degradar a in_progress
            $status = ( 'completed' === $existing['status'] ) ? 'completed' : 'in_progress';
            $prev_responses = json_decode( $existing['responses_json'], true ) ?: array();
            $merged_responses = array_merge( $prev_responses, $sanitized_responses );

            $update_data = array(
                'updated_at'        => $now,
                'last_step_reached' => max( (int) $existing['last_step_reached'], $step ),
                'responses_json'    => wp_json_encode( $merged_responses ),
            );

            if ( ! empty( $first_name ) ) $update_data['first_name'] = $first_name;
            if ( ! empty( $last_name ) ) $update_data['last_name'] = $last_name;
            if ( $age > 0 ) $update_data['age'] = $age;
            if ( ! empty( $phone ) ) $update_data['phone'] = $phone;
            if ( ! empty( $email ) ) $update_data['email'] = $email;
            if ( ! empty( $region ) ) $update_data['region'] = $region;
            if ( ! empty( $is_patient ) ) $update_data['is_current_patient'] = $is_patient;
            if ( ! empty( $health_sys ) ) $update_data['health_system'] = $health_sys;
            if ( ! empty( $age_diag ) ) $update_data['age_diagnosis'] = $age_diag;

            $wpdb->update( $table_name, $update_data, array( 'id' => $submission_id ) );

            $full_row = array_merge( $existing, $update_data );
            $full_row['id'] = $submission_id;

            // Sincronizar a Google Sheets en pestaña Incompleto (upsert)
            if ( class_exists( 'Observatorio\\Survey\\Google_Sheets' ) && 'in_progress' === $status ) {
                try {
                    Google_Sheets::append_submission( $full_row, $merged_responses );
                } catch ( \Throwable $e ) {
                    error_log( '[Observatorio Survey Step-Save] Error en Sheets: ' . $e->getMessage() );
                }
            }

            return new \WP_REST_Response( array(
                'status'        => 'success',
                'submission_id' => $submission_id,
                'step'          => $step,
            ), 200 );
        } else {
            // Nuevo registro inicial (in_progress)
            $insert_data = array(
                'survey_type'        => $survey_type,
                'consent_accepted'   => 0,
                'first_name'         => $first_name,
                'last_name'          => $last_name,
                'age'                => $age,
                'phone'              => $phone,
                'email'              => $email,
                'region'             => $region,
                'is_current_patient' => $is_patient,
                'health_system'      => $health_sys,
                'age_diagnosis'      => $age_diag,
                'responses_json'     => wp_json_encode( $sanitized_responses ),
                'ip_address'         => $ip_address,
                'user_agent'         => $user_agent,
                'status'             => 'in_progress',
                'last_step_reached'  => $step,
                'created_at'         => $now,
                'updated_at'         => $now,
            );

            $inserted = $wpdb->insert( $table_name, $insert_data );
            if ( false !== $inserted ) {
                $submission_id = $wpdb->insert_id;
                $insert_data['id'] = $submission_id;

                if ( class_exists( 'Observatorio\\Survey\\Google_Sheets' ) ) {
                    try {
                        Google_Sheets::append_submission( $insert_data, $sanitized_responses );
                    } catch ( \Throwable $e ) {
                        error_log( '[Observatorio Survey Step-Save] Error en Sheets: ' . $e->getMessage() );
                    }
                }

                return new \WP_REST_Response( array(
                    'status'        => 'success',
                    'submission_id' => $submission_id,
                    'step'          => $step,
                ), 200 );
            }

            return new \WP_REST_Response( array(
                'status'  => 'error',
                'message' => 'No se pudo guardar el paso.',
            ), 500 );
        }
    }

    public function handle_submission( $request ) {
        $params = $request->get_json_params();

        // 1. Honeypot check
        if ( ! empty( $params['hp_field'] ) ) {
            return new \WP_REST_Response( array(
                'status'  => 'error',
                'message' => 'Spam detected.',
            ), 400 );
        }

        // 2. Extraer y sanitizar datos obligatorios
        $submission_id = ! empty( $params['submission_id'] ) ? absint( $params['submission_id'] ) : 0;
        $first_name  = isset( $params['first_name'] ) ? self::format_title_case( sanitize_text_field( $params['first_name'] ) ) : '';
        $last_name   = isset( $params['last_name'] ) ? self::format_title_case( sanitize_text_field( $params['last_name'] ) ) : '';
        $age         = isset( $params['age'] ) ? absint( $params['age'] ) : 0;
        $phone_raw   = isset( $params['phone'] ) ? sanitize_text_field( $params['phone'] ) : '';
        $phone       = self::format_phone( $phone_raw );
        $email       = isset( $params['email'] ) ? sanitize_email( $params['email'] ) : '';
        $region      = isset( $params['region'] ) ? sanitize_text_field( $params['region'] ) : '';
        $is_patient  = isset( $params['is_current_patient'] ) ? sanitize_text_field( $params['is_current_patient'] ) : '';
        $health_sys  = isset( $params['health_system'] ) ? sanitize_text_field( $params['health_system'] ) : '';
        $age_diag    = isset( $params['age_diagnosis'] ) ? sanitize_text_field( $params['age_diagnosis'] ) : '';
        $consent     = ! empty( $params['consent_accepted'] ) ? 1 : 0;
        $survey_type = ! empty( $params['survey_type'] ) ? sanitize_key( $params['survey_type'] ) : 'cancer_mama_journey';

        // Validaciones básicas de backend
        $errors = array();
        if ( empty( $first_name ) ) {
            $errors['first_name'] = 'El nombre es obligatorio.';
        }
        if ( empty( $last_name ) ) {
            $errors['last_name'] = 'El apellido es obligatorio.';
        }
        if ( empty( $age ) || $age < 10 || $age > 120 ) {
            $errors['age'] = 'Por favor ingrese una edad válida.';
        }
        $phone_digits = preg_replace( '/\D/', '', $phone_raw );
        if ( empty( $phone_digits ) || strlen( $phone_digits ) < 9 ) {
            $errors['phone'] = 'El celular es obligatorio y debe tener al menos 9 dígitos.';
        }
        if ( empty( $email ) || ! is_email( $email ) ) {
            $errors['email'] = 'Por favor ingrese un correo electrónico válido.';
        }
        if ( empty( $region ) ) {
            $errors['region'] = 'La región de residencia es obligatoria.';
        }
        if ( ! $consent ) {
            $errors['consent_accepted'] = 'Debe aceptar la autorización de uso anonimizado de datos.';
        }

        if ( ! empty( $errors ) ) {
            return new \WP_REST_Response( array(
                'status'  => 'error',
                'message' => 'Por favor verifique los campos obligatorios.',
                'errors'  => $errors,
            ), 422 );
        }

        // Sanitizar array completo de respuestas
        $raw_responses = isset( $params['responses'] ) && is_array( $params['responses'] ) ? $params['responses'] : array();
        $sanitized_responses = $this->sanitize_array( $raw_responses );

        // Obtener IP anonimizada y User Agent
        $ip_address = $this->get_client_ip();
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( substr( $_SERVER['HTTP_USER_AGENT'], 0, 250 ) ) : '';

        // Guardar o actualizar en BD
        global $wpdb;
        $table_name = $wpdb->prefix . 'obs_survey_submissions';
        $now = current_time( 'mysql' );

        $existing = null;
        if ( $submission_id > 0 ) {
            $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $submission_id ), ARRAY_A );
        }

        if ( $existing ) {
            $prev_responses = json_decode( $existing['responses_json'], true ) ?: array();
            $merged_responses = array_merge( $prev_responses, $sanitized_responses );

            $update_data = array(
                'survey_type'        => $survey_type,
                'consent_accepted'   => $consent,
                'first_name'         => $first_name,
                'last_name'          => $last_name,
                'age'                => $age,
                'phone'              => $phone,
                'email'              => $email,
                'region'             => $region,
                'is_current_patient' => $is_patient,
                'health_system'      => $health_sys,
                'age_diagnosis'      => $age_diag,
                'responses_json'     => wp_json_encode( $merged_responses ),
                'status'             => 'completed',
                'last_step_reached'  => 6,
                'updated_at'         => $now,
            );

            $wpdb->update( $table_name, $update_data, array( 'id' => $submission_id ) );
            $data = array_merge( $existing, $update_data );
            $data['id'] = $submission_id;
            $sanitized_responses = $merged_responses;
        } else {
            $data = array(
                'survey_type'        => $survey_type,
                'consent_accepted'   => $consent,
                'first_name'         => $first_name,
                'last_name'          => $last_name,
                'age'                => $age,
                'phone'              => $phone,
                'email'              => $email,
                'region'             => $region,
                'is_current_patient' => $is_patient,
                'health_system'      => $health_sys,
                'age_diagnosis'      => $age_diag,
                'responses_json'     => wp_json_encode( $sanitized_responses ),
                'ip_address'         => $ip_address,
                'user_agent'         => $user_agent,
                'status'             => 'completed',
                'last_step_reached'  => 6,
                'created_at'         => $now,
                'updated_at'         => $now,
            );

            $inserted = $wpdb->insert( $table_name, $data );

            if ( false === $inserted ) {
                return new \WP_REST_Response( array(
                    'status'  => 'error',
                    'message' => 'Ocurrió un error al guardar las respuestas en la base de datos.',
                ), 500 );
            }

            $submission_id = $wpdb->insert_id;
            $data['id'] = $submission_id;
        }

        // Sincronizar con Google Sheets (pestaña Completo)
        if ( class_exists( 'Observatorio\\Survey\\Google_Sheets' ) ) {
            try {
                Google_Sheets::append_submission( $data, $sanitized_responses );
            } catch ( \Throwable $e ) {
                error_log( '[Observatorio Survey] Error sincronizando a Google Sheets: ' . $e->getMessage() );
            }
        }

        return new \WP_REST_Response( array(
            'status'  => 'success',
            'message' => '¡Muchas gracias! Tu experiencia ha sido registrada exitosamente.',
            'id'      => $submission_id,
        ), 200 );
    }

    private function sanitize_array( $data ) {
        $clean = array();
        foreach ( $data as $key => $value ) {
            $sanitized_key = sanitize_key( $key );
            if ( is_array( $value ) ) {
                $clean[ $sanitized_key ] = $this->sanitize_array( $value );
            } else {
                $clean[ $sanitized_key ] = sanitize_textarea_field( (string) $value );
            }
        }
        return $clean;
    }

    private function get_client_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $parts = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ip = trim( $parts[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field( $ip );
    }

    /**
     * Convierte texto a Title Case respetando caracteres multibyte y acentos en español
     */
    public static function format_title_case( $string ) {
        if ( empty( $string ) ) {
            return '';
        }
        $string = trim( (string) $string );
        if ( function_exists( 'mb_convert_case' ) ) {
            return mb_convert_case( $string, MB_CASE_TITLE, 'UTF-8' );
        }
        return ucwords( strtolower( $string ) );
    }

    /**
     * Formatea el teléfono/celular en bloques de 3 dígitos separados por espacio (ej. 333 333 333)
     */
    public static function format_phone( $phone ) {
        if ( empty( $phone ) ) {
            return '';
        }
        $digits = preg_replace( '/\D/', '', (string) $phone );
        if ( empty( $digits ) ) {
            return trim( (string) $phone );
        }
        $chunks = str_split( $digits, 3 );
        return implode( ' ', $chunks );
    }

    /**
     * Formatea la fecha en formato legible DD/MM/YYYY HH:MM:SS
     */
    public static function format_date( $date_string, $include_seconds = true ) {
        if ( empty( $date_string ) ) {
            return '';
        }
        $timestamp = strtotime( (string) $date_string );
        if ( ! $timestamp ) {
            return (string) $date_string;
        }
        $format = $include_seconds ? 'd/m/Y H:i:s' : 'd/m/Y H:i';
        return date( $format, $timestamp );
    }
}

