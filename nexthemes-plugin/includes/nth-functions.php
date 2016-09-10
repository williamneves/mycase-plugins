<?php

if( !function_exists('nth_help_tip') ) {
    function nth_help_tip( $tip, $allow_html = false ){
        $tip = esc_attr( $tip );
        return '<span class="nth-help-tip" data-tip="' . $tip . '">[?]</span>';
    }
}

if( !function_exists('nth_let_to_num') ) {
    function nth_let_to_num($size){
        $l   = substr( $size, -1 );
        $ret = substr( $size, 0, -1 );
        switch ( strtoupper( $l ) ) {
            case 'P':
                $ret *= 1024;
            case 'T':
                $ret *= 1024;
            case 'G':
                $ret *= 1024;
            case 'M':
                $ret *= 1024;
            case 'K':
                $ret *= 1024;
        }
        return $ret;
    }
}
add_action( 'wp_ajax_nth_notification', 'nth_ajax_notification');
add_action( 'wp_ajax_nopriv_nth_notification', 'nth_ajax_notification');

if( !function_exists('nth_ajax_notification') ) {
    function nth_ajax_notification(){
        $args = array(
            'post_type' 		=> 'shop_order',
            'post_status' 		=> 'wc-on-hold',
            'orderby'     		=> 'date',
            'order'       		=> 'desc',
        );
        $result = new WP_Query($args);
        $return = array();
        foreach($result->posts as $k => $post){
            $the_order = wc_get_order( $post->ID );
            $result->posts[$k]->icon = get_avatar_url($the_order->billing_email);
            $result->posts[$k]->ad_link = admin_url( 'post.php?post=' . absint( $post->ID ) . '&action=edit' );
        }

        wp_send_json($result->posts);
    }
}