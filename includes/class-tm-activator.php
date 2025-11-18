<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_TMS_Activator {

    public static function activate() {

        // Load DB upgrade helpers
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Delegate all table creation to TM_Database
        TM_Database::create_tables();
    }
}
