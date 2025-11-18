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
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY iso_code (iso_code)
        ) $charset;";

        /* ============================================================
             COUNTRY PRICES TABLE
        ============================================================ */
        $price_table = self::table_name('country_prices');

        $sql2 = "CREATE TABLE $price_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            country_id BIGINT(20) UNSIGNED NOT NULL,
            trademark_type ENUM('word','figurative','combined') NOT NULL,
            step_number TINYINT(3) UNSIGNED NOT NULL,
            price_one_class DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            price_add_class DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            currency CHAR(3) NOT NULL DEFAULT 'USD',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY country_step (country_id, step_number, trademark_type)
        ) $charset;";

        /* ============================================================
             SERVICE CONDITIONS TABLE
        ============================================================ */
        $conditions_table = self::table_name('service_conditions');

        $sql3 = "CREATE TABLE $conditions_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            country_id BIGINT(20) UNSIGNED NOT NULL,
            step_number TINYINT(3) UNSIGNED NOT NULL,
            content LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY country_step (country_id, step_number)
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

        // Run all SQL with dbDelta
        dbDelta($sql1);
        dbDelta($sql2);
        dbDelta($sql3);
        dbDelta($sql4);
        dbDelta($sql5);
        dbDelta($sql6);
    }
}
