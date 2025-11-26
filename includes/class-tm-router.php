<?php
if (!defined('ABSPATH')) exit;

class TM_Router {

    public static function init() {
        add_filter('template_include', [__CLASS__, 'load_step_templates']);
          return;
    }

    public static function load_step_templates($template) {

        global $post;
        if (!$post) return $template;

        $slug = $post->post_name;

        switch ($slug) {

            case 'order-form':
                if (get_query_var('tm_study_order')) {
                    return WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step1.php';
                }
                if (get_query_var('tm_reg_order')) {
                    return WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step2.php';
                }
                break;

            case 'active-trademarks':
                return WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/active-trademarks.php';

            case 'order-review':
                return WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/step4.php';
        }

        return $template;
    }
}
