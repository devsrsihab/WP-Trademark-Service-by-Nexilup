<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TM_WooCommerce {

    public static function init() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            // WooCommerce not active, nothing to do.
            return;
        }

        // Add hooks here later for:
        // - adding master product to cart with dynamic price
        // - saving order meta
        // - syncing order status with tm_trademarks status
    }

    /**
     * Helper to get master product ID from settings (placeholder).
     */
    public static function get_master_product_id() {
        $product_id = get_option( 'tm_master_product_id', 0 );
        return absint( $product_id );
    }
}
