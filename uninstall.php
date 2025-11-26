<?php
/**
 * Uninstall handler for WP Trademark Service by Nexilup
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

/**
 * ----------------------------------------------------
 * 1. DELETE CUSTOM DATABASE TABLES
 * ----------------------------------------------------
 */
$tables = [
    $wpdb->prefix . "tm_countries",
    $wpdb->prefix . "tm_country_prices",
    $wpdb->prefix . "tm_service_conditions",
    $wpdb->prefix . "tm_owner_profiles",
    $wpdb->prefix . "tm_trademarks",
    $wpdb->prefix . "tm_trademark_classes",
    $wpdb->prefix . "tm_trademark_files",
];

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS `$table`");
}

/**
 * ----------------------------------------------------
 * 2. DELETE AUTO-CREATED PAGES
 * ----------------------------------------------------
 */
$created_pages = get_option('tm_created_pages', []);

if (!empty($created_pages) && is_array($created_pages)) {
    foreach ($created_pages as $page_id) {
        if (get_post($page_id)) {
            wp_delete_post($page_id, true); // force delete
        }
    }
}

// Remove the tracking option
delete_option('tm_created_pages');

/**
 * ----------------------------------------------------
 * 3. DELETE PLUGIN OPTIONS / SETTINGS
 * ----------------------------------------------------
 */
delete_option('wp_tms_settings');
delete_option('wp_tms_version');

/**
 * ----------------------------------------------------
 * 4. CLEAN TRANSIENTS (if any in future)
 * ----------------------------------------------------
 */
global $wpdb;
$wpdb->query(
    "DELETE FROM $wpdb->options 
     WHERE option_name LIKE '_transient_tm_%' 
        OR option_name LIKE '_transient_timeout_tm_%'"
);

