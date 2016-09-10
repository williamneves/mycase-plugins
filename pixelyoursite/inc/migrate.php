<?php
/**
 * Allow to migrate old settings format without data lost.
 * @since 3.0.0
 */

/**
 * Override all options with old data and remove old option from db.
 * @since 3.0.0
 */
if( !function_exists( 'pys_migrate_from_22x' ) ) {

	function pys_migrate_from_22x() {

		$options    = get_option( 'pixel_your_site' );  // defaults
		$std_events = array();

		$old_free = get_option( 'woofp_admin_settings' );

		// general settings
		$options['general']['pixel_id'] = isset( $old_free['facebookpixel']['ID'] ) ? $old_free['facebookpixel']['ID'] : '';
		$options['general']['enabled']  = isset( $old_free['facebookpixel']['activate'] ) ? $old_free['facebookpixel']['activate'] : 0;

		// standard events enable/disable
		$options['std']['enabled'] = isset( $old_free['standardevent']['activate'] ) ? $old_free['standardevent']['activate'] : 0;

		// woo events settings
		$options['woo']['enabled']            = isset( $old_free['woocommerce']['activate'] ) ? $old_free['woocommerce']['activate'] : 0;
		$options['woo']['on_view_content']    = isset( $old_free['woocommerce']['events']['ViewContent'] ) ? $old_free['woocommerce']['events']['ViewContent'] : 0;
		$options['woo']['on_add_to_cart_btn'] = isset( $old_free['woocommerce']['events']['ProductAddToCart'] ) ? $old_free['woocommerce']['events']['ProductAddToCart'] : 0;
		$options['woo']['on_cart_page']       = isset( $old_free['woocommerce']['events']['AddToCart'] ) ? $old_free['woocommerce']['events']['AddToCart'] : 0;
		$options['woo']['on_checkout_page']   = isset( $old_free['woocommerce']['events']['InitiateCheckout'] ) ? $old_free['woocommerce']['events']['InitiateCheckout'] : 0;
		$options['woo']['on_thank_you_page']  = isset( $old_free['woocommerce']['events']['Purchase'] ) ? $old_free['woocommerce']['events']['Purchase'] : 0;

		// copy standard events
		unset( $old_free['standardevent']['activate'] );
		if ( isset( $old_free['standardevent']['pageurl'] ) ) {
			$events_count = count( $old_free['standardevent']['pageurl'] );

			$i = 0;
			while ( $i < $events_count ) {

				// do not copy empty events
				if( empty( $old_free['standardevent']['pageurl'][$i] ) ) {
					$i++;
					continue;
				}

				$id = uniqid() . $i; // concat used to avoid equal ids

				foreach ( $old_free['standardevent'] as $key => $value ) {
					$std_events[ $id ][ $key ] = $value[ $i ];
				}

				if ( isset( $std_events[ $id ]['code'] ) && ! empty( $std_events[ $id ]['code'] ) ) {
					$std_events[ $id ]['eventtype'] = 'CustomCode';
				}

				$i ++;
			}

		}

		update_option( 'pixel_your_site', $options );
		update_option( 'pixel_your_site_std_events', $std_events );

		// remove old settings
		//delete_option( 'woofp_admin_settings' );

	}

}