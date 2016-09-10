<?php

defined( 'ABSPATH' ) or exit;

/*
 *  Customer
 */

global $wpdb;
add_thickbox();

?>

<div id="yith-woocommerce-customer-history">

    <?php

    $user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;
    if ( $user_id > 0 && current_user_can( 'manage_options' ) ) :
        $user = get_user_by( 'id', $user_id ); ?>

        <div id="customer" class="wrap">

            <h1><?php echo __( 'Customer', 'yith-woocommerce-customer-history' ); ?></h1>

            <p class="description"></p>

            <div class="customer-profile">

                <div class="customer-avatar"><?php echo get_avatar( $user_id ); ?></div>

                <div class="customer-info">
                    <table>
                        <tr>
                            <td>
                                <?php echo __( 'User ID', 'yith-woocommerce-customer-history' ); ?>:<br />
                                <?php echo __( 'Name', 'yith-woocommerce-customer-history' ); ?>:<br />
                                <?php echo __( 'Username', 'yith-woocommerce-customer-history' ); ?>:<br />
                                <?php echo __( 'Email', 'yith-woocommerce-customer-history' ); ?>:<br />
                                <?php echo __( 'Website', 'yith-woocommerce-customer-history' ); ?>:
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <strong><?php echo $user->ID; ?></strong><br />
                                <strong><?php echo $user->display_name; ?></strong><br />
                                <strong><?php echo $user->user_login; ?></strong><br />
                                <a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a><br />
                                <a href="<?php echo $user->user_url; ?>" target="_blank"><?php echo $user->user_url; ?></a>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="customer-info">
                    <table>
                        <tr>
                            <td>
                                <?php echo __( 'Total Orders', 'yith-woocommerce-customer-history' ); ?>:<br />
                                <?php echo __( 'Pending Orders', 'yith-woocommerce-customer-history' ); ?>:<br />
                                <?php echo __( 'Refunded Orders', 'yith-woocommerce-customer-history' ); ?>:<br />
                                <?php echo __( 'Orders average', 'yith-woocommerce-customer-history' ); ?>:<br />
                                <?php echo __( 'Total Spent', 'yith-woocommerce-customer-history' ); ?>:
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <?php
                                    // $order_count = wc_get_customer_order_count( $user->ID );
                                    // $total_spent = wc_get_customer_total_spent( $user->ID );
                                    $total_spent = yith_ch_get_customer_total_spent( $user->ID );

                                    $order_count = count( get_posts( array(
                                        'numberposts'   => -1,
                                        'meta_key'      => '_customer_user',
                                        'meta_value'    => $user_id,
                                        'post_type'     => 'shop_order',
                                        'post_status'   =>  'any',
                                        'post_parent'   => '0',
                                    ) ) );

                                    $pending_orders_count = count( get_posts( array(
                                        'numberposts'   => -1,
                                        'meta_key'      => '_customer_user',
                                        'meta_value'    => $user_id,
                                        'post_type'     => 'shop_order',
                                        'post_status'   =>  array( 'pending', 'wc-pending'),
                                        'post_parent'   => '0',
                                    ) ) );

                                    $refunded_orders_count = count( get_posts( array(
                                        'numberposts'   => -1,
                                        'meta_key'      => '_customer_user',
                                        'meta_value'    => $user_id,
                                        'post_type'     => 'shop_order',
                                        'post_status'   =>  array( 'refunded', 'wc-refunded'),
                                        'post_parent'   => '0',
                                    ) ) );
                                ?>
                                <strong><?php echo $order_count; ?></strong><br />
                                <strong><?php echo $pending_orders_count; ?></strong><br />
                                <strong><?php echo $refunded_orders_count; ?></strong><br />
                                <strong><?php echo $order_count > 0 ? wc_price( $total_spent / $order_count ) : wc_price( $total_spent ); ?></strong><br />
                                <strong><?php echo wc_price( $total_spent ); ?></strong>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="customer-actions">
                    <table>
                        <tr>
                            <td>

                                <br />
                                <br />
                                <br />
                                <i class="fa fa-pencil" aria-hidden="true"></i><a href="user-edit.php?user_id=<?php echo $user->ID; ?>&KeepThis=true&TB_iframe=true&modal=false" onclick="return false;" class="thickbox"><?php echo __( 'Edit this user', 'yith-woocommerce-customer-history' ); ?></a><br />
                                <i class="fa fa-envelope" aria-hidden="true"></i><a href="admin.php?page=yith-wcch-email.php&customer_id=<?php echo $user->ID; ?>"><?php echo __( 'Send an email', 'yith-woocommerce-customer-history' ); ?></a>
                            </td>
                            <td>&nbsp;</td>
                            <td>

                            </td>
                        </tr>
                    </table>
                </div>

                <div class="clear"></div>

            </div>

            <?php

            $customer_orders = get_posts( array(
                'numberposts' => -1,
                'meta_key'    => '_customer_user',
                'meta_value'  => $user_id,
                'post_type'   => 'shop_order',
                'post_status' =>  array( 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed',
                                         'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ),
            ) );

            ?>

            <h2><i class="fa fa-shopping-bag" aria-hidden="true"></i><?php echo __( 'Latest Orders', 'yith-woocommerce-customer-history' ); ?></h2>

            <table class="wp-list-table widefat fixed striped posts">
                <tr>
                    <th><?php echo __( 'Order', 'yith-woocommerce-customer-history' ); ?></th>
                    <th><?php echo __( 'Date', 'yith-woocommerce-customer-history' ); ?></th>
                    <th><?php echo __( 'Type', 'yith-woocommerce-customer-history' ); ?></th>
                    <th><?php echo __( 'Status', 'yith-woocommerce-customer-history' ); ?></th>
                    <th><?php echo __( 'Items', 'yith-woocommerce-customer-history' ); ?></th>
                    <th><?php echo __( 'Refunded Items', 'yith-woocommerce-customer-history' ); ?></th>
                    <th><?php echo __( 'Refunded', 'yith-woocommerce-customer-history' ); ?></th>
                    <th><?php echo __( 'Total', 'yith-woocommerce-customer-history' ); ?></th>
                    <th><?php echo __( 'Actions', 'yith-woocommerce-customer-history' ); ?></th>
                </tr>

                <?php foreach ( $customer_orders as $order ) :

                    if ( wp_get_post_parent_id( $order->ID ) == 0 ) :
                        $order = new WC_Order( $order->ID ); ?>
                        
                        <tr>
                            <td><a href="post.php?post=<?php echo $order->id; ?>&action=edit&KeepThis=true&TB_iframe=true&modal=false" onclick="return false;" class="thickbox">#<?php echo $order->id; ?></a></td>
                            <td><?php echo $order->order_date; ?></td>
                            <td><?php echo ucfirst( $order->order_type ); ?></td>
                            <td><?php echo ucfirst( str_replace( 'wc-', '', $order->post_status ) ); ?></td>
                            <td><?php foreach ( $order->get_items() as $line ) { echo $line['qty'] . ' x <a href="post.php?post=' . $line['product_id'] . '&action=edit&KeepThis=true&TB_iframe=true&modal=false" onclick="return false;" class="thickbox">' . $line['name'] . '</a><br />'; } ?></td>
                            <td><?php echo $order->get_total_qty_refunded(); ?></td>
                            <td><?php echo wc_price( $order->get_total_refunded() ); ?></td>
                            <td><?php echo $order->get_formatted_order_total(); ?></td>
                            <td><a href="post.php?post=<?php echo $order->id; ?>&action=edit&KeepThis=true&TB_iframe=true&modal=false" onclick="return false;" class="thickbox button"><?php echo __( 'View', 'yith-woocommerce-customer-history' ); ?></a></td>
                        </tr>

                    <?php endif; ?>
                <?php endforeach; ?>

            </table>

            <h2 style="margin-top: 50px;"><i class="fa fa-search" aria-hidden="true"></i><?php echo __( 'Search History', 'yith-woocommerce-customer-history' ); ?></h2>

            <table class="wp-list-table widefat fixed striped posts">
                <tr>
                    <th class="date" style="width: 15%;"><?php echo __( 'Date', 'yith-woocommerce-customer-history' ); ?></th>
                    <th style="width: 85%;"><?php echo __( 'Key', 'yith-woocommerce-customer-history' ); ?></th>
                </tr>

                <?php

                $query = "SELECT * FROM {$wpdb->prefix}yith_wcch_sessions WHERE user_id='$user->ID' AND url LIKE 'ACTION::search::%' ORDER BY reg_date DESC LIMIT 0,20";
                $rows = $wpdb->get_results( $query );
                foreach ( $rows as $key => $value ) :
                    $url_array = explode( '::', $value->url );
                    $tr_class = 'action action-' . $url_array['1'];
                    ?>
                    <tr class="<?php echo $tr_class; ?>">
                        <td class="date"><?php echo $value->reg_date; ?></td>
                        <td><?php echo '<strong>' . $url_array['2'] . '</strong>'; ?></td>
                    </tr>
                <?php endforeach; ?>

            </table>

            <h2 style="margin-top: 50px;"><i class="fa fa-eye" aria-hidden="true"></i><?php echo __( 'Sessions History', 'yith-woocommerce-customer-history' ); ?></h2>

            <table class="wp-list-table widefat fixed striped posts">
                <tr>
                    <th class="date" style="width: 15%;"><?php echo __( 'Date', 'yith-woocommerce-customer-history' ); ?></th>
                    <th style="width: 85%;"><?php echo __( 'URL', 'yith-woocommerce-customer-history' ); ?></th>
                </tr>

                <?php

                $query = "SELECT * FROM {$wpdb->prefix}yith_wcch_sessions WHERE user_id='$user->ID' ORDER BY reg_date DESC LIMIT 0,50";
                $rows = $wpdb->get_results( $query );
                foreach ( $rows as $key => $value ) :

                    $tr_class = '';
                    $user = get_user_by( 'id', $value->user_id );
                    $is_session_action = false;

                    if ( strpos( $value->url, 'CTION::' ) == 1 ) {

                        $is_session_action = true;

                        $url_array = explode( '::', $value->url );
                        $tr_class = 'action action-' . $url_array['1'];
                        
                        switch ( $url_array['1'] ) {
                            case 'search':

                                $url = __( 'Search', 'yith-woocommerce-customer-history' ) . ': ' . $url_array['2'];
                                break;
                                
                             case 'add_to_cart':

                                $url = __( 'Add to cart', 'yith-woocommerce-customer-history' ) . ': x' . $url_array['3'] . ' products #' . $url_array['2'];
                                break;

                            case 'new_order':
                                
                                $url = __( 'New order', 'yith-woocommerce-customer-history' ) . ': #' . $url_array['2'];
                                break;

                            default:
                                $url = 'default_action';
                                break;
                        }

                    } else { $url = get_site_url() . '/' . $value->url; }

                    if ( ! isset( $url_array['1'] ) || $url_array['1'] != 'search' ) :

                    ?>

                    <tr class="<?php echo ( isset( $user->caps['administrator'] ) && $user->caps['administrator'] ? 'admin' : '' ) . ' ' . $tr_class; ?>">
                        <td class="date"><?php echo $value->reg_date; ?></td>
                        <td>
                            <?php if ( $is_session_action ) : echo '<strong>' . $url . '</strong>'; else : ?>
                                <a href="<?php echo $url; ?>?KeepThis=true&TB_iframe=true&modal=false" onclick="return false;" class="thickbox"><?php echo $url; ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endif; endforeach; ?>

            </table>

            <style>

                #customer table tr.action-search { background-color: #def; }
                #customer table tr.action-add_to_cart { background-color: #fed; }
                #customer table tr.action-new_order { background-color: #dfd; }

            </style>

        </div>

    <?php else : ?>

        <div class="wrap">
            <h1><?php echo __( 'Error', 'yith-woocommerce-customer-history' ); ?></h1>
            <p><?php echo __( "I'm sorry, you are not allowed to see this page!", 'yith-woocommerce-customer-history' ); ?><br /><br /><a href="#"><?php echo __( 'Back', 'yith-woocommerce-customer-history' ); ?></a></p>
        </div>

    <?php endif; ?>

</div>

<script>jQuery( document ).ready(function() { yit_open_admin_menu( 'toplevel_page_yith-wcch-customers' ); });</script>