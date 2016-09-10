<?php
/**
 * Email Footer
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.0.0
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

$meta               = get_post_meta( $template, '_template_meta', true);
$footer_text_color  = ( isset( $meta['footer_text_color'] ) ) ? $meta['footer_text_color'] : '#555555' ;
$socials_on_footer      = ( isset( $meta['socials_on_footer'] ) ) ? $meta['socials_on_footer'] : 0 ;

$socials_color = ( isset( $meta['socials_color'] ) ) ? '-' . $meta['socials_color'] : '-black' ;

$footer_logo_url = (isset( $meta['footer_logo_url'] ) ) ? $meta['footer_logo_url'] : '';
$footer_text = (isset( $meta['footer_text'] ) ) ? $meta['footer_text'] : '';

$facebook = get_option( 'yith-wcet-facebook' );
$twitter = get_option( 'yith-wcet-twitter' );
$google = get_option( 'yith-wcet-google' );
$linkedin = get_option( 'yith-wcet-linkedin' );
$instagram = get_option( 'yith-wcet-instagram' );
$flickr = get_option( 'yith-wcet-flickr' );

// For gmail compatibility, including CSS styles in head/body are stripped out therefore styles need to be inline. These variables contain rules which are added to the template inline.
$template_footer = "
	border-top:0;
";

$credit = "
	border:0;
	color: $footer_text_color;
	font-family: Arial;
	font-size:12px;
	line-height:125%;
	text-align:right;
";
?>
															</div>
														</td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Footer -->
                                	<table border="0" cellpadding="10" cellspacing="0" id="template_footer" style="<?php echo $template_footer; ?>">
                                    	<tr>
                                        	<td valign="top">
                                                <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td>
                                                            <?php

                                                                if ( strlen( $footer_logo_url ) > 0 ) {
                                                                    echo '<img src="' . esc_url( $footer_logo_url ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" />';
                                                                }
                                                            ?>
                                                        </td>
                                                        <td colspan="2" valign="middle" id="credit" style="<?php echo $credit; ?>">
                                                            <?php 
                                                                    echo $footer_text;
                                                            ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Footer -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="middle" style="height:35px">
                                    <?php if ( $socials_on_footer ){ ?>
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
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
