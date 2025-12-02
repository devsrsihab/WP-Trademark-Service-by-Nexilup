<?php
if (!defined('ABSPATH')) exit;

/**
 * STEP-1 Add to cart
 */
add_action('wp_ajax_tm_add_to_cart_step1', 'tm_add_to_cart_step1');
add_action('wp_ajax_nopriv_tm_add_to_cart_step1', 'tm_add_to_cart_step1');


function tm_add_to_cart_step1() {

    check_ajax_referer('tm_nonce', 'nonce');

    if (!class_exists('WC_Cart')) {
        wp_send_json_error(['message' => 'WooCommerce not active.']);
    }

    /** ----------------------------------------------------
     * GET DATA SAFELY
     * ---------------------------------------------------- */
    $data = isset($_POST['data']) ? (array) $_POST['data'] : [];

    if (empty($data)) {
        wp_send_json_error(['message' => 'Missing step-1 data']);
    }

    $country_id  = intval($data['country_id'] ?? 0);
    $country_iso = sanitize_text_field($data['country_iso'] ?? '');
    $type        = sanitize_text_field($data['trademark_type'] ?? 'word');

    $mark_text   = sanitize_text_field($data['mark_text'] ?? '');
    $goods       = sanitize_textarea_field($data['goods'] ?? '');
    $logo_id     = intval($data['logo_id'] ?? 0);
    $logo_url    = sanitize_text_field($data['logo_url'] ?? '');

    $is_additional = intval($data['tm_additional_class'] ?? 0);
    $classes       = intval($data['classes'] ?? 1);

    if (!$country_id || !$country_iso) {
        wp_send_json_error(['message' => 'Country missing']);
    }

    /** ----------------------------------------------------
     * MASTER PRODUCT
     * ---------------------------------------------------- */
    // FIXED: make method public in TM_WooCommerce OR call via option
    $product_id = get_option('tm_master_product_id');
    if (!$product_id) {
        wp_send_json_error(['message' => 'Master product missing']);
    }

    /** ----------------------------------------------------
     * WIPE OLD TM ITEMS
     * ---------------------------------------------------- */
    // foreach (WC()->cart->get_cart() as $key => $item) {
    //     if (isset($item['tm_data'])) {
    //         WC()->cart->remove_cart_item($key);
    //     }
    // }

    /** ----------------------------------------------------
     * SECURE BACKEND PRICE (ALWAYS RE-CALCULATE)
     * ---------------------------------------------------- */
    list($secure_total, $secure_currency) = tm_backend_calculate_total(
        $country_id,
        $type,
        $is_additional,
        $classes,
        $data['tm_priority'] ?? '0',
        $data['tm_poa'] ?? 'normal'
    );

    /** ----------------------------------------------------
     * STORE META
     * ---------------------------------------------------- */
    $tm_data = [
        'country_id'           => $country_id,
        'country_iso'          => $country_iso,
        'tm_type'              => $type,
        'mark_text'            => $mark_text,
        'tm_from'              => '',  
        'tm_goods'             => $goods,
        'tm_logo_id'           => $logo_id,
        'tm_logo_url'          => $logo_url,

        'tm_additional_class'  => $is_additional,
        'tm_priority'          => sanitize_text_field($data['tm_priority'] ?? '0'),
        'tm_poa'               => sanitize_text_field($data['tm_poa'] ?? 'normal'),

        'tm_class_count'       => $classes,
        'tm_class_list'        => json_encode($data['class_list'] ?? []),
        'tm_class_details'     => json_encode($data['class_details'] ?? []),

        'tm_total_price'       => $secure_total,
        'tm_currency'          => $secure_currency,

        'step'                 => 1
    ];

    /** ----------------------------------------------------
     * FLATTENED FORMAT
     * ---------------------------------------------------- */
    $cart_item_data = [
        'tm_data'       => $tm_data,
        'country_id'    => $country_id,
        'country_iso'   => $country_iso,
        'tm_type'       => $type,
        'tm_text'       => $mark_text,
        'tm_goods'      => $goods,
        'tm_logo_id'    => $logo_id,
        'tm_logo_url'   => $logo_url,
        'tm_class_count'=> $classes,
        'tm_total_price'=> $secure_total,
        'tm_currency'   => $secure_currency,
        'tm_step'       => 1,
    ];

    /** ----------------------------------------------------
     * ADD TO CART
     * ---------------------------------------------------- */
    $cart_key = WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);

    if (!$cart_key) {
        wp_send_json_error(['message' => 'Add to cart failed']);
    }

    wp_send_json_success(['message' => 'added']);
}


/**
 * SECURE BACKEND PRICE CALCULATION — REQUIRED FUNCTION
 */
function tm_backend_calculate_total($country_id, $type, $is_additional, $class_count, $priority_value = '0', $poa_value = 'normal')
{
    if (!class_exists('TM_Country_Prices')) {
        return [0, 'USD'];
    }

    $step = ($is_additional == 1) ? 2 : 1;

    if ($step === 1) {
        $row = TM_Country_Prices::get_step1_price_row($country_id);
    } else {
        $row = TM_Country_Prices::get_priority_price_row($country_id, 'registration');
    }

    if (!$row) {
        return [0, 'USD'];
    }

    $one       = floatval($row->first_class_fee ?? 0);
    $add       = floatval($row->additional_class_fee ?? 0);
    $priority  = floatval($row->priority_claim_fee ?? 0);
    $poa       = floatval($row->poa_late_fee ?? 0);
    $currency  = $row->currency ?: 'USD';

    $classes   = max(1, intval($class_count));
    $extra     = max(0, $classes - 1);

    $total = $one + ($extra * $add);

    if ($is_additional == 1) {
        if ($priority_value == '1') {
            $total += $priority;
        }
        if ($poa_value === 'late') {
            $total += $poa;
        }
    }

    return [$total, $currency];
}
