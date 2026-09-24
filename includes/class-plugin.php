<?php
namespace Observatorio\Survey;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once OBS_SURVEY_PLUGIN_DIR . 'includes/class-rest-controller.php';
require_once OBS_SURVEY_PLUGIN_DIR . 'includes/class-admin-page.php';
require_once OBS_SURVEY_PLUGIN_DIR . 'includes/class-google-sheets.php';

class Plugin {
    private $rest_controller;
    private $admin_page;

    public function __construct() {
        $this->rest_controller = new Rest_Controller();
        $this->admin_page      = new Admin_Page();
    }

    public function run() {
        add_action( 'rest_api_init', array( $this->rest_controller, 'register_routes' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_shortcode( 'encuesta_cancer_mama', array( $this, 'render_shortcode' ) );

        if ( is_admin() ) {
            $this->admin_page->register_hooks();
        }
    }

    public function enqueue_assets() {
        wp_register_style(
            'obs-survey-style',
            OBS_SURVEY_PLUGIN_URL . 'assets/css/survey-frontend.css',
            array(),
            OBS_SURVEY_VERSION
        );

        wp_register_script(
            'obs-survey-script',
            OBS_SURVEY_PLUGIN_URL . 'assets/js/survey-wizard.js',
            array(),
            OBS_SURVEY_VERSION,
            true
        );

        wp_localize_script( 'obs-survey-script', 'obsSurveyConfig', array(
            'apiUrl' => esc_url_raw( rest_url( 'observatorio/v1/survey/submit' ) ),
            'nonce'  => wp_create_nonce( 'wp_rest' ),
        ) );
    }


    public function render_shortcode( $atts = array() ) {
        wp_enqueue_style( 'obs-survey-style' );
        wp_enqueue_script( 'obs-survey-script' );

        ob_start();
        $template_path = OBS_SURVEY_PLUGIN_DIR . 'templates/survey-container.php';
        if ( file_exists( $template_path ) ) {
            include $template_path;
        }
        return ob_get_clean();
    }
}
