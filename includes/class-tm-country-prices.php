<?php
if (!defined('ABSPATH')) exit;

class TM_Country_Prices {

    public static function init() {
        add_action("wp_ajax_tm_save_country_price", [__CLASS__, "save_price"]);
        add_action("wp_ajax_tm_get_country_price",  [__CLASS__, "get_price"]);
        add_action("wp_ajax_tm_delete_country_price", [__CLASS__, "delete_price"]);
    }

    // ⭐ FIX — Add this method
    public static function table() {
        return TM_Database::table_name('country_prices');
    }

public static function get_paginated_prices($paged = 1, $per_page = 20)
{
    global $wpdb;

    $table_prices = self::table();
    $table_countries = TM_Database::table_name('countries');

    $offset = ($paged - 1) * $per_page;

    // ✨ JOIN so we get country_name
    $items = $wpdb->get_results(
        $wpdb->prepare("
            SELECT p.*, c.country_name
            FROM {$table_prices} p
            LEFT JOIN {$table_countries} c ON p.country_id = c.id
            ORDER BY c.country_name ASC, p.trademark_type ASC, p.step_number ASC
            LIMIT %d OFFSET %d
        ", $per_page, $offset)
    );

    // Count total rows
    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_prices}");

    return [
        'items'      => $items,
        'total'      => $total,
        'per_page'   => $per_page,
        'current'    => $paged,
        'max_pages'  => ceil($total / $per_page)
    ];
}


    public static function save_price() {
        check_ajax_referer("tm_country_prices_nonce", "nonce");

        global $wpdb;

        $table = $wpdb->prefix."tm_country_prices";

        $country  = intval($_POST["country"]);
        $type     = sanitize_text_field($_POST["type"]);
        $currency = sanitize_text_field($_POST["currency"]);
        $mode     = intval($_POST["mode"]);

        $steps = [
            1 => ["one" => $_POST["s1_one"], "add" => $_POST["s1_add"]],
            2 => ["one" => $_POST["s2_one"], "add" => $_POST["s2_add"]],
            3 => ["one" => $_POST["s3_one"], "add" => $_POST["s3_add"]],
        ];

        if ($mode == 1) {
            // DELETE old first
            $wpdb->delete($table, ["country_id" => $country, "trademark_type" => $type]);
        }

        foreach ($steps as $step => $v) {
            $wpdb->insert($table, [
                "country_id"        => $country,
                "trademark_type"    => $type,
                "step_number"       => $step,
                "price_one_class"   => floatval($v["one"]),
                "price_add_class"   => floatval($v["add"]),
                "currency"          => $currency,
                "created_at"        => current_time("mysql"),
                "updated_at"        => current_time("mysql")
            ]);
        }

        wp_send_json_success();
    }


    public static function get_price() {
        check_ajax_referer("tm_country_prices_nonce", "nonce");

        global $wpdb;
        $table = $wpdb->prefix."tm_country_prices";

        $country = intval($_POST["country"]);
        $type    = sanitize_text_field($_POST["type"]);

        $rows = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table WHERE country_id=%d AND trademark_type=%s ORDER BY step_number ASC
        ", $country, $type));

        $resp = [
            "currency" => $rows[0]->currency,
            "s1_one"   => $rows[0]->price_one_class,
            "s1_add"   => $rows[0]->price_add_class,
            "s2_one"   => $rows[1]->price_one_class,
            "s2_add"   => $rows[1]->price_add_class,
            "s3_one"   => $rows[2]->price_one_class,
            "s3_add"   => $rows[2]->price_add_class
        ];

        wp_send_json_success($resp);
    }

    public static function delete_price() {
        check_ajax_referer("tm_country_prices_nonce", "nonce");

        global $wpdb;
        $table = $wpdb->prefix."tm_country_prices";

        $wpdb->delete($table, [
            "country_id"     => intval($_POST["country"]),
            "trademark_type" => sanitize_text_field($_POST["type"])
        ]);

        wp_send_json_success();
    }

}
