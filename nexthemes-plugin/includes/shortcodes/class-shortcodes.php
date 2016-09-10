<?php 
// ! File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Nexthemes_Shortcodes' ) ) :

class Nexthemes_Shortcodes extends Nexthemes_Woo_Shortcodes {

	public static function checkPlugin( $path = '' ){
		if( strlen( $path ) == 0 ) return false;
		$_actived = apply_filters( 'active_plugins', get_option( 'active_plugins' )  );
		if ( in_array( trim( $path ), $_actived ) ) return true;
		else return false;
	}

	public static function popString($content, $limit = 15){
		if( strlen( trim($content) ) == 0 ) return '';
		if( !is_numeric($limit) ) return $content;

		$str_args = explode(' ', $content, ($limit + 1));
		if(count($str_args) > $limit) {
			array_pop($str_args);
			$str_args[$limit-1] .= '...';
		}

		return implode(' ', $str_args);
	}

	public function init() {
		$shortcodes = array(
			'theshopier_banner' 			=> __CLASS__ . '::banners',
			'theshopier_brands' 			=> __CLASS__ . '::brands',
			'theshopier_feedburner' 		=> __CLASS__ . '::feedburner',
			'theshopier_infobox' 			=> __CLASS__ . '::infobox',
            'theshopier_recent_comments' 	=> __CLASS__ . '::recent_comments',
			'theshopier_recent_posts'		=> __CLASS__ . '::recent_posts',
			'theshopier_pricing'			=> __CLASS__ . '::pricing',
			'theshopier_action'				=> __CLASS__ . '::action',
			'theshopier_maps'				=> __CLASS__ . '::google_maps',
			'theshopier_button'				=> __CLASS__ . '::buttons',
			'site_url'						=> __CLASS__ . '::site_url',
			'theshopier_head_top_login'		=> __CLASS__ . '::head_top_login',
			'theshopier_head_top_cart'		=> __CLASS__ . '::head_top_cart',
			'theshopier_head_top_search'	=> __CLASS__ . '::head_top_search',
			'theshopier_social'				=> __CLASS__ . '::social_network',
			'theshopier_instagram'			=> __CLASS__ . '::instagram',
			'theshopier_qrcode'				=> __CLASS__ . '::qrcode',
			'theshopier_store_location'		=> __CLASS__ . '::store_location',
			'theshopier_tag_cloud'			=> __CLASS__ . '::tag_cloud',
		);
		
		if( class_exists( 'Nexthemes_StaticBlock' ) ) {
			$shortcodes['theshopier_staticblock'] = __CLASS__ . '::staticblock';
		}
		
		if( class_exists( 'Nexthemes_TeamMembers' )  && class_exists( 'Nexthemes_TeamMembers_Front' ) ) {
			$shortcodes['theshopier_teams'] = __CLASS__ . '::teammember';
		}
		
		if( self::checkPlugin( 'testimonials-by-woothemes/woothemes-testimonials.php' ) ) {
			$shortcodes['theshopier_testimonials'] = __CLASS__ . '::testimonials';
		}
		
		if( self::checkPlugin('features-by-woothemes/woothemes-features.php') ) {
			$shortcodes['theshopier_features'] = __CLASS__ . '::features';
		}
		
		if( self::checkPlugin( 'woocommerce/woocommerce.php' ) ) {
			$woo_args = parent::shortcodeArgs();
			$shortcodes = array_merge( $shortcodes, $woo_args );
		}
		
		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, $function );
		}
		
	}

	public static function head_top_login($atts){
		$atts = shortcode_atts(array(
			'popup'	=> '',
		), $atts);

		$_class = array('valign_middle nth-mini-popup');
		if( strlen($atts['popup']) > 0 ) $_class[] = 'popup_'.esc_attr($atts['popup']);

		if( ! NexThemes_Plg::checkPlugin('woocommerce/woocommerce.php') ) return ;

		$shop_myaccount_id = get_option( 'woocommerce_myaccount_page_id' );
		if( isset( $shop_myaccount_id ) && absint( $shop_myaccount_id ) > 0 ) {
			$myaccount_url = get_permalink( $shop_myaccount_id );
			$ac_link_title = __( "My Account", 'nexthemes-plugin' );
		} else return 'Woocommerce account page was not found!';

		ob_start();

		if( is_user_logged_in() ) {
			echo '<a href="'.esc_url($myaccount_url).'">'.__('My Account', 'nexthemes-plugin').'</a>';
		} else {
			$rand_id = wp_rand();
			$args = array(
				'echo'		=> true,
				'form_id'   => 'nth_header_loginform' . $rand_id,
				'label_username' => esc_html__( 'Username', 'theshopier' ),
				'label_password' => esc_html__( 'Password', 'theshopier' ),
				'label_remember' => esc_html__( 'Remember Me', 'theshopier' ),
				'label_log_in'   => esc_html__( 'Log In', 'theshopier' ),
				'id_username'    => 'nth_user_login' . $rand_id,
				'id_password'    => 'nth_user_pass' . $rand_id,
				'id_remember'    => 'nth_rememberme' . $rand_id,
				'id_submit'      => 'nth_submit' . $rand_id,
				'remember'       => true,
				'value_username' => '',
				'value_remember' => false
			);

			echo '<a class="dropdown-toggle" href="#">'.__('Login', 'nexthemes-plugin').' <span class="caret"></span></a>';
			?>
				<div class="nth-mini-popup-cotent nth-mini-login-content">
					<div class="nth-ajax-login-wrapper">
						<?php wp_login_form( $args );?>
					</div>
                    <div class="nth-mini-popup-footer">
                        <p class="create_account"><?php printf( __('New to our store? <a href="%1$s" title="Create an Account">Create an Account</a>', 'nexthemes-plugin'), esc_url($myaccount_url) )?></p>
                    </div>
                </div>
			<?php
		}

		return '<div class="'.esc_attr(implode(' ', $_class)).'">'.ob_get_clean().'</div>';
	}

	public static function head_top_cart($atts){
		$atts = shortcode_atts(array(
			'popup'	=> '',
		), $atts);

		$_class = array('valign_middle nth-mini-popup');
		if( strlen($atts['popup']) > 0 ) $_class[] = 'popup_'.esc_attr($atts['popup']);

		if( ! NexThemes_Plg::checkPlugin('woocommerce/woocommerce.php') ) return ;

		$shop_myaccount_id = get_option( 'woocommerce_myaccount_page_id' );
		if( isset( $shop_myaccount_id ) && absint( $shop_myaccount_id ) > 0 ) {
			$myaccount_url = get_permalink( $shop_myaccount_id );
			$ac_link_title = __( "My Account", 'nexthemes-plugin' );
		} else return 'Woocommerce account page was not found!';

		ob_start();

		echo '<div class=""><a class="dropdown-toggle" href="#">'.__('My Cart', 'nexthemes-plugin').' <span class="caret"></span></a></div>';
		?>
		<div class="nth-mini-popup-cotent nth-shopping-cart-content">
			<div class="widget_shopping_cart_content"></div>
		</div>
		<?php

		return '<div class="'.esc_attr(implode(' ', $_class)).'">'.ob_get_clean().'</div>';
	}

	public static function head_top_search($atts){
		$atts = shortcode_atts(array(
			'popup'	=> '',
		), $atts);

		$_class = array('nth-dropdown valign_middle');
		if( strlen($atts['popup']) > 0 ) $_class[] = 'popup_'.esc_attr($atts['popup']);

		ob_start();

		echo '<a class="dropdown-toggle" href="#">'.__('Search', 'nexthemes-plugin').' <span class="caret"></span></a>';
		?>
		<div class="nth-mini-popup-cotent nth-shopping-cart-content">
			<?php
			$rand_id = wp_rand();
			$check_woo = Theshopier::checkPlugin('woocommerce/woocommerce.php');
			if($check_woo) {
				$_placeholder = esc_attr__("Search product...", 'nexthemes-plugin' );
			} else {
				$_placeholder = esc_attr__("Search anything...", 'nexthemes-plugin' );
			}
			?>
			<form id="form_<?php echo esc_attr($rand_id)?>" method="get" class="searchform nth-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<div class="nth-search-wrapper">
					<label class="screen-reader-text" for="s_<?php echo esc_attr($rand_id)?>"><?php esc_html_e( 'Search for:', 'theshopier' ); ?></label>
					<input type="text" placeholder="<?php echo esc_attr($_placeholder);?>" value="<?php echo get_search_query() ?>" name="s" id="s_<?php echo esc_attr($rand_id)?>" />
					<?php if($check_woo): ?>
						<input type="hidden" name="post_type" value="product" />
					<?php endif;?>
					<?php if ( defined( 'ICL_LANGUAGE_CODE' ) ): ?>
						<input type="hidden" name="lang" value="<?php echo( ICL_LANGUAGE_CODE ); ?>" />
					<?php endif ?>
					<button type="submit" class="icon-nth-search searchsubmit"><?php echo esc_attr_x( 'Search', 'submit button', 'theshopier' ); ?></button>
				</div>
			</form>
		</div>
		<?php

		return '<div class="'.esc_attr(implode(' ', $_class)).'">'.ob_get_clean().'</div>';
	}

	public static function site_url($atts){
		$atts = shortcode_atts(array(
			'method'	=> 'return',
			'path'		=> '',
			'scheme '	=> null
		), $atts);

		if( strlen($atts['scheme']) > 0 ) $site = site_url( $atts['path'], $atts['scheme']);
		else $site = site_url( $atts['path'], $atts['scheme']);

		if( strcmp($atts['method'], 'return') == 0 ) return $site;
		else echo $site;
	}

	public static function banners( $atts, $content ){
		$atts = shortcode_atts(array(
			"link"			=> '',
			"bg_image"		=> '',
			'bg_img'		=> '',
			"hidden_on"		=> '',
			"class"			=> '',
			"css"			=> ''
		),$atts);
		
		$atts['content'] = strlen( trim( $content ) ) > 0 ? $content : '';
		 
		$classes = array('nth-shortcode', 'nth-banner');
		if( strlen($atts['class']) > 0 ) $classes[] = $atts['class'];
		
		if( strlen(trim($atts['hidden_on'])) > 0 ) $classes[] = $atts['hidden_on'];
		$atts['class'] = implode(' ', $classes);
		
		ob_start();
		
		parent::get_template("shortcode-banners.tpl.php", $atts);
		
		return ob_get_clean();
	}
	
	public static function brands( $atts ){
		$atts = shortcode_atts( array(
			'title'		=> '',
			'h_style'	=> '',
			'style'		=> '',
			'box_style'	=> '',
			'imgs' 		=> '',
			'column'	=> 6,
			'css'		=> ''
		), $atts );
		
		if( strlen( trim( $atts['imgs'] ) ) == 0 ) return;
		
		ob_start();
		
		parent::get_template("shortcode-brands.tpl.php", $atts);

		$classes = array('nth-shortcode nth-'. __FUNCTION__ );
		if( strlen(trim($atts['box_style'])) > 0 ) $classes[] = esc_attr($atts['box_style']);
		
		return '<div class="'.esc_attr(implode(' ', $classes)).'">' . ob_get_clean() . '</div>';
	}
	
	public static function feedburner( $atts ){
		$atts = shortcode_atts( array(
			'fb_id' => 'kinhdon/Ahzl',
		), $atts );
		
		ob_start();
		
		parent::get_template("shortcode-feedburner.tpl.php", $atts);
		
		return '<div class="nth-shortcode '. __FUNCTION__ .'">' . ob_get_clean() . '</div>';
	}
	
	public static function infobox( $atts ){
		
		$atts = shortcode_atts(array(
			"title" 					=> 'title',
			"desc"						=> '',
			"use_icon"					=> 'yes',
			"type"						=> 'fontawesome',
			"icon_fontawesome"			=> 'fa fa-adjust',
			"icon_openiconic"			=> 'vc-oi vc-oi-dial',
			"icon_typicons"				=> 'typcn typcn-adjust-brightness',
			"icon_entypo"				=> 'entypo-icon entypo-icon-note',
			"icon_linecons"				=> 'vc_li vc_li-heart',
			"background_style"			=> 'rounded',
			"color"						=> 'black',
			"custom_color"				=> 'inherit',
			"icon_background"			=> 'white',
			"custom_icon_background" 	=> '#ededed',
			"icon_img"					=>''
		),$atts);
		
		ob_start();
		
		parent::get_template("shortcode-infobox.tpl.php", $atts);
		
		return '<div class="nth-shortcode nth-'. __FUNCTION__ .'">' . ob_get_clean() . '</div>';
	}
    
    public static function recent_comments( $atts ){
        $atts = shortcode_atts(array(
			"title" 		=> '',
			"limit"			=> 5,
			"excerpt_words"	=> 15,
			"as_widget"		=> 0,
		),$atts);
		
		$comments = get_comments( apply_filters( 'widget_comments_args', array(
			'number'      => $atts['limit'],
			'status'      => 'approve',
			'post_status' => 'publish'
		) ) );
		
		ob_start();
		
		if( $comments ) {
			
			$atts['comments'] = $comments;
			
			parent::get_template("shortcode-comments.tpl.php", $atts);
			
		}
		
		return '<div class="nth-shortcode recent-comments">' . ob_get_clean() . '</div>';
    }
	
	public static function recent_posts( $atts ) {
		$atts = shortcode_atts(array(
			"title" 		=> '',
			"cats"			=> '',
			'head_style'	=> '',
			'box_style'		=> '',
			"limit"			=> 5,
			"is_slider"		=> 0,
			"excerpt_words"	=> 15,
			"as_widget"		=> 0,
			"style"			=> '',
			"w_style"		=> 1,
			"hidden_date"	=> 0,
			's_cats'		=> 1,
			's_button'		=> 1,
			's_author'		=> 0,
			's_excerpt'		=> 1,
			'orderby'		=> '',
			'order'			=> 'DESC',
			'columns'		=> '1'
		),$atts);
		
		$args = array(
			'post_type' 			=> 'post'
			,'ignore_sticky_posts' 	=> 1
			,'showposts' 			=> $atts['limit']
			,'meta_query'			=> array(
				array(
					'key' => '_thumbnail_id',
				)
			)
		);

		if(!empty($atts['orderby'])) {
			$args['orderby'] = esc_attr($atts['orderby']);
			$args['order'] = esc_attr($atts['order']);
		}
		
		if( strlen( $atts['cats'] ) > 0 ){
			$args['category__in '] = explode( ',', $atts['cats'] );
		}
		
		ob_start();
		
		$_post = new WP_Query( $args );
		
		if( $_post->have_posts() ) {
			$atts['_post'] = $_post;

			$s_cats 	= absint($atts['s_cats']) == 1? 'return true;': 'return false;';
			$s_author 	= absint($atts['s_author']) == 1? 'return true;': 'return false;';
			$s_button 	= absint($atts['s_button']) == 1? 'return true;': 'return false;';
			$s_excerpt	= absint($atts['s_excerpt']) == 1? 'return true;': 'return false;';

			$call_function_1 = create_function('$thumb', 'return "theshopier_blog_thumb";');
			$call_function_2 = create_function('$class', '$class[] = "col-sm-24"; return $class;');
			$call_function_3 = create_function('$columns', 'return 1;');
			$call_function_4 = create_function('$res', 'return $res;');

			$call_show_cats = create_function('$res', $s_cats);
			$call_show_author = create_function('$res', $s_author);
			$call_show_button = create_function('$res', $s_button);
			$call_show_excerpt = create_function('$res', $s_excerpt);

			$style = array_map('trim', explode(' ', $atts['style']));

			if( strcmp( $style[0], "list" ) == 0 ) {
				$call_function_1 = create_function('$thumb', 'return "theshopier_blog_thumb_list";');
				$call_function_2 = create_function('$class', '$class[] = "'.$atts['style'].'"; return $class;');
				$call_function_4 = create_function('$res', 'return true;');
				$call_function_3 = create_function('$columns', 'return 2;');
			} elseif( strcmp( $style[0], "grid" ) == 0 ) {
				$call_function_1 = create_function('$thumb', 'return "theshopier_blog_thumb_grid";');
				if(absint($atts['columns']) > 2) $atts['style'] .= ' small-blog';
				$call_function_2 = create_function('$class', '$class[] = "'.$atts['style'].'"; return $class;');
				$call_function_4 = create_function('$res', 'return true;');
				$call_function_3 = create_function('$columns', 'return '.$atts['columns'].';');
			}

			add_filter("theshopier_post_content_loop_thumbnail", $call_function_1, 10, 1);
			add_filter("theshopier_post_content_loop_class", $call_function_2, 10, 1);
			add_filter("theshopier_post_content_loop_show_author", $call_show_author, 10, 1);
			add_filter("theshopier_post_content_loop_show_thumbnail", $call_function_4, 10, 1);
			add_filter("theshopier_post_content_loop_columns", $call_function_3, 10, 1);

			add_filter("theshopier_post_content_loop_show_cats", $call_show_cats, 10, 1);
			add_filter("theshopier_post_content_loop_show_button", $call_show_button, 10, 1);
			add_filter("theshopier_post_content_loop_show_excerpt", $call_show_excerpt, 10, 1);

			parent::get_template("shortcode-post-widget.tpl.php", $atts);

			remove_filter("theshopier_post_content_loop_thumbnail", $call_function_1, 10, 1);
			remove_filter("theshopier_post_content_loop_class", $call_function_2, 10, 1);
			remove_filter("theshopier_post_content_loop_columns", $call_function_3, 10, 1);
			remove_filter("theshopier_post_content_loop_show_author", $call_show_author, 10, 1);
			remove_filter("theshopier_post_content_loop_show_thumbnail", $call_function_4, 10, 1);

			remove_filter("theshopier_post_content_loop_show_cats", $call_show_cats, 10, 1);
			remove_filter("theshopier_post_content_loop_show_button", $call_show_button, 10, 1);
			remove_filter("theshopier_post_content_loop_show_excerpt", $call_show_excerpt, 10, 1);
		}
		
		wp_reset_postdata();

		$classes = array('nth-shortcode recent-post');
		if( strlen($atts['box_style']) > 0 )$classes[] = esc_attr($atts['box_style']);
		
		return '<div class="'.esc_attr(implode(' ', $classes)).'">' . ob_get_clean() . '</div>';
	}
	
	public static function staticblock( $atts ) {
        if( !class_exists( 'Nexthemes_StaticBlock' ) ) return '';
		$atts = shortcode_atts(array(
			"title" 		=> '',
			"h_style"		=> '',
			"box_style"		=> '',
			"id"			=> '',
			"style"			=> '',
		),$atts);
		
		ob_start();
		
		if( strcmp($atts['style'], 'grid') == 0 ) {
            Nexthemes_StaticBlock::getImage( $atts['id']);
		}
		
		echo '<div class="shortcode-content">';

        Nexthemes_StaticBlock::getSticBlockContent( $atts['id'] );
		
		echo '</div>';
		
		$class = ( strcmp($atts['style'], 'grid') == 0 )? 'widget_boxed': '';
		
		return self::beforeShortcode($atts, 'theshopier_'.__FUNCTION__, $class) . ob_get_clean() . self::afterShortcode();
	}
	
	public static function testimonials( $atts ){
		$atts = shortcode_atts(array(
			"title" 		=> '',
			"h_style"		=> '',
			"box_style"		=> '',
			"use_slider"	=> '',
			"ids"			=> '',
			"style"			=> 'def'
		),$atts);
		
		if( strlen( trim( $atts['ids'] ) ) == 0 ) return '';
		
		$nexthemes_testimonials = woothemes_get_testimonials( array('id' => $atts['ids'],'limit' => 10, 'size' => '100x100' ));
		
		ob_start();
		
		if( !empty( $nexthemes_testimonials ) && count( $nexthemes_testimonials ) > 0 ) {
			
			$atts['nexthemes_testimonials'] = $nexthemes_testimonials;
			
			parent::get_template("shortcode-testimonials.tpl.php", $atts);
			
		}
		//rewind_posts();
		wp_reset_postdata();
		
		return self::beforeShortcode($atts, 'theshopier_'.__FUNCTION__) . ob_get_clean() . self::afterShortcode();
	}
	
	public static function features( $atts ){
		$atts = shortcode_atts(array(
			"title" 	=> '',
			"box_style"	=> '',
			"h_style"	=> '',
			"id"		=> 0,
			"per_row"	=> 3,
			"limit"		=> 5,
			"size"		=> 150,
			"color"		=> '',
			"t_color"	=> '',
			"l_color"	=> '',
			"style"		=> '',
			"w_limit"	=> -1,
			"use_boxed"	=> '0',
			"learn_more" => 'false',
			"s_excerpt"	=> 'false',
			"l_text"	=> 'Learn more',
			"icon"		=> 'fa fa-long-arrow-right'
		),$atts);
		
		if( strlen( trim( $atts['id'] ) ) == 0 ) return '';
		
		$theshopier_features = woothemes_get_features( array('id' => $atts['id'],'limit' => $atts['limit'], 'size' => $atts['size']));
		
		ob_start();
		
		if( !empty( $theshopier_features ) && count( $theshopier_features ) > 0 ) {
			
			$atts['theshopier_features'] = $theshopier_features;
			
			parent::get_template("shortcode-features.tpl.php", $atts);
			
		}
		
		wp_reset_postdata();
		$class = 'widget widget_woothemes_features';
		return self::beforeShortcode($atts, 'theshopier_'.__FUNCTION__, $class).ob_get_clean().self::afterShortcode();
	}
	
	public static function teammember( $atts ){
		$atts = shortcode_atts(array(
			"title" 	=> '',
			"h_style"	=> '',
			"box_style"	=> '',
			"ids"		=> '',
			"columns"	=> 4,
			"style"		=> '',
		),$atts);
		
		if( strlen( trim( $atts['ids'] ) ) == 0 ) return '';
		$ids = array_map( 'trim', explode( ',', $atts['ids'] ) );
		
		if( !class_exists( 'Nexthemes_TeamMembers_Front' ) ) return;
		
		$front = new Nexthemes_TeamMembers_Front;
		$teams = $front->getByIds( $ids );
		
		ob_start();
		
		if( $teams->have_posts() ) {
			
			$atts['teams'] = $teams;
			
			parent::get_template("team-members.tpl.php", $atts);
			
		}
		
		wp_reset_postdata();
		
		return self::beforeShortcode($atts, 'theshopier_'.__FUNCTION__). ob_get_clean(). self::afterShortcode();
	}
	
	public static function pricing( $atts, $content ){
		$atts = shortcode_atts(array(
			"title" 	=> 'Basic',
			"price"		=> '$|10.99|mo',
			"desc"		=> '',
			"features"	=> '',
			"label"		=> '',
			"label_text"	=> 'Most Popular',
			"bt_text"	=> 'Buy now',
			"bt_link"	=> '#',
		),$atts);
		
		$atts['content'] = $content;

		$bt_link = self::convert_VC_link($atts['bt_link']);
		if( !isset($bt_link['title']) ) $bt_link['title'] = esc_attr($atts['bt_text']);
		$bt_link['target'] = isset($bt_link['target']) && strlen($bt_link['target']) > 0? 'target="'.esc_attr($bt_link['target']).'"' : '';
		if(!isset($bt_link['url']) || strlen($bt_link['url']) == 0) $bt_link['url'] = '#';
		$atts['bt_link'] = $bt_link;
		
		ob_start();
		
		parent::get_template("pricing.tpl.php", $atts);
		
		return '<div class="nth-shortcode nth-'. __FUNCTION__ .'">' . ob_get_clean() . '</div>';
	}
	
	public static function action( $atts ){
		$atts = shortcode_atts( array(
			"label"		=> 'Message here...',
			"bt_text"	=> 'Button text',
			"bt_link"	=> '#',
			"bt_icon"	=> '',
			"use_icon"	=> 0,
			"bg_color"	=> '#ededed',
			"bt_color"	=> '#5a9e74',
		), $atts);

		$icon = '';
		if( absint($atts['use_icon']) ){
			$icon = strlen( $atts['bt_icon']) > 0? '<i class="'.esc_attr($atts['bt_icon']).'"></i>': '';
		}
		
		$bt_link = self::convert_VC_link($atts['bt_link']);

		if( !isset($bt_link['title']) ) $bt_link['title'] = esc_attr($atts['bt_text']);
		
		$bt_link['target'] =
			isset($bt_link['target']) && strlen($bt_link['target']) > 0?
				'target="'.esc_attr($bt_link['target']).'"' : '';

		$bt_link['url'] = !isset($bt_link['url']) || strlen($bt_link['url']) == 0? '#': urldecode($bt_link['url']);
		
		ob_start(); ?>
		
		<span class="nth-label"><?php echo $atts['label']?></span>
		<a <?php echo $bt_link['target'];?> title="<?php echo esc_attr(urldecode($bt_link['title']));?>" href="<?php echo esc_url($bt_link['url']);?>"><?php echo esc_attr($atts['bt_text']);?> &nbsp;&nbsp;<?php echo $icon;?></a>
		<?php 
		return '<div class="nth-shortcode nth-'. __FUNCTION__ .'">' . ob_get_clean() . '</div>';
	}
	
	public static function google_maps( $atts, $content ){
		$atts = shortcode_atts(array(
			'title' 	=> '',
			'address'	=> 'Quan 1, Ho Chi Minh, Viet Nam',
			'zoom'		=> '16',
			'height'	=> '450px',
			'style'		=> '',
			'mk_icon'	=> '',
			'css'		=> '',
			'm_color'	=> 'JTVCJTBBJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjAlMjJhZG1pbmlzdHJhdGl2ZS5jb3VudHJ5JTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZWxlbWVudFR5cGUlMjIlM0ElMjAlMjJsYWJlbHMlMjIlMkMlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJzdHlsZXJzJTIyJTNBJTIwJTVCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIydmlzaWJpbGl0eSUyMiUzQSUyMCUyMm9mZiUyMiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU3RCUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU1RCUwQSUyMCUyMCUyMCUyMCU3RCUyQyUwQSUyMCUyMCUyMCUyMCU3QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMmZlYXR1cmVUeXBlJTIyJTNBJTIwJTIybGFuZHNjYXBlLm5hdHVyYWwubGFuZGNvdmVyJTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZWxlbWVudFR5cGUlMjIlM0ElMjAlMjJhbGwlMjIlMkMlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJzdHlsZXJzJTIyJTNBJTIwJTVCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyY29sb3IlMjIlM0ElMjAlMjIlMjNlYmU3ZDMlMjIlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlN0QlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlNUQlMEElMjAlMjAlMjAlMjAlN0QlMkMlMEElMjAlMjAlMjAlMjAlN0IlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJmZWF0dXJlVHlwZSUyMiUzQSUyMCUyMmxhbmRzY2FwZS5tYW5fbWFkZSUyMiUyQyUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMmVsZW1lbnRUeXBlJTIyJTNBJTIwJTIyYWxsJTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyc3R5bGVycyUyMiUzQSUyMCU1QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU3QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMnZpc2liaWxpdHklMjIlM0ElMjAlMjJvZmYlMjIlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlN0QlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlNUQlMEElMjAlMjAlMjAlMjAlN0QlMkMlMEElMjAlMjAlMjAlMjAlN0IlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJmZWF0dXJlVHlwZSUyMiUzQSUyMCUyMndhdGVyJTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZWxlbWVudFR5cGUlMjIlM0ElMjAlMjJnZW9tZXRyeS5maWxsJTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyc3R5bGVycyUyMiUzQSUyMCU1QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU3QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMmNvbG9yJTIyJTNBJTIwJTIyJTIzODY5M2EzJTIyJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTdEJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTVEJTBBJTIwJTIwJTIwJTIwJTdEJTJDJTBBJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjAlMjJyb2FkLmFydGVyaWFsJTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZWxlbWVudFR5cGUlMjIlM0ElMjAlMjJhbGwlMjIlMkMlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJzdHlsZXJzJTIyJTNBJTIwJTVCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIydmlzaWJpbGl0eSUyMiUzQSUyMCUyMm9mZiUyMiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU3RCUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU1RCUwQSUyMCUyMCUyMCUyMCU3RCUyQyUwQSUyMCUyMCUyMCUyMCU3QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMmZlYXR1cmVUeXBlJTIyJTNBJTIwJTIycm9hZC5sb2NhbCUyMiUyQyUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMmVsZW1lbnRUeXBlJTIyJTNBJTIwJTIyYWxsJTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyc3R5bGVycyUyMiUzQSUyMCU1QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU3QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMnZpc2liaWxpdHklMjIlM0ElMjAlMjJvZmYlMjIlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlN0QlMkMlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlN0IlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJjb2xvciUyMiUzQSUyMCUyMiUyM2ViZTdkMyUyMiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU3RCUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU1RCUwQSUyMCUyMCUyMCUyMCU3RCUyQyUwQSUyMCUyMCUyMCUyMCU3QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMmZlYXR1cmVUeXBlJTIyJTNBJTIwJTIyYWRtaW5pc3RyYXRpdmUubmVpZ2hib3Job29kJTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZWxlbWVudFR5cGUlMjIlM0ElMjAlMjJhbGwlMjIlMkMlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJzdHlsZXJzJTIyJTNBJTIwJTVCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIydmlzaWJpbGl0eSUyMiUzQSUyMCUyMm9uJTIyJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTdEJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTVEJTBBJTIwJTIwJTIwJTIwJTdEJTJDJTBBJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjAlMjJwb2klMjIlMkMlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJlbGVtZW50VHlwZSUyMiUzQSUyMCUyMmFsbCUyMiUyQyUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMnN0eWxlcnMlMjIlM0ElMjAlNUIlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlN0IlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJ2aXNpYmlsaXR5JTIyJTNBJTIwJTIyb2ZmJTIyJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTdEJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTVEJTBBJTIwJTIwJTIwJTIwJTdEJTJDJTBBJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjAlMjJ0cmFuc2l0JTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyZWxlbWVudFR5cGUlMjIlM0ElMjAlMjJhbGwlMjIlMkMlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjJzdHlsZXJzJTIyJTNBJTIwJTVCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTdCJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIydmlzaWJpbGl0eSUyMiUzQSUyMCUyMm9mZiUyMiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU3RCUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU1RCUwQSUyMCUyMCUyMCUyMCU3RCUyQyUwQSUyMCUyMCUyMCUyMCU3QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMmZlYXR1cmVUeXBlJTIyJTNBJTIwJTIycm9hZCUyMiUyQyUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMmVsZW1lbnRUeXBlJTIyJTNBJTIwJTIyYWxsJTIyJTJDJTBBJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIwJTIyc3R5bGVycyUyMiUzQSUyMCU1QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCU3QiUwQSUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMCUyMnZpc2liaWxpdHklMjIlM0ElMjAlMjJvZmYlMjIlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlN0QlMEElMjAlMjAlMjAlMjAlMjAlMjAlMjAlMjAlNUQlMEElMjAlMjAlMjAlMjAlN0QlMEElNUQ='
		),$atts);
		
		ob_start();
		
		$atts['map_id'] = rand();
		$atts['content'] = $content;
		
		parent::get_template("google-maps.tpl.php", $atts);
		
		return '<div class="nth-shortcode nth-'. __FUNCTION__ .'">' . ob_get_clean() . '</div>';
	}
	
	public static function convert_VC_link( $link_str ){
		if( strlen( trim($link_str) ) == 0 ) return '#';
		$link_rgs = array_map( 'trim', explode( '|', $link_str ) );
		$return = array();
		foreach($link_rgs as $var){
			$vars = array_map('trim', explode(':', $var));
			if( isset( $vars[1] ) && strlen(trim($vars[1])) > 0 ) $return[$vars[0]] = urldecode($vars[1]);
		}
		return $return;
	}

	public static function buttons( $atts ){

		$atts = shortcode_atts(array(
			'text'				=> 'Button text',
			'link'				=> '',
			'style'				=> '',
			'bgcl_style'		=> '',
			'bg_color'			=> '#cccccc',
			'border_color'		=> '#cccccc',
			'color'				=> '',
			'use_icon'			=> 0,
			'icon_fontawesome'	=> 'fa fa-adjust',
			'size'				=> '',
			'el_class'			=> ''
		), $atts);

		ob_start();

		parent::get_template('shortcode-button.tpl.php', $atts);

		return ob_get_clean();
	}

	public static function social_network( $atts ){
		$atts = shortcode_atts(array(
			"title" 	=> '',
			"h_style"	=> '',
			"box_style"	=> '',
			"item"		=> '',
			"ic_size"	=> '',
			'class'		=> '',
			'color_hover'	=> 0,
		),$atts);

		if( strlen(trim($atts['item'])) == 0 ) return;

		ob_start();

		parent::get_template("social.tpl.php", $atts);

		return self::beforeShortcode($atts, 'theshopier_'.__FUNCTION__) . ob_get_clean() . self::afterShortcode();
	}

	public static function beforeShortcode($atts, $__name, $__class=''){
		$classes = array("nth-shortcode {$__name}");
		if( !empty($atts['box_style']) ) $classes[] = esc_attr($atts['box_style']);
		if(strlen(trim($__class)) > 0) $classes[] = $__class;
		return '<div class="'.esc_attr(implode(' ', $classes)).'">';
	}

	public static function afterShortcode(){
		return '</div>';
	}

	public static function instagram( $atts ){
		$atts = shortcode_atts(array(
			"title" 	=> '',
			"h_style"	=> '',
			"box_style"	=> '',
			"limit"		=> 6,
			"columns"	=> 6,
			"image_size"	=> 'low_resolution', //thumbnail - low_resolution - standard_resolution
		),$atts);

		$cache_id = 'theshopier_instag_'.md5(serialize($atts));

		$instag_transient = get_transient( $cache_id );

		if( empty( $instag_transient ) || true ) {

			ob_start();

			parent::get_template("instagram.tpl.php", $atts);

			$output = self::beforeShortcode($atts, 'theshopier_'.__FUNCTION__) . ob_get_clean() . self::afterShortcode();

			set_transient( $cache_id, $output, 60*60*12 );
		} else {
			$output = $instag_transient;
		}

		return $output;
	}

	public static function qrcode( $atts ){
		$atts = shortcode_atts(array(
			"data"				=> '',
			"size"				=> '270x270',
			"charset-source"	=> '',
			"charset-target"	=> '',
			"ecc"				=> 'Q',
			"color"				=> '000',
			"bgcolor"			=> 'fff',
			"margin"			=> '0',
			"format"			=> 'png'
		),$atts);

		$atts['color'] = str_replace('#', '', $atts['color']);
		$atts['bgcolor'] = str_replace('#', '', $atts['bgcolor']);

		if(empty($atts['data'])) {
			global $wp;
			$atts['data'] = home_url(add_query_arg(array(), $wp->request));
		}

		$atts = array_filter($atts, 'strlen');
		$atts = array_map('urlencode', $atts);

		$qr_src = add_query_arg( $atts, 'http://api.qrserver.com/v1/create-qr-code/');

		list($width, $height) = explode('x', $atts['size']);

		ob_start();

		NexThemes_Plg::getImage(array(
			'alt'	=> 'QR-code for' . esc_attr($atts['data']),
			'src'	=> $qr_src,
			'width'	=> $width,
			'height' => $height
		));

		return ob_get_clean();
	}

	public static function store_location($atts){
		$atts = shortcode_atts(array(
			"heading"		=> '',
			"box_style"		=> '',
			"h_style"		=> '',
			"stores"		=> '',
			"map_size"		=> '270x170',
			"columns"		=> 4,
		),$atts);

		if(!empty($atts['stores'])) {
			$atts['stores'] = (array) vc_param_group_parse_atts( $atts['stores'] );
			foreach($atts['stores'] as $k => $store){
				$atts['stores'][$k]['link'] = self::convert_VC_link($store['link']);

				if(!empty($atts['stores'][$k]['infos'])) {
					$atts['stores'][$k]['infos'] = (array) vc_param_group_parse_atts( $store['infos'] );
				}
			}

		}

		ob_start();

		parent::get_template("shortcode-store-locations.tpl.php", $atts);

		return self::beforeShortcode($atts, 'theshopier_'.__FUNCTION__) . ob_get_clean() . self::afterShortcode();
	}

	public static function tag_cloud($atts) {
		$atts = shortcode_atts(array(
			"heading"		=> '',
			"box_style"		=> '',
			"h_style"		=> '',
			"taxonomy"		=> ''
		),$atts);

		ob_start();

		parent::get_template("shortcode-tag-cloud.tpl.php", $atts);

		return self::beforeShortcode($atts, 'theshopier_'.__FUNCTION__) . ob_get_clean() . self::afterShortcode();

	}
	
}

endif;