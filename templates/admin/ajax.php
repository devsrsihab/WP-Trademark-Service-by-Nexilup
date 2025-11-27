<?php


add_action('wp_ajax_tm_admin_get_trademark', 'tm_admin_get_trademark_callback');

function tm_admin_get_trademark_callback() {

    check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

    global $wpdb;

    $id = intval($_POST['id']);

    $table = $wpdb->prefix . "tm_trademarks";

    $t = $wpdb->get_row("SELECT * FROM $table WHERE id = $id");

    if (!$t) {
        wp_send_json_error(['message' => 'Trademark not found.']);
    }

    ob_start();
    include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/admin/trademark-details.php';
    $html = ob_get_clean();

    wp_send_json_success(['html' => $html]);
}
