<?php
if (!defined('ABSPATH')) exit;

class TM_Frontend {

    public static function init() {
        add_shortcode('tm_country_table', [__CLASS__, 'shortcode_country_table']);
        add_shortcode('tm_country_single', [__CLASS__, 'shortcode_country_single']);
        add_shortcode('tm_service_form', [__CLASS__, 'render_service_form']);
        add_shortcode('tm_my_trademarks', [__CLASS__, 'shortcode_my_trademarks']);

        add_action('wp_enqueue_scripts', [__CLASS__, 'register_scripts']);
    }

    public static function register_scripts() {

        wp_register_style(
            'tm-step3-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend-step3.css',
            ['tm-frontend-css'],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_script(
            'tm-step3-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-step3.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );


        wp_register_style(
            'tm-frontend-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-frontend-flag',
            WP_TMS_NEXILUP_URL . 'assets/css/country-flag.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-country-single-css',
            WP_TMS_NEXILUP_URL . 'assets/css/country-single.css',
            ['tm-frontend-css'],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-step1-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend-step1.css',
            ['tm-frontend-css'],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-step2-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend-step2.css',
            ['tm-frontend-css'],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_style(
            'tm-frontend-modal-css',
            WP_TMS_NEXILUP_URL . 'assets/css/frontend-modal.css',
            [],
            WP_TMS_NEXILUP_VERSION
        );

        wp_register_script(
            'tm-prices-modal-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-prices-modal.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

        wp_register_script(
            'tm-step1-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-step1.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

        wp_register_script(
            'tm-step2-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-step2.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );

        wp_register_script(
            'tm-my-trademarks-js',
            WP_TMS_NEXILUP_URL . 'assets/js/frontend-my-trademarks.js',
            ['jquery'],
            WP_TMS_NEXILUP_VERSION,
            true
        );
    }

    public static function shortcode_country_table($atts) {
        $atts = shortcode_atts([
            'per_page'    => 20,
            'single_page' => ''
        ], $atts);

        wp_enqueue_style('tm-frontend-css');

        global $wpdb;
        $table = TM_Database::table_name('countries');

        $search = isset($_GET['tm_search']) ? sanitize_text_field($_GET['tm_search']) : '';
        $paged  = isset($_GET['tm_page']) ? max(1, intval($_GET['tm_page'])) : 1;
        $per_page = intval($atts['per_page']);

        $where = "WHERE status = 1";
        $params = [];

        if ($search) {
            $where .= " AND country_name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $total_sql = "SELECT COUNT(*) FROM $table $where";
        $total = $params ? $wpdb->get_var($wpdb->prepare($total_sql, ...$params)) : $wpdb->get_var($total_sql);

        $max_pages = ceil($total / $per_page);
        $offset = ($paged - 1) * $per_page;

        if ($params) {
            $params[] = $per_page;
            $params[] = $offset;
            $sql = $wpdb->prepare(
                "SELECT * FROM $table $where ORDER BY country_name ASC LIMIT %d OFFSET %d",
                ...$params
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT * FROM $table $where ORDER BY country_name ASC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            );
        }

        $countries = $wpdb->get_results($sql);

        ob_start();
        $single_page = trailingslashit($atts['single_page']);
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/country-table.php';
        return ob_get_clean();
    }

    public static function shortcode_country_single($atts) {

        wp_enqueue_style('tm-frontend-css');
        wp_enqueue_style('tm-frontend-modal-css');
        wp_enqueue_style('tm-country-single-css');
        wp_enqueue_script('tm-prices-modal-js');

        if (!isset($_GET['country'])) {
            return "<p class='tm-error'>No country selected.</p>";
        }

        $iso = sanitize_text_field($_GET['country']);

        global $wpdb;
        $countries_table = TM_Database::table_name('countries');
        $prices_table    = TM_Country_Prices::table();

        $country = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $countries_table WHERE iso_code = %s", $iso)
        );

        if (!$country) {
            return "<p class='tm-error'>Invalid country.</p>";
        }

        $steps = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $prices_table
                WHERE country_id = %d
                ORDER BY trademark_type ASC, step_number ASC
            ", $country->id)
        );

        $sc_table = TM_Database::table_name('service_conditions');
        $service_conditions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $sc_table WHERE country_id = %d ORDER BY step_number ASC",
                $country->id
            )
        );

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/country-single.php';
        return ob_get_clean();
    }

    /**
     * MULTISTEP ORDER FORM
     * Step1 = comprehensive study form
     * Step2 = confirm order + payment
     * Step3 = WC Thankyou page
     */
    public static function render_service_form($atts) {

        wp_enqueue_style('tm-frontend-css');
        wp_enqueue_style('tm-frontend-flag');

        $country_code = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
        $step = TM_Service_Form::detect_initial_step();

        if (!$country_code) {
            return "<p class='tm-error'>No country selected</p>";
        }

        global $wpdb;
        $country = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tm_countries WHERE iso_code = %s",
                $country_code
            )
        );

        if (!$country) {
            return "<p class='tm-error'>Invalid country.</p>";
        }

        $js_data = [
            'ajax_url'    => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('tm_nonce'),
            'country_id'  => (int)$country->id,
            'country_iso' => $country->iso_code,
            'step'        => $step,
            'tm_additional_class'=> isset($_GET['tm_additional_class']) ? (int) $_GET['tm_additional_class'] : 0,

        ];

        ob_start();

        if ($step === 1) {
            wp_enqueue_style('tm-step1-css');
            wp_enqueue_script('tm-step1-js');
            wp_localize_script('tm-step1-js', 'TM_GLOBAL', $js_data);
            include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step1.php';

        } elseif ($step === 2) {
            wp_enqueue_style('tm-step2-css');
            wp_enqueue_script('tm-step2-js');
            wp_localize_script('tm-step2-js', 'TM_GLOBAL', $js_data);
            include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step2.php';

        
        } elseif ($step === 3) {

            wp_enqueue_style('tm-step3-css');
            wp_enqueue_script('tm-step3-js');

              // Correct GET param usage
            $order_id  = intval($_GET['tm_order_received'] ?? 0);
            $order_key = sanitize_text_field($_GET['key'] ?? '');


            $js_data['order_id'] =  $order_id;
            $js_data['order_key'] =  $order_key;

            wp_localize_script('tm-step3-js', 'TM_GLOBAL', $js_data);

            include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step3.php';

        } 

        
        else {
            echo "<p class='tm-error'>Invalid step.</p>";
        }

        return ob_get_clean();
    }

    public static function shortcode_my_trademarks() {
        if (!is_user_logged_in()) {
            return "<p class='tm-error'>Please login to view your trademark dashboard.</p>";
        }

        wp_enqueue_style('tm-frontend-css');
        wp_enqueue_script('tm-my-trademarks-js');

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/my-trademarks.php';
        return ob_get_clean();
    }
}
