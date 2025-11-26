<?php
if (!defined('ABSPATH')) exit;

class TM_Trademarks {

    public static function init() {
        add_action('wp_ajax_tm_admin_get_trademark', [ __CLASS__, 'ajax_admin_get_trademark' ]);
        add_action('wp_ajax_tm_admin_update_status', [ __CLASS__, 'ajax_admin_update_status' ]);
        add_action('wp_ajax_tm_admin_upload_doc', [ __CLASS__, 'ajax_admin_upload_doc' ]);
        add_action('wp_ajax_tm_admin_get_docs', [ __CLASS__, 'ajax_admin_get_docs' ]);
        add_action('wp_ajax_tm_user_view_trademark', ['TM_Trademarks', 'ajax_user_view_trademark']);
        add_action('wp_ajax_nopriv_tm_user_view_trademark', ['TM_Trademarks', 'ajax_user_view_trademark']);
        add_action('wp_ajax_tm_user_get_docs', ['TM_Trademarks', 'ajax_user_get_docs']);


    }

    public static function table() {
        global $wpdb;
        return $wpdb->prefix . "tm_trademarks";
    }

    public static function ajax_user_get_docs() {
        check_ajax_referer('tm_user_trademark_nonce', 'nonce');

        $id = intval($_POST['id']);
        $user_id = get_current_user_id();

        $t = self::get($id, $user_id);
        if (!$t) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $docs = TM_Trademarks::get_documents($id); // you already created this earlier

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/user-documents-list.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }


    public static function ajax_user_view_trademark() {
            check_ajax_referer('tm_user_trademark_nonce', 'nonce');

            $id = intval($_POST['id']);
            $user_id = get_current_user_id();

            $t = self::get($id, $user_id);
            if (!$t) {
                wp_send_json_error(['message' => 'Unauthorized or not found']);
            }

            ob_start();
            include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/frontend/trademark-user-details.php';
            $html = ob_get_clean();

            wp_send_json_success(['html' => $html]);
    }



    /**
     * Create trademark record after WooCommerce order created.
     */
    public static function create_from_order($item, $tm) {
        global $wpdb;

        $table = self::table();

        // ✅ Correct country id detection
        $country_id = intval($tm['country_id'] ?? 0);

        // fallback: if country_id missing, try ISO
        if (!$country_id && !empty($tm['country_iso'])) {
            $country_id = TM_Countries::get_id_by_iso($tm['country_iso']);
        }

        if (!$country_id) {
            error_log("TM ERROR: country_id missing, tm data = " . print_r($tm, true));
            return 0;
        }

        // ✅ Logo detection (your tm data uses logo_id / logo_url)
        $has_logo = (!empty($tm['logo_id']) || !empty($tm['logo_url'])) ? 1 : 0;
        $logo_url = $tm['logo_url'] ?? '';

        $wpdb->insert($table, [
            'user_id'          => get_current_user_id(),
            'country_id'       => $country_id,               // ✅ fixed
            'service_step'     => 'completed',
            'trademark_type'   => $tm['type'] ?? '',
            'mark_text'        => $tm['mark_text'] ?? '',    // ✅ was wrong before
            'has_logo'         => $has_logo,                 // ✅ fixed
            'logo_url'         => $logo_url,                 // ✅ fixed
            'goods_services'   => $tm['goods'] ?? '',
            'class_count'      => intval($tm['classes'] ?? 1),
            'final_price'      => floatval($tm['total_price'] ?? 0),
            'currency'         => sanitize_text_field($tm['currency'] ?? 'USD'),
            'woo_order_id'     => $item->get_order_id(),
            'owner_profile_id' => 0,
            'status'           => 'pending_payment',
            'created_at'       => current_time('mysql'),
            'updated_at'       => current_time('mysql'),
        ]);

        return $wpdb->insert_id;
    }


    /**
     * Get all trademarks for current user.
     */
    public static function get_user_trademarks($user_id) {
        global $wpdb;
        $table = self::table();
        $countries = TM_Database::table_name('countries');

        return $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT t.*, c.country_name 
                FROM $table t
                LEFT JOIN $countries c ON c.id = t.country_id
                WHERE t.user_id = %d
                ORDER BY t.created_at DESC
                ",
                $user_id
            )
        );
    }

    /**
     * Get a single trademark by ID.
     */
    public static function get($id, $user_id = 0) {
        global $wpdb;
        $table = self::table();

        if ($user_id > 0) {
            return $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table WHERE id = %d AND user_id = %d", $id, $user_id)
            );
        }

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)
        );
    }

    /**
     * Update trademark status (admin can use).
     */
    public static function update_status($id, $status) {
        global $wpdb;
        $table = self::table();

        return $wpdb->update(
            $table,
            ['status' => $status, 'updated_at' => current_time('mysql')],
            ['id' => $id]
        );
    }


    public static function admin_get_all() {
        global $wpdb;
        $table = self::table();
        $countries = TM_Database::table_name('countries');

        return $wpdb->get_results("
            SELECT t.*, c.country_name
            FROM $table t
            LEFT JOIN $countries c ON c.id = t.country_id
            ORDER BY t.created_at DESC
        ");
    }

    public static function ajax_admin_get_trademark() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id = intval($_POST['id']);
        $t  = self::get($id);

        if (!$t) {
            wp_send_json_error(['message' => 'Trademark not found']);
        }

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'templates/admin/trademark-details.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }


    public static function ajax_admin_update_status() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id     = intval($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        $valid = ['pending_payment','paid','in_process','completed','cancelled'];

        if (!in_array($status, $valid)) {
            wp_send_json_error(['message' => 'Invalid status']);
        }

        self::update_status($id, $status);

        // Send email
        self::send_status_email($id, $status);

        wp_send_json_success(['message' => 'Status updated']);
    }

    public static function send_status_email($id, $status) {
        global $wpdb;

        $t = self::get($id);
        if (!$t) return;

        $user = get_user_by('id', $t->user_id);
        if (!$user) return;

        $country = TM_Countries::get($t->country_id);

        ob_start();
        include WP_TMS_NEXILUP_PLUGIN_PATH . 'emails/status-email.php';
        $message = ob_get_clean();

        wp_mail(
            $user->user_email,
            "Your Trademark Status Updated: " . ucfirst(str_replace('_', ' ', $status)),
            $message,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }

    public static function ajax_admin_upload_doc() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id   = intval($_POST['id']);
        $type = sanitize_text_field($_POST['type']);

        if (!isset($_FILES['file'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $file = $_FILES['file'];

        // validate type
        $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            wp_send_json_error(['message' => 'Invalid file type']);
        }

        // upload file
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $upload = wp_handle_upload($file, ['test_form' => false]);

        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        global $wpdb;
        $files = $wpdb->prefix . "tm_trademark_files";

        $wpdb->insert($files, [
            'trademark_id' => $id,
            'file_name'    => $file['name'],
            'file_url'     => $upload['url'],
            'file_type'    => $type,
            'uploaded_by'  => get_current_user_id(),
            'created_at'   => current_time('mysql')
        ]);

        // email notification
        self::send_document_email($id, $file['name']);

        wp_send_json_success(['message' => 'Uploaded']);
    }

    public static function ajax_admin_get_docs() {
        check_ajax_referer('tm_admin_trademark_nonce', 'nonce');

        $id = intval($_POST['id']);

        global $wpdb;
        $files = $wpdb->prefix . "tm_trademark_files";

        $docs = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $files WHERE trademark_id = %d ORDER BY created_at DESC", $id)
        );

        ob_start();

        if ($docs) {
            echo "<ul class='tm-doc-list'>";
            foreach ($docs as $d) {
                echo "<li>
                    <strong>{$d->file_type}</strong> — {$d->file_name}
                    <a href='{$d->file_url}' target='_blank' class='button'>Download</a>
                </li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No documents uploaded.</p>";
        }

        wp_send_json_success(['html' => ob_get_clean()]);
    }



}
