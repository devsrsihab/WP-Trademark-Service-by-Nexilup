<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_TM_Deactivator {

    public static function deactivate() {
        // Normally you don't drop tables here.
        // You can clear scheduled events or transients if you add them later.
    }
}
