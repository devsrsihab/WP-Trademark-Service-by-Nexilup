<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TM_Admin {

    public static function init() {
        if ( is_admin() ) {
            add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
            // Removed duplicate line
        }
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

        add_submenu_page(
            'tm-dashboard',
            __( 'Settings', 'wp-tms-nexilup' ),
            __( 'Settings', 'wp-tms-nexilup' ),
            'manage_options',
            'tm-settings',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    // Enqueue admin assets
    public static function enqueue_assets( $hook ) {

        // Load only on plugin pages
        if ( strpos( $hook, 'tm-' ) === false ) {
            return;
        }

        // ========== CSS ==========
        wp_enqueue_style(
            'tm-admin-dashboard',
            WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/admin-dashboard.css',
            array(),
            WP_TMS_NEXILUP_VERSION
        );

        // Load specific styles for Countries page
        if ( $hook === 'trademark-service_page_tm-countries' ) {
            wp_enqueue_style(
                'tm-admin-countries',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/admin-countries.css', // Fixed: consistent naming
                array(),
                WP_TMS_NEXILUP_VERSION
            );

            wp_enqueue_style(
                'tm-countries-flags',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/css/country-flag.css',
                array(),
                WP_TMS_NEXILUP_VERSION
            );

            // ========== JS ==========
            wp_enqueue_script(
                'tm-admin-countries',
                WP_TMS_NEXILUP_PLUGIN_URL . 'assets/js/admin-countries.js', // Fixed: consistent naming
                array('jquery'),
                WP_TMS_NEXILUP_VERSION,
                true
            );
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

    public static function render_settings_page() {
        self::load_template( 'settings.php' );
    }

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