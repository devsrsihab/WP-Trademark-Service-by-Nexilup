<?php
if (!defined('ABSPATH')) exit;

    class TM_Country_Prices {

        /* ============================================================
        STATIC MEMORY FOR ERROR + OLD INPUT
        ============================================================ */
        public static $error_message = "";
        public static $old_input = [];

    //     public static function get_priority_price_row($country_id, $type = 'word') {
    //         global $wpdb;
    //         $table = TM_Database::table_name('country_prices');

    //         // Fetch all rows for this country
    //         $rows = $wpdb->get_results(
    //             $wpdb->prepare("SELECT * FROM $table WHERE country_id = %d", $country_id)
    //         );

    //         if (!$rows) return null;

    //         // Priority rules
    //         $filing_basic = null;
    //         $filing_other = null;
    //         $reg_basic = null;
    //         $reg_mid = null;
    //         $reg_other = null;

    //         foreach ($rows as $r) {

    //             // Filing
    //             if (strpos($r->general_remarks, 'filing_basic') === 0) {
    //                 $filing_basic = $r; 
    //             } elseif (strpos($r->general_remarks, 'filing_') === 0) {
    //                 $filing_other = $r;
    //             }

    //             // Registration
    //             if (strpos($r->general_remarks, 'registration_basic') === 0) {
    //                 $reg_basic = $r;
    //             } elseif (
    //                 strpos($r->general_remarks, 'registration_5_years') === 0 ||
    //                 strpos($r->general_remarks, 'registration_10_years') === 0
    //             ) {
    //                 $reg_mid = $r;
    //             } elseif (strpos($r->general_remarks, 'registration_') === 0) {
    //                 $reg_other = $r;
    //             }
    //         }

    //         // Priority resolution
    //         if ($filing_basic) return $filing_basic;
    //         if ($filing_other) return $filing_other;

    //         if ($reg_basic) return $reg_basic;
    //         if ($reg_mid) return $reg_mid;
    //         if ($reg_other) return $reg_other;

    //         return $rows[0]; // fallback
    //    }

    public static function get_priority_price_row($country_id, $mode = 'filing')
    {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_country_prices';

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE country_id = %d", $country_id)
        );

        if (!$rows) return null;

        // Storage for rule matches
        $filing_basic = null;
        $filing_other = null;

        $reg_basic = null;
        $reg_mid   = null; // 5 yrs / 10 yrs
        $reg_other = null;

        foreach ($rows as $r) {
            $remark = trim($r->general_remarks);

            /** --------------------------
             *  FILING PRIORITY
             * ------------------------- */
            if (strpos($remark, 'filing_basic') === 0) {
                $filing_basic = $r;
            } elseif (strpos($remark, 'filing_') === 0) {
                $filing_other = $r;
            }

            /** --------------------------
             *  REGISTRATION PRIORITY
             * ------------------------- */
            if (strpos($remark, 'registration_basic') === 0) {
                $reg_basic = $r;
            } 
            elseif (
                strpos($remark, 'registration_5_years') === 0 ||
                strpos($remark, 'registration_10_years') === 0
            ) {
                $reg_mid = $r;
            } 
            elseif (strpos($remark, 'registration_') === 0) {
                $reg_other = $r;
            }
        }

        /** ===========================================================
         *  MODE SELECTION
         *  Step-1 → filing only
         *  Step-2 → registration only
         * =========================================================== */
        if ($mode === 'filing') {
            if ($filing_basic) return $filing_basic;
            if ($filing_other) return $filing_other;

            // fallback to registration if no filing rows at all
            if ($reg_basic) return $reg_basic;
            if ($reg_mid)   return $reg_mid;
            if ($reg_other) return $reg_other;

            return $rows[0];
        }

        if ($mode === 'registration') {
            if ($reg_basic) return $reg_basic;
            if ($reg_mid)   return $reg_mid;
            if ($reg_other) return $reg_other;

            // fallback to filing if no registration rows
            if ($filing_basic) return $filing_basic;
            if ($filing_other) return $filing_other;

            return $rows[0];
        }

        return $rows[0];
    }




    /* ============================================================
       INIT HOOK
    ============================================================ */
    public static function init() {
        add_action('admin_init', [__CLASS__, 'handle_form_actions']);
    }

    public static function table() {
        return TM_Database::table_name('country_prices');
    }

    /* ============================================================
       ACTION ROUTER
    ============================================================ */
    public static function handle_form_actions() {

        if (!isset($_REQUEST['tms_action'])) return;

        $action = sanitize_text_field($_REQUEST['tms_action']);

        switch ($action) {

            case 'add_price':
                self::add_price();
                break;

            case 'update_price':
                self::update_price();
                break;

            case 'delete_price':
                self::delete_price();
                break;
        }
    }

    /* ============================================================
       ADD PRICE (Normal POST)
    ============================================================ */
    public static function add_price() {

        if (!wp_verify_nonce($_POST['nonce'], 'tm_country_prices_nonce')) {
            self::$error_message = "Security check failed";
            self::$old_input = $_POST;
            return;
        }

        global $wpdb;
        $table = self::table();

        $country            = intval($_POST['country']);
        $general_remarks    = sanitize_text_field($_POST['general_remarks']);
        $currency           = 'USD';

        $first_fee          = floatval($_POST['first_class_fee']);
        $add_fee            = floatval($_POST['additional_class_fee']);
        $priority_claim_fee = floatval($_POST['priority_claim_fee']);
        $poa_late_fee       = floatval($_POST['poa_late_fee']);

        // Validation groups
        $filing_rules = ["filing_20_goods", "filing_basic"];
        $registration_rules = ["registration_5_years", "registration_10_years"];

        $is_filing       = in_array($general_remarks, $filing_rules);
        $is_registration = in_array($general_remarks, $registration_rules);

        // Check existing rule
        $existing = $wpdb->get_row($wpdb->prepare("
            SELECT general_remarks FROM $table WHERE country_id = %d
        ", $country));

        if ($existing) {
            $old_rule = trim($existing->general_remarks);

            if ($old_rule === "") {
                self::$error_message = "This country already has an empty rule.";
                self::$old_input = $_POST;
                return;
            }

            if ($general_remarks === "") {
                self::$error_message = "Cannot save empty pricing rule.";
                self::$old_input = $_POST;
                return;
            }

            // if (in_array($old_rule, $filing_rules) && $is_filing) {
            //     self::$error_message = "This country already has a Filing rule.";
            //     self::$old_input = $_POST;
            //     return;
            // }

            // if (in_array($old_rule, $registration_rules) && $is_registration) {
            //     self::$error_message = "This country already has a Registration rule.";
            //     self::$old_input = $_POST;
            //     return;
            // }
        }

        // Insert NEW RECORD
        $wpdb->insert($table, [
            'country_id'           => $country,
            'general_remarks'      => $general_remarks,
            'first_class_fee'      => $first_fee,
            'additional_class_fee' => $add_fee,
            'priority_claim_fee'   => $priority_claim_fee,
            'poa_late_fee'         => $poa_late_fee,
            'currency'             => $currency,
            'created_at'           => current_time('mysql'),
            'updated_at'           => current_time('mysql')
        ]);

        self::redirect_msg("Price added successfully");
    }


    /* ============================================================
       UPDATE PRICE (Normal POST)
    ============================================================ */
    public static function update_price() {

        if (!wp_verify_nonce($_POST['nonce'], 'tm_country_prices_nonce')) {
            self::$error_message = "Security check failed.";
            self::$old_input = $_POST;
            return;
        }

        global $wpdb;
        $table = self::table();

        $id                 = intval($_POST['id']);
        $country            = intval($_POST['country']);
        $general_remarks    = sanitize_text_field($_POST['general_remarks']);

        $first_fee          = floatval($_POST['first_class_fee']);
        $add_fee            = floatval($_POST['additional_class_fee']);
        $priority_claim_fee = floatval($_POST['priority_claim_fee']);
        $poa_late_fee       = floatval($_POST['poa_late_fee']);

        // Validation groups
        $filing_rules = ["filing_20_goods", "filing_basic"];
        $registration_rules = ["registration_5_years", "registration_10_years"];

        $is_filing       = in_array($general_remarks, $filing_rules);
        $is_registration = in_array($general_remarks, $registration_rules);

        /*
        * 1️⃣ Load ANY OTHER record for this country,
        *    EXCEPT the one currently being edited
        */
        $existing = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table 
            WHERE country_id = %d AND id != %d
        ", $country, $id));

        if ($existing) {
            $old_rule = trim($existing->general_remarks);

            // ❌ Country already has an empty record
            if ($old_rule === "") {
                self::$error_message = "This country already has an empty pricing rule. You cannot add another rule.";
                self::$old_input = $_POST;
                return;
            }

            // ❌ Cannot save empty rule if pricing exists
            // if ($general_remarks === "") {
            //     self::$error_message = "Cannot save an empty pricing rule because this country already has pricing.";
            //     self::$old_input = $_POST;
            //     return;
            // }

            // ❌ Prevent second Filing rule
            // if (in_array($old_rule, $filing_rules) && $is_filing) {
            //     self::$error_message = "This country already has a Filing Fee rule.";
            //     self::$old_input = $_POST;
            //     return;
            // }

            // ❌ Prevent second Registration rule
            // if (in_array($old_rule, $registration_rules) && $is_registration) {
            //     self::$error_message = "This country already has a Registration Fee rule.";
            //     self::$old_input = $_POST;
            //     return;
            // }
        }

        // 2️⃣ If validation passed → update
        $wpdb->update(
            $table,
            [
                'general_remarks'      => $general_remarks,
                'first_class_fee'      => $first_fee,
                'additional_class_fee' => $add_fee,
                'priority_claim_fee'   => $priority_claim_fee,
                'poa_late_fee'         => $poa_late_fee,
                'updated_at'           => current_time('mysql')
            ],
            ['id' => $id]
        );

        self::redirect_msg("Price updated successfully");
    }


    /* ============================================================
       DELETE PRICE (Normal GET)
    ============================================================ */
    public static function delete_price() {

        global $wpdb;
        $table = self::table();

        $id = intval($_GET['id']);

        $wpdb->delete($table, ['id' => $id]);

        self::redirect_msg("Price deleted successfully");
    }


    /* ============================================================
       REDIRECT HELPER
    ============================================================ */
    private static function redirect_msg($msg, $error = false) {

        $url = admin_url(
            "admin.php?page=tm-country-prices&msg=" . urlencode($msg) . "&err=" . intval($error)
        );

        wp_redirect($url);
        exit;
    }

    /* ============================================================
       Pagination System
    ============================================================ */
    public static function get_paginated_prices($paged = 1, $per_page = 20)
    {
        global $wpdb;

        $table_prices    = self::table();
        $table_countries = TM_Database::table_name('countries');
        $offset          = ($paged - 1) * $per_page;

        $items = $wpdb->get_results($wpdb->prepare("
            SELECT p.*, c.country_name
            FROM {$table_prices} p
            LEFT JOIN {$table_countries} c ON p.country_id = c.id
            ORDER BY c.country_name ASC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_prices}");

        return [
            "items"     => $items,
            "total"     => $total,
            "per_page"  => $per_page,
            "current"   => $paged,
            "max_pages" => ceil($total / $per_page)
        ];
    }

    /**
     * UNIVERSAL PRICE FETCHER
     * Always returns EXACTLY one price row per country.
     * Auto-creates a default row if missing.
     */
    public static function get_price_row($country_id)
    {
        global $wpdb;
        $table = self::table();

        // Try to load the single price row
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE country_id = %d LIMIT 1", $country_id)
        );

        // If missing → auto-create new blank price row
        if (!$row) {
            $wpdb->insert($table, [
                'country_id'           => $country_id,
                'general_remarks'      => '',
                'first_class_fee'      => 0,
                'additional_class_fee' => 0,
                'priority_claim_fee'   => 0,
                'poa_late_fee'         => 0,
                'currency'             => 'USD',
                'created_at'           => current_time('mysql'),
                'updated_at'           => current_time('mysql'),
            ]);

            // Reload
            $row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table WHERE country_id = %d LIMIT 1", $country_id)
            );
        }

        return $row;
    }


    public static function get_step1_price_row($country_id) {
        global $wpdb;
        $table = TM_Database::table_name('country_prices');

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE country_id = %d", $country_id)
        );

        if (!$rows) return null;

        $filing_basic = null;
        $filing_20 = null;
        $filing_other = null;
        $empty_row = null;

        foreach ($rows as $r) {
            $rem = trim($r->general_remarks);

            if ($rem === "filing_basic") $filing_basic = $r;
            elseif ($rem === "filing_20_goods") $filing_20 = $r;
            elseif (strpos($rem, "filing_") === 0) $filing_other = $r;
            elseif ($rem === "") $empty_row = $r;
        }

        if ($filing_basic) return $filing_basic;
        if ($filing_20) return $filing_20;
        if ($filing_other) return $filing_other;
        if ($empty_row) return $empty_row;

        return $rows[0];
    }

    public static function get_priority_price_row_filing_only($country_id) {
        global $wpdb;
        $table = TM_Database::table_name('country_prices');

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE country_id = %d", $country_id)
        );

        if (!$rows) return null;

        $filing_basic = null;
        $filing_other = null;

        foreach ($rows as $r) {

            if (strpos($r->general_remarks, 'filing_basic') === 0) {
                $filing_basic = $r;
            }
            elseif (strpos($r->general_remarks, 'filing_') === 0) {
                $filing_other = $r;
            }
        }

        if ($filing_basic) return $filing_basic;
        if ($filing_other) return $filing_other;

        // fallback to first record if country has NO filing rows
        return $rows[0];
}



}
