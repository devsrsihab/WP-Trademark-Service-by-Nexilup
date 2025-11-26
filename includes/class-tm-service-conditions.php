<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TM_Service_Conditions {

    public static function init() {
        // AJAX actions
        add_action( 'wp_ajax_tm_get_service_condition',   [ __CLASS__, 'ajax_get_condition' ] );
        add_action( 'wp_ajax_tm_save_service_condition',  [ __CLASS__, 'ajax_save_condition' ] );
        add_action( 'wp_ajax_tm_delete_service_condition',[ __CLASS__, 'ajax_delete_condition' ] );
    }

    public static function table() {
        return TM_Database::table_name( 'service_conditions' );
    }

    public static function countries_table() {
        return TM_Database::table_name( 'countries' );
    }

    /**
     * Step labels for admin UI
     */
    public static function get_step_labels() {
        return [
            1 => 'Step 1 – Comprehensive Study',
            2 => 'Step 2 – Registration / Filing',
            3 => 'Step 3 – Owner Information',
            4 => 'Step 4 – Confirmation / Summary',
        ];
    }

    /**
     * Paginated list with JOIN to countries
     */
    public static function get_paginated( $paged = 1, $per_page = 20 ) {
        global $wpdb;

        $table_sc = self::table();
        $table_c  = self::countries_table();

        $offset = ( $paged - 1 ) * $per_page;

        // Get rows with country_name
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT sc.*, c.country_name
                 FROM {$table_sc} sc
                 LEFT JOIN {$table_c} c ON sc.country_id = c.id
                 ORDER BY c.country_name ASC, sc.step_number ASC
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        // Total (no need to join)
        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_sc}" );

        return [
            'items'     => $items,
            'total'     => $total,
            'per_page'  => $per_page,
            'current'   => $paged,
            'max_pages' => $total > 0 ? ceil( $total / $per_page ) : 1,
        ];
    }

    /**
     * AJAX: get one condition row by ID
     */
    public static function ajax_get_condition() {
        check_ajax_referer( 'tm_service_conditions_nonce', 'nonce' );

        if ( empty( $_POST['id'] ) ) {
            wp_send_json_error( [ 'message' => 'Missing ID.' ] );
        }

        global $wpdb;
        $table = self::table();

        $id = intval( $_POST['id'] );

        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
        );

        if ( ! $row ) {
            wp_send_json_error( [ 'message' => 'Condition not found.' ] );
        }

        wp_send_json_success( [
            'id'          => $row->id,
            'country_id'  => $row->country_id,
            'step_number' => $row->step_number,
            'content'     => $row->content,
        ] );
    }

    /**
     * AJAX: save (create or update) condition
     */
    public static function ajax_save_condition() {
        check_ajax_referer( 'tm_service_conditions_nonce', 'nonce' );

        global $wpdb;
        $table = self::table();

        $id         = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $country_id = isset( $_POST['country'] ) ? intval( $_POST['country'] ) : 0;
        $step       = isset( $_POST['step'] ) ? intval( $_POST['step'] ) : 0;
        $content    = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';

        if ( ! $country_id || ! $step ) {
            wp_send_json_error( [ 'message' => 'Country and Step are required.' ] );
        }

        $now = current_time( 'mysql' );

        // If editing by ID
        if ( $id ) {
            $updated = $wpdb->update(
                $table,
                [
                    'country_id'  => $country_id,
                    'step_number' => $step,
                    'content'     => $content,
                    'updated_at'  => $now,
                ],
                [ 'id' => $id ]
            );

            if ( false === $updated ) {
                wp_send_json_error( [ 'message' => 'Database update failed.' ] );
            }
        } else {
            // Check if one already exists for this country+step → update instead
            $existing_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table} WHERE country_id = %d AND step_number = %d",
                    $country_id,
                    $step
                )
            );

            if ( $existing_id ) {
                $wpdb->update(
                    $table,
                    [
                        'content'    => $content,
                        'updated_at' => $now,
                    ],
                    [ 'id' => $existing_id ]
                );
            } else {
                $wpdb->insert(
                    $table,
                    [
                        'country_id'  => $country_id,
                        'step_number' => $step,
                        'content'     => $content,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]
                );
            }
        }

        wp_send_json_success();
    }

    /**
     * AJAX: delete
     */
    public static function ajax_delete_condition() {
        check_ajax_referer( 'tm_service_conditions_nonce', 'nonce' );

        if ( empty( $_POST['id'] ) ) {
            wp_send_json_error( [ 'message' => 'Missing ID.' ] );
        }

        global $wpdb;
        $table = self::table();

        $id = intval( $_POST['id'] );

        $deleted = $wpdb->delete( $table, [ 'id' => $id ] );

        if ( ! $deleted ) {
            wp_send_json_error( [ 'message' => 'Nothing deleted.' ] );
        }

        wp_send_json_success();
    }
}
