<?php
if (!defined('ABSPATH')) exit;

class TM_Countries {

    public static function init() {

        // CRUD AJAX Calls
        add_action('wp_ajax_tm_add_country', [__CLASS__, 'add_country']);
        add_action('wp_ajax_tm_update_country', [__CLASS__, 'update_country']);
        add_action('wp_ajax_tm_delete_country', [__CLASS__, 'delete_country']);
        add_action('wp_ajax_tm_bulk_add_countries', [__CLASS__, 'bulk_import']);
    }

    /**
     * Fetch countries for initial table
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_countries';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY country_name ASC");
    }

    /* ============================================================
       ADD COUNTRY (AJAX)
    ============================================================ */
    public static function add_country() {

        check_ajax_referer('tm_countries_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'tm_countries';

        $name = sanitize_text_field($_POST['name']);
        $iso  = sanitize_text_field($_POST['iso']);

        if (!$name || !$iso) {
            wp_send_json_error(['message' => 'Country name and ISO are required.']);
        }

        // Duplicate Validation
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE country_name=%s OR iso_code=%s",
                $name,
                strtoupper($iso)
            )
        );

        if ($exists > 0) {
            wp_send_json_error(['message' => 'This country already exists.']);
        }

        $inserted = $wpdb->insert($table, [
            'country_name' => $name,
            'iso_code'     => strtoupper($iso),
            'status'       => 1
        ]);

        if (!$inserted) {
            wp_send_json_error(['message' => 'Database insert failed.']);
        }

        $id = $wpdb->insert_id;

        wp_send_json_success([
            'message' => 'Country added successfully.',
            'country' => [
                'id'   => $id,
                'name' => $name,
                'iso'  => strtoupper($iso),
                'status' => 1
            ]
        ]);
    }

    /* ============================================================
       UPDATE COUNTRY (AJAX)
    ============================================================ */
    public static function update_country() {

        check_ajax_referer('tm_countries_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'tm_countries';

        $id     = intval($_POST['id']);
        $name   = sanitize_text_field($_POST['name']);
        $iso    = sanitize_text_field($_POST['iso']);
        $status = intval($_POST['status']);

        if (!$id || !$name || !$iso) {
            wp_send_json_error(['message' => 'Invalid data.']);
        }

        // Duplicate Validation (exclude current row)
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table 
                 WHERE (country_name=%s OR iso_code=%s) AND id != %d",
                $name,
                strtoupper($iso),
                $id
            )
        );

        if ($exists > 0) {
            wp_send_json_error(['message' => 'Country with same name or ISO already exists.']);
        }

        $updated = $wpdb->update($table, [
            'country_name' => $name,
            'iso_code'     => strtoupper($iso),
            'status'       => $status
        ], [
            'id' => $id
        ]);

        if ($updated === false) {
            wp_send_json_error(['message' => 'Update failed.']);
        }

        wp_send_json_success([
            'message' => 'Country updated.',
            'country' => [
                'id'     => $id,
                'name'   => $name,
                'iso'    => strtoupper($iso),
                'status' => $status
            ]
        ]);
    }

    /* ============================================================
       DELETE COUNTRY (AJAX)
    ============================================================ */
    public static function delete_country() {

        check_ajax_referer('tm_countries_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'tm_countries';

        $id = intval($_POST['id']);

        if (!$id) {
            wp_send_json_error(['message' => 'Invalid ID.']);
        }

        $wpdb->delete($table, ['id' => $id]);

        wp_send_json_success(['message' => 'Country deleted.']);
    }


    /* ============================================================
       BULK IMPORT COUNTRIES
    ============================================================ */
    public static function bulk_import() {

        check_ajax_referer('tm_countries_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'tm_countries';

        $jsonString = stripslashes($_POST['json']);

        // Split at "},", fix formatting
        $entries = preg_split('/\},\s*\{/', $jsonString);

        $added = [];
        $skipped = 0;
        $invalid = 0;

        foreach ($entries as &$entry) {

            // Format JSON correctly
            if (substr(trim($entry), 0, 1) !== "{") {
                $entry = "{" . $entry;
            }
            if (substr(trim($entry), -1) !== "}") {
                $entry = $entry . "}";
            }

            $data = json_decode($entry, true);

            if (!is_array($data) || !isset($data['name']) || !isset($data['iso'])) {
                $invalid++;
                continue;
            }

            $name = sanitize_text_field($data['name']);
            $iso  = sanitize_text_field($data['iso']);

            // Duplicate check
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE country_name=%s OR iso_code=%s",
                    $name,
                    strtoupper($iso)
                )
            );

            if ($exists > 0) {
                $skipped++;
                continue;
            }

            $wpdb->insert($table, [
                'country_name' => $name,
                'iso_code'     => strtoupper($iso),
                'status'       => 1
            ]);

            $added[] = [
                'id'   => $wpdb->insert_id,
                'name' => $name,
                'iso'  => strtoupper($iso),
                'status' => 1
            ];
        }

        wp_send_json_success([
            'added'   => $added,
            'skipped' => $skipped,
            'invalid' => $invalid
        ]);
    }
}
