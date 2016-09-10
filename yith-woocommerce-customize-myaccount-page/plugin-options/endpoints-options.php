<?php
/**
 * GENERAL ARRAY OPTIONS
 */
if ( ! defined( 'YITH_WCMAP' ) ) {
	exit;
} // Exit if accessed directly

$endpoint_keys = yith_wcmap_default_endpoints_keys();
$endpoint_keys = array_unique( $endpoint_keys );

$general = array(

	'endpoints'  => array(

		array(
			'title' => __( 'Endpoint Options', 'yith-woocommerce-customize-myaccount-page' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'yith-wcmap-endpoints-options'
		),

		array(
			'name' => __( 'Manage Endpoints', 'yith-woocommerce-customize-myaccount-page' ),
			'desc' => '',
			'id'   => 'yith_wcmap_endpoint',
			'default' => implode( ',', $endpoint_keys ),
			'type' => 'wcmap_endpoints'
		),

		array(
			'type'      => 'sectionend',
			'id'        => 'yith-wcmap-end-endpoints-options'
		)
	)
);

return apply_filters( 'yith_wcmap_panel_endpoints_options', $general );