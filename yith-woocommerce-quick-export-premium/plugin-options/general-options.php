<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$general_settings = array(
	array(
		'name' => __( 'General Settings', 'yith-woocommerce-quick-export' ),
		'type' => 'title',
		'desc' => '',
		'id'   => 'ywqe_general'
	),
	'folder_format'   => array(
		'name'    => __( 'Path format', 'yith-woocommerce-quick-export' ),
		'type'    => 'text',
		'id'      => 'ywqe_folder_format',
		'desc'    => __( 'Set the path where you want to store the documents. Use {{year}}, {{month}}, {{day}} as placeholders. For example: "Backup/{{year}}/{{month}}" will create a paths like Backup/2015/05, Backup/2015/06 for invoices stored per year and month; alternatively, leave blank for storing files in the root folder', 'yith-woocommerce-quick-export' ),
		'default' => 'Exported',
		'css'     => 'width:50%;',
		'custom_attributes' => array(
			'required' => 'required'
		)
	),
	'filename_format' => array(
		'name'              => __( 'Filename format', 'yith-woocommerce-quick-export' ),
		'type'              => 'text',
		'id'                => 'ywqe_filename_format',
		'desc'              => __( 'Set filename format for invoice documents. Use {{year}}, {{month}}, {{day}}, {{hours}}, {{minutes}}, {{seconds}} as placeholders.', 'yith-woocommerce-quick-export' ),
		'css'               => 'width:50%;',
		'default'           => 'Export_{{year}}.{{month}}.{{day}}_{{hours}}.{{minutes}}.{{seconds}}',
		'custom_attributes' => array(
			'required' => 'required'
		)
	),
	'dropbox'         => array(
		'name'    => __( 'Send documents to Dropbox', 'yith-woocommerce-quick-export' ),
		'type'    => 'ywqe_dropbox',
		'id'      => 'ywqe_dropbox_key',
		'desc'    => __( 'Set automatic document backup to Dropbox.', 'yith-woocommerce-quick-export' ),
		'default' => 'yes'
	),
	array( 'type' => 'sectionend', 'id' => 'ywqe_general_end' )
);

$general_settings = apply_filters( 'yith_ywqe_general_settings', $general_settings );

$options['general'] = array();

if ( ! defined( 'YITH_YWQE_PREMIUM' ) ) {
	$intro_tab = array(
		'section_general_settings_videobox' => array(
			'name'    => __( 'Upgrade to the PREMIUM VERSION', 'yith-woocommerce-quick-export' ),
			'type'    => 'videobox',
			'default' => array(
				'plugin_name'               => __( 'YITH WooCommerce Quick Export', 'yith-woocommerce-quick-export' ),
				'title_first_column'        => __( 'Discover The Advanced Features', 'yith-woocommerce-quick-export' ),
				'description_first_column'  => __( 'Upgrade to the PREMIUM VERSION of YITH WooCommerce Quick Export to benefit from all features!', 'yith-woocommerce-quick-export' ),
				'video'                     => array(
					'video_id'          => '',
					'video_image_url'   => YITH_YWQE_ASSETS_IMAGES_URL . 'yith-woocommerce-quick-export.jpg',
					'video_description' => __( 'See YITH WooCommerce Quick Export plugin with full premium features in action', 'yith-woocommerce-quick-export' ),
				),
				'title_second_column'       => __( 'Get Support and Pro Features', 'yith-woocommerce-quick-export' ),
				'description_second_column' => __( 'Purchasing the premium version of the plugin, you will take advantage of the advanced features of the product and you will get one year of free updates and support through our platform available 24h/24.', 'yith-woocommerce-quick-export' ),
				'button'                    => array(
					'href'  => YWQE_Plugin_FW_Loader::get_instance()->get_premium_landing_uri(),
					'title' => 'Get Support and Pro Features'
				)
			),
			'id'      => 'ywqe_general_videobox'
		)
	);

	$options['general'] = $intro_tab;
}

$options['general'] = array_merge( $options['general'], $general_settings );

return apply_filters( 'yith_ywqe_tab_options', $options );

