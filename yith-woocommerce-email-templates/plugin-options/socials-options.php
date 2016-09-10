<?php

$socials = array(

	'socials'  => array(

		'general-options' => array(
			'title' => __( 'Social Network Options', 'yith-woocommerce-email-templates' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'yith-wcet-socials-options'
		),

		'facebook' => array(
			'id'        => 'yith-wcet-facebook',
			'name'      => __( 'Facebook Page URL', 'yith-woocommerce-email-templates' ),
			'type'      => 'text',
			'desc'      => __( 'Enter your Facebook page URL', 'yith-woocommerce-email-templates' ),
		),

		'twitter' => array(
			'id'        => 'yith-wcet-twitter',
			'name'      => __( 'Twitter Profile URL', 'yith-woocommerce-email-templates' ),
			'type'      => 'text',
			'desc'      => __( 'Enter your Twitter profile URL', 'yith-woocommerce-email-templates' ),
		),

		'google' => array(
			'id'        => 'yith-wcet-google',
			'name'      => __( 'Google+ Profile URL', 'yith-woocommerce-email-templates' ),
			'type'      => 'text',
			'desc'      => __( 'Enter your Google+ profile URL', 'yith-woocommerce-email-templates' ),
		),

		'general-options-end' => array(
			'type'      => 'sectionend',
			'id'        => 'yith-wcet-socials-options'
		)

	)
);

return $socials;