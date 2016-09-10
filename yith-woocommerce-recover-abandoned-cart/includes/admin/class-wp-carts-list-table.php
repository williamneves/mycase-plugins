<?php



/**
 * Posts List Table class.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 * @access private
 */
class WP_Carts_List_Table extends WP_List_Table {

    private $post_type;

    public function __construct( $args = array() ) {
        parent::__construct( array() );

        $this->post_type = YITH_WC_Recover_Abandoned_Cart()->post_type_name;
    }

    function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'post_title'   => __( 'Info', 'ywrac' ),
            'email'        => __( 'Email', 'ywrac' ),
            'subtotal'     => __( 'Subtotal', 'ywrac' ),
            'status'       => __( 'Status', 'ywrac' ),
            'status_email' => __( 'Email sent', 'ywrac' ),
            'last_update'  => __( 'Last update', 'ywrac' ),
            'action'       => __( 'Action', 'ywrac' ),
        );
        return $columns;
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;

        $screen = get_current_screen();

        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $args = array(
            'post_type' => $this->post_type,
            'orderby'   => 'meta_value_num',
            'meta_key'  => '_cart_subtotal',
        );
        $query = new WP_Query( $args );


        $orderby = !empty( $_GET["orderby"] ) ?  $_GET["orderby"]  : '';
        $order   = !empty( $_GET["order"] ) ?  $_GET["order"]  : 'DESC';

        $link = '';
        $order_string = '';
        if ( !empty( $orderby ) & !empty( $order ) ) {
            $order_string = 'ORDER BY ywrac_pm.meta_value '.$order;
            switch( $orderby ){
                case 'email':
                    $link = " AND ( ywrac_pm.meta_key = '_user_email' ) ";
                    break;
                case 'status':
                    $link = " AND ( ywrac_pm.meta_key = '_cart_status' ) ";
                    break;
                case 'subtotal':
                    $link = " AND ( ywrac_pm.meta_key = '_cart_subtotal' ) ";
                    $order_string = 'ORDER BY ywrac_pm.meta_value_num '.$order;
                    break;
                case 'last_update':
                    $order_string = ' ORDER BY ywrac_p.post_modified ' . $order;
                    break;
                default:
                    $order_string = ' ORDER BY ' . $orderby . ' ' . $order;
            }

        }

        $query = $wpdb->prepare( "SELECT ywrac_p.* FROM $wpdb->posts as ywrac_p INNER JOIN ".$wpdb->prefix."postmeta as ywrac_pm ON ( ywrac_p.ID = ywrac_pm.post_id )
        INNER JOIN ".$wpdb->prefix."postmeta as ywrac_pm2 ON ( ywrac_p.ID = ywrac_pm2.post_id )
        WHERE 1=1 $link
        AND ( ywrac_pm2.meta_key='_cart_status' AND ywrac_pm2.meta_value='abandoned')
        AND ywrac_p.post_type = %s
        AND (ywrac_p.post_status = 'publish' OR ywrac_p.post_status = 'future' OR ywrac_p.post_status = 'draft' OR ywrac_p.post_status = 'pending' OR ywrac_p.post_status = 'private')
        GROUP BY ywrac_p.ID $order_string", $this->post_type
        );


        $totalitems = $wpdb->query($query);

        $perpage = 5;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? $_GET["paged"] : '';
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);
        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)){
            $offset=($paged-1)*$perpage;
            $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
        }

        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters

        $_wp_column_headers[$screen->id]=$columns;
        $this->items = $wpdb->get_results($query);

    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'post_title':
                return $item->$column_name;
                break;
            case 'email':
                $user_email = get_post_meta( $item->ID, '_user_email', true);
                return $user_email;
                break;
            case 'status':
                $user_email = get_post_meta( $item->ID, '_cart_status', true);
                return $user_email;
                break;
            case 'status_email':
                $email_sent = get_post_meta( $item->ID, '_email_sent', true);
                $email_status = ( $email_sent != 'no' && $email_sent != '' ) ? $email_sent : __('Not sent', 'ywrac');
                return '<span class="email_status" data-id="'.$item->ID.'">' . $email_status . '</span>';
                break;
            case 'subtotal':
                $cart_subtotal = wc_price( get_post_meta( $item->ID, '_cart_subtotal', true) );
                return $cart_subtotal;
                break;
            case 'last_update':
                $last_update = $item->post_modified;
                $seconds_offset = get_option( 'gmt_offset' ) * 3600;
                $last_update = date('Y-m-d h:i:sa', strtotime( $last_update ) + $seconds_offset );
                return $last_update;
                break;
            default:
                return ''; //Show the whole array for troubleshooting purposes
        }
    }

    function get_bulk_actions(  ) {

        $actions = $this->current_action();
        if( !empty( $actions) && isset($_POST['ywrac_cart_ids'] )){

            $carts = (array) $_POST['ywrac_cart_ids'];
            if( $actions == 'sendemail'){
                foreach ( $carts as $cart_id ) {
                    YITH_WC_Recover_Abandoned_Cart_Admin()->email_send( $cart_id );
                }
            }elseif( $actions == 'delete' ){
                foreach ( $carts as $cart_id ) {
                    wp_delete_post( $cart_id, true );
                }
            }

            $this->prepare_items();
        }

        $actions = array(
            'sendemail'    => __('Send Email', 'ywrac'),
            'delete'    => __('Delete', 'ywrac')
        );

        return $actions;
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="ywrac_cart_ids[]" value="%s" />',  $item->ID
        );
    }


    function get_sortable_columns() {
        $sortable_columns = array(
            'post_title'   => array( 'post_title', false ),
            'email'        => array( 'email', false ),
            'subtotal'     => array( 'email', false ),
            'status'       => array( 'status', false ),
            'status_email' => array( 'status_email', false ),
            'last_update'  => array( 'last_update', false ),
        );
        return $sortable_columns;
    }

    function column_post_title($item) {
        admin_url( 'post.php?post=' . $item->ID . 'action=edit' );
        $actions = array(
            'edit'   => '<a href="' . admin_url( 'post.php?post=' . $item->ID . '&action=edit' ) . '">' . __( 'View', 'ywrac' ) . '</a>',
        );

        return sprintf( '%1$s %2$s', $item->post_title, $this->row_actions( $actions ) );
    }

    function column_action( $item ) {
        $button = '<input type="button" id="sendemail" class="ywrac_send_email button action"  value="' . __( 'Send email', 'ywrac' ) . '" data-id="'.$item->ID.'">';
        return $button;
    }

}
