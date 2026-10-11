<?php
namespace Observatorio\Survey;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Activator {
    public static function activate() {
        global $wpdb;
        $table_name       = $wpdb->prefix . 'obs_survey_submissions';
        $short_table_name = $wpdb->prefix . 'obs_survey_short_submissions';

        // 1. Obtener charset / collate base
        $charset_collate = $wpdb->get_charset_collate();

        // 2. Si la tabla principal ya existe en el hosting, extraer su collation real
        // para garantizar compatibilidad idéntica con la nueva tabla corta
        $orig_status = $wpdb->get_row( $wpdb->prepare( "SHOW TABLE STATUS LIKE %s", $table_name ), ARRAY_A );
        if ( ! empty( $orig_status['Collation'] ) ) {
            $charset_collate = "DEFAULT CHARACTER SET utf8mb4 COLLATE " . $orig_status['Collation'];
        }

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            survey_type VARCHAR(50) NOT NULL DEFAULT 'cancer_mama_journey',
            consent_accepted TINYINT(1) NOT NULL DEFAULT 0,
            first_name VARCHAR(100) NOT NULL DEFAULT '',
            last_name VARCHAR(100) NOT NULL DEFAULT '',
            age SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            phone VARCHAR(30) NOT NULL DEFAULT '',
            email VARCHAR(150) NOT NULL DEFAULT '',
            region VARCHAR(100) DEFAULT NULL,
            is_current_patient VARCHAR(10) DEFAULT NULL,
            health_system VARCHAR(100) DEFAULT NULL,
            age_diagnosis VARCHAR(50) DEFAULT NULL,
            responses_json LONGTEXT NOT NULL,
            synced_to_sheets TINYINT(1) NOT NULL DEFAULT 0,
            synced_at DATETIME DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'completed',
            last_step_reached TINYINT UNSIGNED NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_survey_type (survey_type),
            KEY idx_email (email),
            KEY idx_status (status),
            KEY idx_synced (synced_to_sheets),
            KEY idx_created_at (created_at)
        ) $charset_collate;";

        $sql_short = "CREATE TABLE IF NOT EXISTS $short_table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            survey_type VARCHAR(50) NOT NULL DEFAULT 'cancer_mama_corta',
            consent_accepted TINYINT(1) NOT NULL DEFAULT 0,
            region VARCHAR(100) DEFAULT '',
            is_current_patient VARCHAR(20) DEFAULT '',
            health_system VARCHAR(100) DEFAULT '',
            age_diagnosis VARCHAR(50) DEFAULT '',
            responses_json LONGTEXT NOT NULL,
            synced_to_sheets TINYINT(1) NOT NULL DEFAULT 0,
            synced_at DATETIME DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'completed',
            last_step_reached TINYINT UNSIGNED NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_synced (synced_to_sheets),
            KEY idx_created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        dbDelta( $sql_short );

        self::ensure_columns_and_defaults();

        update_option( 'obs_survey_db_version', OBS_SURVEY_VERSION );
    }

    public static function maybe_upgrade() {
        $db_version = get_option( 'obs_survey_db_version' );
        if ( $db_version !== OBS_SURVEY_VERSION ) {
            self::activate();
            return;
        }

        global $wpdb;
        $table_name       = $wpdb->prefix . 'obs_survey_submissions';
        $short_table_name = $wpdb->prefix . 'obs_survey_short_submissions';

        // Si se subieron archivos por FTP/cPanel sin reactivar el plugin, verificar existencia de tablas
        $orig_exists  = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
        $short_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $short_table_name ) );

        if ( $orig_exists !== $table_name || $short_exists !== $short_table_name ) {
            self::activate();
        }
    }

    public static function ensure_columns_and_defaults() {
        global $wpdb;
        $table_name       = $wpdb->prefix . 'obs_survey_submissions';
        $short_table_name = $wpdb->prefix . 'obs_survey_short_submissions';

        // 1. Verificar si existe la columna last_step_reached en tabla original
        $column = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name} LIKE 'last_step_reached'" );
        if ( empty( $column ) ) {
            $wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN last_step_reached TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER status" );
        }

        // 2. Asegurar defaults en columnas de contacto para permitir encuestas anónimas
        $wpdb->query( "ALTER TABLE {$table_name} ALTER COLUMN first_name SET DEFAULT '', ALTER COLUMN last_name SET DEFAULT '', ALTER COLUMN age SET DEFAULT 0, ALTER COLUMN phone SET DEFAULT '', ALTER COLUMN email SET DEFAULT ''" );

        // 3. Garantizar que ambas tablas compartan la misma collation para evitar errores en queries UNION
        $orig_status  = $wpdb->get_row( $wpdb->prepare( "SHOW TABLE STATUS LIKE %s", $table_name ), ARRAY_A );
        $short_status = $wpdb->get_row( $wpdb->prepare( "SHOW TABLE STATUS LIKE %s", $short_table_name ), ARRAY_A );

        if ( ! empty( $orig_status['Collation'] ) && ! empty( $short_status['Collation'] ) ) {
            if ( $short_status['Collation'] !== $orig_status['Collation'] ) {
                $wpdb->query( "ALTER TABLE {$short_table_name} CONVERT TO CHARACTER SET utf8mb4 COLLATE " . $orig_status['Collation'] );
            }
        }
    }
}
