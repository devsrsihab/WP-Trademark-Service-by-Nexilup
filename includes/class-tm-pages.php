<?php
if (!defined('ABSPATH')) exit;

class TM_Pages {

    public static $pages = [
        'tm/trademark-choose/order-form' => 'Trademark choose – Order Form',
        'tm/trademark-registration/order-form'        => 'Trademark Registration – Order Form',
        'myaccount/my-trademarks/active-trademarks'   => 'Active Trademarks',
        'tm/trademark-confirmation/order-review'      => 'Trademark Order Review',
    ];

    public static function init() {
        // reserved
    }

    /**
     * Auto-create required pages
     */
    public static function create_required_pages() {

        $created_pages = [];

        foreach (self::$pages as $path => $final_title) {

            $segments  = explode('/', trim($path, '/'));
            $parent_id = 0;
            $full_path = '';

            foreach ($segments as $index => $segment) {

                $full_path .= ($index === 0) ? $segment : "/$segment";
                $existing = get_page_by_path($full_path);

                if ($existing) {
                    $parent_id = $existing->ID;
                    continue;
                }

                // Last segment uses final title, otherwise auto title
                $title = ($index === count($segments) - 1)
                    ? $final_title
                    : ucfirst(str_replace('-', ' ', $segment));

                $page_id = wp_insert_post([
                    'post_title'   => $title,
                    'post_name'    => sanitize_title($segment),
                    'post_type'    => 'page',
                    'post_status'  => 'publish',
                    'post_parent'  => $parent_id,
                    'post_content' => '',
                ]);

                if ($page_id) {
                    $created_pages[] = $page_id;
                    $parent_id = $page_id;
                }
            }
        }

        update_option('tm_created_pages', $created_pages);
    }
}
