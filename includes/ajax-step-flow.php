<?php
if (!defined('ABSPATH')) exit;

/**
 * STEP-1 Add to cart
 */
add_action('wp_ajax_tm_add_to_cart_step1', 'tm_add_to_cart_step1');
add_action('wp_ajax_nopriv_tm_add_to_cart_step1', 'tm_add_to_cart_step1');

function tm_add_to_cart_step1(){

    check_ajax_referer('tm_nonce','nonce');

    if (!class_exists('WC_Cart')) {
        wp_send_json_error(['message' => 'WooCommerce not active.']);
    }

    $data = $_POST['data'] ?? [];

    $country_iso = sanitize_text_field($data['country_iso'] ?? '');
    $type        = sanitize_text_field($data['trademark_type'] ?? 'word');
    $text        = sanitize_text_field($data['mark_text'] ?? '');
    $tm_from     = sanitize_text_field($data['tm_from'] ?? '');
    $goods       = sanitize_textarea_field($data['goods'] ?? '');
    $country_id  = intval($data['country_id'] ?? 0);
    $logo_id     = intval($data['logo_id'] ?? 0);
    $logo_url    = sanitize_text_field($data['logo_url'] ?? '');

    // ✅ FIND product id for STEP-1 (Comprehensive Study)
    // You already have a product setup. Put your real product id here:
    // $product_id = (int) get_option('tm_step1_product_id') ?? TM_MASTER_PRODUCT_ID;
    $product_id = (int)  TM_MASTER_PRODUCT_ID;

    if (!$product_id) {
        wp_send_json_error(['message'=>"{$product_id} Step1 product not configured sfsd."]);
    }

    $cart_item_data = [
        'country_iso' => $country_iso,
        'country_id' => $country_id,
        'tm_type'    => $type,
        'tm_text'    => $text,
        'tm_from'    => $tm_from,
        'tm_goods'   => $goods,
        'tm_logo_id' => $logo_id,
        'tm_logo_url'=> $logo_url,
        'tm_step'    => 1,
    ];

    WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);

    wp_send_json_success(['message'=>'Added to cart']);
}


/**
 * STEP-2 Save selected gateway in WC session then redirect checkout
 */
add_action('wp_ajax_tm_set_gateway', 'tm_set_gateway');
add_action('wp_ajax_nopriv_tm_set_gateway', 'tm_set_gateway');

function tm_set_gateway(){

    check_ajax_referer('tm_nonce','nonce');

    $gateway = sanitize_text_field($_POST['gateway'] ?? '');

    if (!$gateway) {
        wp_send_json_error(['message'=>'No gateway selected']);
    }

    WC()->session->set('chosen_payment_method', $gateway);

    wp_send_json_success([
        'redirect' => wc_get_checkout_url()
    ]);
}
