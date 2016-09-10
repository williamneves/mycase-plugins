<?php

/**
 * Class Yoast_GA_Woo_eCommerce_Tracking
 *
 * Tracks WooCommerce transactions as soon as they're set to paid on the server.
 *
 * @since 3.0
 */
class Yoast_GA_Woo_eCommerce_Tracking extends Yoast_GA_eCommerce_Tracking_Abstract {

	/**
	 * When order is processed, there is a payment_id created. From that moment the user_id can be saved
	 *
	 * @var string
	 */
	protected $store_user_id_hook = 'woocommerce_checkout_order_processed';

	/**
	 * In woocommerce the name of the order post type is 'shop_order'
	 *
	 * @var string
	 */
	protected $order_post_type = 'shop_order';	

	/**
	 * The sequence of the parameters for this method is different with the sequence of its parents.
	 *
	 * By overriding track_transaction the sequence can be set in the correct sequence and calling its parent by
	 * this correct sequence won't break it's process
	 *
	 * @since 3.0
	 *
	 * @param int    $payment_id
	 * @param string $old_status
	 * @param string $new_status
	 */
	public function track_transaction( $payment_id, $old_status, $new_status ) {
		parent::track_transaction( $payment_id, $new_status, $old_status );
	}

	/**
	 * This method will return the value of $this->store_user_id_hook.
	 *
	 * This hook is used for saving the user id, after created a payment. So there will be a payment_id existing
	 *
	 * @since 3.0
	 *
	 * @return mixed|string
	 */
	protected function get_store_user_id_hook() {
		return $this->store_user_id_hook;
	}

	/**
	 * This method will return the value of $this->order_post_type.
	 *
	 * This is used to ensure we're detecting the right kind of post.
	 *
	 * @since 5.5
	 *
	 * @return mixed|string
	 */
	protected function get_order_post_type() {
		return $this->order_post_type;
	}


	/**
	 * This method will add the actions to add/remove orders to GA on.
	 *
	 * This hook is used for changing the status of the payment.
	 *
	 * @since 5.5
	 *
	 * @return void
	 */
	protected function get_order_actions() {
		// When to send to GA
		add_action( 'woocommerce_order_status_completed', array( $this, 'maybe_do_transaction' ), 10 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'maybe_do_transaction' ), 10 );

		// When to remove from GA
		add_action( 'woocommerce_order_status_completed_to_refunded', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'woocommerce_order_status_on-hold_to_refunded', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'woocommerce_order_status_processing_to_refunded', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'woocommerce_order_status_processing_to_cancelled', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'woocommerce_order_status_completed_to_cancelled', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'woocommerce_order_status_pending_to_cancelled', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'woocommerce_order_status_pending_to_failed', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'wc-on-hold_to_trash', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'wc-processing_to_trash', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'wc-completed_to_trash', array( $this, 'maybe_undo_transaction' ), 10 );
	}

	/**
	 * This method will determine whether to do the transaction or not.
	 *
	 * @since 5.5
	 *
	 * @return void
	 */
	public function maybe_do_transaction( $payment_id = 0 ) {
		$this->do_transaction( $payment_id );
	}

	/**
	 * This method will determine whether to undo the transaction or not.
	 *
	 * @since 5.5
	 *
	 * @return void
	 */
	public function maybe_undo_transaction( $payment_id = 0 ) {
		$this->undo_transaction( $payment_id );
	}

	/**
	 * Retrieving the payment method from the post_meta for current payment
	 *
	 * @since 3.0
	 *
	 * @param int $payment_id
	 *
	 * @return string
	 */
	protected function get_payment_method( $payment_id ) {
		return get_post_meta( $payment_id, '_payment_method_title', true );
	}

	/**
	 * Method for getting the order details from WooCommerce
	 *
	 * @since 3.0
	 *
	 * @param int $payment_id
	 *
	 * @return array
	 */
	protected function get_order_details( $payment_id ) {
		// Getting the order details
		$wc_order = new WC_Order( $payment_id );

		// Getting the items in cart
		$wc_order_items = $wc_order->get_items();

		// Calculating totals
		$total_tax    = $wc_order->get_total_tax();
		$total_amount = $wc_order->get_total() - $total_tax;

		return array(
			'items'        => $wc_order_items,
			'total_amount' => $total_amount,
			'total_tax'    => $total_tax,
			'currency'     => $wc_order->get_order_currency(),
		);
	}

	/**
	 * Parse each item in format for google analytics, containing all required field
	 *
	 * @since 3.0
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	protected function parse_item( $item ) {

		$item_category = get_the_terms( $item['product_id'], 'product_cat' );
		if ( is_array( $item_category ) && is_object( $item_category[0] ) ) {
			$item_category = $item_category[0]->slug;
		}

		return array(
			'in' => $item['name'],
			'ip' => ( $item['line_total'] / $item['qty'] ),
			'iq' => $item['qty'],
			'ic' => $this->get_product_sku( $item['product_id'] ),
			'iv' => $item_category,
		);
	}

	/**
	 * Getting the order number.
	 *
	 * Instead of payment_id maybe there is a custom order_number
	 *
	 * @param integer $payment_id
	 *
	 * @return string
	 */
	protected function get_order_number( $payment_id ) {
		$wc_order = new WC_Order( $payment_id );

		return preg_replace( '/^#(.*)$/', '$1', $wc_order->get_order_number() );
	}

	/**
	 * Getting the product SKU if exist otherwise return product_id
	 *
	 * @param integer $product_id
	 *
	 * @return mixed
	 */
	protected function get_product_sku( $product_id ) {
		$wc_product  = new WC_Product( $product_id );
		$product_sku = $wc_product->get_sku();

		if ( ! empty( $product_sku ) ) {
			return $product_sku;
		}
		else {
			return $product_id;
		}
	}

}
