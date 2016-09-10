<?php
/**
 * Common functions for both versions.
 */

if( !function_exists( 'dd' ) ) {

	function dd( $data ) {
		echo "<pre>" . print_r( $data, 1 ) . "</pre>";
		die();
	}

}

if( !function_exists( 'precho' ) ) {

	function precho( $data ) {
		echo "<pre>" . print_r( $data, 1 ) . "</pre>";
	}

}

/**
 * Check if WooCommerce plugin is installed and activated.
 */
if( !function_exists( 'pys_is_woocommerce_active' ) ) {

	function pys_is_woocommerce_active() {
		return class_exists( 'WooCommerce' ) == true ? true : false;
	}

}

/**
 * Check if Easy Digital Downloads plugin is installed and activated.
 */
if( !function_exists( 'pys_is_edd_active' ) ) {

	function pys_is_edd_active() {
		return class_exists( 'Easy_Digital_Downloads' ) == true ? true : false;
	}

}

/**
 * Return option value.
 */
if( !function_exists( 'pys_get_option' ) ) {

	function pys_get_option( $section, $option, $default = '' ) {

		$options = get_option( 'pixel_your_site' );

		return isset( $options[ $section ][ $option ] ) ? $options[ $section ][ $option ] : $default;

	}

}

/**
 * Return checkbox state.
 */
if( !function_exists( 'pys_checkbox_state' ) ) {

	function pys_checkbox_state( $section, $option, $default = '' ) {

		$options = get_option( 'pixel_your_site' );

		if ( isset( $options[ $section ][ $option ] ) ) {
			return $options[ $section ][ $option ] == 1 ? 'checked' : '';
		}

		return $default;

	}

}

/**
 * Return radio box state.
 */
if( !function_exists( 'pys_radio_state' ) ) {

	function pys_radio_state( $section, $option, $value ) {

		$options = get_option( 'pixel_your_site' );

		if ( isset( $options[ $section ][ $option ] ) ) {
			return $options[ $section ][ $option ] == $value ? 'checked' : '';
		}

		return null;
	}

}

/**
 * Facebook Pixel Event types options html.
 */
if( !function_exists( 'pys_event_types_select_options' ) ) {

	function pys_event_types_select_options( $current = null, $full = true ) {
		?>

		<option <?php echo selected( null, $current, true ); ?> disabled>Select Type</option>
		<option <?php echo selected( 'ViewContent', $current, true ); ?> value="ViewContent">ViewContent</option>

		<?php if ( $full ) : ?>
			<option <?php echo selected( 'Search', $current, true ); ?> value="Search">Search</option>
		<?php endif; ?>

		<option <?php echo selected( 'AddToCart', $current, true ); ?> value="AddToCart">AddToCart</option>
		<option <?php echo selected( 'AddToWishlist', $current, true ); ?> value="AddToWishlist">AddToWishlist</option>
		<option <?php echo selected( 'InitiateCheckout', $current, true ); ?> value="InitiateCheckout">InitiateCheckout</option>
		<option <?php echo selected( 'AddPaymentInfo', $current, true ); ?> value="AddPaymentInfo">AddPaymentInfo</option>
		<option <?php echo selected( 'Purchase', $current, true ); ?> value="Purchase">Purchase</option>
		<option <?php echo selected( 'Lead', $current, true ); ?> value="Lead">Lead</option>
		<option <?php echo selected( 'CompleteRegistration', $current, true ); ?> value="CompleteRegistration">
			CompleteRegistration
		</option>

		<?php if ( $full ) : ?>

			<option disabled></option>
			<option <?php echo selected( 'CustomEvent', $current, true ); ?> value="CustomEvent">Custom event</option>
			<option <?php echo selected( 'CustomCode', $current, true ); ?> value="CustomCode">Custom event code</option>

		<?php endif; ?>

		<?php
	}

}

/**
 * Current Page Full URL without trailing slash
 */
if( !function_exists( 'pys_get_current_url' ) ) {

	function pys_get_current_url() {

		$current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$current_url = rtrim( $current_url, '/' );

		return $current_url;

	}

}

/**
 * Returns relative path without protocol, host, slashes.
 */
if( !function_exists( 'pys_get_relative_path' ) ) {

	function pys_get_relative_path( $url ) {

		$host = str_replace( array( 'http://', 'https://', 'http://www.', 'https://www.', 'www.' ), '', home_url() );

		$url = str_replace( array( 'http://', 'https://', 'http://www.', 'https://www.', 'www.' ), '', $url );
		$url = str_replace( $host, '', $url );

		$url = trim( $url );
		$url = ltrim( $url, '/' );
		$url = rtrim( $url, '/' );

		return $url;

	}

}

/**
 * Check if needle URL (full or relative) matches with current.
 */
if( !function_exists( 'pys_match_url' ) ) {

	function pys_match_url( $match_url, $current_url = '' ) {

		// use current url by default
		if ( ! isset( $current_url ) || empty( $current_url ) ) {
			$current_url = pys_get_current_url();
		}

		$current_url = pys_get_relative_path( $current_url );
		$match_url   = pys_get_relative_path( $match_url );

		if ( substr( $match_url, - 1 ) == '*' ) {
			// if match_url ends with wildcard

			$match_url = rtrim( $match_url, '*' );

			if ( pys_startsWith( $current_url, $match_url ) ) {
				return true;
			}

		} else {
			// exact url

			if ( $current_url == $match_url ) {
				return true;
			}

		}

		return false;

	}

}

if( !function_exists( 'pys_startsWith' ) ) {

	function pys_startsWith( $haystack, $needle ) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos( $haystack, $needle, - strlen( $haystack ) ) !== false;
	}

}

if( !function_exists( 'pys_endsWith' ) ) {

	function pys_endsWith( $haystack, $needle ) {
		// search forward starting from end minus needle length characters
		return $needle === "" || ( ( $temp = strlen( $haystack ) - strlen( $needle ) ) >= 0 && strpos( $haystack, $needle, $temp ) !== false );
	}

}

/**
 * Clean string to be UTF-8
 */
if( !function_exists( 'pys_clean_param_value' ) ) {

	function pys_clean_param_value( $value ) {

		//@todo: remove deprecated function

		$replace = array(
			'&lt;'   => '',
			'&gt;'   => '',
			'&#039;' => '',
			'&amp;'  => '',
			'&quot;' => '',
			'À'      => 'A',
			'Á'      => 'A',
			'Â'      => 'A',
			'Ã'      => 'A',
			'Ä'      => 'Ae',
			'&Auml;' => 'A',
			'Å'      => 'A',
			'Ā'      => 'A',
			'Ą'      => 'A',
			'Ă'      => 'A',
			'Æ'      => 'Ae',
			'Ç'      => 'C',
			'Ć'      => 'C',
			'Č'      => 'C',
			'Ĉ'      => 'C',
			'Ċ'      => 'C',
			'Ď'      => 'D',
			'Đ'      => 'D',
			'Ð'      => 'D',
			'È'      => 'E',
			'É'      => 'E',
			'Ê'      => 'E',
			'Ë'      => 'E',
			'Ē'      => 'E',
			'Ę'      => 'E',
			'Ě'      => 'E',
			'Ĕ'      => 'E',
			'Ė'      => 'E',
			'Ĝ'      => 'G',
			'Ğ'      => 'G',
			'Ġ'      => 'G',
			'Ģ'      => 'G',
			'Ĥ'      => 'H',
			'Ħ'      => 'H',
			'Ì'      => 'I',
			'Í'      => 'I',
			'Î'      => 'I',
			'Ï'      => 'I',
			'Ī'      => 'I',
			'Ĩ'      => 'I',
			'Ĭ'      => 'I',
			'Į'      => 'I',
			'İ'      => 'I',
			'Ĳ'      => 'IJ',
			'Ĵ'      => 'J',
			'Ķ'      => 'K',
			'Ł'      => 'K',
			'Ľ'      => 'K',
			'Ĺ'      => 'K',
			'Ļ'      => 'K',
			'Ŀ'      => 'K',
			'Ñ'      => 'N',
			'Ń'      => 'N',
			'Ň'      => 'N',
			'Ņ'      => 'N',
			'Ŋ'      => 'N',
			'Ò'      => 'O',
			'Ó'      => 'O',
			'Ô'      => 'O',
			'Õ'      => 'O',
			'Ö'      => 'Oe',
			'&Ouml;' => 'Oe',
			'Ø'      => 'O',
			'Ō'      => 'O',
			'Ő'      => 'O',
			'Ŏ'      => 'O',
			'Œ'      => 'OE',
			'Ŕ'      => 'R',
			'Ř'      => 'R',
			'Ŗ'      => 'R',
			'Ś'      => 'S',
			'Š'      => 'S',
			'Ş'      => 'S',
			'Ŝ'      => 'S',
			'Ș'      => 'S',
			'Ť'      => 'T',
			'Ţ'      => 'T',
			'Ŧ'      => 'T',
			'Ț'      => 'T',
			'Ù'      => 'U',
			'Ú'      => 'U',
			'Û'      => 'U',
			'Ü'      => 'Ue',
			'Ū'      => 'U',
			'&Uuml;' => 'Ue',
			'Ů'      => 'U',
			'Ű'      => 'U',
			'Ŭ'      => 'U',
			'Ũ'      => 'U',
			'Ų'      => 'U',
			'Ŵ'      => 'W',
			'Ý'      => 'Y',
			'Ŷ'      => 'Y',
			'Ÿ'      => 'Y',
			'Ź'      => 'Z',
			'Ž'      => 'Z',
			'Ż'      => 'Z',
			'Þ'      => 'T',
			'à'      => 'a',
			'á'      => 'a',
			'â'      => 'a',
			'ã'      => 'a',
			'ä'      => 'ae',
			'&auml;' => 'ae',
			'å'      => 'a',
			'ā'      => 'a',
			'ą'      => 'a',
			'ă'      => 'a',
			'æ'      => 'ae',
			'ç'      => 'c',
			'ć'      => 'c',
			'č'      => 'c',
			'ĉ'      => 'c',
			'ċ'      => 'c',
			'ď'      => 'd',
			'đ'      => 'd',
			'ð'      => 'd',
			'è'      => 'e',
			'é'      => 'e',
			'ê'      => 'e',
			'ë'      => 'e',
			'ē'      => 'e',
			'ę'      => 'e',
			'ě'      => 'e',
			'ĕ'      => 'e',
			'ė'      => 'e',
			'ƒ'      => 'f',
			'ĝ'      => 'g',
			'ğ'      => 'g',
			'ġ'      => 'g',
			'ģ'      => 'g',
			'ĥ'      => 'h',
			'ħ'      => 'h',
			'ì'      => 'i',
			'í'      => 'i',
			'î'      => 'i',
			'ï'      => 'i',
			'ī'      => 'i',
			'ĩ'      => 'i',
			'ĭ'      => 'i',
			'į'      => 'i',
			'ı'      => 'i',
			'ĳ'      => 'ij',
			'ĵ'      => 'j',
			'ķ'      => 'k',
			'ĸ'      => 'k',
			'ł'      => 'l',
			'ľ'      => 'l',
			'ĺ'      => 'l',
			'ļ'      => 'l',
			'ŀ'      => 'l',
			'ñ'      => 'n',
			'ń'      => 'n',
			'ň'      => 'n',
			'ņ'      => 'n',
			'ŉ'      => 'n',
			'ŋ'      => 'n',
			'ò'      => 'o',
			'ó'      => 'o',
			'ô'      => 'o',
			'õ'      => 'o',
			'ö'      => 'oe',
			'&ouml;' => 'oe',
			'ø'      => 'o',
			'ō'      => 'o',
			'ő'      => 'o',
			'ŏ'      => 'o',
			'œ'      => 'oe',
			'ŕ'      => 'r',
			'ř'      => 'r',
			'ŗ'      => 'r',
			'š'      => 's',
			'ù'      => 'u',
			'ú'      => 'u',
			'û'      => 'u',
			'ü'      => 'ue',
			'ū'      => 'u',
			'&uuml;' => 'ue',
			'ů'      => 'u',
			'ű'      => 'u',
			'ŭ'      => 'u',
			'ũ'      => 'u',
			'ų'      => 'u',
			'ŵ'      => 'w',
			'ý'      => 'y',
			'ÿ'      => 'y',
			'ŷ'      => 'y',
			'ž'      => 'z',
			'ż'      => 'z',
			'ź'      => 'z',
			'þ'      => 't',
			'ß'      => 'ss',
			'ſ'      => 'ss',
			'ый'     => 'iy',
			'А'      => 'A',
			'Б'      => 'B',
			'В'      => 'V',
			'Г'      => 'G',
			'Д'      => 'D',
			'Е'      => 'E',
			'Ё'      => 'YO',
			'Ж'      => 'ZH',
			'З'      => 'Z',
			'И'      => 'I',
			'Й'      => 'Y',
			'К'      => 'K',
			'Л'      => 'L',
			'М'      => 'M',
			'Н'      => 'N',
			'О'      => 'O',
			'П'      => 'P',
			'Р'      => 'R',
			'С'      => 'S',
			'Т'      => 'T',
			'У'      => 'U',
			'Ф'      => 'F',
			'Х'      => 'H',
			'Ц'      => 'C',
			'Ч'      => 'CH',
			'Ш'      => 'SH',
			'Щ'      => 'SCH',
			'Ъ'      => '',
			'Ы'      => 'Y',
			'Ь'      => '',
			'Э'      => 'E',
			'Ю'      => 'YU',
			'Я'      => 'YA',
			'а'      => 'a',
			'б'      => 'b',
			'в'      => 'v',
			'г'      => 'g',
			'д'      => 'd',
			'е'      => 'e',
			'ё'      => 'yo',
			'ж'      => 'zh',
			'з'      => 'z',
			'и'      => 'i',
			'й'      => 'y',
			'к'      => 'k',
			'л'      => 'l',
			'м'      => 'm',
			'н'      => 'n',
			'о'      => 'o',
			'п'      => 'p',
			'р'      => 'r',
			'с'      => 's',
			'т'      => 't',
			'у'      => 'u',
			'ф'      => 'f',
			'х'      => 'h',
			'ц'      => 'c',
			'ч'      => 'ch',
			'ш'      => 'sh',
			'щ'      => 'sch',
			'ъ'      => '',
			'ы'      => 'y',
			'ь'      => '',
			'э'      => 'e',
			'ю'      => 'yu',
			'я'      => 'ya'
		);

		$value = str_replace( array_keys( $replace ), $replace, $value );
		//$value = preg_replace('/[^A-Za-z0-9\\x{0590}-\\x{05FF}\p{Arabic}]/u',' ', strip_tags($value));
		$value = preg_replace( '/[^A-Za-z0-9\p{Hebrew}\p{Arabic}]/u', ' ', strip_tags( $value ) );
		$value = preg_replace( '/ {2,}/', ' ', $value );

		return trim( $value );

	}

}

if( !function_exists( 'pys_currency_options' ) ) {

	function pys_currency_options( $current = 'USD' ) {

		$currencies = apply_filters( 'pys_currencies_list', array(
			'AUD' => 'Australian Dollar',
			'BRL' => 'Brazilian Real',
			'CAD' => 'Canadian Dollar',
			'CZK' => 'Czech Koruna',
			'DKK' => 'Danish Krone',
			'EUR' => 'Euro',
			'HKD' => 'Hong Kong Dollar',
			'HUF' => 'Hungarian Forint',
			'IDR' => 'Indonesian Rupiah',
			'ILS' => 'Israeli New Sheqel',
			'JPY' => 'Japanese Yen',
			'KRW' => 'Korean Won',
			'MYR' => 'Malaysian Ringgit',
			'MXN' => 'Mexican Peso',
			'NOK' => 'Norwegian Krone',
			'NZD' => 'New Zealand Dollar',
			'PHP' => 'Philippine Peso',
			'PLN' => 'Polish Zloty',
			'RON' => 'Romanian Leu',
			'GBP' => 'Pound Sterling',
			'SGD' => 'Singapore Dollar',
			'SEK' => 'Swedish Krona',
			'CHF' => 'Swiss Franc',
			'TWD' => 'Taiwan New Dollar',
			'THB' => 'Thai Baht',
			'TRY' => 'Turkish Lira',
			'USD' => 'U.S. Dollar',
			'ZAR' => 'South African Rands'
		) );

		foreach( $currencies as $symbol => $name ) {
			echo '<option ' . selected( $symbol, $current, false ) . ' value="' . esc_attr( $symbol ) . '">' . esc_html( $name ) . '</option>';
		}

	}

}

/**
 * Build taxonomies list for current post.
 * Examples:
 * Cat1, Cat2;
 * Cat1, Cat2 > Cat3; Movie1; Tax1 > Tax2
 */
if( !function_exists( 'pys_get_content_taxonomies' ) ) {

	function pys_get_content_taxonomies( $taxonomy = 'category', $id = null ) {

		$post_id = isset( $id ) ? $id : get_the_ID();
		$tax     = get_the_terms( $post_id, $taxonomy );

		if ( is_wp_error( $tax ) || empty ( $tax ) ) {
			return false;
		}

		$tree = pys_build_taxonomy_tree( $tax );

		return pys_explode_taxonomies( $tree );

	}

}

/**
 * Build hierarchy tree array from array of WP_Terms.
 */
if( !function_exists( 'pys_build_taxonomy_tree' ) ) {

	function pys_build_taxonomy_tree( array $wp_terms ) {

		// convert WP_Term objects to array
		$terms = array();
		foreach ( $wp_terms as $id => $term ) {
			$terms[ $id ] = array(
				'id'     => $term->term_id,
				'parent' => $term->parent,
				'name'   => $term->name
			);
		}

		// build tree
		$tree       = array();
		$references = array();
		foreach ( $terms as $id => &$node ) {

			$references[ $node['id'] ] = &$node;

			if ( $node['parent'] == 0 ) {

				// it is root node. add direct to tree root
				$tree[ $node['id'] ] = &$node;

			} else {

				// it is child node. add to parent 'children'
				$references[ $node['parent'] ]['children'][ $node['id'] ] = &$node;

			}

		}

		return $tree;
	}

}

/**
 * Format taxonomies tree to string.
 */
if( !function_exists( 'pys_explode_taxonomies' ) ) {

	function pys_explode_taxonomies( $tree ) {

		$str = '';
		foreach ( $tree as $node ) {

			if ( isset( $node['children'] ) ) {

				$str .= $node['name'] . ', ';
				$str .= pys_explode_taxonomies( $node['children'] );

			} else {

				$str .= $node['name'] . '; ';

			}
		}

		return $str;

	}

}

if( !function_exists( 'pys_get_noscript_code' ) ) {

	function pys_get_noscript_code( $type, $params ) {

		// skip, because event is works only if js supported
		if( $type == 'TimeOnPage' ) {
			return null;
		}

		$args = array();

		$args['id']       = pys_get_option( 'general', 'pixel_id' );
		$args['ev']       = $type;
		$args['noscript'] = 1;

		foreach ( $params as $param => $value ) {
			@$args[ 'cd[' . $param . ']' ] = urlencode( $value );
		}

		$src = add_query_arg( $args, 'https://www.facebook.com/tr' );

		return "<noscript><img height='1' width='1' style='display:none' src='" . $src . "'></noscript>\n";

	}

}

if( !function_exists( 'pys_build_event_pixel_code' ) ) {

	function pys_build_event_pixel_code( $params, $event ) {

		$params = apply_filters( 'pys_event_params', $params, $event );

		$js   = null;
		$nojs = array();

		// explode params to formatted string
		foreach ( $params as $name => $value ) {

			if( empty( $value ) ) {
				continue;
			}

			// skip cleanup
			//@todo: remove after tests
//			if ( ! in_array( $name, array( 'content_ids', 'value', 'time' ) ) ) {
//				$value = pys_clean_param_value( $value );
//			}

			// sanitize prams
			$key   = esc_js( $name );
			$value = esc_js( $value );

			$js .= "{$key}: '{$value}', ";
			$nojs[ $name ] = esc_attr( $value );

		}

		$track = pys_is_standard_event( $event ) ? 'track' : 'trackCustom';

		return array(
			'js'   => "fbq('{$track}', '{$event}', {{$js}} );\n",
			'nojs' => pys_get_noscript_code( $event, $nojs )
		);

	}

}

/**
 * Return product id or sku.
 */
if( !function_exists( 'pys_get_product_content_id' ) ) {

	function pys_get_product_content_id( $product_id ) {
		global $wpdb;

		if ( pys_get_option( 'woo', 'content_id' ) == 'sku' ) {

			$sku = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_sku' AND post_id='%s' LIMIT 1", $product_id ) );

			return '"' . $sku . '"';

		} else {

			return $product_id;

		}

	}

}

/**
 * Return main or variation product id.
 */
if( !function_exists( 'pys_get_product_id' ) ) {

	function pys_get_product_id( $product ) {

		$id = $product['product_id'];

		if ( pys_get_option( 'woo', 'variation_id' ) == 'variation' && isset( $product['variation_id'] ) && $product['variation_id'] != 0 ) {
			$id = $product['variation_id'];
		}

		return $id;
	}

}

/**
 * Add attribute with value to a HTML tag.
 * @param $attr_name
 * @param $attr_value
 * @param $tag
 */
if( !function_exists( 'pys_insert_attribute' ) ) {

	/**
	 * @param $attr_name
	 * @param $attr_value
	 * @param $tag
	 * @param bool|false $overwrite
	 * @param string $tag_name by default function processing only A tags but it can be changed by setting $tag_name parameter. It is used in PayPal button event for example.
	 *
	 * @return string tag HTML with inserted attribute and its value
	 */
	function pys_insert_attribute( $attr_name, $attr_value, $tag, $overwrite = false, $tag_name = 'a' ) {

		// do not modify js attributes
		if( $attr_name == 'on' ) {
			return $tag;
		}

		$attr_value = trim( $attr_value );

		$doc = new DOMDocument();

		/**
		 * Old libxml does not support options parameter.
		 * @since 3.2.0
		 */
		if( defined('LIBXML_DOTTED_VERSION') && version_compare( LIBXML_DOTTED_VERSION, '2.6.0', '>=' ) &&
		    version_compare( phpversion(), '5.4.0', '>=' ) ) {
			@$doc->loadHTML( '<?xml encoding="UTF-8">' . $tag, LIBXML_NOEMPTYTAG );
		} else {
			@$doc->loadHTML( '<?xml encoding="UTF-8">' . $tag );
		}

		$node = $doc->getElementsByTagName( $tag_name )->item(0);

		if( is_null( $node ) ) {
			return $tag;
		}

		$attribute = $node->getAttribute( $attr_name );

		// add attribute or override old one
		if( empty( $attribute ) || $overwrite ) {

			$node->setAttribute( $attr_name, $attr_value );
			return $doc->saveHTML( $node );

		}

		// append value to exist attribute
		if( $overwrite ) {

			$value = $attribute . ' ' . $attr_value;
			$node->setAttribute( $attr_name, $value );
			return $doc->saveHTML( $node );

		}

		return $tag;

	}

}

if( !function_exists( 'pys_convert_quotes' ) ) {

	function pys_convert_quotes( $str ) {

		$chr_map = array(
			// Windows codepage 1252
			"\xC2\x82"     => "'", // U+0082⇒U+201A single low-9 quotation mark
			"\xC2\x84"     => '"', // U+0084⇒U+201E double low-9 quotation mark
			"\xC2\x8B"     => "'", // U+008B⇒U+2039 single left-pointing angle quotation mark
			"\xC2\x91"     => "'", // U+0091⇒U+2018 left single quotation mark
			"\xC2\x92"     => "'", // U+0092⇒U+2019 right single quotation mark
			"\xC2\x93"     => '"', // U+0093⇒U+201C left double quotation mark
			"\xC2\x94"     => '"', // U+0094⇒U+201D right double quotation mark
			"\xC2\x9B"     => "'", // U+009B⇒U+203A single right-pointing angle quotation mark

			// Regular Unicode     // U+0022 quotation mark (")
			// U+0027 apostrophe     (')
			"\xC2\xAB"     => '"', // U+00AB left-pointing double angle quotation mark
			"\xC2\xBB"     => '"', // U+00BB right-pointing double angle quotation mark
			"\xE2\x80\x98" => "'", // U+2018 left single quotation mark
			"\xE2\x80\x99" => "'", // U+2019 right single quotation mark
			"\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
			"\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
			"\xE2\x80\x9C" => '"', // U+201C left double quotation mark
			"\xE2\x80\x9D" => '"', // U+201D right double quotation mark
			"\xE2\x80\x9E" => '"', // U+201E double low-9 quotation mark
			"\xE2\x80\x9F" => '"', // U+201F double high-reversed-9 quotation mark
			"\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
			"\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
		);

		$chr     = array_keys( $chr_map ); // but: for efficiency you should
		$rpl     = array_values( $chr_map ); // pre-calculate these two arrays
		$str     = str_replace( $chr, $rpl, html_entity_decode( $str, ENT_QUOTES, "UTF-8" ) );

		return $str;
	}

}

if( !function_exists( 'pys_is_disabled_for_role' ) ) {

	function pys_is_disabled_for_role() {

		$options = get_option( 'pixel_your_site' );
		$disabled_roles = $options['general'];

		$user           = wp_get_current_user();
		foreach ( (array) $user->roles as $role ) {

			if ( array_key_exists( "disable_for_$role", $disabled_roles ) ) {
				return true;
			}

		}

		if( empty( $user->roles ) && isset( $disabled_roles['disable_for_guest'] ) ) {
			return true;
		}

		return false;

	}

}

/**
 * Output pixel code.
 */
if( !function_exists( 'pys_pixel_code' ) ) {

	function pys_pixel_code() {

		$pixel_id = pys_get_option( 'general', 'pixel_id' );

		// build pixel code...
		$pixelcode = "\n";
		$nojscode  = "\n";

		// pixel id
		$pixelcode .= "fbq('init', '{$pixel_id}', {" . pys_pixel_init_params() . "});\n";

		// default event
		$default_event = pys_build_event_pixel_code( array(), 'PageView' );
		$pixelcode .= $default_event['js'];
		$nojscode  .= $default_event['nojs'];

		// general event
		if ( pys_get_option( 'general', 'general_event_enabled' ) ) {

			$code = pys_get_general_event_code();

			$pixelcode .= $code['js'];
			$nojscode  .= $code['nojs'];

		}

		// search event
		if ( pys_get_option( 'general', 'search_event_enabled' ) && is_search() && isset( $_REQUEST['s'] ) ) {

			$search_event = pys_build_event_pixel_code( array(
				'search_string' => $_REQUEST['s']
			), 'Search' );

			$pixelcode .= $search_event['js'];
			$nojscode  .= $search_event['nojs'];

		}

		// add standard events
		$std_events = get_option( 'pixel_your_site_std_events', array() );
		if ( pys_get_option( 'std', 'enabled' ) && count( $std_events ) > 0 ) {

			foreach ( $std_events as $std_event_params ) {

				// skip wrong events
				if( ! isset( $std_event_params['pageurl'] ) || ! isset( $std_event_params['eventtype'] ) ) {
					continue;
				}

				// add event on url's match
				if ( pys_match_url( $std_event_params['pageurl'] ) ) {

					if ( $std_event_params['eventtype'] == 'CustomCode' ) {

						$custom_code = $std_event_params['code'];
						$custom_code = stripcslashes( $custom_code );
						$custom_code = trim( $custom_code );

						$pixelcode .= "{$custom_code}\n";

					} else {

						$std_event_type = $std_event_params['eventtype'];
						$std_event_params = pys_clean_system_event_params( $std_event_params );

						$std_event_code = pys_build_event_pixel_code( $std_event_params, $std_event_type );

						$pixelcode .= $std_event_code['js'];
						$nojscode  .= $std_event_code['nojs'];

					}

				}

			}

		}

		// add woocommerce events
		if ( pys_get_option( 'woo', 'enabled' ) && pys_is_woocommerce_active() ) {

			$woo_event_code = pys_get_woo_code();

			$pixelcode .= $woo_event_code['js'];
			$nojscode  .= $woo_event_code['nojs'];

		}

		// WooCommerce non-ajax AddToCart Event handler
		if ( pys_get_option( 'woo', 'enabled' ) && pys_is_woocommerce_active() && isset( $_REQUEST['add-to-cart'] ) ) {

			$product_id = isset( $_REQUEST['add-to-cart'] ) ? $_REQUEST['add-to-cart'] : null;

			if ( pys_get_option( 'woo', 'variation_id' ) == 'variation' && isset( $_REQUEST['variation_id'] ) ) {
				$product_id = $_REQUEST['variation_id'];
			}

			$params     = pys_get_woo_ajax_addtocart_params( $product_id );
			$event_code = pys_build_event_pixel_code( $params, 'AddToCart' );

			$pixelcode .= $event_code['js'];
			$nojscode  .= $event_code['nojs'];

		}

		// version data
		if( defined('PYS_PRO_VERSION') ) {
			$version = "PRO v".PYS_PRO_VERSION;
		} elseif( defined('PYS_FREE_VERSION') ) {
			$version = "FREE v".PYS_FREE_VERSION_REAL;
		} else {
			$version = null;
		}

		?>

		<!-- Facebook Pixel Code ( <?php echo esc_attr( $version ); ?> ) -->
		<script>
			var PYS_DOMReady = function (a, b, c) {
				b = document, c = 'addEventListener';
				b[c] ? b[c]('DOMContentLoaded', a) : window.attachEvent('onload', a)
			};
			!function (f, b, e, v, n, t, s) {
				if (f.fbq)return;
				n = f.fbq = function () {
					n.callMethod ?
						n.callMethod.apply(n, arguments) : n.queue.push(arguments)
				};
				if (!f._fbq)f._fbq = n;
				n.push = n;
				n.loaded = !0;
				n.version = '2.0';
				n.queue = [];
				t = b.createElement(e);
				t.async = !0;
				t.src = v;
				s = b.getElementsByTagName(e)[0];
				s.parentNode.insertBefore(t, s)
			}(window,
				document, 'script', '//connect.facebook.net/en_US/fbevents.js');

			/* pixel plugin code */
			PYS_DOMReady(function () {
				<?php echo $pixelcode; ?>
			});
			/* pixel plugin code */

		</script>
		<!-- End Facebook Pixel Code -->

		<?php

		// noscript fallback
		echo $nojscode;
	}

}

if( !function_exists( 'pys_clean_system_event_params' ) ) {

	function pys_clean_system_event_params( $params ) {

		// remove unused params
		unset( $params['pageurl'] );
		unset( $params['eventtype'] );
		unset( $params['code'] );
		unset( $params['trigger_type'] );    // pro
		unset( $params['url'] );             // pro
		unset( $params['css'] );             // pro
		unset( $params['custom_name'] );     // custom events

		return $params;

	}

}

/**
 * Build General Event code.
 */
if( !function_exists( 'pys_get_general_event_code' ) ) {

	function pys_get_general_event_code() {
		global $post;

		$params     = array();
		$pys        = get_option( 'pixel_your_site' );
		$event_name = $pys['general']['general_event_name'];
		$post_type  = get_post_type();

		// Posts
		if ( pys_get_option( 'general', 'general_event_on_posts_enabled' ) && is_singular( 'post' ) ) {

			$params['post_type']    = 'post';
			$params['content_name'] = $post->post_title;
			$params['post_id']      = $post->ID;

			$terms = pys_get_content_taxonomies();
			if ( $terms ) {
				$params['content_category'] = $terms;
			}

			// track post tags
			if ( pys_get_option( 'general', 'general_event_add_tags', 0 ) && $tags = pys_get_post_tags( $post->ID ) ) {
				$params['tags'] = implode( ', ', $tags );
			}

			return pys_build_event_pixel_code( $params, $event_name );

		}

		// Pages or Front Page
		if ( pys_get_option( 'general', 'general_event_on_pages_enabled' ) && ( is_singular( 'page' ) || is_home() ) ) {

			// exclude WooCommerce Cart page
			if ( pys_is_woocommerce_active() && is_cart() ) {
				return false;
			}

			$params['post_type']    = 'page';
			$params['content_name'] = is_home() == true ? get_bloginfo( 'name' ) : $post->post_title;

			is_home() != true ? $params['post_id'] = $post->ID : null;

			return pys_build_event_pixel_code( $params, $event_name );

		}

		// WooCommerce Shop page
		if ( pys_get_option( 'general', 'general_event_on_pages_enabled' ) && pys_is_woocommerce_active() && is_shop() ) {

			$page_id = wc_get_page_id( 'shop' );

			$params['post_type']    = 'page';
			$params['post_id']      = $page_id;
			$params['content_name'] = get_the_title( $page_id );;

			return pys_build_event_pixel_code( $params, $event_name );

		}

		// Taxonomies (built-in and custom)
		if ( pys_get_option( 'general', 'general_event_on_tax_enabled' ) && ( is_category() || is_tax() || is_tag() ) ) {

			$term = null;
			$type = null;

			if ( is_category() ) {

				$cat  = get_query_var( 'cat' );
				$term = get_category( $cat );

				$params['post_type']    = 'category';
				$params['content_name'] = $term->name;
				$params['post_id']      = $cat;

			} elseif ( is_tag() ) {

				$slug = get_query_var( 'tag' );
				$term = get_term_by( 'slug', $slug, 'post_tag' );

				$params['post_type']    = 'tag';
				$params['content_name'] = $term->name;
				$params['post_id']      = $term->term_id;

			} else {

				$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

				$params['post_type']    = get_query_var( 'taxonomy' );
				$params['content_name'] = $term->name;
				$params['post_id']      = $term->term_id;

			}

			return pys_build_event_pixel_code( $params, $event_name );

		}

		// Custom Post Type
		if ( pys_get_option( 'general', 'general_event_on_' . $post_type . '_enabled' )
		     && $post_type != 'post' && $post_type != 'page'
		) {

			// skip products and downloads is plugins are activated
			if ( ( pys_is_woocommerce_active() && $post_type == 'product' ) || ( pys_is_edd_active() && $post_type == 'download' ) ) {
				return false;
			}

			$params['post_type']    = $post_type;
			$params['content_name'] = $post->post_title;
			$params['post_id']      = $post->ID;

			$taxonomies = get_post_taxonomies( get_post() );
			$terms      = pys_get_content_taxonomies( $taxonomies[0] );
			if ( $terms ) {
				$params['content_category'] = $terms;
			}

			// track custom post type tags
			if ( pys_get_option( 'general', 'general_event_add_tags', 0 ) && $tags = pys_get_post_tags( $post->ID ) ) {
				$params['tags'] = implode( ', ', $tags );
			}

			return pys_build_event_pixel_code( $params, $event_name );

		}

		// Easy Digital Downloads
		if ( pys_get_option( 'general', 'general_event_on_edd_enabled' ) && pys_is_edd_active() && $post_type == 'download' ) {

			$download = new EDD_Download( $post->ID );

			$params['post_type']    = 'download';
			$params['content_name'] = $download->post_title;
			$params['post_id']      = $post->ID;
			$params['value']        = $download->get_price();
			$params['currency']     = edd_get_currency();

			$terms = pys_get_content_taxonomies( 'download_category' );
			if ( $terms ) {
				$params['content_category'] = $terms;
			}

			return pys_build_event_pixel_code( $params, $event_name );
		}

		return false;
	}

}

if( !function_exists( 'pys_get_woo_checkout_params' ) ) {

	function pys_get_woo_checkout_params( $additional_params_enabled ) {
		global $woocommerce;

		// set defaults params
		$params                 = array();
		$params['content_type'] = 'product';

		$ids        = array();     // cart items ids or sku
		$names      = '';        // cart items names
		$categories = '';   // cart items categories

		foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $item ) {

			$product_id = pys_get_product_id( $item );
			$value      = pys_get_product_content_id( $product_id );
			$ids[]      = $value;

			// content_name, category_name for each cart item
			if ( $additional_params_enabled ) {

				$temp = array();
				pys_get_additional_woo_params( $product_id, $temp );

				$names .= isset( $temp['content_name'] ) ? $temp['content_name'] . ' ' : null;
				$categories .= isset( $temp['category_name'] ) ? $temp['category_name'] . ' ' : null;

			}

		}

		if ( $additional_params_enabled ) {
			$params['num_items'] = $woocommerce->cart->get_cart_contents_count();
		}

		$params['content_ids'] = '[' . implode( ',', $ids ) . ']';

		if ( ! empty( $names ) ) {
			$params['content_name'] = $names;
		}

		if ( ! empty( $categories ) ) {
			$params['category_name'] = $categories;
		}

		return $params;

	}

}

if( !function_exists( 'pys_get_default_options' ) ) {

	function pys_get_default_options() {

		$options = array();

		$options['general']['pixel_id'] = '';
		$options['general']['enabled']  = 0;
		$options['general']['enable_advance_matching']  = 1;

		$options['general']['general_event_enabled']          = 1;
		$options['general']['general_event_name']             = 'GeneralEvent';
		$options['general']['general_event_on_posts_enabled'] = 1;
		$options['general']['general_event_on_pages_enabled'] = 1;
		$options['general']['general_event_on_tax_enabled']   = 1;
		$options['general']['general_event_on_edd_enabled']   = 0;
		$options['general']['general_event_add_tags']         = 0;

		$options['general']['timeonpage_enabled'] = 1;

		$options['general']['search_event_enabled'] = 1;

		$options['std']['enabled'] = 0;

		$options['dyn']['enabled']            = 0;
		$options['dyn']['enabled_on_content'] = 0;
		$options['dyn']['enabled_on_widget']  = 0;

		$options['woo']['enabled'] = pys_is_woocommerce_active() ? 1 : 0;

		$options['woo']['content_id']   = 'id';
		$options['woo']['variation_id'] = 'main';

		$options['woo']['enable_additional_params'] = 1;
		$options['woo']['enable_tags']              = 1;
		$options['woo']['tax']                      = 'incl';

		$options['woo']['on_view_content']            = 1;
		$options['woo']['enable_view_content_value']  = 1;
		$options['woo']['view_content_value_option']  = 'price';
		$options['woo']['view_content_percent_value'] = '';
		$options['woo']['view_content_global_value']  = '';

		$options['woo']['on_add_to_cart_btn']        = 1;
		$options['woo']['on_cart_page']              = 1;
		$options['woo']['enable_add_to_cart_value']  = 1;
		$options['woo']['add_to_cart_value_option']  = 'price';
		$options['woo']['add_to_cart_percent_value'] = '';
		$options['woo']['add_to_cart_global_value']  = '';

		$options['woo']['on_checkout_page']       = 1;
		$options['woo']['enable_checkout_value']  = 1;
		$options['woo']['checkout_value_option']  = 'price';
		$options['woo']['checkout_percent_value'] = '';
		$options['woo']['checkout_global_value']  = '';

		$options['woo']['on_thank_you_page']      = 1;
		$options['woo']['enable_purchase_value']  = 1;
		$options['woo']['purchase_transport']     = 'included';
		$options['woo']['purchase_value_option']  = 'total';
		$options['woo']['purchase_percent_value'] = '';
		$options['woo']['purchase_global_value']  = '';

		$options['woo']['purchase_add_address']         = 1;
		$options['woo']['purchase_add_payment_method']  = 1;
		$options['woo']['purchase_add_shipping_method'] = 1;
		$options['woo']['purchase_add_coupons']         = 1;

		$options['woo']['enable_aff_event']     = 0;
		$options['woo']['aff_event']            = 'predefined';
		$options['woo']['aff_predefined_value'] = 'Lead';
		$options['woo']['aff_custom_value']     = '';
		$options['woo']['aff_value_option']     = 'none';
		$options['woo']['aff_global_value']     = '';

		$options['woo']['enable_paypal_event'] = 0;
		$options['woo']['pp_event']            = 'predefined';
		$options['woo']['pp_predefined_value'] = 'InitiatePayment';
		$options['woo']['pp_custom_value']     = '';
		$options['woo']['pp_value_option']     = 'none';
		$options['woo']['pp_global_value']     = '';

		return $options;

	}

}

if( !function_exists( 'pys_get_custom_params' ) ) {

	/**
	 * Get custom event (std or dynamic) params => values
	 * @param array $event
	 */
	function pys_get_custom_params( $event ) {

		if( !is_array( $event ) || empty( $event ) ) {
			return array();
		}

		$std_params = array(
			'trigger_type',
			'url',
			'css',
			'pageurl',
			'eventtype',
			'value',
			'currency',
			'content_name',
			'content_ids',
			'content_type',
			'content_category',
			'num_items',
			'order_id',
			'search_string',
			'status',
			'code',
			'custom_name'
		);

		$custom_params = array();
		foreach( $event as $param => $value ) {

			// skip standard params
			if( in_array( $param, $std_params ) ) {
				continue;
			}

			$custom_params[ $param ] = $value;

		}


		return $custom_params;

	}

}

if( !function_exists( 'pys_is_standard_event' ) ) {

	function pys_is_standard_event( $eventtype ) {

		$std_events = array(
			'PageView',
			'ViewContent',
			'Search',
			'AddToCart',
			'AddToWishlist',
			'InitiateCheckout',
			'AddPaymentInfo',
			'Purchase',
			'Lead'
		);

		return in_array( $eventtype, $std_events );

	}

}

/**
 * Add extra params to FB init call.
 */
if( !function_exists( 'pys_pixel_init_params' ) ) {

	function pys_pixel_init_params() {

		$params = array();
		$params = apply_filters( 'pys_pixel_init_params', $params );

		$code = null;
		foreach ( $params as $key => $value ) {

			// remove empty params
			if ( empty( $value ) ) {
				unset( $params[ $key ] );
			}

			// sanitize prams
			$key   = esc_js( $key );
			$value = esc_js( $value );

			$code .= "{$key}: '{$value}', ";

		}

		return $code;

	}

}