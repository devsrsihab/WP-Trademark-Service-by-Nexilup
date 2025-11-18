<?php

// Run only when plugin is truly deleted, not deactivated
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$tables = [
    $wpdb->prefix . "tm_countries",
    $wpdb->prefix . "tm_country_prices",
    $wpdb->prefix . "tm_service_conditions",
    $wpdb->prefix . "tm_owner_profiles",
    $wpdb->prefix . "tm_trademarks",
    $wpdb->prefix . "tm_trademark_classes"
];

// Drop each table
foreach ( $tables as $table ) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

// Remove plugin options (if any)
delete_option('wp_tms_settings');
delete_option('wp_tms_version');

