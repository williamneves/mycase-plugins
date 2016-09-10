<?php
defined( 'ABSPATH' ) or exit;

/*
 *  Sessions
 */

global $wpdb;
add_thickbox();

$page = isset( $_GET['p'] ) && $_GET['p'] > 1 ? $_GET['p'] : 1;
$results_per_page = get_option( 'yith-wcch-results_per_page' ) > 0 ? get_option( 'yith-wcch-results_per_page' ) : 50;
$sessions_offset = ( $page - 1 ) * $results_per_page;

$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}yith_wcch_sessions" );
$num_rows = $wpdb->num_rows;
$max_pages = ceil( $num_rows / $results_per_page );

?>

<div id="yith-woocommerce-customer-history">
    <div id="sessions" class="wrap">

        <h1><?php echo __( 'Sessions', 'yith-woocommerce-customer-history' ); ?></h1>
        <p><?php echo __( 'Complete sessions list.', 'yith-woocommerce-customer-history' ); ?></p>

        <div class="tablenav top">
            <div class="tablenav-pages">
                <div class="pagination-links">
                    <?php echo __( 'Total', 'yith-woocommerce-customer-history' ) . ': ' . $num_rows; ?> &nbsp; | &nbsp;
                    <?php echo __( 'Page', 'yith-woocommerce-customer-history' ) . ': ' . $page . ' of ' . $max_pages; ?> &nbsp;
                    <?php if ( $page > 1 ) : ?>
                    <a class="prev-page" href="admin.php?page=yith-wcch-sessions.php&p=1"><span aria-hidden="true">‹‹</span></a>
                    <a class="prev-page" href="admin.php?page=yith-wcch-sessions.php&p=<?php echo $page - 1; ?>"><span aria-hidden="true">‹</span></a>
                    <?php else : ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹‹</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <?php if ( $page < $max_pages ) : ?>
                    <a class="next-page" href="admin.php?page=yith-wcch-sessions.php&p=<?php echo $page + 1; ?>"><span aria-hidden="true">›</span></a>
                    <a class="next-page" href="admin.php?page=yith-wcch-sessions.php&p=<?php echo $max_pages; ?>"><span aria-hidden="true">››</span></a>
                    <?php else : ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">››</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped posts">
            <tr>
                <th class="user"><?php echo __( 'User', 'yith-woocommerce-customer-history' ); ?></th>
                <th><?php echo __( 'URL', 'yith-woocommerce-customer-history' ); ?></th>
                <th class="date"><?php echo __( 'Date', 'yith-woocommerce-customer-history' ); ?></th>
            </tr>

            <?php

            $query = "SELECT * FROM {$wpdb->prefix}yith_wcch_sessions ORDER BY reg_date DESC LIMIT $sessions_offset,$results_per_page";
            $rows = $wpdb->get_results( $query );
            if ( count( $rows ) > 0 ) :

                foreach ( $rows as $key => $value ) :

                    $tr_class = '';
                    $user = $value->user_id > 0 ? get_user_by( 'id', $value->user_id ) : NULL;
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

                    ?>

                    <tr class="<?php echo ( isset( $user->caps['administrator'] ) && $user->caps['administrator'] ? 'admin' : '' ) . ' ' . $tr_class; ?>">
                        <td class="user">
                            <?php if ( $user == NULL ) : echo __( 'Guest', 'yith-woocommerce-customer-history' ); else : ?>
                                <a href="admin.php?page=yith-wcch-customer.php&user_id=<?php echo esc_html( $user->ID ); ?>"><?php echo $user->first_name . ' ' . $user->last_name; ?></a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $is_session_action ) : echo '<strong>' . $url . '</strong>'; else : ?>
                                <a href="<?php echo $url; ?>?KeepThis=true&TB_iframe=true&modal=false" onclick="return false;" class="thickbox"><?php echo $url; ?></a>
                            <?php endif; ?>
                        </td>
                        <td class="date"><?php echo $value->reg_date; ?></td>
                    </tr>

                <?php endforeach; ?>
            <?php endif; ?>

        </table>

        <div class="tablenav top">
            <div class="tablenav-pages">
                <div class="pagination-links">
                    <?php echo __( 'Total', 'yith-woocommerce-customer-history' ) . ': ' . $num_rows; ?> &nbsp; | &nbsp;
                    <?php echo __( 'Page', 'yith-woocommerce-customer-history' ) . ': ' . $page . ' of ' . $max_pages; ?> &nbsp;
                    <?php if ( $page > 1 ) : ?>
                    <a class="prev-page" href="admin.php?page=yith-wcch-sessions.php&p=1"><span aria-hidden="true">‹‹</span></a>
                    <a class="prev-page" href="admin.php?page=yith-wcch-sessions.php&p=<?php echo $page - 1; ?>"><span aria-hidden="true">‹</span></a>
                    <?php else : ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹‹</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <?php if ( $page < $max_pages ) : ?>
                    <a class="next-page" href="admin.php?page=yith-wcch-sessions.php&p=<?php echo $page + 1; ?>"><span aria-hidden="true">›</span></a>
                    <a class="next-page" href="admin.php?page=yith-wcch-sessions.php&p=<?php echo $max_pages; ?>"><span aria-hidden="true">››</span></a>
                    <?php else : ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">››</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>

            #sessions table tr td { white-space: nowrap; }
            #sessions table tr .date, #sessions table tr .user { width: 15%; }

            /* #sessions table tr.admin { display: none; }
            #sessions table tr.admin td.user a { color: #a00; } */

            #sessions table tr.action-search { background-color: #def; }
            #sessions table tr.action-add_to_cart { background-color: #fed; }
            #sessions table tr.action-new_order { background-color: #dfd; }

        </style>

    </div>
</div>