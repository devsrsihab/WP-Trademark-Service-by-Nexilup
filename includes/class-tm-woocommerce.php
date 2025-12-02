<?php
if (!defined('ABSPATH')) exit;

class TM_WooCommerce {

    const PRODUCT_META_KEY = '_tm_master_product';

    public static function init() {

        add_action('init', [ __CLASS__, 'ensure_master_product' ]);

        add_action('wp_ajax_tm_add_to_cart_step1', [ __CLASS__, 'ajax_add_to_cart_step1' ]);
        add_action('wp_ajax_nopriv_tm_add_to_cart_step1', [ __CLASS__, 'ajax_add_to_cart_step1' ]);

        add_action('wp_ajax_tm_add_to_cart', [ __CLASS__, 'ajax_add_to_cart' ]);
        add_action('wp_ajax_nopriv_tm_add_to_cart', [ __CLASS__, 'ajax_add_to_cart' ]);

        add_action('woocommerce_before_calculate_totals', [ __CLASS__, 'override_dynamic_price' ], 9999);

        add_action('woocommerce_checkout_create_order_line_item', [ __CLASS__, 'save_order_item_meta' ], 10, 3);
        add_action('woocommerce_order_status_completed', [ __CLASS__, 'order_completed' ]);
        add_action('woocommerce_order_status_processing', [ __CLASS__, 'order_completed' ]);

        add_action('wp_ajax_tm_remove_cart_item', [__CLASS__, 'ajax_remove_cart_item']);
        add_action('wp_ajax_nopriv_tm_remove_cart_item', [__CLASS__, 'ajax_remove_cart_item']);

        add_action('wp_ajax_tm_update_cart_title', [__CLASS__, 'ajax_update_cart_title']);
        add_action('wp_ajax_nopriv_tm_update_cart_title', [__CLASS__, 'ajax_update_cart_title']);

        add_action('wp_ajax_tm_place_order', [ __CLASS__, 'ajax_place_order' ]);
        add_action('wp_ajax_nopriv_tm_place_order', [ __CLASS__, 'ajax_place_order' ]);

        add_action('wp_ajax_tm_load_payment_fields', [__CLASS__, 'ajax_load_payment_fields']);
        add_action('wp_ajax_nopriv_tm_load_payment_fields', [__CLASS__, 'ajax_load_payment_fields']);


    }


    public static function ajax_load_payment_fields() {

        check_ajax_referer('tm_nonce', 'nonce');

        $gateway_id = sanitize_text_field($_POST['gateway'] ?? '');

        if (!$gateway_id) {
            wp_send_json_error(['message' => 'No gateway provided']);
        }

        $gateways = WC()->payment_gateways()->payment_gateways();
        $gateway  = $gateways[$gateway_id] ?? null;

        if (!$gateway) {
            wp_send_json_error(['message' => 'Invalid payment method']);
        }

        ob_start();
        $gateway->payment_fields();
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }


    public static function ajax_place_order() {

        // -------------------------------------------------------------
        // GET COUNTRY ISO FROM CART (handles both tm_data and flat keys)
        // -------------------------------------------------------------
        $country_iso = '';

        foreach (WC()->cart->get_cart() as $ci) {
            if (!empty($ci['tm_data']['country_iso'])) {
                $country_iso = $ci['tm_data']['country_iso'];
                break;
            }
            if (!empty($ci['country_iso'])) {
                $country_iso = $ci['country_iso'];
                break;
            }
        }




        check_ajax_referer('tm_nonce', 'nonce');

        if (!WC()->cart || WC()->cart->is_empty()) {
            wp_send_json_error(['message' => 'Cart is empty.']);
        }

        $gateway_id = sanitize_text_field($_POST['gateway'] ?? '');
        if (!$gateway_id) {
            wp_send_json_error(['message' => 'No payment gateway selected.']);
        }
            WC()->cart->calculate_totals();
         WC()->cart->set_session();


        // Create order from current cart
        $order_id = WC()->checkout()->create_order([
            'payment_method' => $gateway_id
        ]);
      
        if (is_wp_error($order_id)) {
            wp_send_json_error(['message' => 'Order creation failed.']);
        }

        $order = wc_get_order($order_id);


        // Set customer info automatically (optional)
        $order->set_billing_email(wp_get_current_user()->user_email);
        $order->calculate_totals();
        $order->save();

        // Set chosen payment method
        $order->set_payment_method($gateway_id);
        $order->save();

        // Get gateway instance
        $gateways = WC()->payment_gateways()->payment_gateways();
        $gateway = $gateways[$gateway_id] ?? null;

        if (!$gateway) {
            wp_send_json_error(['message' => 'Invalid payment method.']);
        }

        // Process payment
        $result = $gateway->process_payment($order_id);

        if ($result && isset($result['result']) && $result['result'] === 'success') {
        wp_send_json_success([
            'redirect' => home_url("/tm/trademark-confirmation/order-review/?tm_order_received={$order_id}&country={$country_iso}&key={$order->get_order_key()}")
        ]);

        }

        wp_send_json_error(['message' => 'Payment failed or gateway error.']);
    }


    

    public static function ensure_master_product() {

        if (defined('TM_MASTER_PRODUCT_ID') && TM_MASTER_PRODUCT_ID) {
            return;
        }

        $id = get_option('tm_master_product_id');
        if ($id && get_post($id)) return;

        $product_id = wp_insert_post([
            'post_title'  => 'Trademark Service (Master Product)',
            'post_type'   => 'product',
            'post_status' => 'publish'
        ]);

        if (!$product_id) return;

        update_post_meta($product_id, '_price', 0);
        update_post_meta($product_id, '_virtual', 'yes');
        update_post_meta($product_id, '_sold_individually', 'yes');

        update_option('tm_master_product_id', $product_id);
    }

    public  static function get_master_product_id() {
        if (defined('TM_MASTER_PRODUCT_ID') && TM_MASTER_PRODUCT_ID) {
            return (int) TM_MASTER_PRODUCT_ID;
        }
        return (int) get_option('tm_master_product_id');
    }

    /**
     * STEP 1 — Add to cart
     * also store flattened keys for backward compatibility
     */
    public static function ajax_add_to_cart_step1() {

        check_ajax_referer('tm_nonce', 'nonce');

        $data = isset($_POST['data']) ? (array) $_POST['data'] : [];
        if (empty($data)) {
            wp_send_json_error(['message' => 'Step1 data missing.']);
        }

        $product_id = self::get_master_product_id();
        if (!$product_id) {
            wp_send_json_error(['message' => 'Step1 product not configured.']);
        }

        // foreach (WC()->cart->get_cart() as $key => $item) {
        //     if (isset($item['tm_data']) || isset($item['tm_type'])) {
        //         WC()->cart->remove_cart_item($key);
        //     }
        // }




        $tm_data = [
            // 'country'     => sanitize_text_field($data['country_iso'] ?? ''),
            'tm_country'  => intval($data['country']),


            'type'        => sanitize_text_field($data['trademark_type'] ?? 'word'),
            'classes'     => intval($data['classes'] ?? 1),

            // Step 2 Advanced Data
            'tm_additional_class' => intval($data['tm_additional_class'] ?? 0),
            'class_list'          => isset($data['class_list']) ? json_decode($data['class_list'], true) : [],
            'class_details'       => isset($data['class_details']) ? json_decode($data['class_details'], true) : [],
            'tm_priority'         => sanitize_text_field($data['tm_priority'] ?? '0'),
            'tm_poa'              => sanitize_text_field($data['tm_poa'] ?? 'normal'),

            'total_price' => floatval($data['total_price'] ?? 0),
            'currency'    => sanitize_text_field($data['currency'] ?? 'USD'),
            'goods'       => sanitize_text_field($data['goods'] ?? ''),
            'logo_id'     => intval($data['logo_id'] ?? 0),
            'logo_url'    => sanitize_text_field($data['logo_url'] ?? ''),
            'mark_text'   => sanitize_text_field($data['mark_text'] ?? ''),
            'tm_from'     => sanitize_text_field($data['tm_from'] ?? ''),
            'step'        => 1,
            'step_data'   => $data,
        ];

        $cart_item_data = [
            'tm_data'     => $tm_data,

            // flattened keys (so Step2 works even if tm_data missing)
            'tm_country'  => $tm_data['tm_country'],
            'tm_type'     => $tm_data['type'],
            'tm_text'     => $tm_data['mark_text'],
            'tm_from'     => $tm_data['tm_from'],
            'tm_goods'    => $tm_data['goods'],
            'tm_logo_id'  => $tm_data['logo_id'],
            'tm_logo_url' => $tm_data['logo_url'],

            'tm_classes'  => $tm_data['classes'],
            'tm_total'    => $tm_data['total_price'],
            'tm_currency' => $tm_data['currency'],
            'tm_step'     => $data['tm_additional_class1'],
        ];

        $cart_key = WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);

        if (!$cart_key) {
            wp_send_json_error(['message' => 'Add to cart failed (step1).']);
        }

        wp_send_json_success(['message' => 'added']);
    }

    public static function ajax_add_to_cart() {

        check_ajax_referer('tm_nonce', 'nonce');

        $product_id = self::get_master_product_id();
        if (!$product_id) {
            wp_send_json_error(['message' => 'Master product missing']);
        }

        // foreach (WC()->cart->get_cart() as $key => $item) {
        //     if (isset($item['tm_data']) || isset($item['tm_type'])) WC()->cart->remove_cart_item($key);
        // }

        $tm_data = [
            // 'country'     => sanitize_text_field($_POST['country']),
            'country_iso'     => intval($_POST['country_iso'] ?? 0),
            'country_id'     => intval($_POST['country_id'] ?? 0),
            'type'        => sanitize_text_field($_POST['type']),
            'classes'     => intval($_POST['classes']),
            'total_price' => floatval($_POST['total']),
            'currency'    => sanitize_text_field($_POST['currency']),
            'goods'       => sanitize_text_field($_POST['goods']),
            'logo'        => sanitize_text_field($_POST['tm_logo_url']),
            'owner'       => isset($_POST['owner']) ? $_POST['owner'] : [],
            'step'        => intval($_POST['step'] ?? 2),
            'step_data'   => $_POST['steps']
        ];

        $cart_item_data = [
            'tm_data'     => $tm_data,

            'country_id'  => $tm_data['country_id'] ,
            'country_iso'  => $tm_data['country_iso'] ,
            'tm_type'     => $tm_data['type'],
            'tm_text'     => sanitize_text_field($_POST['mark_text'] ?? ''),
            'tm_from'     => sanitize_text_field($_POST['tm_from'] ?? ''),
            'tm_goods'    => $tm_data['goods'],
            'tm_logo'     => $tm_data['tm_logo_url'],
            'tm_classes'  => $tm_data['classes'],
            'tm_total'    => $tm_data['total_price'],
            'tm_currency' => $tm_data['currency'],
            'tm_step'     => $tm_data['step'],
        ];

        $cart_key = WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);

        if (!$cart_key) {
            wp_send_json_error(['message' => 'Add to cart failed']);
        }

        wp_send_json_success([
            'redirect' => wc_get_checkout_url()
        ]);
    }

    /**
     * Helper: compute correct dynamic price from DB
     */
    public  static function compute_item_total($country_id, $type, $step, $classes, $item = []) {

        if (!class_exists('TM_Country_Prices')) {
            return floatval($item['tm_total'] ?? 0);
        }

        $row = TM_Country_Prices::get_price_row($country_id, $type, $step);
        if (!$row) {
            return floatval($item['tm_total'] ?? 0);
        }

        $one  = floatval($row->price_one_class);
        $add  = floatval($row->price_add_class);

        $priority_fee = floatval($row->priority_claim_fee ?? 0);
        $poa_fee      = floatval($row->poa_late_fee ?? 0);

        $extra = max(0, intval($classes) - 1);
        $total = $one + ($extra * $add);

        // ------------------------------
        // ENHANCED STEP-2 FEES
        // ------------------------------
        $is_extra = intval($item['tm_additional_class'] ?? $item['tm_data']['tm_additional_class'] ?? 0);

        if ($is_extra == 1) {

            // Priority claim
            $priority_selected = $item['tm_priority'] ?? $item['tm_data']['tm_priority'] ?? "0";
            if ($priority_selected == "1") {
                $total += $priority_fee;
            }

            // POA Late filing
            $poa_selected = $item['tm_poa'] ?? $item['tm_data']['tm_poa'] ?? "normal";
            if ($poa_selected === "late") {
                $total += $poa_fee;
            }
        }

        return max(1, $total);
    }


        /**
         * Apply dynamic price so WC cart total matches your DB price
         */
    public static function override_dynamic_price($cart)
    {
        if (is_admin() && !wp_doing_ajax()) return;
        if (!$cart) return;

        foreach ($cart->get_cart() as $key => $item) {

            //-----------------------------------------
            // 1) EXTRACT META (nested or flattened)
            //-----------------------------------------
            $tm = $item['tm_data'] ?? $item;

            $country_id = intval(
                $tm['country_id']
                ?? $tm['tm_country']
                ?? 0
            );

            $type = sanitize_text_field(
                $tm['type']
                ?? $tm['tm_type']
                ?? 'word'
            );

            $step = intval(
                $tm['step']
                ?? $tm['tm_step']
                ?? 1
            );

            $classes = intval(
                $tm['classes']
                ?? $tm['tm_class_count']
                ?? $tm['tm_classes']
                ?? 1
            );

            //-----------------------------------------
            // 2) SECURE BACKEND TOTAL
            // prefer "tm_total_price" stored earlier
            //-----------------------------------------
            $stored_total = floatval(
                $tm['tm_total_price']
                ?? $tm['total_price']
                ?? $tm['tm_total']
                ?? 0
            );

            //-----------------------------------------
            // 3) CHOOSE FINAL PRICE
            //-----------------------------------------
            if ($stored_total > 0) {
                $total = $stored_total; // safest
            } else {
                // legacy fallback = compute from DB
                $total = self::compute_item_total(
                    $country_id,
                    $type,
                    $step,
                    $classes,
                    $stored_total
                );
            }

            //-----------------------------------------
            // 4) APPLY PRICE
            //-----------------------------------------
            $item['data']->set_price($total);

            //-----------------------------------------
            // 5) SYNC BACK INTO CART ARRAY
            //-----------------------------------------
            if (!empty($item['tm_data'])) {
                // nested format
                $cart->cart_contents[$key]['tm_data']['tm_total_price'] = $total;
            }

            // flattened format
            $cart->cart_contents[$key]['tm_total_price'] = $total;
            $cart->cart_contents[$key]['tm_total']       = $total;
        }
    }





    public static function save_order_item_meta($item, $cart_item_key, $values) {

        if (!isset($values['tm_data']) && !isset($values['tm_type'])) return;

        $tm = isset($values['tm_data']) ? $values['tm_data'] : [
            'country_id'     => $values['country_id'] ?? '',
            'country_iso'     => $values['country_iso'] ?? '',
            'type'        => $values['tm_type'] ?? '',
            'classes'     => $values['tm_classes'] ?? 1,
            'total_price' => $values['tm_total'] ?? 0,
            'currency'    => $values['tm_currency'] ?? 'USD',
            'goods'       => $values['tm_goods'] ?? '',
            'logo_id'     => $values['tm_logo_id'] ?? 0,
            'logo_url'    => $values['tm_logo_url'] ?? '',

            'mark_text'   => $values['tm_text'] ?? '',
            'tm_from'     => $values['tm_from'] ?? '',
            'step'        => $values['tm_step'] ?? 1,
        ];

        foreach ($tm as $k => $v) {
            $item->add_meta_data("tm_$k", $v);
        }

        $tm_id = TM_Trademarks::create_from_order($item, $tm);
        $item->add_meta_data('tm_trademark_id', $tm_id, true);
    }

    public static function order_completed($order_id) {

        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $item) {
            $tm_id = $item->get_meta('tm_trademark_id');
            if ($tm_id) {
                TM_Trademarks::update_status($tm_id, 'paid');
                // TM_Email::send_payment_confirmation($order, $tm_id);
            }
        }
    }

    public static function ajax_remove_cart_item() {

        check_ajax_referer('tm_nonce', 'nonce');

        if (!WC()->cart) {
            wp_send_json_error(['message' => 'Cart not ready']);
        }

        $key = sanitize_text_field($_POST['cart_key'] ?? '');
        if (!$key || !isset(WC()->cart->cart_contents[$key])) {
            wp_send_json_error(['message' => 'Invalid cart key']);
        }

        WC()->cart->remove_cart_item($key);
        WC()->cart->calculate_totals();
        WC()->cart->set_session();

        wp_send_json_success([
            'total_html' => WC()->cart->get_total()
        ]);
    }

    /**
     * AJAX: Update title for BOTH formats
     */
    public static function ajax_update_cart_title() {

        check_ajax_referer('tm_nonce', 'nonce');

        if (!WC()->cart) {
            wp_send_json_error(['message' => 'Cart not ready']);
        }

        $key   = sanitize_text_field($_POST['cart_key'] ?? '');
        $title = sanitize_text_field($_POST['title'] ?? '');

        if (!$key || !isset(WC()->cart->cart_contents[$key])) {
            wp_send_json_error(['message' => 'Invalid cart key']);
        }
        if (!$title) {
            wp_send_json_error(['message' => 'Title empty']);
        }

        // support nested OR flattened
        if (isset(WC()->cart->cart_contents[$key]['tm_data'])) {
            WC()->cart->cart_contents[$key]['tm_data']['mark_text']       = $title;
            WC()->cart->cart_contents[$key]['tm_data']['editable_title']  = $title;
        }

        // flattened
        WC()->cart->cart_contents[$key]['tm_text'] = $title;

        WC()->cart->set_session();
        WC()->cart->calculate_totals();

        wp_send_json_success([
            'title'      => $title,
            'total_html' => WC()->cart->get_total()
        ]);
    }
}
