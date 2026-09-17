<?php
namespace Observatorio\Survey;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin_Page {

    public function register_hooks() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'handle_actions' ) );
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
    }

    public function handle_actions() {
        if ( ! isset( $_GET['page'] ) || 'observatorio-surveys' !== $_GET['page'] ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
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
    }

    private function export_csv() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'obs_survey_submissions';
        $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A );

        $filename = 'encuestas_cancer_mama_' . date( 'Y-m-d_His' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM

        $questions_map = $this->get_questions_map();

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
                $line = array(
                    $row['id'],
                    $row['created_at'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['age'],
                    $row['phone'],
                    $row['email'],
                    $row['region'],
                    $row['is_current_patient'],
                    $row['health_system'],
                    $row['age_diagnosis'],
                    $row['consent_accepted'] ? 'Sí' : 'No',
                );

                foreach ( $questions_map as $key => $label ) {
                    $line[] = isset( $responses[ $key ] ) ? $responses[ $key ] : '';
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
        $paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
        $limit = 20;
        $offset = ( $paged - 1 ) * $limit;
        $total_pages = ceil( $total_submissions / $limit );

        $submissions = $wpdb->get_results( "SELECT * FROM $table_name $where ORDER BY id DESC LIMIT $limit OFFSET $offset", ARRAY_A );
        $export_url = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=export_csv' ), 'obs_export_csv_action', 'obs_export_nonce' );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Encuestas: El Viaje de la Paciente con Cáncer de Mama</h1>
            <a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary" style="margin-left: 10px; background: #381e72; border-color: #2a1458;">
                <span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: -2px;"></span> Exportar Todo a CSV (Excel)
            </a>
            <hr class="wp-header-end">

            <?php if ( isset( $_GET['deleted'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>Registro eliminado correctamente.</p></div>
            <?php endif; ?>

            <div style="margin: 20px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; gap: 20px;">
                    <div style="background: #fff; padding: 14px 22px; border-radius: 8px; border-left: 4px solid #381e72; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                        <div style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">Total de Encuestas</div>
                        <div style="font-size: 26px; font-weight: bold; color: #381e72; margin-top: 2px;"><?php echo esc_html( $total_submissions ); ?></div>
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
                        <th style="width: 65px;">ID</th>
                        <th style="width: 130px;">Fecha</th>
                        <th>Paciente</th>
                        <th>Contacto</th>
                        <th>Región</th>
                        <th>Sistema Salud</th>
                        <th>Edad Diagnóstico</th>
                        <th style="width: 140px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $submissions ) ) : ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 35px; color: #64748b; font-size: 15px;">
                                No se encontraron respuestas registradas.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $submissions as $sub ) : ?>
                            <?php 
                            $view_url = admin_url( 'admin.php?page=observatorio-surveys&view_id=' . $sub['id'] );
                            $delete_url = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=delete&id=' . $sub['id'] ), 'obs_delete_entry_' . $sub['id'] );
                            ?>
                            <tr>
                                <td><strong>#<?php echo esc_html( $sub['id'] ); ?></strong></td>
                                <td><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $sub['created_at'] ) ) ); ?></td>
                                <td>
                                    <strong style="color: #381e72; font-size: 14px;"><?php echo esc_html( $sub['first_name'] . ' ' . $sub['last_name'] ); ?></strong><br>
                                    <span style="color: #64748b;"><?php echo esc_html( $sub['age'] ); ?> años</span>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo esc_attr( $sub['email'] ); ?>"><?php echo esc_html( $sub['email'] ); ?></a><br>
                                    <span style="color: #64748b;">📞 <?php echo esc_html( $sub['phone'] ); ?></span>
                                </td>
                                <td><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-weight: 600; font-size: 12px;"><?php echo esc_html( $sub['region'] ); ?></span></td>
                                <td><?php echo esc_html( $sub['health_system'] ); ?></td>
                                <td><?php echo esc_html( $sub['age_diagnosis'] ); ?></td>
                                <td style="text-align: center;">
                                    <a href="<?php echo esc_url( $view_url ); ?>" class="button button-small" style="background: #381e72; color: #fff; border-color: #381e72;">
                                        Ver Respuestas
                                    </a>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('¿Estás seguro de eliminar este registro?');" title="Eliminar">
                                        <span class="dashicons dashicons-trash" style="vertical-align: middle; margin-top: -1px; font-size: 16px;"></span>
                                    </a>
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
                        <?php
                        echo paginate_links( array(
                            'base'      => add_query_arg( 'paged', '%#%' ),
                            'format'    => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total'     => $total_pages,
                            'current'   => $paged,
                        ) );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_detail_view( $id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'obs_survey_submissions';
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), ARRAY_A );

        if ( ! $sub ) {
            echo '<div class="wrap"><div class="notice notice-error"><p>Registro no encontrado.</p></div><a href="' . esc_url( admin_url( 'admin.php?page=observatorio-surveys' ) ) . '" class="button">Volver al listado</a></div>';
            return;
        }

        $responses = json_decode( $sub['responses_json'], true ) ?: array();
        $sections = $this->get_grouped_questions();
        $back_url = admin_url( 'admin.php?page=observatorio-surveys' );
        $delete_url = wp_nonce_url( admin_url( 'admin.php?page=observatorio-surveys&action=delete&id=' . $sub['id'] ), 'obs_delete_entry_' . $sub['id'] );
        ?>
        <div class="wrap">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <a href="<?php echo esc_url( $back_url ); ?>" class="button" style="margin-bottom: 8px;">
                        &larr; Volver al Listado
                    </a>
                    <h1 style="margin: 0;">Detalle de Encuesta #<?php echo esc_html( $sub['id'] ); ?> — <?php echo esc_html( $sub['first_name'] . ' ' . $sub['last_name'] ); ?></h1>
                </div>
                <div>
                    <button onclick="window.print();" class="button"><span class="dashicons dashicons-printer" style="vertical-align: middle;"></span> Imprimir</button>
                    <a href="<?php echo esc_url( $delete_url ); ?>" class="button button-link-delete" onclick="return confirm('¿Estás seguro de eliminar este registro?');">Eliminar Registro</a>
                </div>
            </div>

            <!-- Ficha de Datos Generales -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px 25px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; color: #381e72; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">
                    <span class="dashicons dashicons-id-alt" style="margin-right: 5px;"></span> Datos de la Paciente y Perfil
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-top: 15px;">
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Nombre Completo:</strong>
                        <div style="font-size: 16px; font-weight: 600; color: #1e293b;"><?php echo esc_html( $sub['first_name'] . ' ' . $sub['last_name'] ); ?></div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Edad Actual:</strong>
                        <div style="font-size: 15px; color: #1e293b;"><?php echo esc_html( $sub['age'] ); ?> años</div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Celular:</strong>
                        <div style="font-size: 15px; color: #1e293b;"><a href="tel:<?php echo esc_attr( $sub['phone'] ); ?>">📞 <?php echo esc_html( $sub['phone'] ); ?></a></div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Correo Electrónico:</strong>
                        <div style="font-size: 15px; color: #1e293b;"><a href="mailto:<?php echo esc_attr( $sub['email'] ); ?>"><?php echo esc_html( $sub['email'] ); ?></a></div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Región de Residencia:</strong>
                        <div style="font-size: 15px; font-weight: 600; color: #381e72;"><?php echo esc_html( $sub['region'] ); ?></div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">¿Paciente Actual de Cáncer?:</strong>
                        <div style="font-size: 15px; color: #1e293b;"><?php echo esc_html( $sub['is_current_patient'] ); ?></div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Sistema de Salud:</strong>
                        <div style="font-size: 15px; color: #1e293b;"><?php echo esc_html( $sub['health_system'] ); ?></div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Edad al Diagnóstico:</strong>
                        <div style="font-size: 15px; color: #1e293b;"><?php echo esc_html( $sub['age_diagnosis'] ); ?></div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Fecha de Envío:</strong>
                        <div style="font-size: 14px; color: #64748b;"><?php echo esc_html( date_i18n( 'd/m/Y H:i:s', strtotime( $sub['created_at'] ) ) ); ?></div>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase;">Consentimiento de Datos:</strong>
                        <div><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">✓ Autorizado</span></div>
                    </div>
                </div>
            </div>

            <!-- Secciones con las 38 Preguntas -->
            <?php foreach ( $sections as $sec_title => $questions ) : ?>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px 25px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; color: #381e72; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; font-size: 16px;">
                        <?php echo esc_html( $sec_title ); ?>
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 15px;">
                        <?php foreach ( $questions as $q_key => $q_title ) : ?>
                            <?php 
                            $ans = isset( $responses[ $q_key ] ) && '' !== $responses[ $q_key ] ? $responses[ $q_key ] : null;
                            ?>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px;">
                                <div style="font-size: 13.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                    <?php echo esc_html( $q_title ); ?>
                                </div>
                                <div style="font-size: 15px; color: #381e72; font-weight: 600;">
                                    <?php if ( null !== $ans ) : ?>
                                        <span style="background: #f3effc; border: 1px solid #d8b4fe; padding: 4px 10px; border-radius: 4px; display: inline-block;">
                                            <?php echo esc_html( $ans ); ?>
                                        </span>
                                    <?php else : ?>
                                        <span style="color: #94a3b8; font-style: italic; font-weight: normal; font-size: 13px;">(No aplica o no respondida)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function get_grouped_questions() {
        return array(
            'A. Lugar de Diagnóstico y Derivación' => array(
                'q1_diag_place'            => '1. ¿Dónde fuiste diagnosticada con cáncer de mama?',
                'q2_derivada_lima'         => '2. ¿Fuiste derivada a Lima para continuar tu atención por cáncer de mama?',
                'q3_etapa_derivada'        => '3. ¿Para qué etapa de tu atención fuiste derivada a Lima?',
                'q4_tiempo_atencion_lima'  => '4. Desde que te indicaron la derivación hasta que pudiste ser atendida en Lima, ¿cuánto tiempo pasó?',
            ),
            'B. Aparece una Señal' => array(
                'q5_deteccion_inicial'             => '5. ¿Cómo se detectó inicialmente algo que podía indicar un problema en tu mama?',
                'q6_tiempo_hasta_buscar_atencion' => '6. Después de identificar la señal, ¿cuánto tiempo pasó hasta que buscaste atención médica?',
            ),
            'C. Primera Atención' => array(
                'q7_tiempo_primera_consulta'                => '7. Después de buscar atención, ¿cuánto tiempo pasó hasta tu primera consulta médica?',
                'q8_tipo_establecimiento_primera_consulta' => '8. ¿En qué tipo de establecimiento fue tu primera consulta?',
                'q9_solicitaron_examenes'                  => '9. ¿Te solicitaron exámenes para evaluar la mama?',
            ),
            'D. Exámenes Mamarios' => array(
                'q10_examenes_solicitados'              => '10. ¿Qué exámenes te solicitaron?',
                'q11_tiempo_hasta_realizar_examen'      => '11. Desde que te solicitaron el examen hasta que pudiste realizarlo, ¿cuánto tiempo pasó?',
                'q12_tiempo_hasta_resultado_examen'     => '12. Desde que realizaste el examen hasta que recibiste el resultado, ¿cuánto tiempo pasó?',
                'q13_dificultad_examenes'               => '13. ¿Tuviste alguna dificultad para realizar los exámenes?',
                'q14_principal_dificultad_examenes'     => '14. ¿Cuál fue la principal dificultad?',
            ),
            'E. Derivación a Otro Establecimiento' => array(
                'q15_derivaron_otro_establecimiento' => '15. ¿Te derivaron a otro establecimiento o especialista?',
                'q16_tiempo_atencion_derivacion'     => '16. Si te derivaron, ¿cuánto tiempo pasó hasta que lograste ser atendida?',
                'q17_cambio_sistema'                 => '17. ¿La derivación implicó pasar de un sistema de atención a otro?',
            ),
            'F. Biopsia y Confirmación del Diagnóstico' => array(
                'q18_indicaron_biopsia'               => '18. ¿Te indicaron una biopsia para confirmar el diagnóstico?',
                'q19_tiempo_hasta_realizar_biopsia'  => '19. Desde que te indicaron la biopsia hasta que se realizó, ¿cuánto tiempo pasó?',
                'q20_tiempo_resultado_biopsia'        => '20. Desde que se realizó la biopsia hasta que recibiste el resultado, ¿cuánto tiempo pasó?',
                'q21_establecimiento_biopsia'         => '21. ¿En qué tipo de establecimiento se realizó principalmente la biopsia?',
            ),
            'G. Tipo de Cáncer de Mama y H. Estadio' => array(
                'q22_informaron_subtipo'        => '22. ¿Te informaron qué subtipo de cáncer de mama tenías?',
                'q23_subtipo_indicado'          => '23. Si te informaron el subtipo, ¿cuál te indicaron?',
                'q24_estadio_detectado'         => '24. ¿En qué estadio te detectaron el cáncer de mama?',
                'q25_tiempo_estudios_estadio'   => '25. ¿Cuánto tiempo pasó desde la confirmación del diagnóstico hasta que se completaron los estudios para determinar el estadio?',
                'q26_estadio_diferente'         => '26. Cuándo inició tu primer tratamiento, ¿te informaron de un estadio diferente al que te habían indicado inicialmente?',
                'q27_estadio_inicio_tratamiento' => '27. Si respondiste “Sí”, ¿qué estadio te informaron al inicio del tratamiento?',
            ),
            'I. Inicio del Tratamiento' => array(
                'q28_tratamiento_inicial'                  => '28. ¿Qué tratamiento te indicaron inicialmente?',
                'q29_tiempo_confirmacion_a_indicacion'     => '29. Desde que recibiste la confirmación del diagnóstico hasta que te indicaron el tratamiento, ¿cuánto tiempo pasó?',
                'q30_tiempo_indicacion_a_inicio'           => '30. Desde que te indicaron el tratamiento hasta que efectivamente lo iniciaste, ¿cuánto tiempo pasó?',
                'q31_lugar_inicio_tratamiento'             => '31. ¿Dónde iniciaste principalmente tu tratamiento?',
            ),
            'J. Barreras, Experiencia y Consentimiento' => array(
                'q32_etapa_mayor_espera'                   => '32. ¿En qué etapa sentiste que tuviste que esperar más?',
                'q33_principal_dificultad_recorrido'       => '33. ¿Cuál fue la principal dificultad que enfrentaste durante tu recorrido?',
                'q34_acudio_privado_rapidez'               => '34. ¿En algún momento tuviste que acudir a un establecimiento privado para poder avanzar más rápido en alguna etapa?',
                'q35_demora_retraso_tratamiento'           => '35. Durante tu recorrido, ¿sentiste que la demora en tu atención retrasó el inicio de tu tratamiento?',
                'q36_tiempo_total_recorrido'               => '36. Pensando en todo tu recorrido, desde la primera señal hasta el inicio de tu primer tratamiento, ¿cuánto tiempo pasó aproximadamente?',
                'q37_informacion_clara_siguiente_paso'     => '37. Durante tu recorrido, ¿recibiste información clara sobre cuál era el siguiente paso de tu atención?',
                'q38_tratamiento_innovador_no_cubierto'    => '38. ¿Algún médico le indicó que existe un tratamiento innovador que el sistema o su seguro no cubre?',
            ),
        );
    }

    private function get_questions_map() {
        $flat = array();
        foreach ( $this->get_grouped_questions() as $group => $questions ) {
            foreach ( $questions as $key => $title ) {
                $flat[ $key ] = $title;
            }
        }
        return $flat;
    }
}
