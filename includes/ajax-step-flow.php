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

    /** -------------------------------------------
     *  REQUIRED FIELDS (common for both modes)
     * ------------------------------------------- */
    $country_id  = intval($data['country_id'] ?? 0);
    $country_iso = sanitize_text_field($data['country_iso'] ?? '');
    $type        = sanitize_text_field($data['trademark_type'] ?? 'word');
    $text        = sanitize_text_field($data['mark_text'] ?? '');
    $tm_from     = sanitize_text_field($data['tm_from'] ?? '');
    $logo_id     = intval($data['logo_id'] ?? 0);
    $logo_url    = sanitize_text_field($data['logo_url'] ?? '');
    $is_additional = intval($data['tm_additional_class'] ?? 0);

    if (!$country_id || !$country_iso) {
        wp_send_json_error(['message' => 'Missing country information.']);
    }

    /** -------------------------------------------
     *  PRODUCT ID
     * ------------------------------------------- */
    $product_id = (int) TM_MASTER_PRODUCT_ID;

    if (!$product_id) {
        wp_send_json_error(['message'=>"Step-1 master product not configured."]);
    }

    /** -------------------------------------------
     *  BUILD CART META (flat meta structure)
     * ------------------------------------------- */

    // -------- ADD YOUR CALCULATED PRICE INTO CART --------
    $cart_item_data['tm_total_price'] = floatval($data['total_price'] ?? 0);
    $cart_item_data['tm_currency']    = sanitize_text_field($data['currency'] ?? 'USD');


    $cart_item_data = [
        'tm_step'            => 1,
        'country_id'         => $country_id,
        'country_iso'        => $country_iso,
        'tm_type'            => $type,
        'tm_text'            => $text,
        'tm_from'            => $tm_from,
        'tm_logo_id'         => $logo_id,
        'tm_logo_url'        => $logo_url,
        'tm_additional_class'=> $is_additional,
            // ⭐ FIX: store calculated total
    'tm_total_price'     => floatval($data['total_price'] ?? 0),
    'tm_currency'        => sanitize_text_field($data['currency'] ?? 'USD'),
    ];

    error_log("is_additional ". $is_additional);

    /** -------------------------------------------
     *  MODE 1: ADDITIONAL CLASS MODE (tm_additional_class=1)
     * ------------------------------------------- */
    if ($is_additional == 1) {

        // Total number of classes  
        $cart_item_data['tm_class_count'] = intval($data['classes'] ?? 1);

        // Class numbers array
        $class_list_json = $data['class_list'] ?? '[]';
        $cart_item_data['tm_class_list'] = $class_list_json;

        // Class goods/services text
        $class_details_json = $data['class_details'] ?? '[]';
        $cart_item_data['tm_class_details'] = $class_details_json;

        // Priority + POA
        $cart_item_data['tm_priority'] = sanitize_text_field($data['tm_priority'] ?? '0');
        $cart_item_data['tm_poa']      = sanitize_text_field($data['tm_poa'] ?? 'normal');

    }
    /** -------------------------------------------
     *  MODE 2: SIMPLE ONE-CLASS MODE
     * ------------------------------------------- */
    /**
     * MODE 2 — SIMPLE ONE-CLASS MODE (tm_additional_class = 0)
     */
    else {

        $goods = sanitize_textarea_field($data['goods'] ?? '');
        $cart_item_data['tm_goods'] = $goods;

        // Always 1 class for simple mode
        $cart_item_data['tm_class_count'] = 1;
        $cart_item_data['tm_class_list']  = json_encode([1]);
        $cart_item_data['tm_class_details'] = json_encode([
            ['class' => 1, 'goods' => $goods]
        ]);

        /**
         * ★ BACKEND PRICE CALCULATION (STEP-1 PRICE — ONE CLASS)
         * This fixes normal trademark pricing being 0.
         */
        if (class_exists('TM_WooCommerce')) {

            $backend_price = TM_WooCommerce::compute_item_total(
                $country_id,      // country
                $type,            // word / figurative / combined
                1,                // step = 1 (simple registration)
                1,                // always 1 class
                0                 // fallback
            );

            error_log("tm_add_to_cart_step1 backend price calc: {$backend_price}");

            // Save into cart meta so Step2 reads correct price
            $cart_item_data['tm_total_price'] = $backend_price;
            $cart_item_data['tm_currency'] = 'USD';
        }
    }


    error_log("tm_add_to_cart_step1 add to cart data: ".print_r($cart_item_data, true));


     // -------------------------------------------
    // SECURE BACKEND PRICE CALC
    // -------------------------------------------
    $class_count   = intval($cart_item_data['tm_class_count'] ?? 1);
    $priority_val  = $cart_item_data['tm_priority'] ?? '0';
    $poa_val       = $cart_item_data['tm_poa'] ?? 'normal';

    list($secure_total, $secure_currency) = tm_backend_calculate_total(
        $country_id,
        $type,
        $is_additional,
        $class_count,
        $priority_val,
        $poa_val
    );

    if ($is_additional !== 1) {

  
}


    // store trusted total in cart meta
    $cart_item_data['tm_total_price'] = $secure_total;
    $cart_item_data['tm_currency']    = $secure_currency;

    error_log("tm_add_to_cart_step1 backend total: {$secure_total} {$secure_currency}");


    /** -------------------------------------------
     *  ADD TO CART
     * ------------------------------------------- */
    WC()->cart->add_to_cart(
        $product_id,
        1,
        0,
        [],
        $cart_item_data
    );

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



if (!function_exists('tm_backend_calculate_total')) {
    /**
     * Secure server-side price calculation
     * - Uses DB prices (TM_Country_Prices)
     * - Includes extra classes, Priority, POA
     */
    function tm_backend_calculate_total($country_id, $type, $is_additional, $class_count, $priority_value, $poa_value) {

        if (!class_exists('TM_Country_Prices')) {
            return [0, 'USD'];
        }

        // Step 1 = normal, Step 2 = additional-class mode
        $step = ($is_additional == 1) ? 2 : 1;

        $row = TM_Country_Prices::get_price_row($country_id, $type, $step);
        if (!$row) {
            return [0, 'USD'];
        }

        $one           = floatval($row->price_one_class);
        $add           = floatval($row->price_add_class);
        $priority_fee  = floatval($row->priority_claim_fee ?? 0);
        $poa_fee       = floatval($row->poa_late_fee ?? 0);
        $currency      = $row->currency ?: 'USD';

        $classes = max(1, (int) $class_count);
        $extra   = max(0, $classes - 1);

        // Base = first class + extra classes
        $base_total = $one + ($extra * $add);
        $total      = $base_total;

        // Only in additional-class mode we add these extras
        if ($is_additional == 1) {
            if ($priority_value === '1') {
                $total += $priority_fee;
            }
            if ($poa_value === 'late') {
                $total += $poa_fee;
            }
        }

        return [$total, $currency];
    }
}
