<?php



class TM_Orders {

    public static function get_user_trademarks($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . "tm_trademarks";

        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY id DESC", $user_id)
        );
    }
}
