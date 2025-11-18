<?php
/**
 * Plugin Name:       WP Trademark Service by Nexilup
 * Description:       Trademark country pricing, multi-step order forms, and WooCommerce integration.
 * Version:           1.0.0
 * Author:            Md. Sohanur Rahman Sihab
 * Text Domain:       wp-tms-nexilup
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define constants
 */
define( 'WP_TMS_NEXILUP_VERSION', '1.0.0' );
define( 'WP_TMS_NEXILUP_PLUGIN_FILE', __FILE__ );
define( 'WP_TMS_NEXILUP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_TMS_NEXILUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoload includes
 */
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-activator.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-deactivator.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-database.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-admin.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-frontend.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-woocommerce.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-countries.php';


/**
 * Activation / Deactivation hooks
 */
function wp_tms_nexilup_activate() {
    WP_TMS_Activator::activate();
}
register_activation_hook( __FILE__, 'wp_tms_nexilup_activate' );

function wp_tms_nexilup_deactivate() {
    WP_TM_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'wp_tms_nexilup_deactivate' );

/**
 * Bootstrap plugin
 */
function wp_tms_nexilup_init() {

    // Load textdomain
    load_plugin_textdomain(
        'wp-tms-nexilup',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );

    // Core modules
    TM_Admin::init();
    TM_Frontend::init();

    // Load WooCommerce module only if WooCommerce is active
    if ( class_exists( 'WooCommerce' ) ) {
        TM_WooCommerce::init();
    }
    TM_Countries::init();

}

/**
 * Add settings link on plugin list
 */
function wp_tms_nexilup_settings_link( $links ) {
    $settings_url = admin_url( 'admin.php?page=tm-dashboard' );

    $settings_link = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Dashboard', 'wp-tms-nexilup' ) . '</a>';

    array_unshift( $links, $settings_link );
    return $links;
}

add_filter(
    'plugin_action_links_' . plugin_basename( __FILE__ ),
    'wp_tms_nexilup_settings_link'
);



add_action( 'plugins_loaded', 'wp_tms_nexilup_init' );
