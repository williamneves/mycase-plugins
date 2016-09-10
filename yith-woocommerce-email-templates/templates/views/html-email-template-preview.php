<?php
/**
 * Admin View: Email Template Preview
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// Load Template
$template        = get_option( 'yith-wcet-email-template' );

// used for preview
global $template_id;
if ( isset($template_id)){
    $template = $template_id;
}

$meta            = get_post_meta( $template, '_template_meta', true);

$show_image      = ( isset( $meta['show_prod_thumb'] ) ) ? $meta['show_prod_thumb'] : 0;

?>

<p><?php printf( __( 'You have received an order from %s. The order is as follows:', 'woocommerce' ), __('User', 'woocommerce') ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', false , true, false ); ?>

<h2><a href=""><?php printf( __( 'Order #%s', 'woocommerce'), 1 ); ?></a> (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( date('F j, Y') ) ), date_i18n( wc_date_format(), strtotime( date('F j, Y') ) ) ); ?>)</h2>

<table id="yith-wcet-order-items-table" cellspacing="0" cellpadding="6" style="width: 100%;">
	<thead>
		<tr>
			<th class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th class="yith-wcet-order-items-table-element" scope="col" style="text-align:left;"><?php _e( 'Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php // echo $order->email_order_items_table( false, true ); ?>
		<?php
		$img_path = YITH_WCET_ASSETS_URL . '/images/preview-emails/';
		$items = array(
					array(
						'img'		=> $img_path . '1.jpg',
						'name'		=> 'Test 1',
						'qty'	=> '1',
						'price'		=> '$30.00'
						),
					array(
						'img'		=> $img_path . '2.jpg',
						'name'		=> 'Test 2',
						'qty'	=> '3',
						'price'		=> '$23.00'
						),
			);
		foreach ( $items as $item ) :
			?>
				<tr>
					<td class="yith-wcet-order-items-table-element" style="text-align:left; vertical-align:middle; word-wrap:break-word;"><?php
						// Show title/image etc
						if ( $show_image ) {
							echo apply_filters( 'woocommerce_order_item_thumbnail', '<img src="' . $item['img'] .'" alt="' . __( 'Product Image', 'woocommerce' ) . '" height="32" width="32" style="vertical-align:middle; margin-right: 10px;" />' );
						}
						// Product name
						echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );
					?></td>
					<td class="yith-wcet-order-items-table-element" style="text-align:left; vertical-align:middle;"><?php echo $item['qty'] ;?></td>
					<td class="yith-wcet-order-items-table-element" style="text-align:left; vertical-align:middle;"><?php echo $item['price']; ?></td>
				</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<?php
				$totals = array(
						array(
							'label'		=> __('Subtotal', 'woocommerce'),
							'value'		=> '$99,00'
							),
						array(
							'label'		=> __('Shipping', 'woocommerce'),
							'value'		=> __('Free Shipping', 'woocommerce')
							),
						array(
							'label'		=> __('Payment', 'woocommerce'),
							'value'		=> __('PayPal', 'woocommerce')
							),
						array(
							'label'		=> __('Total', 'woocommerce'),
							'value'		=> '$99,00'
							),
					);
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?><tr>
						<th class="yith-wcet-order-items-table-element <?php if ( $i == 1 ) echo 'yith-wcet-order-items-table-element-bigtop'; ?>" scope="row" colspan="2" style="text-align:left; "><?php echo $total['label']; ?></th>
						<td class="yith-wcet-order-items-table-element <?php if ( $i == 1 ) echo 'yith-wcet-order-items-table-element-bigtop'; ?>" style="text-align:left;"><?php echo $total['value']; ?></td>
					</tr><?php
				}
		?>
	</tfoot>
</table>