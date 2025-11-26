<?php
if (!defined('ABSPATH')) exit;

class TM_Upload {

    public static function init() {
        add_action('wp_ajax_tm_upload_logo', [__CLASS__, 'ajax_upload_logo']);
        add_action('wp_ajax_nopriv_tm_upload_logo', [__CLASS__, 'ajax_upload_logo']);
    }

    public static function ajax_upload_logo() {
        check_ajax_referer('tm_nonce','nonce');

        if (empty($_FILES['logo'])) {
            wp_send_json_error(['message' => 'No file received']);
        }

        $file = $_FILES['logo'];

        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        if (!in_array($file['type'], $allowed)) {
            wp_send_json_error(['message' => 'Invalid image type']);
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(['message' => 'Max 5MB allowed']);
        }

        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/media.php';
        require_once ABSPATH.'wp-admin/includes/image.php';

        $upload = wp_handle_upload($file, ['test_form' => false]);
        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name($file['name']),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        $attach_id = wp_insert_attachment($attachment, $upload['file']);
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        wp_send_json_success([
            'id'  => $attach_id,
            'url' => wp_get_attachment_url($attach_id)
        ]);
    }
}
