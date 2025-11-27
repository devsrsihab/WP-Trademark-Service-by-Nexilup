<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TM_Admin {

    public static function init() {
        if ( is_admin() ) {
            add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
            add_action('wp_ajax_tm_get_trademark_details', [__CLASS__, 'ajax_get_trademark_details']);

        }
    }


    public static function ajax_get_trademark_details() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $id = intval($_POST['id']);

        global $wpdb;
        $table = TM_Database::table_name('trademarks');

        $t = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id=%d LIMIT 1", $id)
        );

        if (!$t) {
            wp_send_json_error(['message' => 'Trademark not found']);
        }

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/admin/trademark-details.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
}

    

    public static function register_menus() {
        add_menu_page(
            __( 'Trademark Service', 'wp-tms-nexilup' ),
            __( 'Trademark Service', 'wp-tms-nexilup' ),
            'manage_options',
            'tm-dashboard',
            array( __CLASS__, 'render_dashboard_page' ),
            'dashicons-forms',
            26
        );

        add_submenu_page(
            'tm-dashboard',
            __( 'Countries', 'wp-tms-nexilup' ),
            __( 'Countries', 'wp-tms-nexilup' ),
            'manage_options',
            'tm-countries',
            array( __CLASS__, 'render_countries_page' )
        );

        add_submenu_page(
            'tm-dashboard',
            __( 'Country Prices', 'wp-tms-nexilup' ),
            __( 'Country Prices', 'wp-tms-nexilup' ),
            'manage_options',
            'tm-country-prices',
            array( __CLASS__, 'render_country_prices_page' )
        );

        add_submenu_page(
            'tm-dashboard',
            __( 'Service Conditions', 'wp-tms-nexilup' ),
            __( 'Service Conditions', 'wp-tms-nexilup' ),
            'manage_options',
            'tm-service-conditions',
            array( __CLASS__, 'render_service_conditions_page' )
        );

        // add_submenu_page(
        //     'tm-dashboard',
        //     __( 'Settings', 'wp-tms-nexilup' ),
        //     __( 'Settings', 'wp-tms-nexilup' ),
        //     'manage_options',
        //     'tm-settings',
        //     array( __CLASS__, 'render_settings_page' )
        // );
        add_submenu_page(
            'tm-dashboard',
            __( 'Trademarks', 'wp-tms-nexilup' ),
            __( 'Trademarks', 'wp-tms-nexilup' ),
            'manage_options',
            'tm-trademarks',
            array( __CLASS__, 'render_trademarks_page' )
        );

    }


    public static function render_trademarks_page() {
     self::load_template('trademarks.php');
    }


    // Enqueue admin assets
    public static function enqueue_assets( $hook ) {

        // Load only plugin pages
        if ( strpos( $hook, 'tm-' ) === false ) {
            return;
        }

        // ========== GLOBAL ADMIN CSS ==========
        wp_enqueue_style(
            'tm-admin-dashboard',
            WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/admin-dashboard.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        // ===============================
        // LOAD FOR COUNTRIES PAGE
        // ===============================
        if ( $hook === 'trademark-service_page_tm-countries' ) {

            wp_enqueue_style(
                'tm-admin-countries',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/admin-countries.css',
                [],
                WP_TMS_NEXILUP_VERSION
            );

            wp_enqueue_style(
                'tm-countries-flags',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/country-flag.css',
                [],
                WP_TMS_NEXILUP_VERSION
            );

            wp_enqueue_style(
                'tm-admin-css',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/admin.css',
                [],
                WP_TMS_NEXILUP_VERSION
            );

            wp_enqueue_script(
                'tm-admin-countries',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/js/admin-countries.js',
                ['jquery'],
                WP_TMS_NEXILUP_VERSION,
                true
            );

            wp_enqueue_script(
                'tm-admin-countries-prices',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/js/admin-prices.js',
                ['jquery'],
                WP_TMS_NEXILUP_VERSION,
                true
            );
        }

        // ===============================
        // LOAD FOR TRADEMARKS PAGE (IMPORTANT)
        // ===============================
        if ( $hook === 'trademark-service_page_tm-trademarks' ) {

            // Modal CSS
            wp_enqueue_style(
                'tm-admin-modal-css',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/admin-trademark-modal.css',
                [],
                WP_TMS_NEXILUP_VERSION
            );

            // Modal JS
            wp_enqueue_script(
                'tm-admin-modal-js',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/js/admin-trademark-modal.js',
                ['jquery'],
                WP_TMS_NEXILUP_VERSION,
                true
            );

            // Localize ajax
            wp_localize_script('tm-admin-modal-js', 'TM_ADMIN_MODAL', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('tm_admin_trademark_nonce')
            ]);
        }

    }


    public static function render_dashboard_page() {
        self::load_template( 'dashboard.php' );
    }

    public static function render_countries_page() {
        self::load_template( 'countries.php' );
    }

    public static function render_country_prices_page() {
        self::load_template( 'country-prices.php' );
    }

    public static function render_service_conditions_page() {
        self::load_template( 'service-conditions.php' );
    }

    // public static function render_settings_page() {
    //     self::load_template( 'settings.php' );
    // }

    /**
     * Template loader
     */
    private static function load_template( $file ) {
        $path = WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/admin/' . $file;

        if ( file_exists( $path ) ) {
            include $path;
        } else {
            echo '<div class="wrap"><h1>Template Missing</h1><p>' . esc_html( $file ) . ' not found.</p></div>';
        }
    }

}