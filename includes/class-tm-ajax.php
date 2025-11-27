<?php
if (!defined('ABSPATH')) exit;

class TM_Ajax {

    public static function init() {
        add_action('wp_ajax_tm_calc_price', [__CLASS__, 'calc_price']);
        add_action('wp_ajax_nopriv_tm_calc_price', [__CLASS__, 'calc_price']);
        add_action('wp_ajax_tm_add_to_cart_step1', '__return_false'); // handled in WooCommerce class

    }

    /**
     * AJAX: Calculate total price for any step (1 or 2)
     * POST:
     *  country (id)
     *  type (word|figurative|combined)
     *  classes (int)
     *  step (1|2)
     */
    public static function calc_price() {
        check_ajax_referer('tm_nonce', 'nonce');

        $country_id = intval($_POST['country'] ?? 0);
        $type = sanitize_text_field($_POST['type'] ?? 'word');

        if ($type !== 'word') {
            $type = 'word'; // fallback – only Word Mark has pricing
        }

        $classes    = max(1, intval($_POST['classes'] ?? 1));
        $step       = max(1, intval($_POST['step'] ?? 1)); // ✅ allow step1 & step2

        if (!$country_id || !$type) {
            wp_send_json_error(['message' => 'Invalid request']);
        }

        // only allow step 1 or 2 in current flow
        if ($step < 1 || $step > 2) $step = 1;

        // ✅ Get correct step price row for that type
        $row = TM_Country_Prices::get_price_row($country_id, $type, $step);

        if (!$row) {
            wp_send_json_error(['message' => 'Price not found for this step']);
        }

        $one = floatval($row->price_one_class);
        $add = floatval($row->price_add_class);

        $priority_fee = floatval($row->priority_claim_fee ?? 0);
        $poa_fee      = floatval($row->poa_late_fee ?? 0);

        $currency = $row->currency ?: 'USD';

        $extra_classes = max(0, $classes - 1);
        $total = $one + ($extra_classes * $add);



        wp_send_json_success([
            'one'         => $one,
            'add'         => $add,
            'classes'     => $classes,
            'extra'       => $extra_classes,
            'total'       => $total,
            'currency'    => $currency,
            'step'        => $step,
            'type'        => $type,
            // NEW FIELDS
            'priority_claim_fee' => $priority_fee,
            'poa_late_fee' => $poa_fee,
        ]);
    }
}
