<?php

/**
 * Class Yoast_GA_eCommerce_Tracking_Abstract
 *
 * Tracks transactions as soon as they're set to paid on the server. Abstract so needs to be extended.
 *
 * @since 3.0
 */
abstract class Yoast_GA_eCommerce_Tracking_Abstract {

	/**
	 * @var string $uuid_meta_key The name of the meta key used to store the UUID
	 */
	protected $uuid_meta_key = '_yoast_gau_uuid';

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Store the visitor ID and attached experiments and variations, as stored in the cookie, with the transaction.
	 *
	 * @since 3.0
	 *
	 * @param int $payment_id The ID of the payment to attached the data to.
	 */
	public function store_user_id( $payment_id ) {
		$ga_uuid = $this->read_cookie();
		if ( $ga_uuid ) {
			update_post_meta( $payment_id, $this->uuid_meta_key, $ga_uuid );
		}
	}

	/**
	 * Hooks the needed actions.
	 *
	 * @since 3.0
	 */
	protected function load() {
		add_action( $this->get_store_user_id_hook(), array( $this, 'store_user_id' ), 10, 1 );

		$this->get_order_actions();
	}

	/**
	 * Executing the transaction, only when the new status is paid.
	 *
	 * @since 3.0
	 *
	 * @param int $payment_id
	 *
	 */
	protected function do_transaction( $payment_id ) {
		if ( is_a( $payment_id, 'WP_Post' ) ) {
			$payment_id = $payment_id->ID;
		}

		$post_type = $this->get_order_post_type();

		if ( $post_type != get_post_type( $payment_id ) ) {
			return;
		}

		$is_in_ga = get_post_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		if ( $is_in_ga === 'yes' ) {
			return;
		}

		$payload = $this->get_payment_payload( $payment_id );

		$this->send_hit( $payload['main'] );

		foreach ( $payload['products'] as $single_payload ) {
			$this->send_hit( $single_payload );
		}

		update_post_meta( $payment_id, '_monsterinsights_is_in_ga', 'yes' );
	}



	/**
	 * Undo the transaction, will executed when going from paid to another status
	 *
	 * @since 3.0
	 *
	 * @link  https://support.google.com/analytics/answer/1037443?hl=en
	 *
	 * @param int $payment_id
	 */
	protected function undo_transaction( $payment_id ) {
		if ( is_a( $payment_id, 'WP_Post' ) ) {
			$payment_id = $payment_id->ID;
		}

		$post_type = $this->get_order_post_type();

		if ( $post_type != get_post_type( $payment_id ) ) {
			return;
		}
		
		$is_in_ga = get_post_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		if ( $is_in_ga !== 'yes' ) {
			return;
		}

		$payload = $this->get_payment_payload( $payment_id );

		// Reverse the transaction
		$payload['main']['tr'] = 0 - $payload['main']['tr'];
		$payload['main']['tt'] = 0 - $payload['main']['tt'];

		$this->send_hit( $payload['main'] );

		// Reverse each product too
		foreach ( $payload['products'] as $single_payload ) {
			$single_payload['iq'] = 0 - $single_payload['iq'];
			$this->send_hit( $single_payload );
		}

		delete_post_meta( $payment_id, '_monsterinsights_is_in_ga', 'yes' );
	}

	/**
	 * Default array, with values that should be in every payload
	 *
	 * @since 3.0
	 *
	 * @param int $payment_id
	 *
	 * @return array $payload
	 */
	protected function get_default_payload( $payment_id ) {

		$ga_uuid   = get_post_meta( $payment_id, $this->uuid_meta_key, true );
		$no_cookie = false;
		if ( ! is_string( $ga_uuid ) || '' === $ga_uuid ) {
			$ga_uuid   = 'payment_' . $payment_id;
			$no_cookie = true;
		}

		$payload = array(
			'cid' => $ga_uuid,
			't'   => 'transaction',
			'ti'  => $this->get_order_number( $payment_id ),
			'ta'  => $this->get_payment_method( $payment_id ),
			'ts'  => '0.00',
		);

		if ( $no_cookie ) {
			$payload['cn'] = 'eCommerce / No GA ID';
			$payload['cs'] = 'eCommerce / No GA ID';
		}

		return $payload;
	}

	/**
	 * Getting the order number.
	 *
	 * Instead of payment_id maybe there is a custom order_number
	 *
	 * @param integer $payment_id
	 *
	 * @return integer
	 */
	protected function get_order_number( $payment_id ) {
		return $payment_id;
	}

	/**
	 * Getting the product SKU if exist otherwise return product_id
	 *
	 * @param integer $product_id
	 *
	 * @return mixed
	 */
	protected function get_product_sku( $product_id ) {
		return $product_id;
	}

	/**
	 * Retrieve the details for the payment
	 *
	 * @since 3.0
	 *
	 * @param int $payment_id
	 *
	 * @link  https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#ecom
	 *
	 * @return array $payload
	 */
	protected function get_payment_payload( $payment_id ) {

		// Get the order_details
		$order = $this->get_order_details( $payment_id );

		$payload = array(
			'main' => array_merge(
				$this->get_default_payload( $payment_id ),
				array(
					'tr' => (string) number_format( $order['total_amount'], 2, '.', '' ),
					'tt' => (string) number_format( $order['total_tax'], 2, '.', '' ),
					'cu' => $order['currency'],
				)
			),
		);

		$payload['products'] = $this->parse_items( $order['items'], $payload['main'] );

		return $payload;

	}

	/**
	 * Parses the cart items for analytics
	 *
	 * Uses payload to get similar data to use in the array to return
	 *
	 * @since 3.0
	 *
	 * @param array $items
	 * @param array $payload
	 *
	 * @return array
	 */
	protected function parse_items( $items, $payload ) {

		$return = array();

		if ( is_array( $items ) ) {
			$default_item = array(
				'cid' => $payload['cid'],
				't'   => 'item',
				'ti'  => $payload['ti'],
				'cu'  => $payload['cu'],
			);

			foreach ( $items as $item ) {
				$return[] = array_merge( $default_item, $this->parse_item( $item ) );
			}
		}

		return $return;
	}

	/**
	 * Sends a hit to Google Analytics Universal collection.
	 *
	 * @since 3.0
	 *
	 * @link  https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide
	 *
	 * @param array $payload The values to send to Google Analytics Universal.
	 *
	 * @return bool
	 */
	private function send_hit( $payload ) {

		static $options;

		if ( $options === null ) {
			$options = new Yoast_GA_Options();
		}

		$default_payload = array(
			'v'   => 1,
			'tid' => $options->get_tracking_code(),
		);

		$body = http_build_query( array_merge( $default_payload, $payload ) );

		if ( defined( 'YST_GA_DEBUG' ) && YST_GA_DEBUG ) {
			error_log( $body . "\n", 3, plugin_dir_path( __FILE__ ) . 'log.txt' );
		}

		$args = array(
			'body'       => $body,
			'user-agent' => 'Yoast GA eCommerce Tracker ' . Yoast_GA_eCommerce_Tracking::VERSION,
			'timeout'    => 60,
			'blocking'   => false,
		);

		wp_remote_post( 'http://www.google-analytics.com/collect', $args );

		return true;
	}

	/**
	 * Returns the Google Analytics clientId to store for later use
	 *
	 * @since 3.0
	 *
	 * @link  https://developers.google.com/analytics/devguides/collection/analyticsjs/domains#getClientId
	 *
	 * @return bool|string False if cookie isn't set, GA UUID otherwise
	 */
	private function read_cookie() {
		if ( ! isset( $_COOKIE['_ga'] ) ) {
			return false;
		}

		// The _ga cookie consists of GA[version_number][user_id], we are only interested in the user_id
		// so strip the version number.
		return preg_replace( '/^(GA\d\.\d\.)/', '', $_COOKIE['_ga'] );
	}

	/**
	 * Every class extending this class, should have get_store_user_id_hook method
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	abstract protected function get_store_user_id_hook();

	/**
	 * Every class extending this class, should have get_order_actions method
	 *
	 * @since 5.5
	 *
	 * @return void
	 */
	abstract protected function get_order_actions();

	/**
	 * Every class extending this class, should have maybe_do_transaction method
	 *
	 * @since 5.5
	 *
	 * @return string
	 */
	abstract public function maybe_do_transaction();

	/**
	 * Every class extending this class, should have maybe_undo_transaction method
	 *
	 * @since 5.5
	 *
	 * @return string
	 */
	abstract public function maybe_undo_transaction();

	/**
	 * Every class extending this class, should have get_order_post_type method
	 *
	 * @since 5.5
	 *
	 * @return string
	 */
	abstract protected function get_order_post_type();

	/**
	 * Every class extending this class, should have get_payment_method method
	 *
	 * @since 3.0
	 *
	 * @param integer $payment_id
	 *
	 * @return mixed
	 */
	abstract protected function get_payment_method( $payment_id );

	/**
	 * Every class extending this class, should have get_order_details method
	 *
	 * @since 3.0
	 *
	 * @param integer $payment_id
	 *
	 * @return array
	 */
	abstract protected function get_order_details( $payment_id );

	/**
	 * Every class extending this class, should have get_order_details method
	 *
	 * @since 3.0
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	abstract protected function parse_item( $item );
}

