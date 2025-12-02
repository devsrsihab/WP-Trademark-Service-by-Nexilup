<?php
if (!defined('ABSPATH')) exit;

class TM_Ajax {

    public static function init() {
        add_action('wp_ajax_tm_calc_price',       [__CLASS__, 'calc_price']);
        add_action('wp_ajax_nopriv_tm_calc_price', [__CLASS__, 'calc_price']);
        add_action('wp_ajax_tm_add_to_cart_step1', '__return_false');
    }

    /**
     * AJAX Price Calculator (Step1 + Step2)
     *
     * POST:
     *  country  (int)
     *  type     (word|figurative|combined)
     *  classes  (int)
     *  step     (1|2)
     */
    // public static function calc_price() {

    //     check_ajax_referer('tm_nonce', 'nonce');

    //     $country_id = intval($_POST['country'] ?? 0);
    //     $type       = sanitize_text_field($_POST['type'] ?? 'word');
    //     $classes    = max(1, intval($_POST['classes'] ?? 1));
    //     $step       = max(1, intval($_POST['step'] ?? 1));

    //     if (!$country_id) {
    //         wp_send_json_error(['message' => 'Invalid country']);
    //     }

    //     // Normalize type
    //     if (!in_array($type, ['word', 'figurative', 'combined'])) {
    //         $type = 'word';
    //     }

    //     // Only steps 1 or 2
    //     if ($step > 2) $step = 1;

    //     // Load correct price rule
    //     $row = TM_Country_Prices::get_price_row($country_id, $type, $step);

    //     if (!$row) {
    //         wp_send_json_error(['message' => 'Price not found']);
    //     }

    //     // Base pricing
    //     $first_fee = floatval($row->first_class_fee);
    //     $add_fee   = floatval($row->additional_class_fee);

    //     // Multiply for Combined mark
    //     $mult = ($type === 'combined') ? 2 : 1;

    //     $first_fee *= $mult;
    //     $add_fee   *= $mult;

    //     // Optional rule-based extras
    //     $priority_fee = floatval($row->priority_claim_fee ?? 0);
    //     $poa_fee      = floatval($row->poa_late_fee ?? 0);

    //     $currency = $row->currency ?: 'USD';

    //     // Class math
    //     $extra_classes = max(0, $classes - 1);
    //     $total = $first_fee + ($extra_classes * $add_fee);

    //     wp_send_json_success([
    //         'country_id' => $country_id,
    //         'type'       => $type,
    //         'step'       => $step,

    //         'one'        => $first_fee,
    //         'add'        => $add_fee,
    //         'classes'    => $classes,
    //         'extra'      => $extra_classes,

    //         'total'      => $total,
    //         'currency'   => $currency,

    //         'priority_claim_fee' => $priority_fee,
    //         'poa_late_fee'       => $poa_fee,
    //     ]);
    // }

    public static function calc_price() {

    check_ajax_referer('tm_nonce', 'nonce');

    $country_id = intval($_POST['country'] ?? 0);
    $type       = sanitize_text_field($_POST['type'] ?? 'word');
    $classes    = max(1, intval($_POST['classes'] ?? 1));
    $step       = max(1, intval($_POST['step'] ?? 1));

    if (!$country_id) {
        wp_send_json_error(['message' => 'Invalid country']);
    }

    // Normalize type
    if (!in_array($type, ['word', 'figurative', 'combined'])) {
        $type = 'word';
    }

    // Only steps 1 or 2
    if ($step > 2) $step = 1;

    // -----------------------------
    // STEP-1 MUST USE FILING ONLY!
    // -----------------------------
    if ($step === 1) {
        $row = TM_Country_Prices::get_priority_price_row_filing_only($country_id);
    } else {
        // Step-2 uses full priority chain
        $row = TM_Country_Prices::get_priority_price_row($country_id);
    }

    if (!$row) {
        wp_send_json_error(['message' => 'Price not found']);
    }

    // Base fees
    $first_fee = floatval($row->first_class_fee);
    $add_fee   = floatval($row->additional_class_fee);

    // Combined mark multiplier
    $mult = ($type === 'combined') ? 2 : 1;
    $first_fee *= $mult;
    $add_fee   *= $mult;

    $priority_fee = floatval($row->priority_claim_fee ?? 0);
    $poa_fee      = floatval($row->poa_late_fee ?? 0);
    $currency     = $row->currency ?: 'USD';

    // Class math
    $extra_classes = max(0, $classes - 1);
    $total = $first_fee + ($extra_classes * $add_fee);

    wp_send_json_success([
        'country_id' => $country_id,
        'type'       => $type,
        'step'       => $step,

        'one'        => $first_fee,
        'add'        => $add_fee,
        'classes'    => $classes,
        'extra'      => $extra_classes,

        'total'      => $total,
        'currency'   => $currency,

        'priority_claim_fee' => $priority_fee,
        'poa_late_fee'       => $poa_fee,
    ]);
}

}
