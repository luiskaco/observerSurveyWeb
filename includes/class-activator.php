<?php
namespace Observatorio\Survey;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Activator {
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'obs_survey_submissions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            survey_type VARCHAR(50) NOT NULL DEFAULT 'cancer_mama_journey',
            consent_accepted TINYINT(1) NOT NULL DEFAULT 0,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            age SMALLINT UNSIGNED NOT NULL,
            phone VARCHAR(30) NOT NULL,
            email VARCHAR(150) NOT NULL,
            region VARCHAR(100) DEFAULT NULL,
            is_current_patient VARCHAR(10) DEFAULT NULL,
            health_system VARCHAR(100) DEFAULT NULL,
            age_diagnosis VARCHAR(50) DEFAULT NULL,
            responses_json LONGTEXT NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'completed',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_survey_type (survey_type),
            KEY idx_email (email),
            KEY idx_created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
