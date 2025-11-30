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

    public static function get_id_by_iso($iso) {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}tm_countries WHERE iso_code = %s",
                $iso
            )
        );
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

    /* ====================================================
       RECEIVE & SANITIZE FIELDS
    ==================================================== */
    $name  = sanitize_text_field($_POST['name']);
    $iso   = sanitize_text_field($_POST['iso']);

    $madrid_member     = isset($_POST['madrid_member']) ? intval($_POST['madrid_member']) : 0;
    $registration_time = sanitize_text_field($_POST['registration_time']);
    $opposition_period = sanitize_text_field($_POST['opposition_period']);
    $poa_required      = sanitize_text_field($_POST['poa_required']);
    $multi_class       = sanitize_text_field($_POST['multi_class']);
    $evidence_required = sanitize_text_field($_POST['evidence_required']);
    $protection_term   = sanitize_text_field($_POST['protection_term']);

    // REMARK TYPE (Price rule)
    $general_remarks   = sanitize_text_field($_POST['general_remarks']);

    // OTHER Remarks
    $other_remarks     = sanitize_textarea_field($_POST['other_remarks']);

    // Belt & Road
    $belt_road         = isset($_POST['belt_road']) ? intval($_POST['belt_road']) : 0;

    if (!$name || !$iso) {
        wp_send_json_error(['message' => 'Country name and ISO code are required.']);
    }

    /* ====================================================
       CHECK DUPLICATES
    ==================================================== */
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

    /* ====================================================
       INSERT NEW COUNTRY
    ==================================================== */
    $inserted = $wpdb->insert(
        $table,
        [
            'country_name'        => $name,
            'iso_code'            => strtoupper($iso),

            'is_madrid_member'    => $madrid_member,
            'registration_time'   => $registration_time,
            'opposition_period'   => $opposition_period,
            'poa_required'        => $poa_required,
            'multi_class_allowed' => $multi_class,
            'evidence_required'   => $evidence_required,
            'protection_term'     => $protection_term,

            'general_remarks'     => $general_remarks,
            'other_remarks'       => $other_remarks,
            'belt_and_road'       => $belt_road,

            'status'              => 1,
            'created_at'          => current_time('mysql'),
            'updated_at'          => current_time('mysql'),
        ]
    );

    if (!$inserted) {
        wp_send_json_error(['message' => 'Database insert failed.']);
    }

    /* ====================================================
       RETURN SUCCESS
    ==================================================== */
    $id = $wpdb->insert_id;

    wp_send_json_success([
        'message' => 'Country added successfully.',
        'country' => [
            'id'                  => $id,
            'name'                => $name,
            'iso'                 => strtoupper($iso),
            'madrid_member'       => $madrid_member,
            'registration_time'   => $registration_time,
            'opposition_period'   => $opposition_period,
            'poa_required'        => $poa_required,
            'multi_class'         => $multi_class,
            'evidence_required'   => $evidence_required,
            'protection_term'     => $protection_term,
            'general_remarks'     => $general_remarks,
            'other_remarks'       => $other_remarks,
            'belt_and_road'       => $belt_road,
            'status'              => 1
        ]
    ]);
}



/* ============================================================
   UPDATE COUNTRY (AJAX) — FULL UPDATED VERSION
============================================================ */
public static function update_country() {

    check_ajax_referer('tm_countries_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'tm_countries';

    $id   = intval($_POST['id']);
    $name = sanitize_text_field($_POST['name']);
    $iso  = sanitize_text_field($_POST['iso']);

    if (!$id || !$name || !$iso) {
        wp_send_json_error(['message' => 'Invalid data.']);
    }

    // Receive all fields
    $data = [
        'country_name'        => $name,
        'iso_code'            => strtoupper($iso),
        'is_madrid_member'    => intval($_POST['madrid_member']),
        'registration_time'   => sanitize_text_field($_POST['registration_time']),
        'opposition_period'   => sanitize_text_field($_POST['opposition_period']),
        'poa_required'        => sanitize_text_field($_POST['poa_required']),
        'multi_class_allowed' => sanitize_text_field($_POST['multi_class']),
        'evidence_required'   => sanitize_text_field($_POST['evidence_required']),
        'protection_term'     => sanitize_text_field($_POST['protection_term']),
        'general_remarks'     => sanitize_text_field($_POST['general_remarks']),
        'other_remarks'       => sanitize_text_field($_POST['other_remarks']),
        'belt_and_road'       => intval($_POST['belt_road']),
        'status'              => intval($_POST['status']),
        'updated_at'          => current_time('mysql'),
    ];

    // Run update
    $updated = $wpdb->update($table, $data, ['id' => $id]);

    if ($updated === false) {
        wp_send_json_error(['message' => 'Update failed']);
    }

    wp_send_json_success(['message' => 'Country updated successfully', 'country' => $data]);
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
