<?php
namespace Observatorio\Survey;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin_Page {

    public function register_hooks() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'handle_actions' ) );
        add_action( 'wp_ajax_obs_test_gsheets', array( $this, 'ajax_test_gsheets' ) );
        add_action( 'wp_ajax_obs_sync_pending_gsheets', array( $this, 'ajax_sync_pending_gsheets' ) );
    }

    public function add_menu_page() {
        add_menu_page(
            __( 'Encuestas Observatorio', 'observatorio-survey' ),
            __( 'Encuestas Cáncer', 'observatorio-survey' ),
            'manage_options',
            'observatorio-surveys',
            array( $this, 'render_admin_page' ),
            'dashicons-clipboard',
            25
        );

        add_submenu_page(
            'observatorio-surveys',
            __( 'Respuestas Recibidas', 'observatorio-survey' ),
            __( 'Respuestas', 'observatorio-survey' ),
            'manage_options',
            'observatorio-surveys',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'observatorio-surveys',
            __( 'Configuración Google Sheets', 'observatorio-survey' ),
            __( 'Google Sheets', 'observatorio-survey' ),
            'manage_options',
            'observatorio-survey-gsheets',
            array( $this, 'render_gsheets_page' )
        );
    }

    public function handle_actions() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Guardar configuración de Google Sheets
        if ( isset( $_POST['obs_save_gsheets_settings'] ) ) {
            check_admin_referer( 'obs_save_gsheets_action', 'obs_gsheets_nonce' );

            $data = array(
                'enabled'                => isset( $_POST['enabled'] ) ? 1 : 0,
                'spreadsheet_id'         => sanitize_text_field( $_POST['spreadsheet_id'] ?? '' ),
                'sheet_name'             => sanitize_text_field( $_POST['sheet_name'] ?? 'Completo' ),
                'sheet_name_incomplete'  => sanitize_text_field( $_POST['sheet_name_incomplete'] ?? 'Incompleto' ),
                'sheet_name_short_survey' => sanitize_text_field( $_POST['sheet_name_short_survey'] ?? 'EncuestaCorta' ),
                'credentials'            => ! empty( $_POST['credentials'] ) ? wp_unslash( $_POST['credentials'] ) : '',
            );

            Google_Sheets::save_config( $data );
            wp_safe_redirect( admin_url( 'admin.php?page=observatorio-survey-gsheets&saved=1' ) );
            exit;
        }

        if ( ! isset( $_GET['page'] ) || 'observatorio-surveys' !== $_GET['page'] ) {
            return;
        }

        // Exportar CSV
        if ( isset( $_GET['action'] ) && 'export_csv' === $_GET['action'] ) {
            check_admin_referer( 'obs_export_csv_action', 'obs_export_nonce' );
            $status_filter = isset( $_GET['status_filter'] ) ? sanitize_key( $_GET['status_filter'] ) : 'all';
            $type_filter   = isset( $_GET['type_filter'] ) ? sanitize_key( $_GET['type_filter'] ) : 'all';
            $this->export_csv( $status_filter, $type_filter );
        }

        // Reiniciar contador de la Encuesta Corta
        if ( isset( $_GET['action'] ) && 'reset_short_counter' === $_GET['action'] ) {
            check_admin_referer( 'obs_reset_short_counter_action', 'obs_reset_nonce' );

            global $wpdb;
            $short_table = $wpdb->prefix . 'obs_survey_short_submissions';

            // Eliminar registros de la encuesta corta y reiniciar contador a 1
            $wpdb->query( "DELETE FROM $short_table" );
            $wpdb->query( "ALTER TABLE $short_table AUTO_INCREMENT = 1" );

            wp_safe_redirect( admin_url( 'admin.php?page=observatorio-surveys&short_reset=1' ) );
            exit;
        }

        // Eliminar Registro
        if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) ) {
            $id = absint( $_GET['id'] );
            check_admin_referer( 'obs_delete_entry_' . $id );

            global $wpdb;
            $type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';
            if ( 'short' === $type || 'cancer_mama_corta' === $type ) {
                $wpdb->delete( $wpdb->prefix . 'obs_survey_short_submissions', array( 'id' => $id ), array( '%d' ) );
            } else {
                $wpdb->delete( $wpdb->prefix . 'obs_survey_submissions', array( 'id' => $id ), array( '%d' ) );
            }

            wp_safe_redirect( admin_url( 'admin.php?page=observatorio-surveys&deleted=1' ) );
            exit;
        }

        // Sincronizar un registro individual a Google Sheets
        if ( isset( $_GET['action'] ) && 'sync_single' === $_GET['action'] && isset( $_GET['id'] ) ) {
            $id = absint( $_GET['id'] );
            check_admin_referer( 'obs_sync_single_' . $id );

            global $wpdb;
            $type        = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';
            $orig_table  = $wpdb->prefix . 'obs_survey_submissions';
            $short_table = $wpdb->prefix . 'obs_survey_short_submissions';

            if ( 'short' === $type || 'cancer_mama_corta' === $type ) {
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $short_table WHERE id = %d", $id ), ARRAY_A );
            } else {
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $orig_table WHERE id = %d", $id ), ARRAY_A );
                if ( ! $row ) {
                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $short_table WHERE id = %d", $id ), ARRAY_A );
                }
            }

            if ( $row ) {
                $responses = json_decode( $row['responses_json'], true ) ?: array();
                $res = Google_Sheets::append_submission( $row, $responses, true );
                if ( true === $res ) {
                    wp_safe_redirect( admin_url( 'admin.php?page=observatorio-surveys&synced=1' ) );
                } else {
                    $err = is_wp_error( $res ) ? $res->get_error_message() : 'Error al conectar con Google Sheets';
                    wp_safe_redirect( admin_url( 'admin.php?page=observatorio-surveys&sync_error=' . urlencode( $err ) ) );
                }
            } else {
                wp_safe_redirect( admin_url( 'admin.php?page=observatorio-surveys' ) );
            }
            exit;
        }
    }

    /**
     * Endpoint AJAX para probar conexión con Google Sheets
     */
    public function ajax_test_gsheets() {
        check_ajax_referer( 'obs_gsheets_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permisos insuficientes.' ) );
        }

        $spreadsheet_id = sanitize_text_field( $_POST['spreadsheet_id'] ?? '' );
        $sheet_name     = sanitize_text_field( $_POST['sheet_name'] ?? 'Completo' );

        $result = Google_Sheets::test_connection( $spreadsheet_id, $sheet_name );
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }

    /**
     * Endpoint AJAX para sincronizar todos los registros pendientes
     */
    public function ajax_sync_pending_gsheets() {
        check_ajax_referer( 'obs_gsheets_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permisos insuficientes.' ) );
        }

        $result = Google_Sheets::sync_all_pending( 100 );
        wp_send_json_success( $result );
    }

    private function export_csv( $status_filter = 'all', $type_filter = 'all' ) {
        global $wpdb;
        $orig_table  = $wpdb->prefix . 'obs_survey_submissions';
        $short_table = $wpdb->prefix . 'obs_survey_short_submissions';
        
        $where = "WHERE 1=1";
        if ( 'completed' === $status_filter ) {
            $where .= " AND status = 'completed'";
        } elseif ( 'in_progress' === $status_filter ) {
            $where .= " AND status = 'in_progress'";
        }

        if ( 'cancer_mama_corta' === $type_filter ) {
            $results = $wpdb->get_results( "SELECT *, 'cancer_mama_corta' as survey_type FROM $short_table $where ORDER BY id DESC", ARRAY_A ) ?: array();
        } elseif ( 'cancer_mama_journey' === $type_filter ) {
            $results = $wpdb->get_results( "SELECT *, 'cancer_mama_journey' as survey_type FROM $orig_table $where ORDER BY id DESC", ARRAY_A ) ?: array();
        } else {
            $orig_res  = $wpdb->get_results( "SELECT *, 'cancer_mama_journey' as survey_type FROM $orig_table $where ORDER BY id DESC", ARRAY_A ) ?: array();
            $short_res = $wpdb->get_results( "SELECT *, 'cancer_mama_corta' as survey_type FROM $short_table $where ORDER BY id DESC", ARRAY_A ) ?: array();
            $results   = array_merge( $orig_res, $short_res );
            usort( $results, function( $a, $b ) {
                return strtotime( $b['created_at'] ) - strtotime( $a['created_at'] );
            } );
        }

        $prefix = ( 'cancer_mama_corta' === $type_filter ) ? 'encuesta_corta_' : 'encuestas_cancer_mama_';
        $filename = $prefix . ( 'all' !== $status_filter ? $status_filter . '_' : '' ) . gmdate( 'Y-m-d_His' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM

        $is_short_export = ( 'cancer_mama_corta' === $type_filter );
        $questions_map   = $is_short_export ? Google_Sheets::get_questions_map_short() : Google_Sheets::get_questions_map();

        if ( $is_short_export ) {
            $headers = array(
                'ID',
                'Fecha Registro',
                'Estado',
                'Último Paso',
                'Región Residencia',
                'Condición (Paciente / Cuidador)',
                'Sistema de Salud',
                'Edad al Diagnóstico',
                'Consentimiento',
            );
        } else {
            $headers = array(
                'ID',
                'Fecha Registro',
                'Estado',
                'Último Paso',
                'Tipo Encuesta',
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
        }

        foreach ( $questions_map as $key => $label ) {
            $headers[] = $label;
        }

        fputcsv( $output, $headers );

        if ( ! empty( $results ) ) {
            foreach ( $results as $row ) {
                $responses = json_decode( $row['responses_json'], true ) ?: array();
                $formatted_date = Rest_Controller::format_date( $row['created_at'] );
                $estado_label   = ( 'completed' === $row['status'] ) ? 'Completa' : 'Incompleta';
                $ultimo_paso    = 'Paso ' . ( $row['last_step_reached'] ?? 1 );

                if ( $is_short_export ) {
                    $line = array(
                        $row['id'],
                        $formatted_date,
                        $estado_label,
                        $ultimo_paso,
                        $row['region'],
                        $row['is_current_patient'],
                        $row['health_system'],
                        $row['age_diagnosis'],
                        ! empty( $row['consent_accepted'] ) ? 'Sí' : 'No',
                    );
                } else {
                    $is_row_short = ( 'cancer_mama_corta' === ( $row['survey_type'] ?? '' ) );
                    $first_name   = $is_row_short ? '—' : Rest_Controller::format_title_case( $row['first_name'] );
                    $last_name    = $is_row_short ? '—' : Rest_Controller::format_title_case( $row['last_name'] );
                    $phone        = $is_row_short ? '—' : Rest_Controller::format_phone( $row['phone'] );
                    $email        = $is_row_short ? '—' : $row['email'];
                    $tipo_label   = $is_row_short ? 'Encuesta Corta' : 'Encuesta Original';

                    $line = array(
                        $row['id'],
                        $formatted_date,
                        $estado_label,
                        $ultimo_paso,
                        $tipo_label,
                        $first_name,
                        $last_name,
                        $row['age'],
                        $phone,
                        $email,
                        $row['region'],
                        $row['is_current_patient'],
                        $row['health_system'],
                        $row['age_diagnosis'],
                        ! empty( $row['consent_accepted'] ) ? 'Sí' : 'No',
                    );
                }

                foreach ( $questions_map as $key => $label ) {
                    $val = isset( $responses[ $key ] ) ? $responses[ $key ] : '';
                    if ( is_array( $val ) ) {
                        $val = implode( ', ', $val );
                    }
                    $line[] = $val;
                }

                fputcsv( $output, $line );
            }
        }

        fclose( $output );
        exit;
    }

    public function render_admin_page() {
        if ( isset( $_GET['view_id'] ) ) {
            $this->render_detail_view( absint( $_GET['view_id'] ) );
            return;
        }

        global $wpdb;
        $orig_table  = $wpdb->prefix . 'obs_survey_submissions';
        $short_table = $wpdb->prefix . 'obs_survey_short_submissions';

        $status_filter = isset( $_GET['status_filter'] ) ? sanitize_key( $_GET['status_filter'] ) : 'all';
        $type_filter   = isset( $_GET['type_filter'] ) ? sanitize_key( $_GET['type_filter'] ) : 'all';
        $search        = isset( $_GET['s'] ) ? sanitize_text_field( trim( $_GET['s'] ) ) : '';

        // Conteos por tabla
        $count_orig_all       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $orig_table" );
        $count_orig_completed = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $orig_table WHERE status = 'completed'" );
        $count_orig_in_prog   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $orig_table WHERE status = 'in_progress'" );
        $unsynced_orig        = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $orig_table WHERE synced_to_sheets = 0" );

        $count_short_all       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $short_table" );
        $count_short_completed = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $short_table WHERE status = 'completed'" );
        $count_short_in_prog   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $short_table WHERE status = 'in_progress'" );
        $unsynced_short        = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $short_table WHERE synced_to_sheets = 0" );

        $count_all        = $count_orig_all + $count_short_all;
        $count_completed  = $count_orig_completed + $count_short_completed;
        $count_in_prog    = $count_orig_in_prog + $count_short_in_prog;
        $count_short      = $count_short_all;
        $unsynced_count   = $unsynced_orig + $unsynced_short;

        $paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
        $limit  = 20;
        $offset = ( $paged - 1 ) * $limit;

        if ( 'cancer_mama_corta' === $type_filter ) {
            $where = "WHERE 1=1";
            if ( 'completed' === $status_filter ) {
                $where .= " AND status = 'completed'";
            } elseif ( 'in_progress' === $status_filter ) {
                $where .= " AND status = 'in_progress'";
            }
            if ( ! empty( $search ) ) {
                $like = '%' . $wpdb->esc_like( $search ) . '%';
                $where .= $wpdb->prepare( " AND (region LIKE %s OR health_system LIKE %s OR is_current_patient LIKE %s)", $like, $like, $like );
            }
            $total_filtered = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $short_table $where" );
            $submissions    = $wpdb->get_results( "SELECT *, 'cancer_mama_corta' as survey_type, 'short' as table_source FROM $short_table $where ORDER BY id DESC LIMIT $limit OFFSET $offset", ARRAY_A );
        } elseif ( 'cancer_mama_journey' === $type_filter ) {
            $where = "WHERE 1=1";
            if ( 'completed' === $status_filter ) {
                $where .= " AND status = 'completed'";
            } elseif ( 'in_progress' === $status_filter ) {
                $where .= " AND status = 'in_progress'";
            }
            if ( ! empty( $search ) ) {
                $like = '%' . $wpdb->esc_like( $search ) . '%';
                $where .= $wpdb->prepare( " AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s OR region LIKE %s)", $like, $like, $like, $like, $like );
            }
            $total_filtered = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $orig_table $where" );
            $submissions    = $wpdb->get_results( "SELECT *, 'cancer_mama_journey' as survey_type, 'orig' as table_source FROM $orig_table $where ORDER BY id DESC LIMIT $limit OFFSET $offset", ARRAY_A );
        } else {
            // 'all': Combinar ambas tablas
            $orig_where  = "WHERE 1=1";
            $short_where = "WHERE 1=1";
            if ( 'completed' === $status_filter ) {
                $orig_where  .= " AND status = 'completed'";
                $short_where .= " AND status = 'completed'";
            } elseif ( 'in_progress' === $status_filter ) {
                $orig_where  .= " AND status = 'in_progress'";
                $short_where .= " AND status = 'in_progress'";
            }
            if ( ! empty( $search ) ) {
                $like = '%' . $wpdb->esc_like( $search ) . '%';
                $orig_where  .= $wpdb->prepare( " AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s OR region LIKE %s)", $like, $like, $like, $like, $like );
                $short_where .= $wpdb->prepare( " AND (region LIKE %s OR health_system LIKE %s OR is_current_patient LIKE %s)", $like, $like, $like );
            }

            $count_filtered_orig  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $orig_table $orig_where" );
            $count_filtered_short = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $short_table $short_where" );
            $total_filtered       = $count_filtered_orig + $count_filtered_short;

            $union_sql = "
                SELECT * FROM (
                    SELECT id, 'cancer_mama_journey' as survey_type, status, last_step_reached, first_name, last_name, age, phone, email, region, health_system, synced_to_sheets, synced_at, created_at, 'orig' as table_source FROM $orig_table $orig_where
                    UNION ALL
                    SELECT id, 'cancer_mama_corta' as survey_type, status, last_step_reached, '' as first_name, '' as last_name, 0 as age, '' as phone, '' as email, region, health_system, synced_to_sheets, synced_at, created_at, 'short' as table_source FROM $short_table $short_where
                ) AS combined_surveys
                ORDER BY created_at DESC LIMIT $limit OFFSET $offset
            ";
            $submissions = $wpdb->get_results( $union_sql, ARRAY_A ) ?: array();

            // Salvaguarda para hosting: Si la consulta UNION fallase por alguna restricción de base de datos
            if ( empty( $submissions ) && $total_filtered > 0 ) {
                $fetch_limit = min( 200, $limit * 5 );
                $orig_rows   = $wpdb->get_results( "SELECT id, 'cancer_mama_journey' as survey_type, status, last_step_reached, first_name, last_name, age, phone, email, region, health_system, synced_to_sheets, synced_at, created_at, 'orig' as table_source FROM $orig_table $orig_where ORDER BY created_at DESC LIMIT $fetch_limit", ARRAY_A ) ?: array();
                $short_rows  = $wpdb->get_results( "SELECT id, 'cancer_mama_corta' as survey_type, status, last_step_reached, '' as first_name, '' as last_name, 0 as age, '' as phone, '' as email, region, health_system, synced_to_sheets, synced_at, created_at, 'short' as table_source FROM $short_table $short_where ORDER BY created_at DESC LIMIT $fetch_limit", ARRAY_A ) ?: array();
                $combined    = array_merge( $orig_rows, $short_rows );
                usort( $combined, function( $a, $b ) {
                    return strtotime( $b['created_at'] ) - strtotime( $a['created_at'] );
                } );
                $submissions = array_slice( $combined, $offset, $limit );
            }
        }

        $total_pages = ceil( $total_filtered / $limit );
        
        $export_url = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=export_csv&status_filter=' . $status_filter . '&type_filter=' . $type_filter ), 'obs_export_csv_action', 'obs_export_nonce' );
        $gsheets_config = Google_Sheets::get_config();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Encuestas: La Ruta de la Paciente con Cáncer de Mama</h1>
            
            <a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary" style="margin-left: 10px; background: #381e72; border-color: #2a1458;">
                <span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: -2px;"></span> Exportar CSV (<?php echo esc_html( 'completed' === $status_filter ? 'Completas' : ( 'in_progress' === $status_filter ? 'Incompletas' : 'Todo' ) ); ?>)
            </a>

            <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-survey-gsheets' ) ); ?>" class="button" style="margin-left: 5px;">
                <span class="dashicons dashicons-google" style="vertical-align: middle; margin-top: -2px;"></span> Ajustes Google Sheets
            </a>

            <?php
            $reset_short_url = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=reset_short_counter' ), 'obs_reset_short_counter_action', 'obs_reset_nonce' );
            ?>
            <a href="<?php echo esc_url( $reset_short_url ); ?>" class="button" style="margin-left: 5px; color: #b91c1c; border-color: #fca5a5;" onclick="return confirm('⚠️ ¿Estás seguro de reiniciar el contador de la Encuesta Corta?\n\nEsta acción eliminará las respuestas registradas en la Encuesta Corta y reiniciará su contador desde el #1.\n\n(Las respuestas de la encuesta original no se verán afectadas).');">
                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px;"></span> Reiniciar Contador Corta (a #1)
            </a>

            <hr class="wp-header-end">

            <?php if ( isset( $_GET['short_reset'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p><strong>✓ El contador de la Encuesta Corta ha sido reiniciado a #1 exitosamente.</strong> Respuestas de prueba eliminadas.</p></div>
            <?php endif; ?>
            <?php if ( isset( $_GET['deleted'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>Registro eliminado correctamente.</p></div>
            <?php endif; ?>
            <?php if ( isset( $_GET['synced'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>Registro sincronizado exitosamente con Google Sheets.</p></div>
            <?php endif; ?>
            <?php if ( isset( $_GET['sync_error'] ) ) : ?>
                <div class="notice notice-error is-dismissible"><p>Error al sincronizar con Google Sheets: <?php echo esc_html( urldecode( $_GET['sync_error'] ) ); ?></p></div>
            <?php endif; ?>

            <!-- Tarjetas de métricas -->
            <div style="margin: 20px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div style="background: #fff; padding: 14px 20px; border-radius: 8px; border-left: 4px solid #381e72; box-shadow: 0 1px 3px rgba(0,0,0,0.06); min-width: 120px;">
                        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Total Registros</div>
                        <div style="font-size: 24px; font-weight: bold; color: #381e72; margin-top: 2px;"><?php echo esc_html( $count_all ); ?></div>
                    </div>

                    <div style="background: #fff; padding: 14px 20px; border-radius: 8px; border-left: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.06); min-width: 120px;">
                        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Completadas</div>
                        <div style="font-size: 24px; font-weight: bold; color: #059669; margin-top: 2px;"><?php echo esc_html( $count_completed ); ?></div>
                    </div>

                    <div style="background: #fff; padding: 14px 20px; border-radius: 8px; border-left: 4px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.06); min-width: 120px;">
                        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Incompletas (Paso 1-5)</div>
                        <div style="font-size: 24px; font-weight: bold; color: #d97706; margin-top: 2px;"><?php echo esc_html( $count_in_prog ); ?></div>
                    </div>

                    <div style="background: #fff; padding: 14px 20px; border-radius: 8px; border-left: 4px solid <?php echo ! empty( $gsheets_config['enabled'] ) ? '#10b981' : '#64748b'; ?>; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Google Sheets Sync</div>
                        <div style="font-size: 15px; font-weight: 600; color: #1e293b; margin-top: 4px;">
                            <?php if ( ! empty( $gsheets_config['enabled'] ) ) : ?>
                                <span style="color: #059669;">● Activo</span>
                                <?php if ( $unsynced_count > 0 ) : ?>
                                    <span style="font-size: 12px; color: #d97706; margin-left: 5px;">(<?php echo esc_html( $unsynced_count ); ?> pendientes)</span>
                                <?php endif; ?>
                            <?php else : ?>
                                <span style="color: #64748b;">● Inactivo</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <form method="get" style="display: flex; gap: 6px;">
                    <input type="hidden" name="page" value="observatorio-surveys">
                    <?php if ( 'all' !== $status_filter ) : ?>
                        <input type="hidden" name="status_filter" value="<?php echo esc_attr( $status_filter ); ?>">
                    <?php endif; ?>
                    <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por nombre, email, región..." style="min-width: 260px;">
                    <button type="submit" class="button">Buscar</button>
                    <?php if ( ! empty( $search ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys' . ( 'all' !== $status_filter ? '&status_filter=' . $status_filter : '' ) ) ); ?>" class="button">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Filtros por Pestañas de Estado y Tipo -->
            <ul class="subsubsub" style="margin-bottom: 12px;">
                <li>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys' ) ); ?>" class="<?php echo ( 'all' === $status_filter && 'all' === $type_filter ) ? 'current' : ''; ?>">
                        Todas <span class="count">(<?php echo esc_html( $count_all ); ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys&type_filter=cancer_mama_journey' . ( 'all' !== $status_filter ? '&status_filter=' . $status_filter : '' ) ) ); ?>" class="<?php echo 'cancer_mama_journey' === $type_filter ? 'current' : ''; ?>">
                        Encuesta Original <span class="count">(<?php echo esc_html( $count_orig_all ); ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys&type_filter=cancer_mama_corta' . ( 'all' !== $status_filter ? '&status_filter=' . $status_filter : '' ) ) ); ?>" class="<?php echo 'cancer_mama_corta' === $type_filter ? 'current' : ''; ?>">
                        Solo Encuesta Corta <span class="count">(<?php echo esc_html( $count_short ); ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys&status_filter=completed' . ( 'all' !== $type_filter ? '&type_filter=' . $type_filter : '' ) ) ); ?>" class="<?php echo 'completed' === $status_filter ? 'current' : ''; ?>">
                        Completadas <span class="count">(<?php echo esc_html( $count_completed ); ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys&status_filter=in_progress' . ( 'all' !== $type_filter ? '&type_filter=' . $type_filter : '' ) ) ); ?>" class="<?php echo 'in_progress' === $status_filter ? 'current' : ''; ?>">
                        Incompletas / En Progreso <span class="count">(<?php echo esc_html( $count_in_prog ); ?>)</span>
                    </a>
                </li>
            </ul>

            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 115px;">Fecha</th>
                        <th>Paciente</th>
                        <th>Contacto</th>
                        <th>Región</th>
                        <th style="width: 130px; text-align: center;">Estado</th>
                        <th>Sistema Salud</th>
                        <th style="width: 100px; text-align: center;">Google Sheets</th>
                        <th style="width: 120px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $submissions ) ) : ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 35px; color: #64748b; font-size: 15px;">
                                No se encontraron respuestas con los criterios seleccionados.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $submissions as $sub ) : ?>
                            <?php
                            $is_short_row = ( 'cancer_mama_corta' === ( $sub['survey_type'] ?? '' ) );
                            $src          = $sub['table_source'] ?? ( $is_short_row ? 'short' : 'orig' );
                            $detail_url   = admin_url( 'admin.php?page=observatorio-surveys&view_id=' . $sub['id'] . '&type=' . $src );
                            $delete_url   = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=delete&id=' . $sub['id'] . '&type=' . $src ), 'obs_delete_entry_' . $sub['id'] );
                            $sync_url     = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=sync_single&id=' . $sub['id'] . '&type=' . $src ), 'obs_sync_single_' . $sub['id'] );
                            $is_synced    = ! empty( $sub['synced_to_sheets'] );
                            $is_completed = ( 'completed' === ( $sub['status'] ?? 'completed' ) );
                            $last_step    = (int) ( $sub['last_step_reached'] ?? 1 );
                            ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo esc_html( $sub['id'] ); ?></strong>
                                    <?php if ( 'all' === $type_filter ) : ?>
                                        <br><span style="font-size: 10px; font-weight: 600; color: <?php echo $is_short_row ? '#381e72' : '#64748b'; ?>;"><?php echo $is_short_row ? 'Corta' : 'Original'; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( date( 'd/m/Y H:i', strtotime( $sub['created_at'] ) ) ); ?></td>
                                <td>
                                    <?php if ( $is_short_row ) : ?>
                                        <strong style="color: #381e72;">Encuesta Corta</strong>
                                        <span style="display: block; font-size: 11px; color: #64748b;">(Participante Anónima)</span>
                                    <?php else : ?>
                                        <strong><?php echo esc_html( $sub['first_name'] . ' ' . $sub['last_name'] ); ?></strong>
                                        <?php if ( ! empty( $sub['age'] ) ) : ?>
                                            <small style="color:#64748b;">(<?php echo esc_html( $sub['age'] ); ?> años)</small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $is_short_row ) : ?>
                                        <small style="color: #94a3b8;">No aplica (Anónima)</small>
                                    <?php else : ?>
                                        <?php echo esc_html( $sub['email'] ); ?><br>
                                        <small style="color:#64748b;"><?php echo esc_html( $sub['phone'] ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $sub['region'] ?: '—' ); ?></td>
                                <td style="text-align: center;">
                                    <?php if ( $is_completed ) : ?>
                                        <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 2px 8px; border-radius: 12px; font-weight: 600; font-size: 11px;">
                                            ✓ Completa
                                        </span>
                                    <?php else : ?>
                                        <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 12px; font-weight: 600; font-size: 11px;" title="Abandonada / En progreso en Paso <?php echo esc_attr( $last_step ); ?>">
                                            ⏳ Paso <?php echo esc_html( $last_step ); ?> / <?php echo $is_short_row ? '4' : '6'; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $sub['health_system'] ?: '—' ); ?></td>
                                <td style="text-align: center;">
                                    <?php if ( $is_synced ) : ?>
                                        <span class="dashicons dashicons-yes-alt" style="color: #10b981;" title="Sincronizado a Google Sheets (<?php echo esc_attr( $sub['synced_at'] ?? '' ); ?>)"></span>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url( $sync_url ); ?>" class="button button-small" title="Sincronizar ahora con Google Sheets" style="font-size: 11px; padding: 0 6px;">
                                            <span class="dashicons dashicons-update" style="font-size: 14px; vertical-align: middle; margin-top: -2px;"></span> Enviar
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="<?php echo esc_url( $detail_url ); ?>" class="button button-small" title="Ver Detalles">Ver</a>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('¿Estás seguro de eliminar esta respuesta?');" style="color: #b91c1c; margin-left: 4px;">Borrar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo esc_html( $total_filtered ); ?> elementos</span>
                        <span class="pagination-links">
                            <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                                <?php if ( $i === $paged ) : ?>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true"><?php echo esc_html( $i ); ?></span>
                                <?php else : ?>
                                    <a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>"><?php echo esc_html( $i ); ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Renderiza la página de configuración de Google Sheets
     */
    public function render_gsheets_page() {
        $config = Google_Sheets::get_config();
        $creds  = Google_Sheets::get_credentials();
        $client_email = $creds['client_email'] ?? 'No configurado';
        $ajax_nonce = wp_create_nonce( 'obs_gsheets_ajax_nonce' );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Conexión con Google Sheets</h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys' ) ); ?>" class="button" style="margin-left: 10px;">← Volver a Encuestas</a>
            <hr class="wp-header-end">

            <?php if ( isset( $_GET['saved'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>Ajustes de Google Sheets guardados correctamente.</p></div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px; margin-top: 20px;">
                <!-- Formulario de Configuración -->
                <div style="background: #fff; padding: 24px 28px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <h2 style="margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; color: #1e293b;">
                        Parámetros de Integración
                    </h2>

                    <form method="post" action="">
                        <?php wp_nonce_field( 'obs_save_gsheets_action', 'obs_gsheets_nonce' ); ?>
                        <input type="hidden" name="obs_save_gsheets_settings" value="1">

                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row">Sincronización Automática</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $config['enabled'] ) ); ?>>
                                        <strong>Habilitar envío automático a Google Sheets en tiempo real</strong>
                                    </label>
                                    <p class="description">Guarda las encuestas incompletas en la pestaña <code>Incompleto</code> y las finalizadas en <code>Completo</code>.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="spreadsheet_id">ID o URL de Google Sheet</label></th>
                                <td>
                                    <input type="text" id="spreadsheet_id" name="spreadsheet_id" value="<?php echo esc_attr( $config['spreadsheet_id'] ); ?>" class="regular-text" style="width: 100%;" placeholder="https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit">
                                    <p class="description">Puedes pegar el ID directo o la URL completa de tu Google Sheet.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="sheet_name">Pestaña para Encuestas Completadas</label></th>
                                <td>
                                    <input type="text" id="sheet_name" name="sheet_name" value="<?php echo esc_attr( $config['sheet_name'] ); ?>" class="regular-text" placeholder="Completo">
                                    <p class="description">Nombre de la pestaña para respuestas enviadas al final del Paso 6 (por defecto: <code>Completo</code>).</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="sheet_name_incomplete">Pestaña para Encuestas Incompletas</label></th>
                                <td>
                                    <input type="text" id="sheet_name_incomplete" name="sheet_name_incomplete" value="<?php echo esc_attr( $config['sheet_name_incomplete'] ?? 'Incompleto' ); ?>" class="regular-text" placeholder="Incompleto">
                                    <p class="description">Nombre de la pestaña para respuestas parciales guardadas paso a paso de la encuesta original (por defecto: <code>Incompleto</code>).</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="sheet_name_short_survey">Pestaña para Encuesta Corta (<code>[short_survey]</code>)</label></th>
                                <td>
                                    <input type="text" id="sheet_name_short_survey" name="sheet_name_short_survey" value="<?php echo esc_attr( $config['sheet_name_short_survey'] ?? 'EncuestaCorta' ); ?>" class="regular-text" placeholder="EncuestaCorta">
                                    <p class="description">Nombre de la pestaña única en Google Sheets para la encuesta corta (por defecto: <code>EncuestaCorta</code>). Utiliza columna de estado (<code>Incompleto</code> / <code>Completo</code>).</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="credentials">Credenciales JSON Personalizadas (Opcional)</label></th>
                                <td>
                                    <textarea id="credentials" name="credentials" rows="4" style="width: 100%; font-family: monospace; font-size: 11px;" placeholder="Pega el contenido del archivo .json solo si deseas sobrescribir el archivo por defecto."><?php echo esc_textarea( $config['credentials'] ); ?></textarea>
                                    <p class="description">
                                        <?php if ( ! empty( $creds ) ) : ?>
                                            <span style="color: #059669; font-weight: 600;">✓ Credenciales activas detectadas:</span> <code><?php echo esc_html( $client_email ); ?></code>
                                        <?php else : ?>
                                            <span style="color: #dc2626; font-weight: 600;">✕ No se detectaron credenciales. Pega el JSON del Service Account arriba.</span>
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="display: flex; gap: 10px; align-items: center;">
                            <button type="submit" class="button button-primary" style="background: #381e72; border-color: #2a1458; padding: 4px 18px;">
                                Guardar Ajustes
                            </button>
                            <button type="button" id="btn-test-connection" class="button button-secondary">
                                <span class="dashicons dashicons-admin-plugins" style="vertical-align: middle; margin-top: -2px;"></span> Probar Conexión
                            </button>
                            <button type="button" id="btn-sync-pending" class="button button-secondary">
                                <span class="dashicons dashicons-update" style="vertical-align: middle; margin-top: -2px;"></span> Sincronizar Pendientes
                            </button>
                        </p>
                    </form>

                    <!-- Caja de Resultados AJAX -->
                    <div id="test-result-box" style="display: none; margin-top: 15px; padding: 14px 18px; border-radius: 6px; font-size: 13px;"></div>
                </div>

                <!-- Tarjeta de Instrucciones y Service Account -->
                <div>
                    <div style="background: #fff; padding: 22px 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-top: 4px solid #381e72;">
                        <h3 style="margin-top: 0; color: #1e293b;">Paso Obligatorio para Conectar</h3>
                        <p style="color: #475569; font-size: 13px; line-height: 1.5;">
                            Para que el plugin pueda escribir en tu Google Sheet, debes <strong>compartir la hoja de cálculo</strong> con la cuenta de servicio con rol de <strong>Editor</strong>.
                        </p>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; margin: 15px 0;">
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">Correo de la Cuenta de Servicio:</label>
                            <input type="text" id="service-account-email" readonly value="<?php echo esc_attr( $client_email ); ?>" style="width: 100%; background: #fff; font-family: monospace; font-size: 12px;" onclick="this.select();">
                            <button type="button" class="button button-small" style="margin-top: 8px; width: 100%;" onclick="navigator.clipboard.writeText(document.getElementById('service-account-email').value); alert('¡Correo copiado al portapapeles!');">
                                📋 Copiar Correo para Compartir en Google Sheets
                            </button>
                        </div>

                        <div style="font-size: 12px; color: #64748b; line-height: 1.6;">
                            <strong>Instrucciones rápidas:</strong>
                            <ol style="margin: 6px 0 0 16px; padding: 0;">
                                <li>Abre tu hoja en Google Sheets.</li>
                                <li>Clic en <strong>Compartir</strong> (botón verde/azul arriba a la derecha).</li>
                                <li>Pega el correo anterior.</li>
                                <li>Asegúrate que el rol sea <strong>Editor</strong> y desmarca "Notificar a los usuarios".</li>
                                <li>Guarda y haz clic en <strong>Probar Conexión</strong>.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnTest = document.getElementById('btn-test-connection');
            const btnSync = document.getElementById('btn-sync-pending');
            const resultBox = document.getElementById('test-result-box');

            if (btnTest) {
                btnTest.addEventListener('click', function() {
                    const sheetId = document.getElementById('spreadsheet_id').value.trim();
                    const sheetName = document.getElementById('sheet_name').value.trim();

                    if (!sheetId) {
                        alert('Por favor ingresa primero el ID o URL del Google Sheet.');
                        return;
                    }

                    btnTest.disabled = true;
                    btnTest.textContent = 'Probando conexión...';
                    resultBox.style.display = 'block';
                    resultBox.style.background = '#f1f5f9';
                    resultBox.style.color = '#334155';
                    resultBox.style.border = '1px solid #cbd5e1';
                    resultBox.innerHTML = '⏳ Conectando con Google Sheets API...';

                    const formData = new FormData();
                    formData.append('action', 'obs_test_gsheets');
                    formData.append('nonce', '<?php echo esc_js( $ajax_nonce ); ?>');
                    formData.append('spreadsheet_id', sheetId);
                    formData.append('sheet_name', sheetName);

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        btnTest.disabled = false;
                        btnTest.innerHTML = '<span class="dashicons dashicons-admin-plugins" style="vertical-align: middle; margin-top: -2px;"></span> Probar Conexión';

                        if (data.success) {
                            resultBox.style.background = '#ecfdf5';
                            resultBox.style.color = '#065f46';
                            resultBox.style.border = '1px solid #6ee7b7';
                            resultBox.innerHTML = '<strong>✅ ' + data.data.message + '</strong><br>' +
                                '• <strong>Título del libro:</strong> ' + data.data.spreadsheet_title + '<br>' +
                                '• <strong>Pestaña destino:</strong> ' + data.data.target_sheet + ' ' + (data.data.sheet_found ? '(Encontrada)' : '(Se creará al insertar)') + '<br>' +
                                '• <strong>Pestañas existentes:</strong> ' + data.data.sheets.join(', ');
                        } else {
                            resultBox.style.background = '#fef2f2';
                            resultBox.style.color = '#991b1b';
                            resultBox.style.border = '1px solid #fca5a5';
                            resultBox.innerHTML = '<strong>❌ ' + (data.data.message || 'Error de conexión') + '</strong><br>' +
                                (data.data.email ? '<small>Service Account: ' + data.data.email + '</small>' : '');
                        }
                    })
                    .catch(err => {
                        btnTest.disabled = false;
                        btnTest.innerHTML = '<span class="dashicons dashicons-admin-plugins" style="vertical-align: middle; margin-top: -2px;"></span> Probar Conexión';
                        resultBox.style.background = '#fef2f2';
                        resultBox.style.color = '#991b1b';
                        resultBox.style.border = '1px solid #fca5a5';
                        resultBox.innerHTML = '<strong>❌ Error en la solicitud AJAX:</strong> ' + err.message;
                    });
                });
            }

            if (btnSync) {
                btnSync.addEventListener('click', function() {
                    btnSync.disabled = true;
                    btnSync.textContent = 'Sincronizando...';
                    resultBox.style.display = 'block';
                    resultBox.style.background = '#f1f5f9';
                    resultBox.style.color = '#334155';
                    resultBox.style.border = '1px solid #cbd5e1';
                    resultBox.innerHTML = '⏳ Enviando registros pendientes a Google Sheets...';

                    const formData = new FormData();
                    formData.append('action', 'obs_sync_pending_gsheets');
                    formData.append('nonce', '<?php echo esc_js( $ajax_nonce ); ?>');

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        btnSync.disabled = false;
                        btnSync.innerHTML = '<span class="dashicons dashicons-update" style="vertical-align: middle; margin-top: -2px;"></span> Sincronizar Pendientes';

                        if (data.success) {
                            if (data.data.synced === data.data.total && data.data.total > 0) {
                                resultBox.style.background = '#ecfdf5';
                                resultBox.style.color = '#065f46';
                                resultBox.style.border = '1px solid #6ee7b7';
                                resultBox.innerHTML = '<strong>✅ Sincronización completada:</strong> ' + data.data.synced + ' de ' + data.data.total + ' registros pendientes sincronizados con Google Sheets.';
                            } else if (data.data.total === 0) {
                                resultBox.style.background = '#ecfdf5';
                                resultBox.style.color = '#065f46';
                                resultBox.style.border = '1px solid #6ee7b7';
                                resultBox.innerHTML = '<strong>✅ Todo al día:</strong> No hay registros pendientes de sincronización.';
                            } else {
                                resultBox.style.background = '#fffbeb';
                                resultBox.style.color = '#92400e';
                                resultBox.style.border = '1px solid #fcd34d';
                                let errMsg = (data.data.errors && data.data.errors.length) ? '<br><small style="color:#b91c1c;">Detalle: ' + data.data.errors.join(', ') + '</small>' : '';
                                resultBox.innerHTML = '<strong>⚠️ Sincronización parcial:</strong> Se sincronizaron ' + data.data.synced + ' de ' + data.data.total + ' registros.' + errMsg;
                            }
                        } else {
                            resultBox.style.background = '#fef2f2';
                            resultBox.style.color = '#991b1b';
                            resultBox.style.border = '1px solid #fca5a5';
                            resultBox.innerHTML = '<strong>❌ Error:</strong> ' + (data.data.message || 'No se pudo completar la sincronización.');
                        }
                    })
                    .catch(err => {
                        btnSync.disabled = false;
                        btnSync.innerHTML = '<span class="dashicons dashicons-update" style="vertical-align: middle; margin-top: -2px;"></span> Sincronizar Pendientes';
                        resultBox.style.background = '#fef2f2';
                        resultBox.style.color = '#991b1b';
                        resultBox.style.border = '1px solid #fca5a5';
                        resultBox.innerHTML = '<strong>❌ Error de red:</strong> ' + err.message;
                    });
                });
            }
        });
        </script>
        <?php
    }

    private function render_detail_view( $id ) {
        global $wpdb;
        $type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';
        $sub  = null;

        if ( 'short' === $type || 'cancer_mama_corta' === $type ) {
            $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}obs_survey_short_submissions WHERE id = %d", $id ), ARRAY_A );
        } elseif ( 'orig' === $type ) {
            $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}obs_survey_submissions WHERE id = %d", $id ), ARRAY_A );
        }

        if ( ! $sub ) {
            $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}obs_survey_short_submissions WHERE id = %d", $id ), ARRAY_A );
        }
        if ( ! $sub ) {
            $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}obs_survey_submissions WHERE id = %d", $id ), ARRAY_A );
        }

        if ( ! $sub ) {
            wp_die( 'Registro no encontrado.' );
        }

        $responses     = json_decode( $sub['responses_json'], true ) ?: array();
        $is_short      = ( 'cancer_mama_corta' === ( $sub['survey_type'] ?? '' ) );
        $questions_map = $is_short ? Google_Sheets::get_questions_map_short() : Google_Sheets::get_questions_map();
        $src_param     = $is_short ? 'short' : 'orig';
        $sync_url      = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=sync_single&id=' . $sub['id'] . '&type=' . $src_param ), 'obs_sync_single_' . $sub['id'] );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Detalle de Encuesta #<?php echo esc_html( $sub['id'] ); ?> <?php if ( $is_short ) : ?><span style="background: #381e72; color: #fff; font-size: 13px; padding: 3px 10px; border-radius: 12px; font-weight: normal; vertical-align: middle;">Encuesta Corta</span><?php endif; ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys' ) ); ?>" class="button" style="margin-left: 10px;">← Volver al Listado</a>
            <hr class="wp-header-end">

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 20px;">
                <!-- Datos Generales -->
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="margin-top: 0; border-bottom: 2px solid #381e72; padding-bottom: 8px; color: #381e72;">Datos del Perfil</h3>
                    <table class="widefat" style="border: none;">
                        <tbody>
                            <tr>
                                <td><strong>Tipo:</strong></td>
                                <td><?php echo esc_html( $is_short ? 'Encuesta Corta ([short_survey])' : 'Encuesta Completa' ); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Estado Encuesta:</strong></td>
                                <td>
                                    <?php if ( 'completed' === ( $sub['status'] ?? 'completed' ) ) : ?>
                                        <span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 2px 8px; border-radius: 12px; font-weight: 600; font-size: 11px;">
                                            ✓ Completa
                                        </span>
                                    <?php else : ?>
                                        <span style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 12px; font-weight: 600; font-size: 11px;">
                                            ⏳ Incompleta (Último paso alcanzado: Paso <?php echo esc_html( $sub['last_step_reached'] ?? 1 ); ?> / 6)
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ( ! $is_short ) : ?>
                            <tr><td><strong>Nombres:</strong></td><td><?php echo esc_html( $sub['first_name'] ); ?></td></tr>
                            <tr><td><strong>Apellidos:</strong></td><td><?php echo esc_html( $sub['last_name'] ); ?></td></tr>
                            <tr><td><strong>Edad:</strong></td><td><?php echo esc_html( $sub['age'] ); ?> años</td></tr>
                            <tr><td><strong>Celular:</strong></td><td><?php echo esc_html( $sub['phone'] ); ?></td></tr>
                            <tr><td><strong>Correo:</strong></td><td><?php echo esc_html( $sub['email'] ); ?></td></tr>
                            <?php else : ?>
                            <tr><td><strong>Identificación:</strong></td><td><em style="color: #64748b;">Anónima (Sin datos de contacto personal)</em></td></tr>
                            <?php endif; ?>
                            <tr><td><strong>Región:</strong></td><td><?php echo esc_html( $sub['region'] ); ?></td></tr>
                            <tr><td><strong>Paciente o Cuidador:</strong></td><td><?php echo esc_html( $sub['is_current_patient'] ); ?></td></tr>
                            <tr><td><strong>Sistema Salud:</strong></td><td><?php echo esc_html( $sub['health_system'] ); ?></td></tr>
                            <tr><td><strong>Edad Diag.:</strong></td><td><?php echo esc_html( $sub['age_diagnosis'] ); ?></td></tr>
                            <tr><td><strong>Fecha:</strong></td><td><?php echo esc_html( $sub['created_at'] ); ?></td></tr>
                            <tr>
                                <td><strong>Google Sheets:</strong></td>
                                <td>
                                    <?php if ( ! empty( $sub['synced_to_sheets'] ) ) : ?>
                                        <span style="color: #10b981; font-weight: 600;">✓ Sincronizado</span> (<?php echo esc_html( $sub['synced_at'] ); ?>)
                                    <?php else : ?>
                                        <a href="<?php echo esc_url( $sync_url ); ?>" class="button button-small">Sincronizar a Google Sheets</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Respuestas del Cuestionario -->
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="margin-top: 0; border-bottom: 2px solid #d81b60; padding-bottom: 8px; color: #d81b60;">Respuestas del Cuestionario</h3>
                    
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Pregunta</th>
                                <th>Respuesta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $questions_map as $key => $label ) : ?>
                                <?php
                                $has_val = isset( $responses[ $key ] ) && '' !== trim( (string) ( is_array( $responses[ $key ] ) ? implode( '', $responses[ $key ] ) : $responses[ $key ] ) );
                                $val     = $has_val ? $responses[ $key ] : '';
                                if ( is_array( $val ) ) {
                                    $val = implode( ', ', $val );
                                }
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $label ); ?></strong></td>
                                    <td>
                                        <?php if ( $has_val ) : ?>
                                            <?php echo esc_html( (string) $val ); ?>
                                        <?php else : ?>
                                            <em style="color: #94a3b8;">No respondida</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}
