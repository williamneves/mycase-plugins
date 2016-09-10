<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see           http://docs.woothemes.com/document/template-structure/
 * @author        WooThemes
 * @package       WooCommerce/Templates/Emails
 * @version       2.5.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

global $current_email;
$template           = yith_wcet_get_email_template( $current_email );
$meta               = get_post_meta( $template, '_template_meta', true );
$show_thumbs        = ( isset( $meta[ 'show_prod_thumb' ] ) ) ? $meta[ 'show_prod_thumb' ] : false;
$premium_mail_style = ( !empty( $meta[ 'premium_mail_style' ] ) ) ? $meta[ 'premium_mail_style' ] : 0;

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php if ( !$sent_to_admin ) : ?>
    <h2><?php printf( __( 'Order #%s', 'woocommerce' ), $order->get_order_number() ); ?></h2>
<?php else : ?>
    <h2><a class="link" href="<?php echo esc_url( admin_url( 'post.php?post=' . $order->id . '&action=edit' ) ); ?>"><?php printf( __( 'Order #%s', 'woocommerce' ), $order->get_order_number() ); ?></a>
        (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order->order_date ) ), date_i18n( wc_date_format(), strtotime( $order->order_date ) ) ); ?>)</h2>
<?php endif; ?>

<table id="yith-wcet-order-items-table" cellspacing="0" cellpadding="6" style="width: 100%;">
    <thead>
    <tr>
        <th id="yith-wcet-th-title-product" class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Product', 'woocommerce' ); ?></th>
        <th id="yith-wcet-th-title-quantity" class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
        <th id="yith-wcet-th-title-price" class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Price', 'woocommerce' ); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php echo $order->email_order_items_table( array(
        'show_sku'    => $sent_to_admin,
        'show_image'  => !!$show_thumbs,
        'image_size' => array( 32, 32 ),
        'plain_text'  => $plain_text,
        'sent_to_admin' => $sent_to_admin
    ) ); ?>
    </tbody>

    <?php if ( $premium_mail_style < 2 ): ?>
        <tfoot>
        <?php
        if ( $totals = $order->get_order_item_totals() ) {
            $i = 0;
            $t_count = count($totals);
            foreach ( $totals as $total ) {
                $i++;
                $last_class = $i == $t_count ? 'last' : 'not_last';
                ?>
                <tr>
                <th class="yith-wcet-order-items-table-element<?php if ( $i == 1 )
                    echo '-bigtop'; ?> <?php echo $last_class; ?>" scope="row" colspan="2"><?php echo $total[ 'label' ]; ?></th>
                <td class="yith-wcet-order-items-table-element<?php if ( $i == 1 )
                    echo '-bigtop'; ?> <?php echo $last_class; ?>"><?php echo $total[ 'value' ]; ?></td>
                </tr><?php
            }
        }
        ?>
        </tfoot>
    <?php endif ?>
</table>

<?php if ( $premium_mail_style > 1 ): ?>
    <table width="100%" height="20px"><tr></tr></table>
    <table class="yith-wcet-two-columns" width="100%">
        <tr>
            <td width="50%" style="padding:0px">

            </td>
            <td width="50%" style="padding:0px">
                    <table id="yith-wcet-foot-price-list">
                        <?php
                        if ( $totals = $order->get_order_item_totals() ) {
                            $i       = 0;
                            $t_count = count( $totals );
                            foreach ( $totals as $total ) {
                                $i++;
                                $last_class = $i == $t_count ? 'last' : 'not_last';
                                ?>
                                <tr>
                                <th <?php if ( $i == $t_count ) {
                                    echo 'id="yith-wcet-total-title"';
                                } ?> class="<?php echo $last_class; ?>" scope="row" colspan="2"><?php echo $total[ 'label' ]; ?></th>
                                <td <?php if ( $i == $t_count ) {
                                    echo 'id="yith-wcet-total-price"';
                                } ?> class="<?php echo $last_class; ?>"><?php echo $total[ 'value' ]; ?></td>
                                </tr><?php
                            }
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </table>
<?php endif ?>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
