<?php
/**
 * Email Header
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Load Template
$template = '';
if (defined('YITH_WCET_PREMIUM')){
    $template        = get_option( 'yith-wcet-email-template-' . $mail_type );
}else{
    $template        = get_option( 'yith-wcet-email-template' );
}

// used for preview
if ( isset($_GET['template_id'])){
    $template = $_GET['template_id'];
}
$meta            = get_post_meta( $template, '_template_meta', true);

$custom_links_array     = ( !empty( $meta['custom_links'] ) ) ? $meta['custom_links'] : array();
$socials_on_header      = ( isset( $meta['socials_on_header'] ) ) ? $meta['socials_on_header'] : 0 ;

$socials_color = ( isset( $meta['socials_color'] ) ) ? '-' . $meta['socials_color'] : '-black' ;

$logo_url = (isset( $meta['logo_url'] ) ) ? $meta['logo_url'] : '';

$facebook = get_option( 'yith-wcet-facebook' );
$twitter = get_option( 'yith-wcet-twitter' );
$google = get_option( 'yith-wcet-google' );
$linkedin = get_option( 'yith-wcet-linkedin' );
$instagram = get_option( 'yith-wcet-instagram' );
$flickr = get_option( 'yith-wcet-flickr' );

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
	</head>
    <body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    	<div id="wrapper">
        	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
            	<tr>
                	<td align="center" valign="top">
                    	<table border="0" cellpadding="0" cellspacing="0" id="template_container">
                        <?php
                            if ( strlen( $logo_url ) > 0 ) { ?>
                            <tr>
                                <td align="center" valign="top">
                                    <!-- Header -->
                                    <table border="0" cellpadding="0" cellspacing="0" id="template_header_image">
                                        <tr>
                                            <td>
                                                            <?php echo '<img src="' . esc_url( $logo_url ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" />'; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <?php } ?>
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Header -->
                                	<table border="0" cellpadding="0" cellspacing="0" id="template_header">
                                        <tr>
                                            <td>
                                            	<h1><?php echo $email_heading; ?></h1>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Header -->
                                </td>
                            </tr>
                            <?php if ( count( $custom_links_array ) > 0 || ( $socials_on_header && ( (strlen($facebook) + strlen($twitter) + strlen($google) + strlen($linkedin) + strlen($instagram) + strlen($flickr) ) > 0) ) ){ ?>

                            <tr>
                            	<td align="center" valign="top">
                                    <!-- Custom Links -->
                                	<table border="0" cellpadding="0" cellspacing="0" id="template_custom_links">
                                        <tr>
                                            <td>
                                            	<ul>
                                                <?php foreach ($custom_links_array as $cl) {
                                                    echo '<li><a href="'. $cl['url'].'">'. $cl['text'].'</a></li>';
                                                }
                                                ?>
                                                <?php if ( $socials_on_header ){ ?>
                                                    <?php if ( strlen($facebook) > 0 ) { ?>
                                                        <li class="yith-wcet-socials-icons"><a href="<?php echo $facebook ?>"><img src="<?php echo YITH_WCET_ASSETS_URL ?>/images/socials-icons/facebook<?php echo $socials_color ?>.png"></a></li>
                                                    <?php } ?>
                                                    <?php if ( strlen($twitter) > 0 ) { ?>
                                                        <li class="yith-wcet-socials-icons"><a href="<?php echo $twitter ?>"><img src="<?php echo YITH_WCET_ASSETS_URL ?>/images/socials-icons/twitter<?php echo $socials_color ?>.png"></a></li>
                                                    <?php } ?>
                                                    <?php if ( strlen($google) > 0 ) { ?>
                                                        <li class="yith-wcet-socials-icons"><a href="<?php echo $google ?>"><img src="<?php echo YITH_WCET_ASSETS_URL ?>/images/socials-icons/google<?php echo $socials_color ?>.png"></a></li>
                                                    <?php } ?>
                                                    <?php if ( strlen($linkedin) > 0 ) { ?>
                                                        <li class="yith-wcet-socials-icons"><a href="<?php echo $linkedin ?>"><img src="<?php echo YITH_WCET_ASSETS_URL ?>/images/socials-icons/linkedin<?php echo $socials_color ?>.png"></a></li>
                                                    <?php } ?>
                                                    <?php if ( strlen($instagram) > 0 ) { ?>
                                                        <li class="yith-wcet-socials-icons"><a href="<?php echo $instagram ?>"><img src="<?php echo YITH_WCET_ASSETS_URL ?>/images/socials-icons/instagram<?php echo $socials_color ?>.png"></a></li>
                                                    <?php } ?>
                                                    <?php if ( strlen($flickr) > 0 ) { ?>
                                                        <li class="yith-wcet-socials-icons"><a href="<?php echo $flickr ?>"><img src="<?php echo YITH_WCET_ASSETS_URL ?>/images/socials-icons/flickr<?php echo $socials_color ?>.png"></a></li>
                                                    <?php } ?>
                                                <?php } ?>
                                            	</ul>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Custom Links -->
                                </td>
                            </tr>
                            <?php } ?>

                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Body -->
                                	<table border="0" cellpadding="0" cellspacing="0" id="template_body">
                                    	<tr>
                                            <td valign="top" id="body_content">
                                                <!-- Content -->
                                                <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td valign="top">
                                                            <div id="body_content_inner">
