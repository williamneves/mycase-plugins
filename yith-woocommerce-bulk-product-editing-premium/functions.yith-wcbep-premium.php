<?php
/**
 * Functions
 *
 * @author  Yithemes
 * @package YITH WooCommerce Bulk Product Editing
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

if ( !function_exists( 'yith_wcbep_get_template' ) ) {
    function yith_wcbep_get_template( $template, $args = array() ) {
        extract( $args );
        include( YITH_WCBEP_TEMPLATE_PATH . '/' . $template );
    }
}

if ( !function_exists( 'yith_wcbep_strContains' ) ) {
    function yith_wcbep_strContains( $haystack, $needle ) {
        return stripos( $haystack, $needle ) !== false;
    }
}

if ( !function_exists( 'yith_wcbep_strStartsWith' ) ) {
    function yith_wcbep_strStartsWith( $haystack, $needle ) {
        return $needle === "" || strirpos( $haystack, $needle, -strlen( $haystack ) ) !== false;
    }
}

if ( !function_exists( 'yith_wcbep_strEndsWith' ) ) {
    function yith_wcbep_strEndsWith( $haystack, $needle ) {
        return $needle === "" || ( ( $temp = strlen( $haystack ) - strlen( $needle ) ) >= 0 && stripos( $haystack, $needle, $temp ) !== false );
    }
}

if ( !function_exists( 'yith_wcbep_posts_filter_where' ) ) {
    function yith_wcbep_posts_filter_where( $where = '' ) {
        $f_title_sel = !empty( $_REQUEST[ 'f_title_select' ] ) ? $_REQUEST[ 'f_title_select' ] : 'cont';
        $f_title_val = isset( $_REQUEST[ 'f_title_value' ] ) ? $_REQUEST[ 'f_title_value' ] : '';

        // Filter Title
        if ( isset( $f_title_val ) && strlen( $f_title_val ) > 0 ) {
            $compare = 'LIKE';
            $value   = '%' . $f_title_val . '%';
            switch ( $f_title_sel ) {
                case 'cont':
                    $compare = 'LIKE';
                    break;
                case 'notcont':
                    $compare = 'NOT LIKE';
                    break;
                case 'starts':
                    $compare = 'LIKE';
                    $value   = $f_title_val . '%';
                    break;
                case 'ends':
                    $compare = 'LIKE';
                    $value   = '%' . $f_title_val;
                    break;
            }

            $where .= " AND post_title {$compare} '{$value}'";
        }

        return $where;
    }
}


?>