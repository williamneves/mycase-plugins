<?php

$settings = array(

	'settings'  => array(

		'general-options' => array(
			'title' => __( 'General Options', 'yith-woocommerce-email-templates' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'yith-wcet-general-options'
		),

		'custom-default-header-logo' => array(
			'id'        => 'yith-wcet-custom-default-header-logo',
			'name'      => __( 'Default Logo', 'yith-woocommerce-email-templates' ),
			'type'      => 'yith_wcet_upload',
			'desc'      => __( 'Upload your custom default logo', 'yith-woocommerce-email-templates' ),
		),

		'use-mini-social-icons' => array(
				'id'        => 'yith-wcet-use-mini-social-icons',
				'name'      => __( 'Use social mini-icons', 'yith-woocommerce-email-templates' ),
				'type'      => 'checkbox',
				'desc'      => __( 'Use always social mini-icons (30x30 px) in all email templates', 'yith-woocommerce-email-templates' ),
				'default' => 'no',
		),

		'general-options-end' => array(
			'type'      => 'sectionend',
			'id'        => 'yith-wcet-general-options'
		)

	)
);

return apply_filters( 'yith_wcet_panel_settings_options', $settings );