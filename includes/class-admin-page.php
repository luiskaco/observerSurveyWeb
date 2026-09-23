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
                'enabled'        => isset( $_POST['enabled'] ) ? 1 : 0,
                'spreadsheet_id' => sanitize_text_field( $_POST['spreadsheet_id'] ?? '' ),
                'sheet_name'     => sanitize_text_field( $_POST['sheet_name'] ?? 'Respuestas' ),
                'credentials'    => ! empty( $_POST['credentials'] ) ? wp_unslash( $_POST['credentials'] ) : '',
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
            $this->export_csv();
        }

        // Eliminar Registro
        if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) ) {
            $id = absint( $_GET['id'] );
            check_admin_referer( 'obs_delete_entry_' . $id );

            global $wpdb;
            $table_name = $wpdb->prefix . 'obs_survey_submissions';
            $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );

            wp_safe_redirect( admin_url( 'admin.php?page=observatorio-surveys&deleted=1' ) );
            exit;
        }

        // Sincronizar un registro individual a Google Sheets
        if ( isset( $_GET['action'] ) && 'sync_single' === $_GET['action'] && isset( $_GET['id'] ) ) {
            $id = absint( $_GET['id'] );
            check_admin_referer( 'obs_sync_single_' . $id );

            global $wpdb;
            $table_name = $wpdb->prefix . 'obs_survey_submissions';
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), ARRAY_A );

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
        $sheet_name     = sanitize_text_field( $_POST['sheet_name'] ?? 'Respuestas' );

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

    private function export_csv() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'obs_survey_submissions';
        $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A );

        $filename = 'encuestas_cancer_mama_' . gmdate( 'Y-m-d_His' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM

        $questions_map = Google_Sheets::get_questions_map();

        // Cabeceras base
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

        foreach ( $questions_map as $key => $label ) {
            $headers[] = $label;
        }

        fputcsv( $output, $headers );

        if ( ! empty( $results ) ) {
            foreach ( $results as $row ) {
                $responses = json_decode( $row['responses_json'], true ) ?: array();

                $first_name = Rest_Controller::format_title_case( $row['first_name'] );
                $last_name  = Rest_Controller::format_title_case( $row['last_name'] );
                $phone      = Rest_Controller::format_phone( $row['phone'] );

                $line = array(
                    $row['id'],
                    $row['created_at'],
                    $first_name,
                    $last_name,
                    $row['age'],
                    $phone,
                    $row['email'],
                    $row['region'],
                    $row['is_current_patient'],
                    $row['health_system'],
                    $row['age_diagnosis'],
                    $row['consent_accepted'] ? 'Sí' : 'No',
                );

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
        $table_name = $wpdb->prefix . 'obs_survey_submissions';

        $search = isset( $_GET['s'] ) ? sanitize_text_field( trim( $_GET['s'] ) ) : '';
        $where = "WHERE 1=1";
        if ( ! empty( $search ) ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            $where .= $wpdb->prepare( " AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s OR region LIKE %s)", $like, $like, $like, $like, $like );
        }

        $total_submissions = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where" );
        $unsynced_count    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE synced_to_sheets = 0" );
        $paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
        $limit = 20;
        $offset = ( $paged - 1 ) * $limit;
        $total_pages = ceil( $total_submissions / $limit );

        $submissions = $wpdb->get_results( "SELECT * FROM $table_name $where ORDER BY id DESC LIMIT $limit OFFSET $offset", ARRAY_A );
        $export_url = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=export_csv' ), 'obs_export_csv_action', 'obs_export_nonce' );
        $gsheets_config = Google_Sheets::get_config();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Encuestas: El Viaje de la Paciente con Cáncer de Mama</h1>
            
            <a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary" style="margin-left: 10px; background: #381e72; border-color: #2a1458;">
                <span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: -2px;"></span> Exportar Todo a CSV
            </a>

            <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-survey-gsheets' ) ); ?>" class="button" style="margin-left: 5px;">
                <span class="dashicons dashicons-google" style="vertical-align: middle; margin-top: -2px;"></span> Ajustes Google Sheets
            </a>

            <hr class="wp-header-end">

            <?php if ( isset( $_GET['deleted'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>Registro eliminado correctamente.</p></div>
            <?php endif; ?>
            <?php if ( isset( $_GET['synced'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>Registro sincronizado exitosamente con Google Sheets.</p></div>
            <?php endif; ?>
            <?php if ( isset( $_GET['sync_error'] ) ) : ?>
                <div class="notice notice-error is-dismissible"><p>Error al sincronizar con Google Sheets: <?php echo esc_html( urldecode( $_GET['sync_error'] ) ); ?></p></div>
            <?php endif; ?>

            <div style="margin: 20px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; gap: 15px;">
                    <div style="background: #fff; padding: 14px 22px; border-radius: 8px; border-left: 4px solid #381e72; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Total Encuestas</div>
                        <div style="font-size: 24px; font-weight: bold; color: #381e72; margin-top: 2px;"><?php echo esc_html( $total_submissions ); ?></div>
                    </div>

                    <div style="background: #fff; padding: 14px 22px; border-radius: 8px; border-left: 4px solid <?php echo ! empty( $gsheets_config['enabled'] ) ? '#10b981' : '#f59e0b'; ?>; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Google Sheets Sync</div>
                        <div style="font-size: 15px; font-weight: 600; color: #1e293b; margin-top: 4px;">
                            <?php if ( ! empty( $gsheets_config['enabled'] ) ) : ?>
                                <span style="color: #059669;">● Activo</span>
                                <?php if ( $unsynced_count > 0 ) : ?>
                                    <span style="font-size: 12px; color: #d97706; margin-left: 5px;">(<?php echo esc_html( $unsynced_count ); ?> pendientes)</span>
                                <?php endif; ?>
                            <?php else : ?>
                                <span style="color: #d97706;">● Inactivo</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <form method="get" style="display: flex; gap: 6px;">
                    <input type="hidden" name="page" value="observatorio-surveys">
                    <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por nombre, email, región..." style="min-width: 260px;">
                    <button type="submit" class="button">Buscar</button>
                    <?php if ( ! empty( $search ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys' ) ); ?>" class="button">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>

            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th style="width: 55px;">ID</th>
                        <th style="width: 120px;">Fecha</th>
                        <th>Paciente</th>
                        <th>Contacto</th>
                        <th>Región</th>
                        <th>Sistema Salud</th>
                        <th>Edad Diag.</th>
                        <th style="width: 100px; text-align: center;">Google Sheets</th>
                        <th style="width: 130px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $submissions ) ) : ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 35px; color: #64748b; font-size: 15px;">
                                No se encontraron respuestas registradas.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $submissions as $sub ) : ?>
                            <?php
                            $detail_url = admin_url( 'admin.php?page=observatorio-surveys&view_id=' . $sub['id'] );
                            $delete_url = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=delete&id=' . $sub['id'] ), 'obs_delete_entry_' . $sub['id'] );
                            $sync_url   = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=sync_single&id=' . $sub['id'] ), 'obs_sync_single_' . $sub['id'] );
                            $is_synced  = ! empty( $sub['synced_to_sheets'] );
                            ?>
                            <tr>
                                <td><strong>#<?php echo esc_html( $sub['id'] ); ?></strong></td>
                                <td><?php echo esc_html( date( 'd/m/Y H:i', strtotime( $sub['created_at'] ) ) ); ?></td>
                                <td><strong><?php echo esc_html( $sub['first_name'] . ' ' . $sub['last_name'] ); ?></strong> (<?php echo esc_html( $sub['age'] ); ?> años)</td>
                                <td>
                                    <?php echo esc_html( $sub['email'] ); ?><br>
                                    <small style="color:#64748b;"><?php echo esc_html( $sub['phone'] ); ?></small>
                                </td>
                                <td><?php echo esc_html( $sub['region'] ); ?></td>
                                <td><?php echo esc_html( $sub['health_system'] ); ?></td>
                                <td><?php echo esc_html( $sub['age_diagnosis'] ); ?></td>
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
                        <span class="displaying-num"><?php echo esc_html( $total_submissions ); ?> elementos</span>
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
                                        <strong>Habilitar envío inmediato a Google Sheets al completar cada encuesta</strong>
                                    </label>
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
                                <th scope="row"><label for="sheet_name">Nombre de la Pestaña / Hoja</label></th>
                                <td>
                                    <input type="text" id="sheet_name" name="sheet_name" value="<?php echo esc_attr( $config['sheet_name'] ); ?>" class="regular-text" placeholder="Respuestas">
                                    <p class="description">Nombre de la pestaña dentro del libro (ej. <code>Respuestas</code> o <code>Hoja 1</code>). Si no tiene cabeceras, se crearán automáticamente.</p>
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
                            resultBox.style.background = '#ecfdf5';
                            resultBox.style.color = '#065f46';
                            resultBox.style.border = '1px solid #6ee7b7';
                            resultBox.innerHTML = '<strong>✅ Sincronización completada:</strong> ' + data.data.synced + ' de ' + data.data.total + ' registros pendientes sincronizados.';
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
        $table_name = $wpdb->prefix . 'obs_survey_submissions';
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), ARRAY_A );

        if ( ! $sub ) {
            wp_die( 'Registro no encontrado.' );
        }

        $responses = json_decode( $sub['responses_json'], true ) ?: array();
        $questions_map = Google_Sheets::get_questions_map();
        $sync_url = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=sync_single&id=' . $sub['id'] ), 'obs_sync_single_' . $sub['id'] );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Detalle de Encuesta #<?php echo esc_html( $sub['id'] ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=observatorio-surveys' ) ); ?>" class="button" style="margin-left: 10px;">← Volver al Listado</a>
            <hr class="wp-header-end">

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 20px;">
                <!-- Datos Generales -->
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="margin-top: 0; border-bottom: 2px solid #381e72; padding-bottom: 8px; color: #381e72;">Datos del Paciente</h3>
                    <table class="widefat" style="border: none;">
                        <tbody>
                            <tr><td><strong>Nombres:</strong></td><td><?php echo esc_html( $sub['first_name'] ); ?></td></tr>
                            <tr><td><strong>Apellidos:</strong></td><td><?php echo esc_html( $sub['last_name'] ); ?></td></tr>
                            <tr><td><strong>Edad:</strong></td><td><?php echo esc_html( $sub['age'] ); ?> años</td></tr>
                            <tr><td><strong>Celular:</strong></td><td><?php echo esc_html( $sub['phone'] ); ?></td></tr>
                            <tr><td><strong>Correo:</strong></td><td><?php echo esc_html( $sub['email'] ); ?></td></tr>
                            <tr><td><strong>Región:</strong></td><td><?php echo esc_html( $sub['region'] ); ?></td></tr>
                            <tr><td><strong>Paciente Actual:</strong></td><td><?php echo esc_html( $sub['is_current_patient'] ); ?></td></tr>
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
                                $val = $responses[ $key ] ?? '<em style="color:#94a3b8;">No respondida</em>';
                                if ( is_array( $val ) ) {
                                    $val = implode( ', ', $val );
                                }
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $label ); ?></strong></td>
                                    <td><?php echo esc_html( (string) $val ); ?></td>
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
