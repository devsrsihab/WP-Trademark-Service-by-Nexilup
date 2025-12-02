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
define('TM_MASTER_PRODUCT_ID', 2905 );

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
    require_once WP_TMS_NEXILUP_PLUGIN_PATH . 'includes/class-tm-orders.php';

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

/**
 * Safe function to get order item meta with fallback
 */
function tm_get_meta_safe($item, $key) {
    if (is_callable([$item, 'get_meta'])) {
        return $item->get_meta($key, true);
    }
    return '';
}


add_action('woocommerce_after_order_itemmeta', 'tm_display_pretty_order_item_meta', 10, 3);

/**
 * Display trademark details in WooCommerce order admin
 */
function tm_display_pretty_order_item_meta($item_id, $item, $order) {

    if (!$item->get_meta('tm_trademark_id', true)) {
        return;
    }

    // Collect all data
    $data = [
        'country'        => tm_get_meta_safe($item, 'country_iso'),
        'type'           => tm_get_meta_safe($item, 'type'),
        'name'           => tm_get_meta_safe($item, 'mark_text'),
        'logo'           => tm_get_meta_safe($item, 'logo_url'),
        'goods'          => tm_get_meta_safe($item, 'goods'),
        'classes'        => tm_get_meta_safe($item, 'classes'),
        'class_list'     => tm_get_meta_safe($item, 'class_list'),
        'class_details'  => tm_get_meta_safe($item, 'class_details'),
        'priority'       => tm_get_meta_safe($item, 'priority'),
        'poa'            => tm_get_meta_safe($item, 'poa'),
        'total'          => tm_get_meta_safe($item, 'total_price'),
        'currency'       => tm_get_meta_safe($item, 'currency'),
    ];

    $list = json_decode($data['class_list'], true);
    $details = json_decode($data['class_details'], true);

    echo '<div class="tm-wrapper" style="
        margin:20px 0;padding:18px;
        background:#fff;border:1px solid #ccd0d4;border-radius:8px;">
        <h3 style="margin:0 0 15px;">Trademark Details</h3>
        <table style="width:100%; border-collapse:collapse;">';

    $row = function($label, $value) {
        if (!$value) return;
        echo "<tr>
                <th style='text-align:left;padding:6px 0;width:200px;'>$label:</th>
                <td style='padding:6px 0;'>$value</td>
              </tr>";
    };

    $row('Country', $data['country']);
    $row('Trademark Type', ucfirst($data['type']));
    $row('Trademark Name', $data['name']);

    if ($data['logo']) {
        $row('Logo', "<img src='{$data['logo']}' style='max-width:120px;border:1px solid #ccc;'>");
    }

    $row('Goods / Services', nl2br($data['goods']));
    $row('Total Classes', $data['classes']);

    if (!empty($list)) {
        $row('Class Numbers', implode(', ', $list));
    }

    if (!empty($details)) {
        $html = "<ul style='margin:0;padding-left:16px;'>";
        foreach ($details as $d) {
            $class_num = isset($d['class']) ? $d['class'] : '';
            $class_goods = isset($d['goods']) ? $d['goods'] : '';
            $html .= "<li><strong>Class {$class_num}:</strong> {$class_goods}</li>";
        }
        $html .= "</ul>";
        $row('Class Details', $html);
    }

    $row('Priority Claim', ($data['priority'] == "1" ? 'Yes' : 'No'));
    $row('POA Type', ucfirst($data['poa']));
    $row('Total Price', $data['total'] . ' ' . $data['currency']);

    echo '</table></div>';
}