<?php
/**
 * Plugin Name:       Observatorio Survey
 * Plugin URI:        https://observatoriodelcancer.pe
 * Description:       Módulo interactivo de encuestas multi-paso para el viaje de la paciente oncológica en el Perú.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Observatorio de Por Un Perú Sin Cáncer
 * Text Domain:       observatorio-survey
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'OBS_SURVEY_VERSION', '1.1.0' );
define( 'OBS_SURVEY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OBS_SURVEY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader o carga de archivos principales
 */
require_once OBS_SURVEY_PLUGIN_DIR . 'includes/class-activator.php';
require_once OBS_SURVEY_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once OBS_SURVEY_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Observatorio\\Survey\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Observatorio\\Survey\\Deactivator', 'deactivate' ) );

function obs_survey_run() {
    $plugin = new Observatorio\Survey\Plugin();
    $plugin->run();
}
obs_survey_run();
