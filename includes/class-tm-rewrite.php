<?php
if (!defined('ABSPATH')) exit;

class TM_Rewrite {

    public static function init() {
        add_action('init', [__CLASS__, 'routes']);
        add_filter('query_vars', [__CLASS__, 'register_query_vars']);
        add_filter('template_include', [__CLASS__, 'load_templates'], 99);
    }

    public static function register_query_vars($vars) {
        $vars[] = 'tm_study_order';
        $vars[] = 'tm_reg_order';
        $vars[] = 'tm_active_trademarks';
        $vars[] = 'tm_order_received';
        return $vars;
    }

    public static function routes() {

        add_rewrite_tag('%tm_study_order%', '([0-1])');
        add_rewrite_tag('%tm_reg_order%', '([0-1])');
        add_rewrite_tag('%tm_active_trademarks%', '([0-1])');
        add_rewrite_tag('%tm_order_received%', '([0-1])');

        add_rewrite_rule(
            '^tm/trademark-choose/order-form/?$',
            'index.php?tm_study_order=1',
            'top'
        );

        add_rewrite_rule(
            '^tm/trademark-registration/order-form/?$',
            'index.php?tm_reg_order=1',
            'top'
        );

        add_rewrite_rule(
            '^myaccount/my-trademarks/active-trademarks/?$',
            'index.php?tm_active_trademarks=1',
            'top'
        );

        add_rewrite_rule(
            '^tm/trademark-confirmation/order-review/?$',
            'index.php?tm_order_received=1',
            'top'
        );
    }

    public static function load_templates($template) {
        error_log('TM_Rewrite::load_templates called'. get_query_var('tm_order_received'));

        if (get_query_var('tm_study_order')) {
            return WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/study-order-form.php';
        }

        if (get_query_var('tm_reg_order')) {
            return WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/registration-order-form.php';
        }

        if (get_query_var('tm_active_trademarks')) {
            return WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/active-trademarks.php';
        }

        if (get_query_var('tm_order_received')) {
            return WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/order-review.php';
        }

        return $template;
    }
}
