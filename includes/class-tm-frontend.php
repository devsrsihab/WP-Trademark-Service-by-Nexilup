<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TM_Frontend {

    public static function init() {
        add_shortcode( 'tm_country_table', array( __CLASS__, 'shortcode_country_table' ) );
        add_shortcode( 'tm_country_single', array( __CLASS__, 'shortcode_country_single' ) );
        add_shortcode( 'tm_order_form_step1', array( __CLASS__, 'shortcode_order_form_step1' ) );
        add_shortcode( 'tm_order_form_step2', array( __CLASS__, 'shortcode_order_form_step2' ) );
        add_shortcode( 'tm_my_trademarks', array( __CLASS__, 'shortcode_my_trademarks' ) );
    }

    public static function shortcode_country_table( $atts ) {
        ob_start();

        $countries = TM_Database::get_countries();

        echo '<div class="tm-country-table">';
        echo '<h2>' . esc_html__( 'Trademark Registration Prices by Country', 'wp-tms-nexilup' ) . '</h2>';

        if ( ! empty( $countries ) ) {
            echo '<table class="widefat striped">';
            echo '<thead><tr><th>' . esc_html__( 'Country', 'wp-tms-nexilup' ) . '</th><th>' . esc_html__( 'Actions', 'wp-tms-nexilup' ) . '</th></tr></thead><tbody>';

            foreach ( $countries as $country ) {
                echo '<tr>';
                echo '<td>' . esc_html( $country->name ) . '</td>';
                echo '<td><a href="#" class="button">' . esc_html__( 'View Prices (to build)', 'wp-tms-nexilup' ) . '</a></td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>' . esc_html__( 'No countries available.', 'wp-tms-nexilup' ) . '</p>';
        }

        echo '</div>';

        return ob_get_clean();
    }

    public static function shortcode_country_single( $atts ) {
        ob_start();
        echo '<div class="tm-country-single">';
        echo '<h2>' . esc_html__( 'Country single page placeholder', 'wp-tms-nexilup' ) . '</h2>';
        echo '<p>' . esc_html__( 'Here you will render the 3-step description and Order buttons like Nominus.', 'wp-tms-nexilup' ) . '</p>';
        echo '</div>';
        return ob_get_clean();
    }

    public static function shortcode_order_form_step1( $atts ) {
        ob_start();
        echo '<div class="tm-order-form tm-step1">';
        echo '<h2>' . esc_html__( 'Step 1 – Comprehensive Trademark Study (Form placeholder)', 'wp-tms-nexilup' ) . '</h2>';
        echo '<p>' . esc_html__( 'Here you will build the full Step 1 form (type, mark, goods/services, etc.).', 'wp-tms-nexilup' ) . '</p>';
        echo '</div>';
        return ob_get_clean();
    }

    public static function shortcode_order_form_step2( $atts ) {
        ob_start();
        echo '<div class="tm-order-form tm-step2">';
        echo '<h2>' . esc_html__( 'Step 2 – Trademark Application Filing (Form placeholder)', 'wp-tms-nexilup' ) . '</h2>';
        echo '<p>' . esc_html__( 'Here you will build the full Step 2 form (type, classes, priority claim, POA, etc.).', 'wp-tms-nexilup' ) . '</p>';
        echo '</div>';
        return ob_get_clean();
    }

    public static function shortcode_my_trademarks( $atts ) {
        ob_start();
        echo '<div class="tm-my-trademarks">';
        echo '<h2>' . esc_html__( 'My Trademarks (Dashboard placeholder)', 'wp-tms-nexilup' ) . '</h2>';
        echo '<p>' . esc_html__( 'Here you will list user trademarks with status, country, expiration, etc.', 'wp-tms-nexilup' ) . '</p>';
        echo '</div>';
        return ob_get_clean();
    }
}
