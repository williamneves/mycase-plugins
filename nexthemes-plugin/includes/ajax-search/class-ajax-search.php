<?php

/**
 * Package: TheShopier.
 * User: kinhdon
 * Date: 10/31/2015
 * Vertion: 1.0
 */
class AjaxSeach
{
    private $__default = array();
    function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

        add_action( 'wp_ajax_nth_ajax_search_products', array( $this, 'ajax_search_products' ) );
        add_action( 'wp_ajax_nopriv_nth_ajax_search_products', array( $this, 'ajax_search_products' ) );

        add_action('init', array($this,'addImageSize') );
    }

    public function load_scripts(){
        wp_register_script( 'jquery.autocomplete.min', NEXTHEMES_PLUGIN_URL . 'assets/js/jquery.autocomplete.min.js', array("jquery"), '1.2.24', true );
        wp_enqueue_script( 'jquery.autocomplete.min' );
    }

    public function ajax_search_products(){
        global $woocommerce;
        $ajax_settings = NexThemes_Plg::$nexthemes_settings['nexthemes_ajaxsearch_settings'];
        $st_def = array(
            'min_char'      => '3',
            'result_limit'  => '3'
        );
        if( ! empty( $ajax_settings['min_char'] ) ) $st_def['min_char'] = $ajax_settings['min_char'];
        if( ! empty( $ajax_settings['result_limit'] ) ) $st_def['result_limit'] = $ajax_settings['result_limit'];

        $search_keyword =  $_REQUEST['query'];
        $ordering_args = $woocommerce->query->get_catalog_ordering_args( 'title', 'asc' );
        $suggestions   = array();

        $args = array(
            's'                   => $search_keyword,
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => 1,
            'orderby'             => $ordering_args['orderby'],
            'order'               => $ordering_args['order'],
            'posts_per_page'      => 5,
            'suppress_filters'    => false,
            'meta_query'          => array(
                array(
                    'key'     => '_visibility',
                    'value'   => array( 'search', 'visible' ),
                    'compare' => 'IN'
                )
            )
        );

        if( absint($st_def['result_limit']) > 0 ) $args['posts_per_page'] = absint($st_def['result_limit']);

        if ( isset( $_REQUEST['product_cat'] ) ) {
            $args['tax_query'] = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $_REQUEST['product_cat']
                ) );
        }

        $products = get_posts( $args );

        if ( !empty( $products ) ) {
            foreach ( $products as $post ) {
                $product = wc_get_product( $post );
                $thumb_id = get_post_thumbnail_id($product->id);
                $image  	= wp_get_attachment_image( $thumb_id, 'woo_ajax_search_thumb', 0, array("class" => "suggestion-thumbnail") );
                $suggestions[] =  array(
                    'id'    => $product->id,
                    'value' => strip_tags($product->get_title()),
                    'url'   => $product->get_permalink(),
                    'img'   => $image,
                    'cats'  => $product->get_categories(',', '<p class="suggestion-cats">', '</p>'),
                    'price' => '<div class="suggestion-prices">'.$product->get_price_html().'</div>'
                );
            }
        }
        else {
            $suggestions[] = array(
                'id'    => - 1,
                'value' => __( 'No results', 'nexthemes-plugin' ),
                'url'   => '',
            );
        }
        wp_reset_postdata();

        $suggestions = array(
            'suggestions' => $suggestions
        );

        echo json_encode( $suggestions );
        die();
    }

    public function addImageSize(){
        $ajax_settings = NexThemes_Plg::$nexthemes_settings['nexthemes_ajaxsearch_settings'];
        $img_size = array(
            'width'     => 32,
            'height'    => 32,
            'crop'      => '1',
        );
        if( !empty($ajax_settings['search_thumb']) ) {
            $img_size = wp_parse_args($ajax_settings['search_thumb'], $img_size);
        }

        add_image_size('woo_ajax_search_thumb', absint($img_size['width']), absint($img_size['height']), absint($img_size['crop']));
    }

}

new AjaxSeach();