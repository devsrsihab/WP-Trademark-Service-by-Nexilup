<?php
if (!defined('ABSPATH')) exit;

class TM_Service_Form {

    /**
     * Detect which step should load based on rewrite query vars.
     * Flow:
     *  - /tm/trademark-choose/order-form/  => Step 1
     *  - /tm/trademark-registration/order-form/        => Step 2 (Confirm Order)
     *  - Step 3 = WooCommerce Thank You page
     */
    public static function detect_initial_step() {

        if (get_query_var('tm_study_order')) return 1;
        if (get_query_var('tm_reg_order'))   return 2;

        $step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        if ($step < 1 || $step > 2) $step = 1;

        if (!empty($_GET['tm_order_received']) && !empty($_GET['key'])) {
            return 3; // Step 3
        }


        return $step;
    }

    public static function get_country_from_request() {

        $iso = isset($_GET['country']) ? strtoupper(sanitize_text_field($_GET['country'])) : '';
        if (!$iso) return null;

        $countries = TM_Database::get_countries();
        if (!$countries) return null;

        foreach ($countries as $c) {
            if (strtoupper($c->iso_code) === $iso) {
                return $c;
            }
        }

        return null;
    }
}
