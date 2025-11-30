<?php
if (!defined('ABSPATH')) exit;

class TM_Database {

    /**
     * Define table names
     */
    protected static $tables = [
        'countries'          => 'tm_countries',
        'country_prices'     => 'tm_country_prices',
        'service_conditions' => 'tm_service_conditions',
        'owner_profiles'     => 'tm_owner_profiles',
        'trademarks'         => 'tm_trademarks',
        'trademark_classes'  => 'tm_trademark_classes',
        'trademark_files'    => 'tm_trademark_files',
    ];

    /**
     * Helper: return table name with prefix
     */
    public static function table_name($key) {
        global $wpdb;

        return isset(self::$tables[$key])
            ? $wpdb->prefix . self::$tables[$key]
            : '';
    }

    /**
     * Run on plugin activation — create all tables
     */
    public static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        /* ============================================================
             COUNTRIES TABLE
        ============================================================ */
        $countries_table = self::table_name('countries');

        $sql1 = "CREATE TABLE $countries_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,

            iso_code VARCHAR(5) NOT NULL,
            country_name VARCHAR(150) NOT NULL,

            is_madrid_member TINYINT(1) NOT NULL DEFAULT 0,
            registration_time VARCHAR(100) DEFAULT NULL,
            opposition_period VARCHAR(100) DEFAULT NULL,
            poa_required VARCHAR(100) DEFAULT NULL,
            multi_class_allowed VARCHAR(20) DEFAULT NULL,
            evidence_required VARCHAR(20) DEFAULT NULL,
            protection_term VARCHAR(255) DEFAULT NULL,

            general_remarks VARCHAR(255) DEFAULT NULL,
            other_remarks TEXT DEFAULT NULL,
            belt_and_road VARCHAR(10) DEFAULT NULL,

            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,

            PRIMARY KEY (id),
            UNIQUE KEY iso_code_idx (iso_code)
        ) $charset;";



        /* ============================================================
        COUNTRY PRICES TABLE - NEW STRUCTURE (NO STEPS)
        ============================================================ */
        $price_table = self::table_name('country_prices');

        $sql2 = "CREATE TABLE $price_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            country_id BIGINT(20) UNSIGNED NOT NULL,
            first_class_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            additional_class_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            general_remarks TEXT DEFAULT NULL,

            priority_claim_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            poa_late_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            currency CHAR(3) NOT NULL DEFAULT 'USD',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,

            PRIMARY KEY (id),
            KEY country_idx (country_id)
        ) $charset;";



        /* ============================================================
             SERVICE CONDITIONS TABLE
        ============================================================ */
        $conditions_table = self::table_name('service_conditions');

        $sql3 = "CREATE TABLE $conditions_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            country_id BIGINT(20) UNSIGNED NOT NULL,
            content LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY country_idx (country_id)
        ) $charset;";






        /* ============================================================
             OWNER PROFILES TABLE
        ============================================================ */
        $owner_table = self::table_name('owner_profiles');

        $sql4 = "CREATE TABLE $owner_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            profile_name VARCHAR(150) NOT NULL,
            company_name VARCHAR(200) DEFAULT NULL,
            country VARCHAR(150) DEFAULT NULL,
            state VARCHAR(150) DEFAULT NULL,
            city VARCHAR(150) DEFAULT NULL,
            address_line1 VARCHAR(255) DEFAULT NULL,
            address_line2 VARCHAR(255) DEFAULT NULL,
            postal_code VARCHAR(30) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            email VARCHAR(150) DEFAULT NULL,
            tax_id VARCHAR(100) DEFAULT NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset;";

        /* ============================================================
             TRADEMARKS TABLE
        ============================================================ */
        $trademark_table = self::table_name('trademarks');

        $sql5 = "CREATE TABLE $trademark_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            country_id BIGINT(20) UNSIGNED NOT NULL,
            service_step ENUM('1','2','3') NOT NULL,
            trademark_type ENUM('word','figurative','combined') NOT NULL,
            mark_text VARCHAR(255) DEFAULT NULL,
            has_logo TINYINT(1) NOT NULL DEFAULT 0,
            logo_url VARCHAR(255) DEFAULT NULL,
            goods_services TEXT DEFAULT NULL,
            priority_claim TINYINT(1) NOT NULL DEFAULT 0,
            priority_details TEXT DEFAULT NULL,
            poa_type ENUM('normal','late','none') NOT NULL DEFAULT 'none',
            class_count INT(11) UNSIGNED NOT NULL DEFAULT 1,
            extra_class_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
            final_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            currency CHAR(3) NOT NULL DEFAULT 'USD',
            status ENUM('draft','pending_payment','paid','in_process','completed','cancelled') NOT NULL DEFAULT 'draft',
            woo_order_id BIGINT(20) UNSIGNED DEFAULT NULL,
            woo_order_item_id BIGINT(20) UNSIGNED DEFAULT NULL,
            owner_profile_id BIGINT(20) UNSIGNED DEFAULT NULL,
            raw_form_data LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_status (user_id, status),
            KEY order_id (woo_order_id)
        ) $charset;";

        /* ============================================================
             TRADEMARK CLASSES
        ============================================================ */
        $classes_table = self::table_name('trademark_classes');

        $sql6 = "CREATE TABLE $classes_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            trademark_id BIGINT(20) UNSIGNED NOT NULL,
            class_number VARCHAR(10) NOT NULL,
            description TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY trademark_id (trademark_id)
        ) $charset;";



        /* ============================================================
             FILE TABLE
        ============================================================ */

        $files_table = self::table_name('trademark_files');

        $sql7 = "CREATE TABLE `$files_table` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `trademark_id` BIGINT(20) UNSIGNED NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `file_url` VARCHAR(255) NOT NULL,
            `file_type` VARCHAR(50) NOT NULL,
            `uploaded_by` BIGINT(20) UNSIGNED NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `tm_tf_trademark_id` (`trademark_id`),
            KEY `tm_tf_uploaded_by` (`uploaded_by`)
        ) $charset;";

        // Run all SQL with dbDelta
        dbDelta($sql1);
        dbDelta($sql2);
        dbDelta($sql3);
        dbDelta($sql4);
        dbDelta($sql5);
        dbDelta($sql6);
        dbDelta($sql7);
    }


    public static function get_countries( $args = [] ) {
        global $wpdb;

        $table = self::table_name( 'countries' );
        if ( ! $table ) {
            return [];
        }

        // Defaults
        $defaults = [
            'active_only' => true,
            'order_by'    => 'country_name',
            'order'       => 'ASC',
        ];

        $args = wp_parse_args( $args, $defaults );

        // WHERE
        $where = 'WHERE 1=1';
        if ( ! empty( $args['active_only'] ) ) {
            // your countries table uses "status" or "is_active" depending on schema
            // adjust this line based on your actual column name
            $where .= ' AND status = 1';
            // if your column name is is_active, use:
            // $where .= ' AND is_active = 1';
        }

        // ORDER BY safety
        $allowed_order_by = [ 'country_name', 'iso_code', 'id' ];
        $order_by = in_array( $args['order_by'], $allowed_order_by, true )
            ? $args['order_by']
            : 'country_name';

        $order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM {$table} {$where} ORDER BY {$order_by} {$order}";

        return $wpdb->get_results( $sql );
    }

    public static function paginate($table, $where = "WHERE 1=1", $orderby = "id DESC", $per_page = 20) {
        global $wpdb;

        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($page - 1) * $per_page;

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS *
            FROM {$table}
            {$where}
            ORDER BY {$orderby}
            LIMIT {$offset}, {$per_page}
        ";

        $items = $wpdb->get_results($sql);
        $total = $wpdb->get_var("SELECT FOUND_ROWS()");

        return [
            'items'     => $items,
            'total'     => $total,
            'per_page'  => $per_page,
            'current'   => $page,
            'max_pages' => ceil($total / $per_page),
        ];
    }


}
