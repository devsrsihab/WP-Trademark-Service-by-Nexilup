<?php
/**
 * Plugin Name:       WP Trademark Service by Nexilup
 * Description:       Trademark country pricing, multi-step order forms, and WooCommerce integration.
 * Version:           1.0.0
 * Author:            Md. Sohanur Rahman Sihab
 * Text Domain:       wp-tms-nexilup
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define constants
 */
define( 'WP_TMS_NEXILUP_VERSION', '1.0.0' );
define( 'WP_TMS_NEXILUP_PLUGIN_FILE', __FILE__ );
define( 'WP_TMS_NEXILUP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_TMS_NEXILUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_TMS_NEXILUP_URL', plugin_dir_url( __FILE__ ) );
define('TM_MASTER_PRODUCT_ID', 2984);

/**
 * Load activation/deactivation deps early
 */
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-activator.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-deactivator.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-database.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-pages.php';
require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-rewrite.php';

/**
 * Activation Hook
 */
function wp_tms_nexilup_activate() {

    WP_TMS_Activator::activate();

    // Create required pages once
    TM_Pages::create_required_pages();

    // Add rewrite rules and flush once
    TM_Rewrite::routes();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wp_tms_nexilup_activate' );

/**
 * Deactivation Hook
 */
function wp_tms_nexilup_deactivate() {
    WP_TM_Deactivator::deactivate();
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wp_tms_nexilup_deactivate' );

/**
 * Bootstrap plugin
 */
function wp_tms_nexilup_init() {

    load_plugin_textdomain(
        'wp-tms-nexilup',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );

    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-admin.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-frontend.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-woocommerce.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-countries.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-country-prices.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-service-conditions.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-trademarks.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-ajax.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-service-form.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/ajax-step-flow.php';
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-upload.php';

    TM_Admin::init();
    TM_Frontend::init();

    if ( class_exists( 'WooCommerce' ) ) {
        TM_WooCommerce::init();
    }

    TM_Countries::init();
    TM_Country_Prices::init();
    TM_Service_Conditions::init();
    TM_Trademarks::init();
    TM_Ajax::init();
    TM_Upload::init();


    // IMPORTANT: only one router system
    TM_Rewrite::init();
    TM_Pages::init();
}
add_action( 'plugins_loaded', 'wp_tms_nexilup_init' );

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
