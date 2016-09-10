<?php
if ( !defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAC_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements helper functions for YITH WooCommerce Recover Abandoned Cart
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */

if ( !function_exists( 'yith_ywrac_locate_template' ) ) {
    /**
     * Locate the templates and return the path of the file found
     *
     * @param string $path
     * @param array  $var
     *
     * @return void
     * @since 1.0.0
     */
    function yith_ywrac_locate_template( $path, $var = NULL ) {
        global $woocommerce;

        if ( function_exists( 'WC' ) ) {
            $woocommerce_base = WC()->template_path();
        }
        elseif ( defined( 'WC_TEMPLATE_PATH' ) ) {
            $woocommerce_base = WC_TEMPLATE_PATH;
        }
        else {
            $woocommerce_base = $woocommerce->plugin_path() . '/templates/';
        }

        $template_woocommerce_path = $woocommerce_base . $path;
        $template_path             = '/' . $path;
        $plugin_path               = YITH_YWRAC_DIR . 'templates/' . $path;

        $located = locate_template( array(
            $template_woocommerce_path, // Search in <theme>/woocommerce/
            $template_path,             // Search in <theme>/
            $plugin_path                // Search in <plugin>/templates/
        ) );

        if ( !$located && file_exists( $plugin_path ) ) {
            return apply_filters( 'yith_ywrac_locate_template', $plugin_path, $path );
        }

        return apply_filters( 'yith_ywrac_locate_template', $located, $path );
    }
}