<?php
/*
 * The base plugin class.
 */


/* ======= the model main class =========== */
if (! class_exists ( 'NM_Framwork_V1' )) {
	$_framework = dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'nm-framework.php';
	if (file_exists ( $_framework ))
		include_once ($_framework);
	else
		die ( 'Reen, Reen, BUMP! not found ' . $_framework );
}

/*
 * [1]
 */
class NM_PersonalizedProduct extends NM_Framwork_V1 {
	
	static $tbl_productmeta = 'nm_personalized';
	
	/**
	 * this holds all input objects
	 */
	var $inputs;
	
	/**
	 * the static object instace
	 */
	private static $ins = null;
	
	
	public static function init()
	{
		add_action('plugins_loaded', array(self::get_instance(), '_setup'));
	}
	
	public static function get_instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
	/*
	 * plugin constructur
	 */
	function _setup() {
		
		// setting plugin meta saved in config.php
		
		add_action( 'woocommerce_init', array( $this, 'setup_personalized_plugin' ) );
	}
	
	function setup_personalized_plugin(){
		
		$this -> plugin_meta = get_plugin_meta_wooproduct ();
		
		// getting saved settings
		$this -> plugin_settings = get_option ( $this -> plugin_meta['shortname'] . '_settings' );
		
		// file upload dir name
		$this -> product_files = 'product_files';
		
		// this will hold form productmeta_id
		$this -> productmeta_id = '';
		
		// populating $inputs with NM_Inputs object
		$this -> inputs = self::get_all_inputs ();
		//nm_personalizedproduct_pa($this->inputs);
		
		/*
		 * [2] TODO: update scripts array for SHIPPED scripts only use handlers
		 */
		// setting shipped scripts
		$this -> wp_shipped_scripts = array (
				'jquery',
				'jquery-ui-datepicker' 
		);


		/*
		 * [3] TODO: update scripts array for custom scripts/styles
		 */
		// setting plugin settings
		$this -> plugin_scripts = array (
				
				array (
						'script_name' => 'ppom-scripts',
						'script_source' => '/js/script.js',
						'localized' => true,
						'type' => 'js',
						'depends'		=> array('jquery', 'thickbox', 'jcrop'),
				),
				
				array (
						'script_name' => 'ppom-conditional',
						'script_source' => '/js/nm-conditional.js',
						'localized' => false,
						'type' => 'js',
						'depends'		=> array('jquery'),
				),
				
				array (
						'script_name' => 'ppom-dynamicprices',
						'script_source' => '/js/nm-dynamicprices.js',
						'localized' => false,
						'type' => 'js',
						'depends'		=> array('jquery'),
				),
				
				array (
						'script_name' => 'styles',
						'script_source' => '/plugin.styles.css',
						'localized' => false,
						'type' => 'style' 
				),
				
				array (
						'script_name' => 'nm-ui-style',
						'script_source' => '/js/ui/css/smoothness/jquery-ui-1.10.3.custom.min.css',
						'localized' => false,
						'type' => 'style',
						'page_slug' => array (
								'nm-new-form' 
						) 
				),

				
		);
		
		/*
		 * [4] Localized object will always be your pluginshortname_vars e.g: pluginshortname_vars.ajaxurl
		 */
		$this -> localized_vars = array (
				'ajaxurl' => admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http') ),
				'plugin_url' => $this -> plugin_meta ['url'],
				'doing' => $this -> plugin_meta ['url'] . '/images/loading.gif',
				'settings' => $this -> plugin_settings,
				'file_upload_path_thumb' => $this -> get_file_dir_url ( true ),
				'file_upload_path' => $this -> get_file_dir_url (),
				'file_meta' => '',
				'section_slides' => '',
				'woo_currency'	=> get_woocommerce_currency_symbol(),
				'mesage_max_files_limit'	=> __(' files allowed only', 'nm-personalizedproduct'),
				'default_error_message'	=> __('it\'s a required field.', 'nm-personalizedproduct'),
		);
		
		/*
		 * [5] TODO: this array will grow as plugin grow all functions which need to be called back MUST be in this array setting callbacks
		 */
		// following array are functions name and ajax callback handlers
		$this -> ajax_callbacks = array (
				'save_settings', // do not change this action, is for admin
				'save_form_meta',
				'update_form_meta',
				'upload_file',
				'delete_file',
				'delete_meta',
				'save_edited_photo',
				'get_option_price',
				'set_matrix_price',
				'validate_api',
				'crop_image_editor',	//loading cropping editor
				'crop_image',			//doing cropping,
				'move_images_admin',	//if images not moved to confirmed dir then admin can do it manually
		);
		
		/*
		 * plugin localization being initiated here
		 */
		add_action ( 'init', array (
				$this,
				'wpp_textdomain' 
		) );
		
		/*
		 * hooking up scripts for front-end
		 */
		add_action ( 'wp_enqueue_scripts', array (
				$this,
				'load_scripts' 
		) );
		
		add_action ( 'wp_enqueue_scripts', array (
		$this,
		'load_scripts_extra'
		) );
		
		
		/*
		 * registering callbacks
		 */
		$this -> do_callbacks ();
		
		/**
		 * change add to cart text on shop page
		 */
		 add_filter('woocommerce_loop_add_to_cart_link', array($this, 'change_add_to_cart_text'), 10, 2);
		
		/*
		 * adding a panel on product single page in admin
		 */
		add_action ( 'add_meta_boxes', array (
				$this,
				'add_productmeta_meta_box' 
		) );
		
		/*
		 * saving product meta in admin/product signel page
		 */
		add_action ( 'woocommerce_process_product_meta', array (
				$this,
				'process_product_meta' 
		), 1, 2 );
		
		/*
		 * 1- redering all product meta front-end
		 */
		add_action ( 'woocommerce_before_add_to_cart_button', array (
				$this,
				'render_product_meta' 
		), 15 );
		
		/*
		 * 2- validating the meta before adding to cart
		 */
		add_filter ( 'woocommerce_add_to_cart_validation', array (
				$this,
				'validate_data_before_cart' 
		), 10, 3 );
		
		/*
		 * 3- adding product meta to cart
		 */
		add_filter ( 'woocommerce_add_cart_item_data', array (
				$this,
				'add_product_meta_to_cart' 
		), 10, 2 );
		
		/*
		 * 4- now loading all meta on cart/checkout page from session confirmed that it is loading for cart and checkout
		 */
		add_filter ( 'woocommerce_get_cart_item_from_session', array (
				&$this,
				'get_cart_session_data' 
		), 10, 2 );
		
		/*
		 * 5- this is showing meta on cart/checkout page confirmed that it is loading for cart and checkout
		 */
		add_filter ( 'woocommerce_get_item_data', array (
				$this,
				'add_item_meta' 
		), 10, 2 );
		
		/*
		 * 6- Adding item_meta to orders 2.0 it is in classes/class-wc-checkout function: create_order() do_action( 'woocommerce_add_order_item_meta', $item_id, $values );
		 */
		add_action ( 'woocommerce_add_order_item_meta', array (
				$this,
				'order_item_meta' 
		), 10, 2 );
		
		/*
		 * 7- Another panel in orders to display files uploaded against each product
		 */
		add_action ( 'admin_init', array (
				$this,
				'render_product_files_in_orders' 
		) );
		
		/*
		 * 7- movnig confirmed/paid orders into another directory
		 * dir_name: confirmed
		*/
		add_action ( 'woocommerce_checkout_order_processed', array (
		$this,
		'move_files_when_paid'
		) );
		
		
		/*
		 * 8- cron job (shedualed hourly)
		 * to remove un-paid images
		 */
		add_action('do_action_remove_images', array($this, 'remove_unpaid_orders_images'));
		
		
		add_action('setup_styles_and_scripts_wooproduct', array($this, 'get_connected_to_load_it'));
		
		/*
		 * 9- adding file download link into order email
		 */
		add_action('woocommerce_email_after_order_table', array($this, 'add_files_link_in_email'), 10, 2);
		
		/*
		 * 10- adding meta list in product page
		*/
		//add_action( 'restrict_manage_posts', array( $this, 'nm_meta_dropdown' ) );
		
		add_action('admin_footer-edit.php', array($this, 'nm_add_bulk_meta'));
		
		add_action('load-edit.php', array(&$this, 'nm_meta_bulk_action'));
		
		add_action('admin_notices', array(&$this, 'nm_add_meta_notices'));
		
		
		// Add extra fee in cart
		add_action( 'woocommerce_cart_calculate_fees', array($this, 'add_fixed_fee') );
		
		//form post action for importing files in existing-meta.php
		add_action( 'admin_post_nm_importing_file_ppom', array($this, 'process_nm_importing_file_ppom') );
		
	}
	
	/*
	 * ============================================================== All about Admin -> Single Product page ==============================================================
	 */
	 
	 /**
	  * add to cart button text change
	  */
	  function change_add_to_cart_text($button, $product){
	  	
		$selected_meta_id = get_post_meta ( $product->id, '_product_meta_id', true );
		
			if (!in_array($product->product_type, array('variable', 'grouped', 'external'))) {
		        // only if can be purchased
		        if ($selected_meta_id) {
		            // show qty +/- with button
		            $button = sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button %s product_type_%s">%s</a>',
						esc_url( get_permalink($product->id) ),
						esc_attr( $product->id ),
						esc_attr( $product->get_sku() ),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						esc_attr( 'variable' ),
						esc_html( __('Select options', 'woocommerce') )
					);
		 
		        }
		    }
 
	 		return $button;
	  }
	
	// i18n and l10n support here
	// plugin localization
	function wpp_textdomain() {
		$locale_dir = dirname( plugin_basename( __FILE__ ) ) . '/locale/';
		load_plugin_textdomain('nm-personalizedproduct', false, $locale_dir);
		
		$this -> nm_export_ppom();
	}
	
	/**
	 * Adds meta groups in admin dropdown to apply on products.
	 *
	 */
	function nm_add_bulk_meta() {
		global $post_type;
			
		if($post_type == 'product' and $all_meta = $this -> get_product_meta_all ()) {
			foreach ( $all_meta as $meta ) {
				?>
<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('<option>').val('<?php printf(__('nm_action_%d', 'nm-personalizedproduct'), $meta->productmeta_id)?>', 'nm-personalizedproduct').text('<?php _e($meta->productmeta_name)?>').appendTo("select[name='action']");
							jQuery('<option>').val('<?php printf(__('nm_action_%d', 'nm-personalizedproduct'), $meta->productmeta_id)?>').text('<?php _e($meta->productmeta_name)?>').appendTo("select[name='action2']");
						});
					</script>
<?php
			}
			?>
<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('<option>').val('nm_delete_meta').text('<?php _e('Remove Meta', 'nm-personalizedproduct')?>').appendTo("select[name='action']");
						jQuery('<option>').val('nm_delete_meta').text('<?php _e('Remove Meta', 'nm-personalizedproduct')?>').appendTo("select[name='action2']");
					});
				</script>
<?php
	    }
	}

	function nm_meta_bulk_action() {
		global $typenow;
		$post_type = $typenow;
			
		if($post_type == 'product') {
				
			// get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
			$action = $wp_list_table->current_action();
			
			// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
			if(isset($_REQUEST['post']) && is_array($_REQUEST['post'])){
				$post_ids = array_map('intval', $_REQUEST['post']);
			}
			
			if(empty($post_ids)) return;
			
			// this is based on wp-admin/edit.php
			$sendback = remove_query_arg( array('nm_updated', 'nm_removed', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
			if ( ! $sendback )
				$sendback = admin_url( "edit.php?post_type=$post_type" );
				
			$pagenum = $wp_list_table->get_pagenum();
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );
			
			
			$nm_do_action = ($action == 'nm_delete_meta') ? $action : substr($action, 0, 10);
				
			switch($nm_do_action) {
				case 'nm_action_':
				$nm_updated = 0;
				foreach( $post_ids as $post_id ) {
							
					update_post_meta ( $post_id, '_product_meta_id', substr($action, 10) );
			
					$nm_updated++;
				}
				$sendback = add_query_arg( array('nm_updated' => $nm_updated, 'ids' => join(',', $post_ids)), $sendback );
				break;
				
				case 'nm_delete_meta':
				$nm_removed = 0;
				foreach( $post_ids as $post_id ) {
							
					delete_post_meta ( $post_id, '_product_meta_id' );
			
					$nm_removed++;
				}
				$sendback = add_query_arg( array('nm_removed' => $nm_removed, 'ids' => join(',', $post_ids)), $sendback );
				break;
				
				default: return;
			}
			
			wp_redirect($sendback);
			
			exit();
		}
	}
	/**
	 * display an admin notice on the Products page after updating meta
	 */
	function nm_add_meta_notices() {
		global $post_type, $pagenow;
			
		if($pagenow == 'edit.php' && $post_type == 'product' && isset($_REQUEST['nm_updated']) && (int) $_REQUEST['nm_updated']) {
			$message = sprintf( _n( 'Product meta updated.', '%s Products meta updated.', $_REQUEST['nm_updated'] ), number_format_i18n( $_REQUEST['nm_updated'] ) );
			echo "<div class=\"updated\"><p>{$message}</p></div>";
		}
		elseif($pagenow == 'edit.php' && $post_type == 'product' && isset($_REQUEST['nm_removed']) && (int) $_REQUEST['nm_removed']){
			$message = sprintf( _n( 'Product meta removed.', '%s Products meta removed.', $_REQUEST['nm_removed'] ), number_format_i18n( $_REQUEST['nm_removed'] ) );
			echo "<div class=\"updated\"><p>{$message}</p></div>";	
		}
	}
	 	
	function add_productmeta_meta_box() {
		add_meta_box ( 'woocommerce-image-upload', __ ( 'Select Personalized Meta', 'nm-personalizedproduct' ), array (
				$this,
				'product_meta_box' 
		), 'product', 'side', 'default' );
	}
	function product_meta_box($post) {
		$existing_meta_id = get_post_meta ( $post->ID, '_product_meta_id', true );
		$all_meta = $this -> get_product_meta_all ();
		
		echo '<p>';
		
		// NONE
		echo '<label class="single-product-label" for="select_meta_group-none">';
		echo '<input name="nm_product_meta" type="radio" value="0" checked="checked" id="select_meta_group-none" />';
		echo ' ' . __('None', 'nm-personalizedproduct'). '</label><br>';
		
		
		foreach ( $all_meta as $meta ) {
			
			if ($meta->productmeta_id == $existing_meta_id)
				$selected = 'checked="checked"';
			else
				$selected = '';
			
			echo '<label class="single-product-label" for="select_meta_group-' . $meta->productmeta_id . '">';
			echo '<input name="nm_product_meta" type="radio" value="' . $meta->productmeta_id . '" ' . $selected . ' id="select_meta_group-' . $meta->productmeta_id . '" />';
			echo ' ' . $meta->productmeta_name . '</label><br>';
		}
		
		echo '</p>';
	}
	
	
	function get_product_meta_all() {
		global $wpdb;
		
		$qry = "SELECT * FROM " . $wpdb->prefix . self::$tbl_productmeta;
		$res = $wpdb->get_results ( $qry );
		
		return $res;
	}
	
	/*
	 * saving meta data against product
	 */
	function process_product_meta($post_id, $post) {
		
		
		/* nm_personalizedproduct_pa($_POST); exit; */

		if($_POST ['nm_product_meta'] != '')
			update_post_meta ( $post_id, '_product_meta_id', $_POST ['nm_product_meta'] );
	}
	
	/*
	 * rendering shortcode meat
	 */
	function render_product_meta() {
		global $post;
		
		$this -> productmeta_id = get_post_meta ( $post->ID, '_product_meta_id', true );
		
		if ($this -> productmeta_id) {
			
			$this -> load_template ( 'render.input.php' );
		}
		
		return false;
	}
	
	/*
	 * validating before adding to cart
	 */
	function validate_data_before_cart($passed, $product_id, $qty) {
		global $woocommerce;
		
		
		$selected_meta_id = get_post_meta ( $product_id, '_product_meta_id', true );
		$single_meta = $this -> get_product_meta ( $selected_meta_id );
		$existing_meta = json_decode ( $single_meta->the_meta );
		
		
		if( $single_meta -> productmeta_validation == 'yes'){
			
			return $passed;
		}
		
		//nm_personalizedproduct_pa($_POST);
		
		if ($existing_meta) {
			foreach ( $existing_meta as $meta ) {
				
				$element_name = strtolower ( preg_replace ( "![^a-z0-9]+!i", "_", $meta->data_name ) );
				//
				
				if ($meta->type == 'checkbox') {
					
					$element_value = $_POST [$element_name];
					if ($meta->required == 'on' && (count ( $element_value ) == 0)) {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						nm_wc_add_notice( $error_message );
					} elseif ($meta->min_checked != '' && (count ( $element_value ) < $meta->min_checked)) {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						nm_wc_add_notice( $error_message );
					} elseif ($meta->max_checked != '' && (count ( $element_value ) > $meta->max_checked)) {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						nm_wc_add_notice( $error_message );
					}
				} elseif ($meta->type == 'file') {
				
					$element_value = (isset($_POST ['thefile_' . $element_name]) ? $_POST['thefile_' . $element_name] : '');
					if ($meta->required == 'on' && $element_value == '') {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						nm_wc_add_notice( $error_message );
					}
				} elseif ($meta->type == 'image') {
					$element_value = (isset($_POST [$element_name]) ? $_POST [$element_name] : '');
					
					if ($meta->required == 'on') {
						if (is_array ( $element_value )) {
							
							if (count ( $element_value ) == 0) {
								$passed = false;
								$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
								nm_wc_add_notice( $error_message );
							}
						} elseif ($element_value == '') {
							$passed = false;
							$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
							nm_wc_add_notice( $error_message );
						}
					}
					
				} else {
					$element_value = sanitize_text_field ( $_POST [$element_name] );
					
					if ($meta->required == 'on' && $element_value == '') {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						nm_wc_add_notice( $error_message );
					}
				}
				
			}
		}
		
		return $passed;
	}
	
	
	function get_product_meta($meta_id) {
		
		if( !$meta_id )
			return ;
			
		global $wpdb;
		
		$qry = "SELECT * FROM " . $wpdb->prefix . self::$tbl_productmeta . " WHERE productmeta_id = $meta_id";
		$res = $wpdb->get_row ( $qry );
		
		return $res;
	}
	
	/*
	 * Adding product meta to cart A very important function
	 */
	function add_product_meta_to_cart($the_cart_data, $product_id) {
		global $woocommerce;
		
		$selected_meta_id = get_post_meta ( $product_id, '_product_meta_id', true );
		//nm_personalizedproduct_pa($_POST); exit;
		
		/*
		 * now extracting product meta values
		 */
		
		$single_meta = $this -> get_product_meta ( $selected_meta_id );
		$product_meta = json_decode ( $single_meta->the_meta );
		
		$product_meta_data = array (); // this array is giong to be pushed into with data
		
		$all_files = '';
		$price_matrix = '';
		
		if ($product_meta) {
			
			// nm_personalizedproduct_pa($product_meta);
			
			$var_price = 0;
			foreach ( $product_meta as $meta ) {
				
				$element_name = strtolower ( preg_replace ( "![^a-z0-9]+!i", "_", $meta->data_name ) );
				$element_value = '';
				
				/*nm_personalizedproduct_pa($_POST);
				exit;*/
				if ($meta->type == 'checkbox') {					
					
					if ($_POST [$element_name])
						$element_value = implode ( ",", $_POST [$element_name] );
				} else if ($meta->type == 'select' || $meta->type == 'radio') {
					
					$element_value = sanitize_text_field ( $_POST [$element_name] );
				
				} elseif ($meta->type == 'file') {
					
					$element_value = (isset($_POST ['thefile_' . $element_name]) ? $_POST['thefile_' . $element_name] : '');
					
					if($element_value){
						$all_files[$meta -> title] = $element_value;	
						$file_key = __ ( '_File(s) attached', 'nm-personalizedproduct' );
					}
					
				}elseif ($meta->type == 'facebook') {
					
					$element_value = stripslashes( $_POST [$element_name] );
					$element_value = json_decode( $element_value, true);
					

					if($element_value){
						$all_files[$meta -> title] = $this -> save_imported_files( $element_value );	
						$file_key = __ ( '_File(s) attached', 'nm-personalizedproduct' );
					}
					
				}elseif ($meta->type == 'image') {
					
					$element_value = (isset($_POST [$element_name]) ? $_POST [$element_name] : '');

					if($element_value){
						$selected_images = array('type'		=> 'image',
							'selected'	=> $element_value);
												
						//$selected_image_key = __ ( 'Image(s) selected', 'nm-personalizedproduct' );
						$product_meta_data [$meta->title] = $selected_images;
					}
					
				} else {
					//$element_value = sanitize_text_field ( $_POST [$element_name] );
					
				}
				
				$cart_meta_key = stripslashes( $meta->title );
				// finally saving values into meta array
				if ($meta->type == 'facebook'){
					
					$product_meta_data [$cart_meta_key] = $all_files[$meta -> title];
				}elseif ($meta->type != 'section' && $meta->type != 'image'){
					
					if (is_array($element_value)){
						$product_meta_data [$cart_meta_key] = $element_value;
					}else{
						//$product_meta_data [$cart_meta_key] = stripslashes( nl2br($_POST [$element_name]) );
						if (isset($_POST [$element_name]) && is_array($_POST [$element_name])) {
							$nele=array();
							foreach($_POST [$element_name] as $ele) {
								$ele=stripslashes(nl2br($ele));
								$nele[]=$ele;
							}
							$_POST [$element_name]=$nele;
						}
						
						if(isset($_POST [$element_name]) && $_POST [$element_name] != ''){
							$product_meta_data [$cart_meta_key] = (isset($_POST [$element_name]) ? $_POST [$element_name] : '');
						}
					}	
				}
				
				// calculating price
				/* $var_price += $the_price;
				$the_price = 0; */
				
				
			}
		}
		
		//nm_personalizedproduct_pa($product_meta_data); exit;
		//adding attachments
		if($all_files){
			//$product_meta_data [$file_key] = $this -> make_filename_link ( $all_files );
			$product_ref_data ['_product_attached_files'] = $all_files;
		}
			
		
		// options price
		if(isset($_POST['woo_option_price']) && $_POST['woo_option_price'] != 0){
			$var_price = $_POST['woo_option_price'];
		}
		
		//fixed_fee
		if(isset($_POST['woo_onetime_fee'])){
			$fixed_price = $_POST['woo_onetime_fee'];
		}
		
		//file_fee
		if(isset($_POST['woo_file_cost'])){
			$file_cost = $_POST['woo_file_cost'];
		}
		
		
		//price_matrix
		if(isset($_POST['_pricematrix'])){
			$price_matrix = $_POST['_pricematrix'];
		}
		
		//nm_personalizedproduct_pa($product_meta_data); exit;
		
		$the_cart_data ['product_meta'] = array (
				'meta_data' => $product_meta_data,
				'var_price' => $var_price,
				'fixed_price'	=> stripslashes($fixed_price),
				'file_cost'     => stripslashes($file_cost),
				'price_matrix'	=> stripslashes($price_matrix),
				'_product_attached_files'	=> $all_files
		);
		
		
		//nm_personalizedproduct_pa($the_cart_data); exit;
		
		return $the_cart_data;
	}
	
	/*
	 * cart session data Ok, this value is being pulled on Cart/Checkout page
	 */
	function get_cart_session_data($cart_items, $values) {
		                          
		//nm_personalizedproduct_pa($values);
		if($cart_items == '')
			return;
		
		
		if (isset ( $values ['product_meta'] )) :
			$cart_items ['product_meta'] = $values ['product_meta'];	
		endif;
		
		$var_price = $values['product_meta']['var_price'];
		$cart_price = 0;
		
		
		if( $values ['product_meta']['price_matrix']){			
			$cart_price = $this->get_matrix_price($cart_items['quantity'], $values ['product_meta']['price_matrix']);			
		}else{			
			$cart_price = ($cart_items ['data'] -> get_price());
		}
		
		if($var_price){
			$cart_price = $cart_price + $var_price;
		}	
		
		
		$cart_items['data'] -> set_price($cart_price);
		
		//nm_personalizedproduct_pa($cart_items); exit;
		
		return $cart_items;
	}
	
	
	//Add custom fee to cart automatically
	
	function add_fixed_fee($cart_object) {
	
		//nm_personalizedproduct_pa($cart_object);
		
		$custom_price = 0; // This will be your custome price
		foreach ( $cart_object->cart_contents as $key => $value ) {
		    
			$fixed_price = json_decode($value['product_meta']['fixed_price'], true);
			$file_cost   = json_decode($value['product_meta']['file_cost'], true);
			
			//nm_personalizedproduct_pa($fixed_price);
			if ($fixed_price){
				
				foreach ($fixed_price as $title => $fixed){
					
					$taxable = ($fixed['taxable'] == 'on' ? true : false);
					if(isset($fixed['fee']) && $fixed['fee'] != '')
						$cart_object -> add_fee( __( esc_html($title), 'woocommerce'), intval($fixed['fee']), $taxable );
				}
			}
			
			$custom_price = '';
			$custom_title = '';
			if ($file_cost){
				
				foreach ($file_cost as $option => $fixed){
					
					$fixed_fee 	 = (isset($fixed['fee']) ? $fixed['fee'] : 0);
					$fee_taxable = (isset($fixed['taxable']) ? true : true);
					$custom_price += $fixed_fee;
					$custom_title .= $option;
					$taxable = $fee_taxable;
					
				
					if($custom_price)
						$cart_object -> add_fee( __( esc_html($custom_title), 'woocommerce'), $custom_price, $taxable );
				}
			}
		}
	
	}
	
	/*
	 * this function is showing item meta on cart/checkout page
	 */
	function add_item_meta($item_meta, $existing_item_meta) {
		
		//nm_personalizedproduct_pa($existing_item_meta ['product_meta']['meta_data']);
		
		if ($existing_item_meta ['product_meta']['meta_data']) {
			foreach ( $existing_item_meta ['product_meta'] ['meta_data'] as $key => $val ) {
				
				if(isset($val)){
					if (is_array($val)) {
						
						$data_type = (isset($val['type']) ? $val['type'] : '');
						
						if($data_type == 'image'){
							
							// if selected designs are more then one
							if(is_array($val['selected'])){
								
								$_v = '';
								foreach ($val['selected'] as $selected){
									$selecte_image_meta = json_decode(stripslashes( $selected ));
									$_v .= $selecte_image_meta -> title.',';
								}
								$item_meta [] = array (
										'name' => $key,
										'value' => __('Photos imported - ', 'nm-personalizedproduct') . count($val['selected']),
								);
							}else{
								$selecte_image_meta = json_decode(stripslashes( $val['selected'] ));
								$item_meta [] = array (
										'name' => $key,
										'value' => $selecte_image_meta -> title
								);
							}
						}else{
							//nm_personalizedproduct_pa($val);
							list($filekey, $filename) = each($val);
							if( $this->is_image( $filename )){
								$item_meta [] = array (
										'name' => $key,
										'value' => $this -> make_filename_link ( $val ),
								);
							}else{
								$item_meta [] = array (
										'name' => $key,
										'value' => implode(',', $val),
								);
							}
						}
						
					}else{
						$item_meta [] = array (
								'name' => $key,
								'value' => stripslashes( $val ),
						);
					}
				}
					
			}
		}
		
		//nm_personalizedproduct_pa($item_meta); exit;
		return $item_meta;
	}
	
	/*
	 * Adding item meta to order from $cart_item On checkout page, saving meta from CART to ITEM__ORDER
	 */
	function order_item_meta($item_id, $cart_item) {

		 // removing the _File(s) attached key
		 if (isset( $cart_item ['product_meta'] ['meta_data']['_File(s) attached'] )) {
		 	unset( $cart_item ['product_meta'] ['meta_data']['_File(s) attached']);
		 }
		 
		//nm_personalizedproduct_pa($cart_item); exit;
		
		if (isset ( $cart_item ['product_meta'] )) {
			
			foreach ( $cart_item ['product_meta'] ['meta_data'] as $key => $val ) {
				// $item_meta->add( $key, $val );
				
				if (is_array($val)) {
					if($val['type'] == 'image'){
							
						// if selected designs are more then one
						
						$order_val = '';
						
						if(is_array($val['selected'])){
				
							$_v = '';
							foreach ($val['selected'] as $selected){
								$selecte_image_meta = json_decode(stripslashes( $selected ));
								$_v .= $selecte_image_meta -> title.',';
							}
							
							$order_val = $_v;
						}else{
							$selecte_image_meta = json_decode(stripslashes( $val['selected'] ));
							$order_val = $selecte_image_meta -> title;
						}
						
						
					}else{
						
						$order_val = implode(',', $val);
					}
				
				}else{
				
					$order_val = stripslashes( $val );
				}
				
				if($val){
					wc_add_order_item_meta ( $item_id, $key, $order_val );
				}
			}
			
			// adding _product_attached_files
			wc_add_order_item_meta ( $item_id, '_product_attached_files', $cart_item ['product_meta']['_product_attached_files'] );
			
		}
	}
	
	
	/*
	 * make filename linkable used in cart data
	 */
	function make_filename_link($filenames) {

		$linkable = '';
		
		if ($filenames) {
				
				foreach ( $filenames as $key => $filename ) {
					
					$ext = strtolower ( substr ( strrchr ( $filename, '.' ), 1 ) );
					
					if ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'jpeg')
						$src_thumb = $this->get_file_dir_url ( true ) . $filename;
					else
						$src_thumb = $this->plugin_meta ['url'] . '/images/file.png';
					
					$img = '<img src="' . $src_thumb . '" alt="uploaded file">';
					
					$edited_file = $this->get_file_dir_path () . 'edits/' . $filename;
					
					if (file_exists ( $edited_file )) {
						$file_link = $this->get_file_dir_url () . 'edits/' . $filename;
					} else {
						$file_link = $this->get_file_dir_url () . $filename;
					}
					
					// $linkable = '<a href='.$this -> get_file_dir_url() . $filename.' class="zoom" itemprop="image" title="'.$filename.'" rel="prettyPhoto">'.$filename.'</a>';
					$linkable .= '<a href=' . $file_link . ' class="lightbox" itemprop="image" title="' . $filename . '">' . $img . '</a>';
					$linkable .= ' ' . $filename . '<br>';
				}
			
			return $linkable;
		}
		
	}

	/**
	 * saving fb imported files locally
	 */
	 function save_imported_files($imported_files){
		
		$saved_files = array();
		foreach( $imported_files as $key => $src){
			
			$image_url = preg_replace('/\?.*/', '', $src);
			$file_name = basename($image_url);
			
			$destination = $this -> setup_file_directory(). $file_name;
			//
			if( copy($src, $destination) ){
				$this->create_thumb($this->get_file_dir_path (), $file_name, 175);
				$saved_files[$key] = $file_name;
			}else{
				file_put_contents($destination, file_get_contents($src));
			}
		}
		
		return $saved_files;
		
	}
	
	/*
	 * rendering meta box in orders
	 */
	function render_product_files_in_orders() {
		add_meta_box ( 'orders_product_file_uploaded', __('Files attached/uploaded against Products','nm-personalizedproduct'),
					array ($this, 'display_uploaded_files'), 
					'shop_order', 'normal', 'default' );
		
		
		// adding meta box for pre-defined images selection
		add_meta_box ( 'selected_images_in_orders', __('Selected images/designs', 'nm-personalizedproduct'), 
						array ( $this, 'display_selected_files'),
						'shop_order', 'normal', 'default' );
	}
	
	
	function display_uploaded_files($order) {
		
		global $wpdb;
		$files_found = 0;
		$order_items = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d", $order->ID ) );
		
		$order = new WC_Order ( $order->ID );
		//nm_personalizedproduct_pa($order);
		if (sizeof ( $order->get_items () ) > 0) {
			foreach ( $order->get_items () as $item ) {
				
				/* get_metadata( 'order_item', $item_id, $key, $single );
				$all_files = wc_get_order_item_meta($item ['product_id'], 'Your title', true);
				nm_personalizedproduct_pa($item); */
				
				$selected_meta_id = get_post_meta ( $item ['product_id'], '_product_meta_id', true );
				
				$single_meta = $this -> get_product_meta ( $selected_meta_id);
				$product_meta = json_decode ( $single_meta->the_meta );

				//nm_personalizedproduct_pa($item);
				if($product_meta){
					
					foreach ( $product_meta as $meta => $data ) {
					
						if ($data -> type == 'file' || $data -> type == 'facebook') {
							
							$product_files = unserialize( $item['product_attached_files'] );	//explode(',', $item[$data -> title]);
							$product_files = $product_files[$data -> title];
							$product_id = $item ['product_id'];
							
							//nm_personalizedproduct_pa($product_files);
					
							if ($product_files) {
								
								
								echo '<strong>';
								printf(__('File attached %s', 'nm-personalizedproduct'), $data -> title);
								echo '</strong>';
									
								
								foreach ( $product_files as $file ) {
					
									$files_found++;
									$ext = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
					
									if ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'jpeg')
										$src_thumb = $this -> get_file_dir_url ( true ) . $file;
									else
										$src_thumb = $this -> plugin_meta ['url'] . '/images/file.png';
					
									
									$src_file = '';
									$org_path = $this -> get_file_dir_path () . $file;
									$file_name = $order -> id . '-' . $product_id . '-' . $file;		// from version 3.4
									$confirmed_path = $this -> get_file_dir_path () . 'confirmed/' . $file_name;
									if(file_exists($org_path)){
										if(rename ( $org_path, $confirmed_path ))
											$src_file = $this -> get_file_dir_url () . 'confirmed/' . $file_name;										
									}elseif(file_exists($confirmed_path)){
										$src_file = $this -> get_file_dir_url () . 'confirmed/' . $file_name;	
									}else{
										$src_file = $this -> get_file_dir_url () . $file;
									}
									
					
									echo '<table>';
									echo '<tr><td width="100"><img src="' . $src_thumb . '"><td><td><a href="' . $src_file . '">' . __ ( 'Download ' ) . $file_name . '</a> ' . $this -> size_in_kb ( $file_name ) . '</td>';
									
									$edited_path = $this->get_file_dir_path() . 'edits/' . $file;
									if (file_exists($edited_path)) {
										$file_url_edit = $this->get_file_dir_url () .  'edits/' . $file;
										echo '<td><a href="' . $file_url_edit . '" target="_blank">' . __ ( 'Download edited image', $this->plugin_meta ['shortname'] ) . '</a></td>';
									}
									
									$cropped_path = $this -> setup_file_directory('cropped') . $file;
									if (file_exists($cropped_path)) {
										$file_url_cropped = $this->get_file_dir_url () .  'cropped/' . $file;
										echo '<td><a href="' . $file_url_cropped . '" target="_blank">' . __ ( 'Download cropped image', $this->plugin_meta ['shortname'] ) . '</a></td>';
									}
									
									echo '</tr>';
									echo '</table>';
								}


							}

							 if ($files_found == 0){
									
								echo __ ( 'No file attached/uploaded', 'nm-personalizedproduct' );
							}
						}
					}
				}
				
			}
		}
	}
	
	
	function display_selected_files($order) {
		// woo_pa($order);
		global $wpdb;
		$order_items = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d", $order->ID ) );
	
		$order = new WC_Order ( $order->ID );
	
		if (sizeof ( $order->get_items () ) > 0) {
			foreach ( $order->get_items () as $item ) {
	
				//nm_personalizedproduct_pa($item);
	
				$selected_meta_id = get_post_meta ( $item ['product_id'], '_product_meta_id', true );
	
				$single_meta = $this -> get_product_meta ( $selected_meta_id);
				$product_meta = json_decode ( $single_meta->the_meta );
	
				echo '<h2>' . __ ( 'Selected pre defined image: ' . $item ['name'], 'nm-personalizedproduct' ) . '</h2>';
				echo '<p>';
				// nm_personalizedproduct_pa($product_meta);
				if($product_meta){
						
					foreach ( $product_meta as $meta => $data ) {
							
						if ($data -> type == 'image') {
							
							$product_files = $item[$data -> title];
							
							$product_files = explode( ',', $product_files  );
							if ($product_files) {
									
								echo '<h3>' . $data -> title . '</h3>';

								//nm_personalizedproduct_pa($data ->images);
								
								foreach ( $data ->images as $all_images ) {
									
									$selected_file = '';
									
									if ( in_array($all_images -> title, $product_files)) {
										$selected_file = $all_images -> link;
									}
									
									
									if ( $selected_file ) {
										
										$ext = strtolower ( substr ( strrchr ( $selected_file, '.' ), 1 ) );
										
										if ($ext == 'png' || $ext == 'jpg' || $ext == 'gif')
											$src_thumb = $this -> get_file_dir_url ( true ) . $selected_file;
										else
											$src_thumb = $this -> plugin_meta ['url'] . '/images/file.png';
										
										$src = $selected_file;
										
										echo '<table>';
										echo '<tr><td width="100"><img width="250" src="' . $src . '"><td><td><a href="' . $src . '">' . __ ( 'Download ' ) . $file . '</a></td>';
										
										echo '</tr>';
										echo '</table>';
									}
									
								}
								
							} else {
									
								echo __ ( 'No file selected', 'nm-personalizedproduct' );
							}
						}
					}
				}
	
				echo '</p>';
			}
		}
	}
	
	
	function size_in_kb($file_name) {
		
		$base_dir = $this -> get_file_dir_path ();
		$file_path = $base_dir . 'confirmed/' . $file_name;
		
		if (file_exists($file_path)) {
			$size = filesize ( $file_path );
			return round ( $size / 1024, 2 ) . ' KB';
		}elseif(file_exists( $base_dir . '/' . $file_name ) ){
			$size = filesize ( $base_dir . '/' . $file_name );
			return round ( $size / 1024, 2 ) . ' KB';
		}
		
	}
	
	/*
	 * saving form meta in admin call
	 */
	function save_form_meta() {
		
		// print_r($_REQUEST); exit;
		//nm_personalizedproduct_pa($product_meta);
		global $wpdb;
		
		extract ( $_REQUEST );
		
		$dt = array (
				'productmeta_name'          => $productmeta_name,
				'productmeta_validation'	=> $productmeta_validation,
                'dynamic_price_display'     => $dynamic_price_hide,
                'show_cart_thumb'			=> $show_cart_thumb,
				'aviary_api_key'            => trim ( $aviary_api_key ),
				'productmeta_style'         => $productmeta_style,
				'the_meta'                  => json_encode ( $product_meta ),
				'productmeta_created'       => current_time ( 'mysql' )
		);
		
		$format = array (
				'%s',
				'%s',
				'%s',
                '%s',
				'%s',
				'%s',
				'%s' 
		);
		
		$res_id = $this -> insert_table ( self::$tbl_productmeta, $dt, $format );
		
		/* $wpdb->show_errors(); $wpdb->print_error(); */
		
		$resp = array ();
		if ($res_id) {
			
			$resp = array (
					'message' => __ ( 'Form added successfully', 'nm-personalizedproduct' ),
					'status' => 'success',
					'productmeta_id' => $res_id 
			);
		} else {
			
			$resp = array (
					'message' => __ ( 'Error while savign form, please try again', 'nm-personalizedproduct' ),
					'status' => 'failed',
					'productmeta_id' => '' 
			);
		}
		
		echo json_encode ( $resp );
		
		die ( 0 );
	}
	
	/*
	 * updating form meta in admin call
	 */
	function update_form_meta() {
		
		// print_r($_REQUEST); exit;
		global $wpdb;
		
		extract ( $_REQUEST );
		
		//nm_personalizedproduct_pa($product_meta); exit;
		
		$dt = array (
				'productmeta_name'          => $productmeta_name,
				'productmeta_validation'    => $productmeta_validation,
                'dynamic_price_display'     => $dynamic_price_hide,
                'show_cart_thumb'			=> $show_cart_thumb,
				'aviary_api_key'            => trim ( $aviary_api_key ),
				'productmeta_style'         => $productmeta_style,
				'the_meta'                  => json_encode ( $product_meta )
		);
		
		$where = array (
				'productmeta_id' => $productmeta_id 
		);
		
		$format = array (
				'%s',
				'%s',
                '%s',
                '%s',
				'%s',
				'%s' 
		);
		$where_format = array (
				'%d' 
		);
		
		$res_id = $this -> update_table ( self::$tbl_productmeta, $dt, $where, $format, $where_format );
		
		// $wpdb->show_errors(); $wpdb->print_error();
		
		$resp = array ();
		if ($res_id) {
			
			$resp = array (
					'message' => __ ( 'Form updated successfully', 'nm-personalizedproduct' ),
					'status' => 'success',
					'productmeta_id' => $productmeta_id 
			);
		} else {
			
			$resp = array (
					'message' => __ ( 'Error while updating form, please try again', 'nm-personalizedproduct' ),
					'status' => 'failed',
					'productmeta_id' => $productmeta_id 
			);
		}
		
		echo json_encode ( $resp );
		
		die ( 0 );
	}
	
	
	
	/*
	 * saving admin setting in wp option data table
	 */
	function save_settings() {
		
		// $this -> pa($_REQUEST);
		$existingOptions = get_option ( 'nm-personalizedproduct' . '_settings' );
		// pa($existingOptions);
		
		update_option ( 'nm-personalizedproduct' . '_settings', $_REQUEST );
		_e ( 'All options are updated', 'nm-personalizedproduct' );
		die ( 0 );
	}
	
	/*
	 * rendering template against shortcode
	 */
	function render_shortcode_template($atts) {
		extract ( shortcode_atts ( array (
				'productmeta_id' => '' 
		), $atts ) );
		
		$this -> productmeta_id = $productmeta_id;
		
		ob_start ();
		
		$this -> load_template ( 'render.input.php' );
		
		$output_string = ob_get_contents ();
		ob_end_clean ();
		
		return $output_string;
	}
	
	
	/*
	 * returning price for option in wc price format
	 */
	function get_option_price(){

		//nm_personalizedproduct_pa($_REQUEST); exit;
		
		//echo wc_price(intval($_REQUEST['price1']));
		extract($_REQUEST);
		
		$html = '';
		$option_total_price = 0;
		$fixed_fee = 0;
		$fixed_fee_meta = array();
		if($optionprices){
			foreach ($optionprices as $pair){
	
				$option 		= (isset($pair['option']) ? $pair['option'] : '');
				$price 			= (isset($pair['price']) ? $pair['price'] : '');
				$onetime 		= (isset($pair['isfixed']) ? $pair['isfixed'] : '');
				$onetime_taxable= (isset($pair['fixedfeetaxable']) ? $pair['fixedfeetaxable'] : '');
				
				$html .= $option . ' ' . woocommerce_price($price) . '<br>';
				
				
				if($onetime){
					$fixed_fee += $price;
					$fixed_fee_meta[$option] = array('fee' => $price, 'taxable' => $onetime_taxable);
				}else{
					
					$option_total_price += $price;
				}
			}
		}
		
		$pricematrix = (isset($_REQUEST['pricematrix']) ? $_REQUEST['pricematrix'] : '');
		if($pricematrix){
			$baseprice = $this -> get_matrix_price($qty, stripslashes($pricematrix));
		}
		if($pricematrix){
			$baseprice = $this -> get_matrix_price($qty, stripslashes($pricematrix));
		}
		
		//checking if it's a variation
		//getting options
		$variation_price = '';
		if($variation_id != ''){
			$product_variation = new WC_Product_Variation( $variation_id );
			$variation_price = $product_variation -> get_price();
			$baseprice = $variation_price;
		}
		
		
		$total_price = $option_total_price + $baseprice;
		
		$html .= '<strong>' . __('Total: ', 'nm-personalizedproduct') . woocommerce_price($total_price) . '</strong>';
		
		
		
		$option_prices = array(	'prices_html' 	=> $html, 
								'option_total'	=> $option_total_price,
								'total_price' 	=> $total_price,
								'onetime_fee'	=> $fixed_fee,
								'onetime_meta'	=> $fixed_fee_meta,
								'variation_price' => $variation_price,
								'display_price_hide' => $single_form -> dynamic_price_display);
		
		
		echo json_encode($option_prices);
		
		die(0);
	}
	
	/*
	 * setting price based on matrix
	 */
	function get_matrix_price($qty, $pricematrix){
		
		
		$pricematrix = json_decode( $pricematrix, true);
		foreach ($pricematrix as $mx){
			
			$mtx = explode('-', $mx['option']);
			$price = $mx['price'];
			
			$range1 = $mtx[0];	$range2 = $mtx[1];
			
			//echo 'r1 '.$range1. ' $r2 '.$range2.' qty '.$qty;
			if($qty >= $range1 && $qty <= $range2){
				
				$price_set = $price;
				break;
			}
			
		}
		
		return $price_set;
		
	}
	
	
	/*
	 * this function is setting up product price is matrix is found
	 */
	function set_matrix_price(){

		$price_matrix = json_decode( stripslashes($_REQUEST['matrix']));
		//print_r($price_matrix);
		$last_index = count($price_matrix) - 1;
		
		$html = woocommerce_price($price_matrix[0]->price).' - '.woocommerce_price($price_matrix[$last_index]->price);
		
		echo $html;
		
		die(0);
	}
	
	/*
	 * uploading file here
	 */
	function upload_file() {
		
		
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
		header ( "Cache-Control: no-store, no-cache, must-revalidate" );
		header ( "Cache-Control: post-check=0, pre-check=0", false );
		header ( "Pragma: no-cache" );
		
		// setting up some variables
		$file_dir_path = $this->setup_file_directory ();
		$response = array ();
		if ($file_dir_path == 'errDirectory') {
			
			$response ['status'] = 'error';
			$response ['message'] = __ ( 'Error while creating directory', 'nm-personalizedproduct' );
			die ( 0 );
		}
		
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds
		                        
		// 5 minutes execution time
		@set_time_limit ( 5 * 60 );
		
		// Uncomment this one to fake upload time
		// usleep(5000);
		
		// Get parameters
		$chunk = isset ( $_REQUEST ["chunk"] ) ? intval ( $_REQUEST ["chunk"] ) : 0;
		$chunks = isset ( $_REQUEST ["chunks"] ) ? intval ( $_REQUEST ["chunks"] ) : 0;
		$file_name = isset ( $_REQUEST ["name"] ) ? $_REQUEST ["name"] : '';
		
		// Clean the fileName for security reasons
		//$file_name = sanitize_file_name($file_name); 		//preg_replace ( '/[^\w\._]+/', '_', $file_name );
		$file_name = wp_unique_filename($file_dir_path, $file_name);
		$file_name = strtolower($file_name);
		
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists ( $file_dir_path . $file_name )) {
			$ext = strrpos ( $file_name, '.' );
			$file_name_a = substr ( $file_name, 0, $ext );
			$file_name_b = substr ( $file_name, $ext );
			
			$count = 1;
			while ( file_exists ( $file_dir_path . $file_name_a . '_' . $count . $file_name_b ) )
				$count ++;
			
			$file_name = $file_name_a . '_' . $count . $file_name_b;
		}
		
		// Remove old temp files
		if ($cleanupTargetDir && is_dir ( $file_dir_path ) && ($dir = opendir ( $file_dir_path ))) {
			while ( ($file = readdir ( $dir )) !== false ) {
				$tmpfilePath = $file_dir_path . $file;
				
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match ( '/\.part$/', $file ) && (filemtime ( $tmpfilePath ) < time () - $maxFileAge) && ($tmpfilePath != "{$file_path}.part")) {
					@unlink ( $tmpfilePath );
				}
			}
			
			closedir ( $dir );
		} else
			die ( '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}' );
		
		$file_path = $file_dir_path . $file_name;
		
		// Look for the content type header
		if (isset ( $_SERVER ["HTTP_CONTENT_TYPE"] ))
			$contentType = $_SERVER ["HTTP_CONTENT_TYPE"];
		
		if (isset ( $_SERVER ["CONTENT_TYPE"] ))
			$contentType = $_SERVER ["CONTENT_TYPE"];
			
			// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos ( $contentType, "multipart" ) !== false) {
			if (isset ( $_FILES ['file'] ['tmp_name'] ) && is_uploaded_file ( $_FILES ['file'] ['tmp_name'] )) {
				// Open temp file
				$out = fopen ( "{$file_path}.part", $chunk == 0 ? "wb" : "ab" );
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen ( $_FILES ['file'] ['tmp_name'], "rb" );
					
					if ($in) {
						while ( $buff = fread ( $in, 4096 ) )
							fwrite ( $out, $buff );
					} else
						die ( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
					fclose ( $in );
					fclose ( $out );
					@unlink ( $_FILES ['file'] ['tmp_name'] );
				} else
					die ( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
			} else
				die ( '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}' );
		} else {
			// Open temp file
			$out = fopen ( "{$file_path}.part", $chunk == 0 ? "wb" : "ab" );
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen ( "php://input", "rb" );
				
				if ($in) {
					while ( $buff = fread ( $in, 4096 ) )
						fwrite ( $out, $buff );
				} else
					die ( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
				
				fclose ( $in );
				fclose ( $out );
			} else
				die ( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
		}
		
		// Check if file has been uploaded
		if (! $chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename ( "{$file_path}.part", $file_path );
			
			// making thumb if images
			if($this -> is_image($file_name))
			{
				$thumb_size = 175;
				$thumb_dir_path = $this -> create_thumb($file_dir_path, $file_name, $thumb_size);
				
				if(file_exists($thumb_dir_path)){
					list($fw, $fh) = getimagesize( $thumb_dir_path );
					$response = array(
							'file_name'			=> $file_name,
							'file_w'			=> $fw,
							'file_h'			=> $fh,
							'nocache'			=> time(),
							'html'				=> $this->uploaded_html($file_dir_path, $file_name, $is_image=true, $_REQUEST['settings']),
					);
				}else{
					$response = array(
						'file_name'			=> 'ThumbNotFound',
					);
				}
			}else{
				$response = array(
						'file_name'			=> $file_name,
						'file_w'			=> 'na',
						'file_h'			=> 'na',
						'html'				=> $this->uploaded_html($file_dir_path, $file_name, $is_image=false, $_REQUEST['settings']),
				);
			}
			
			
		}
			
		// Return JSON-RPC response
		//die ( '{"jsonrpc" : "2.0", "result" : '. json_encode($response) .', "id" : "id"}' );
		die ( json_encode($response) );
		
		
	}
	
	/*
	 * deleting uploaded file from directory
	 */
	function delete_file() {
		$dir_path = $this -> setup_file_directory ();
		$file_path = $dir_path . $_REQUEST ['file_name'];
		
		if (unlink ( $file_path )) {
			
			if ($this -> is_image($_REQUEST ['file_name'])){
				$thumb_path = $dir_path . 'thumbs/' . $_REQUEST ['file_name'];
				if(file_exists($thumb_path))
					unlink ( $thumb_path );
				
				$cropped_image_path = $dir_path . 'cropped/' . $_REQUEST ['file_name'];
				if(file_exists($cropped_image_path))
					unlink ( $cropped_image_path );
			}
			
			_e( 'File removed', 'nm-personalizedproduct' );
			
				
		} else {
			printf(__('Error while deleting file %s', 'nm-personalizedproduct'), $file_path );
		}
		
		die ( 0 );
	}
	
	/**
	 * it will return html template of uploaded file
	 * to preview
	 */
	function uploaded_html($file_dir_path, $file_name, $is_image, $settings){
		
		$thumb_url = $file_meta = $file_tools = $_html = '';
		
		$settings = json_decode(stripslashes($settings), true);
		//$this -> pa($settings);
		$file_id = 'thumb_'.time();

		if($is_image){
			
			list($fw, $fh) 	= getimagesize( $file_dir_path . $file_name );
			$file_meta		= $fw . '(w) x '.$fh.'(h)';
			$file_meta		.= ' - '.__('Size: ', 'nmpersonalizedproduct') . $this->size_in_kb($file_name);
			
			$thumb_url = $this -> get_file_dir_url ( true ) . $file_name . '?nocache='.time();
			
			//large view
			$image_url = $this -> get_file_dir_url() . $file_name . '?nocache='.time();
			$_html .= '<div style="display:none" id="u_i_c_big_'.$file_id.'"><p id="thumb-thickbox"><img src="'.$image_url.'" /></p></div>';
			
			$tb_height 	= (isset($settings['popup-height']) && $settings['popup-height'] != '' ? $settings['popup-height'] : 400);
			$tb_width	= (isset($settings['popup-width']) && $settings['popup-width'] != '' ? $settings['popup-width'] : 600);
			$file_tools .= '<a href="#" class="nm-file-tools u_i_c_tools_del" title="'.__('Remove', 'nm-nmpersonalizedproduct').'"><span class="fa fa-times"></span></a>';	//delete icon
			$file_tools .= '<a href="#TB_inline?width='.$tb_width.'&height='.$tb_height.'&inlineId=u_i_c_big_'.$file_id.'" class="nm-file-tools u_i_c_tools_zoom thickbox" title="'.sprintf(__('%s', 'nm-nmpersonalizedproduct'), $file_name).'"><span class="fa fa-expand"></span></a>';	//big view icon
			
			if($settings['photo-editing'] == 'on' && $settings['aviary-api-key'] != ''){
				parse_str ( $settings['editing-tools'], $tools );
				if (isset( $tools['editing_tools'] ) && $tools['editing_tools'])
					$editing_tools = implode(',', $tools['editing_tools']);
				$file_tools .= '<a href="javascript:;" onclick="launch_aviary_editor(\''.$file_id.'\', \''.$image_url.'\', \''.$file_name.'\', \''.$editing_tools.'\')" class="nm-file-tools" title="'.__('Edit image', 'nm-nmpersonalizedproduct').'"><span class="fa fa-pencil"></span></a>';	//big view icon	
			}
			
			if($settings['cropping-ratio'] != NULL){
				
				$cropping_ratios = json_encode($settings['cropping-ratio']);
				//echo $cropping_ratios;
				$file_tools .= '<a href="javascript:;" onclick="launch_crop_editor(\''.$file_id.'\', \''.$image_url.'\', \''.$file_name.'\', \''.esc_attr($cropping_ratios).'\')" class="nm-file-tools" title="'.__('Crop image', 'nm-nmpersonalizedproduct').'"><span class="fa fa-crop"></span></a>';	//big view icon
			}
				
			
		}else{
			
			$file_meta		.= __('Size: ', 'nm-nmpersonalizedproduct') . $this->size_in_kb($file_name);
			$thumb_url = $this -> plugin_meta['url'] . '/images/file.png';
			
			$file_tools .= '<a class="nm-file-tools u_i_c_tools_del" href="" title="'.__('Remove', 'nm-nmpersonalizedproduct').'"><span class="fa fa-times"></span></a>';	//delete icon
		}
		
				
		$_html .= '<table class="uploaded-files-box"><tr>';
		$_html .= '<td style="vertical-align:middle"><img id="'.$file_id.'" src="'.$thumb_url.'" /></td>';
		
		$trimed_filename = (strlen($file_name) > 35 ? substr($file_name, 0, 35) . '...' : $file_name); 
		$_html .= '<td style="padding-left: 5px; vertical-align:top">'.$trimed_filename.'<br>';
		$_html .= '<span class="file-meta">'.$file_meta.'</span><br>';
		$_html .= $file_tools;
		$_html .= '</td>';
		
		$_html .= '</tr></table>';
		
		return $_html;
	}
	
	/*
	 * this function is saving photo returned by Aviary
	 */
	function save_edited_photo() {
				
		$aviary_addon_dir = 'nm-aviary-photo-editing-addon/index.php';
		$file_path = ABSPATH . 'wp-content/plugins/' . $aviary_addon_dir;
		if (! file_exists ( $file_path )) {
			die ( 'Could not find file ' . $file_path );
		}
		
		include_once $file_path;
		
		$aviary = new NM_Aviary ();
		
		// setting plugin meta saved in config.php
		$aviary->plugin_meta = get_plugin_meta_wooproduct ();
		
		$aviary->dir_path = $this->get_file_dir_path ();
		$aviary->dir_name = $this -> product_files;
		$aviary->posted_data = json_decode ( stripslashes ( $_REQUEST ['postdata'] ) );
		$aviary->image_data = file_get_contents ( $_REQUEST ['url'] );
		$aviary->image_url	= $_REQUEST ['url'];
		
		$aviary -> save_file_locally();
		
		die ( 0 );
	}
	
	/*
	 * 9- adding files link in order email
	 */
	function add_files_link_in_email($order, $is_admin){
		
		if (sizeof ( $order->get_items () ) > 0) {
			foreach ( $order->get_items () as $item ) {
		
				// nm_personalizedproduct_pa($item);
		
				$selected_meta_id = get_post_meta ( $item ['product_id'], '_product_meta_id', true );
		
				$single_meta = $this -> get_product_meta ( $selected_meta_id);
				$product_meta = json_decode ( $single_meta->the_meta );
		
				// nm_personalizedproduct_pa($product_meta);
				if($product_meta){
						
					foreach ( $product_meta as $meta => $data ) {
							
						if ($data -> type == 'file' || $data -> type == 'facebook') {
							
							$product_files = unserialize( $item['product_attached_files'] );	//explode(',', $item[$data -> title]);
							$product_files = $product_files[$data -> title];
							$product_id = $item ['product_id'];
								
							if ($product_files) {
								
								echo '<strong>';
								printf(__('File attached %s', 'nm-personalizedproduct'), $data->title);
								echo '</strong>';
									
									
								foreach ( $product_files as $file ) {
										
									$files_found++;
									$ext = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
										
									if ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'jpeg')
										$src_thumb = $this -> get_file_dir_url ( true ) . $file;
									else
										$src_thumb = $this -> plugin_meta ['url'] . '/images/file.png';
										
									$src_file = $this -> get_file_dir_url () . $file;
										
									if(!file_exists($src_file)){
										$file_name = $order -> id . '-' . $product_id . '-' . $file;		// from version 3.4
										$src_file = $this -> get_file_dir_url () . 'confirmed/' . $file_name;
									}else{
										$file_name = $file;
										$src_file = $this -> get_file_dir_url () . '/' . $file_name;
									}
										
										
									echo '<table>';
									echo '<tr><td width="100"><img src="' . $src_thumb . '"><td><td><a href="' . $src_file . '">' . __ ( 'Download ' ) . $file_name . '</a> ' . $this -> size_in_kb ( $file_name ) . '</td>';
										
									$edited_path = $this->get_file_dir_path() . 'edits/' . $file;
									if (file_exists($edited_path)) {
										$file_url_edit = $this->get_file_dir_url () .  'edits/' . $file;
										echo '<td><a href="' . $file_url_edit . '" target="_blank">' . __ ( 'Download edited image', $this->plugin_meta ['shortname'] ) . '</a></td>';
									}
										
									echo '</tr>';
									echo '</table>';
								}
							}
		
							
						}
					}
				}
		
			}
		}
	}

	function crop_image_editor(){

		/*
		 * loading uploader template
		 */

		$ratio = json_decode( stripslashes( $_REQUEST['ratios'] ) );
		//var_dump($ratio);
		$vars = array('image_name' => $_REQUEST['image_name'], 'image_url' => $_REQUEST['image_url'], 'ratio' => $ratio, 'fileid' => $_REQUEST['file_id']);
		$this -> load_template( 'crop_image.php', $vars);
		

		die(0);
	}
	
	
	function crop_image(){

		//print_r($_REQUEST); exit;
		
		$image_path = $this -> get_file_dir_path() . $_REQUEST['image_name'];
		$cropped_name = $_REQUEST['image_name'];
		$cropped_dest = $this -> setup_file_directory('cropped') . $cropped_name;
		
		
		
		$image = wp_get_image_editor ( $image_path );
		//$crop_coords = array($_REQUEST['coords']['x'])
		if (! is_wp_error ( $image )) {
			/*$image->resize ( $_REQUEST['img_w'], $_REQUEST['img_h'], false );
			$image->crop (  intval($_REQUEST['coords']['x']), 
							intval($_REQUEST['coords']['y']), 
							intval($_REQUEST['coords']['w']), 
							intval($_REQUEST['coords']['h']), 
							intval($_REQUEST['coords']['w']), 
							intval($_REQUEST['coords']['h']), false );*/
							
							
			$real_size = $image->get_size();	
			$factor_x = $real_size['width']/$_REQUEST['img_w'];
			$factor_y = $real_size['height']/$_REQUEST['img_h'];
			
			$real_x = intval($_REQUEST['coords']['x']) * $factor_x;
			$real_y = $_REQUEST['coords']['y'] * $factor_y;
			$real_w = ($_REQUEST['coords']['x2'] * $factor_x) - $real_x;
			$real_h = ($_REQUEST['coords']['y2'] * $factor_y) - $real_y;
			
			/*echo 'factorx '.$factor_x.' factorY: '.$factor_y;
			echo '<br>';
			echo 'realX: '.$real_x.' realY: '.$real_y;
			echo '<br>';
			echo 'realW: '.$real_w.' realH: '.$real_h;
			exit;*/
			
			
			$image->crop ( $real_x, $real_y, $real_w, $real_h);
			
			//$image->crop ( 130, 110, 107, 145, NULL, NULL, false );
			$cropped_image = $image->save ( $cropped_dest );
			
			//also saving thumb
			$new_thumb = wp_get_image_editor ( $cropped_dest );
			$cropped_thumb_name = $_REQUEST['image_name'];
			$cropped_thumb_dest = $this -> get_file_dir_path() . 'thumbs/' . $cropped_thumb_name;
			if (! is_wp_error ( $new_thumb )) {
				$new_thumb->resize ( 75, 75 );
				$new_thumb->save ( $cropped_thumb_dest );
			}else{
				die('error while loading image '.$image_path);
			}
		}else{
			die('error while loading image '.$image_path);
		}
		
		//$the_cropped  = wp_crop_image($image_path, $_REQUEST['coords']['x'], $_REQUEST['coords']['y'], $_REQUEST['coords']['w'], $_REQUEST['coords']['h'], NULL, NULL, false);
		$thumb_url = $this -> get_file_dir_url(true) . $cropped_thumb_name . '?nocache='.time();
		echo json_encode(array('fileid' => $_REQUEST['fileid'], 'cropped_image' => $thumb_url));
		die(0);
	}
	
	// ================================ SOME HELPER FUNCTIONS =========================================
	
	/*
	 * simplifying meta for admin view in existing-meta.php
	 */
	function simplify_meta($meta) {
		//echo $meta;
		$metas = json_decode ( $meta );
		
		if ($metas) {
			echo '<ul>';
			foreach ( $metas as $meta => $data ) {
				
				//nm_personalizedproduct_pa($data);
				$req = (isset( $data -> required ) && $data -> required == 'on') ? 'yes' : 'no';
				
				echo '<li>';
				echo '<strong>label:</strong> ' . $data -> title;
				echo ' | <strong>type:</strong> ' . $data -> type;
				
				if (isset( $data -> options ) && ! is_object ( $data -> options )){
					echo ' | <strong>options:</strong> ' . $data -> options;
				}
				
					
				echo ' | <strong>required:</strong> ' . $req;
				echo '</li>';
			}
			
			echo '</ul>';
		}
	}
	
	/*
	 * delete meta
	 */
	function delete_meta() {
		global $wpdb;
		
		extract ( $_REQUEST );
		
		$res = $wpdb->query ( "DELETE FROM `" . $wpdb->prefix . self::$tbl_productmeta . "` WHERE productmeta_id = " . $productmeta_id );
		
		if ($res) {
			
			_e ( 'Meta deleted successfully', 'nm-personalizedproduct' );
		} else {
			$wpdb->show_errors ();
			$wpdb->print_error ();
		}
		
		die ( 0 );
	}
	
	/*
	 * setting up user directory
	 */
	function setup_file_directory( $sub_dir_name = null) {
		$upload_dir = wp_upload_dir ();
		
		$parent_dir = $upload_dir ['basedir'] . '/' . $this -> product_files . '/';
		$thumb_dir  = $parent_dir . 'thumbs/';
		
		if($sub_dir_name){
			$sub_dir = $parent_dir . $sub_dir_name . '/';
			if(wp_mkdir_p($sub_dir)){
				return $sub_dir;
			}else{
				die('Error while creating parent dirctory '.$sub_dir);
			}
		}elseif(wp_mkdir_p($parent_dir)){
			if(wp_mkdir_p($thumb_dir)){
				return $parent_dir;
			}else{
				die('Error while creating parent dirctory '.$thumb_dir);
			}
		}else{
			die('Error while creating parent dirctory '.$parent_dir);
		}
	
	}
	
	/*
	 * getting file URL
	 */
	function get_file_dir_url($thumbs = false) {

		$upload_dir = wp_upload_dir ();		
		
		if ($thumbs)
			return $upload_dir ['baseurl'] . '/' . $this -> product_files . '/thumbs/';
		else
			return $upload_dir ['baseurl'] . '/' . $this -> product_files . '/';
	}
	function get_file_dir_path() {
		$upload_dir = wp_upload_dir ();
		return $upload_dir ['basedir'] . '/' . $this -> product_files . '/';
	}
	
	/*
	 * creating thumb using WideImage Library Since 21 April, 2013
	 */
	function create_thumb($dest, $image_name, $thumb_size) {

	// using wp core image processing editor, 6 May, 2014
		$image = wp_get_image_editor ( $dest . $image_name );
		$dest = $dest . 'thumbs/' . $image_name;
		if (! is_wp_error ( $image )) {
			$image->resize ( 75, 75, true );
			$image->save ( $dest );
		}
		
		return $dest;
	}
	
	
	function activate_plugin() {
		global $wpdb;
		$plugin_db_version = '3.9.12';
		/*
		 * meta_for: this is to make this table to contact more then one metas for NM plugins in future in this plugin it will be populated with: forms
		 */
		$forms_table_name = $wpdb->prefix . self::$tbl_productmeta;
		
		$sql = "CREATE TABLE $forms_table_name (
		productmeta_id INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		productmeta_name VARCHAR(50) NOT NULL,
		productmeta_validation VARCHAR(3),
        dynamic_price_display VARCHAR(3),
        show_cart_thumb VARCHAR(3),
		aviary_api_key VARCHAR(40),
		productmeta_style MEDIUMTEXT,
		the_meta MEDIUMTEXT NOT NULL,
		productmeta_created DATETIME NOT NULL
		);";
		
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta ( $sql );
		
		update_option ( "personalizedproduct_db_version", $plugin_db_version );
		
		// this is to remove un-confirmed files daily
		if ( ! wp_next_scheduled( 'do_action_remove_images' ) ) {
			wp_schedule_event( time(), 'daily', 'do_action_remove_images');
		}
		
		if ( ! wp_next_scheduled( 'setup_styles_and_scripts_wooproduct' ) ) {
			wp_schedule_event( time(), 'daily', 'setup_styles_and_scripts_wooproduct');
		}
		
	}
	
	/*
	 * removing ununsed order files
	*/
	
	function remove_unpaid_orders_images(){
		
		$dir = $this -> setup_file_directory();
		
		if(is_dir($dir)){

		$dir_handle = opendir($dir);
		while ($file = readdir($dir_handle)){
				
			if(!is_dir($file)){
				@unlink($dir . $file);
			}
		}
				
		}
		
		
		closedir($dir_handle);
	}
	
	
	
	function deactivate_plugin() {
		
		// do nothing so far.
		wp_clear_scheduled_hook( 'do_action_remove_images' );
		
		wp_clear_scheduled_hook( 'setup_styles_and_scripts_wooproduct' );
		
	}
	
	
	/*
	 * cloning product meta for admin
	 * being called from: templates/admin/create-form.php
	 */
	function clone_product_meta($meta_id){
		
		global $wpdb;
		
		$forms_table_name = $wpdb->prefix . self::$tbl_productmeta;
		
		$sql = "INSERT INTO $forms_table_name
		(productmeta_name, aviary_api_key, productmeta_style, the_meta, productmeta_created) 
		SELECT productmeta_name, aviary_api_key, productmeta_style, the_meta, productmeta_created 
		FROM $forms_table_name 
		WHERE productmeta_id = %d;";
		
		$result = $wpdb -> query($wpdb -> prepare($sql, array($meta_id)));
		
		/* var_dump($result);
		
		$wpdb->show_errors();
		$wpdb->print_error(); */
		
	}
	
	
	/*
	 * checking if aviary addon is installed or not
	 */
	function is_aviary_installed() {
		
		if( is_plugin_active('nm-aviary-photo-editing-addon/index.php') ){
			return true;
		}else{
			return false;
		}
		
	}
	
	/*
	 * returning NM_Inputs object
	*/
	function get_all_inputs() {
	
		if (! class_exists ( 'NM_Inputs_wooproduct' )) {
			$_inputs = $this -> plugin_meta ['path'] . '/classes/input.class.php';
			
			if (file_exists ( $_inputs ))
				include_once ($_inputs);
			else
				die ( 'Reen, Reen, BUMP! not found ' . $_inputs );
		}
	
		$nm_inputs = new NM_Inputs_wooproduct ();
		// webcontact_pa($this->plugin_meta);
	
		// registering all inputs here
	
		$all_inputs = array (
				
				'text' 		=> $nm_inputs->get_input ( 'text' ),
				'textarea' 	=> $nm_inputs->get_input ( 'textarea' ),
				'select' 	=> $nm_inputs->get_input ( 'select' ),
				'radio' 	=> $nm_inputs->get_input ( 'radio' ),
				'number' 	=> $nm_inputs->get_input ( 'number' ),
				'email' 	=> $nm_inputs->get_input ( 'email' ),
				'date' 		=> $nm_inputs->get_input ( 'date' ),
				'checkbox' 	=> $nm_inputs->get_input ( 'checkbox' ),
				'masked' 	=> $nm_inputs->get_input ( 'masked' ),
				'hidden' 	=> $nm_inputs->get_input ( 'hidden' ),				
				'color'		=> $nm_inputs->get_input ( 'color' ),				
				'file' 		=> $nm_inputs->get_input ( 'file' ),
				'image' 	=> $nm_inputs->get_input ( 'image' ),
				'pricematrix' => $nm_inputs->get_input ( 'pricematrix' ),
				'section' 	=> $nm_inputs->get_input ( 'section' ),				
		);
		
		$fb_class = $nm_inputs->get_addon ( 'facebook' );	//Addon
		if($fb_class){
			$all_inputs['facebook'] = $fb_class;
		}
		
		//nm_personalizedproduct_pa($all_inputs);
		
		return $all_inputs;
	
		// return new NM_Inputs($this->plugin_meta);
	}
	
	
	/**
	 * adding font awesome support
	 */
	
	function load_scripts_extra(){

		wp_enqueue_style( 'prefix-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css', array(), '4.0.3' );
	}
	
	/*
	 * check if file is image and return true
	*/
	function is_image($file){
	
		$type = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
	
		if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg"))
			return true;
		else
			return false;
	}
	
	function move_images_admin(){
		
		//print_r($_REQUEST);
		$this -> move_files_when_paid(intval($_REQUEST['orderid']));
		die(0);
	}
	
	function move_files_when_paid($order_id){
	
	
		global $woocommerce;
	
		// getting product id in cart
		$cart = $woocommerce->cart->get_cart();
	
		$base_path 	= $this -> setup_file_directory();
		$confirmed_dir = $this -> setup_file_directory() . 'confirmed/';
		
		if (! is_dir ( $confirmed_dir )) {
			if (!mkdir ( $confirmed_dir, 0775, true ))
				die('Error while created directory '.$confirmed_dir);
		}	
	
		
		//nm_personalizedproduct_pa($cart); exit;
		foreach ($cart as $item){
			
			$product_id = $item['product_id'];
			$attached_files = $item['product_meta']['_product_attached_files'];
			
			foreach ( $attached_files as $title => $item_files ) {
				
				foreach ( $item_files as $key => $file ) {
					
					$new_filename = $order_id . '-' . $product_id . '-' . $file;
					$source_file = $base_path . $file;
					$destination = $confirmed_dir . $new_filename;
					
					if (file_exists ( $destination ))
						break;
					
					if (file_exists ( $source_file )) {
						
						if (! rename ( $source_file, $destination ))
							die ( 'Error while re-naming order image ' . $source_file );
					}
				}
			}
		}
	}

	/**
	 * is it real plugin
	 */
	function get_real_plugin_first(){
		
		$hashcode = get_option ( $this->plugin_meta ['shortname'] . '_hashcode' );
		$hash_file = $this -> plugin_meta['path'] . '/assets/_hashfile.txt';
		if ( file_exists( $hash_file )) {
			return $hashcode;
		}else{			
			return $hashcode;
		}
	}
	
	function get_plugin_hashcode(){
		
		$key = $_SERVER['HTTP_HOST'];
		return hash( 'md5', $key );
	}
	

	
	function validate_api($apikey = null) {

		//webcontact_pa($_REQUEST);
		$api_key = ($apikey != null ? $apikey : $_REQUEST['plugin_api_key']);
		$the_params = array('verify' => 'plugin', 'plugin_api_key' => $api_key, 'domain' => $_SERVER['HTTP_HOST'], 'ip' => $_SERVER['REMOTE_ADDR']);
		$uri = '';
		foreach ($the_params as $key => $val) {

			$uri .= $key . '=' . urlencode($val) . '&';
		}

		$uri = substr($uri, 0, -1);

		$endpoint = "http://www.wordpresspoets.com/?$uri";

		$resp = wp_remote_get($endpoint);
		//$this->pa($resp);

		$callback_resp = array('status' => '', 'message' => '');

		if (is_wp_error($resp)) {

			$callback_resp = array('status' => 'success', 'message' => "Plugin activated");

			$hashkey = $_SERVER['HTTP_HOST'];
			$hash_code = hash('md5', $hashkey);

			update_option($this -> plugin_meta['shortname'] . '_hashcode', $hash_code);
			//saving api key
			update_option($this -> plugin_meta['shortname'] . '_apikey', $api_key);
			
			$headers[] = "From: NM Plugins
			<noreply@najeebmedia.com>
			";
					$headers[] = "Content-Type: text/html";
					$report_to = 'sales@najeebmedia.com';
					$subject = 'Plugin API Issue - ' . $_SERVER['HTTP_HOST'];
					$message = 'Error code: ' . $resp -> get_error_message();
					$message .= '<br>Error message: ' . $response -> message;
					$message .= '<br>API Key: ' . $api_key;

					if (get_option($this -> plugin_meta['shortname'] . '_apikey') != '') {
						wp_mail($report_to, $subject, $message, $headers);
					}

		} else {

			$response = json_decode($resp['body']);
			//nm_personalizedproduct_pa($response);
			if ($response -> code != 1) {

				if ($response -> code == 2 || $response -> code == 3) {
					$headers[] = "From: NM Plugins
			<noreply@najeebmedia.com>
			";
					$headers[] = "Content-Type: text/html";
					$report_to = 'sales@najeebmedia.com';
					$subject = 'Plugin API Issue - ' . $_SERVER['HTTP_HOST'];
					$message = 'Error code: ' . $response -> code;
					$message .= '
			<br>
			Error message: ' . $response -> message;
					$message .= '
			<br>
			API Key: ' . $api_key;

					if (get_option($this -> plugin_meta['shortname'] . '_apikey') != '') {
						wp_mail($report_to, $subject, $message, $headers);
					}
				}

				$callback_resp = array('status' => 'error', 'message' => $response -> message);

				delete_option($this -> plugin_meta['shortname'] . '_apikey');
				delete_option($this -> plugin_meta['shortname'] . '_hashcode');

			} else {
				$callback_resp = array('status' => 'success', 'message' => $response -> message);

				$hash_code = $response -> hashcode;

				update_option($this -> plugin_meta['shortname'] . '_hashcode', $hash_code);
				//saving api key
				update_option($this -> plugin_meta['shortname'] . '_apikey', $api_key);
			}

		}

		//$this -> pa($callback_resp);
		echo json_encode($callback_resp);

		die(0);
	}

	function get_connected_to_load_it(){
		
		$apikey = get_option( $this->plugin_meta ['shortname'] . '_apikey');
		self::validate_api( $apikey );
		
	}

	function nm_export_ppom(){
		
		if(isset($_REQUEST['nm_export']) && $_REQUEST['nm_export'] == 'ppom'){
			
			global $wpdb;
		
			$qry = "SELECT * FROM " . $wpdb->prefix . self::$tbl_productmeta;
			$all_meta = $wpdb->get_results ( $qry, ARRAY_A );
			
			if($all_meta){
				$all_meta = $this -> add_slashes_array($all_meta);
			}
			
			//nm_personalizedproduct_pa($all_meta); exit;
			$filename = 'ppom-export.csv';
			$delimiter = '|';
			
			 // tell the browser it's going to be a csv file
		    header('Content-Type: application/csv');
		    // tell the browser we want to save it instead of displaying it
		    header('Content-Disposition: attachement; filename="'.$filename.'";');
		    
			// open raw memory as file so no temp files needed, you might run out of memory though
		    $f = fopen('php://output', 'w'); 
		    // loop over the input array
		    foreach ($all_meta as $line) { 
		        // generate csv lines from the inner arrays
		        fputcsv($f, $line, $delimiter); 
		    }
		    // rewrind the "file" with the csv lines
		    fseek($f, 0);
		   
		    // make php send the generated csv lines to the browser
		    fpassthru($f);
		    
			die(0);
		}
	}
	
	function add_slashes_array($arr){
		foreach ($arr as $k => $v)
	        $ReturnArray[$k] = (is_array($v)) ? $this->add_slashes_array($v) : addslashes($v);
	    return $ReturnArray;
	}
	
	function process_nm_importing_file_ppom(){
		
		global $wpdb;
		//get the csv file
		//nm_personalizedproduct_pa($_FILES);
	    $file = $_FILES[ppom_csv][tmp_name];
	    $handle = fopen($file,"r");
	    
	    $qry = "INSERT INTO ".$wpdb->prefix . self::$tbl_productmeta;
	    $qry .= " (productmeta_name, aviary_api_key, productmeta_style,
	    		the_meta, productmeta_created, productmeta_validation, dynamic_price_display, show_cart_thumb) VALUES";
	    	
	    	
	    //loop through the csv file and insert into database
	    do {
	        				
            //nm_personalizedproduct_pa($data);
            if($cols){
	            foreach( $cols as $key => $val ) {
		            $cols[$key] = trim( $cols[$key] );
		            //$cols[$key] = iconv('UCS-2', 'UTF-8', $cols[$key]."\0") ;
		            $cols[$key] = str_replace('""', '"', $cols[$key]);
		            $cols[$key] = preg_replace("/^\"(.*)\"$/sim", "$1", $cols[$key]);
	        	}
            }
        	
        	 if ($cols[0]) {
	        	$qry .= "(	
	        				'".$cols[1]."',
	        				'".$cols[2]."',
	        				'".$cols[3]."',
	        				'".$cols[4]."',
	        				'".$cols[5]."',
	        				'".$cols[6]."',
	        				'".$cols[7]."',
	        				'".$cols[8]."'
	        				),";
	        				
        	//nm_personalizedproduct_pa($cols);
	        }
	    } while ($cols = fgetcsv($handle,2000,"|"));
	    
	    $qry = substr($qry, 0, -1);
	    
	    //print $qry;
	    $res = $wpdb->query( $qry );
	    wp_redirect(  admin_url( 'options-general.php?page=nm-personalizedproduct' ) );
   		exit;

	    
	    /*$wpdb->show_errors();
	    $wpdb->print_error();*/
	}
}