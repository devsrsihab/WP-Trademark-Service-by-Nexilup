<?php
if (!defined('ABSPATH')) exit;

class TM_Service_Conditions {

    public static function init() {
        add_action('wp_ajax_tm_get_service_condition',    [__CLASS__, 'ajax_get_condition']);
        add_action('wp_ajax_tm_save_service_condition',   [__CLASS__, 'ajax_save_condition']);
        add_action('wp_ajax_tm_delete_service_condition', [__CLASS__, 'ajax_delete_condition']);
    }

    public static function table() {
        return TM_Database::table_name('service_conditions');
    }

    public static function countries_table() {
        return TM_Database::table_name('countries');
    }

    /* ============================================================
       PAGINATION
    ============================================================ */
    public static function get_paginated($paged = 1, $per_page = 20) {
        global $wpdb;

        $table_sc = self::table();
        $table_c  = self::countries_table();

        $offset = ($paged - 1) * $per_page;

        $items = $wpdb->get_results($wpdb->prepare("
            SELECT sc.*, c.country_name
            FROM {$table_sc} sc
            LEFT JOIN {$table_c} c ON sc.country_id = c.id
            ORDER BY c.country_name ASC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));

        $total = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$table_sc}");

        return [
            'items'     => $items,
            'total'     => $total,
            'per_page'  => $per_page,
            'current'   => $paged,
            'max_pages' => $total ? ceil($total / $per_page) : 1
        ];
    }

    /* ============================================================
       GET ONE CONDITION
    ============================================================ */
    public static function ajax_get_condition() {
        check_ajax_referer('tm_service_conditions_nonce', 'nonce');

        if (empty($_POST['id'])) {
            wp_send_json_error(['message' => 'Missing ID']);
        }

        global $wpdb;
        $table = self::table();
        $id    = intval($_POST['id']);

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));

        if (!$row) wp_send_json_error(['message' => 'Condition not found']);

        wp_send_json_success([
            'id'         => $row->id,
            'country_id' => $row->country_id,
            'content'    => $row->content
        ]);
    }

    /* ============================================================
       SAVE CONDITION
    ============================================================ */
    public static function ajax_save_condition() {
        check_ajax_referer('tm_service_conditions_nonce', 'nonce');

        global $wpdb;

        $table      = self::table();
        $id         = intval($_POST['id']);
        $country_id = intval($_POST['country']);
        $content    = wp_kses_post(wp_unslash($_POST['content']));
        $now        = current_time('mysql');

        if (!$country_id) {
            wp_send_json_error(['message' => 'Country is required']);
        }

        // Check if country already has a condition
        $existing_id = $wpdb->get_var($wpdb->prepare("
            SELECT id FROM {$table} WHERE country_id=%d
        ", $country_id));

        // If editing
        if ($id) {
            $wpdb->update($table, [
                'country_id' => $country_id,
                'content'    => $content,
                'updated_at' => $now
            ], ['id' => $id]);
        } else {
            if ($existing_id) {
                // update existing
                $wpdb->update($table, [
                    'content'    => $content,
                    'updated_at' => $now
                ], ['id' => $existing_id]);
            } else {
                // insert new
                $wpdb->insert($table, [
                    'country_id' => $country_id,
                    'content'    => $content,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }

        wp_send_json_success();
    }

    /* ============================================================
       DELETE CONDITION
    ============================================================ */
    public static function ajax_delete_condition() {
        check_ajax_referer('tm_service_conditions_nonce', 'nonce');

        global $wpdb;

        $id = intval($_POST['id']);
        $wpdb->delete(self::table(), ['id' => $id]);

        wp_send_json_success();
    }
}
