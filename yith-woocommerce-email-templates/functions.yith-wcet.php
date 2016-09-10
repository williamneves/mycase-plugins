<?php
/**
 * Functions
 *
 * @author Yithemes
 * @package YITH WooCommerce Email Templates
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCET' ) ) { exit; } // Exit if accessed directly

/**
 * Print the content of metabox options [Free Version]
 *
 * @return   void
 * @since    1.0
 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
 */
if ( ! function_exists( 'yith_wcet_metabox_options_content' ) ) {
	function yith_wcet_metabox_options_content( $args ){
		extract( $args );
        global $post;
?>

            <input type="hidden" value="<?php echo $logo_url ?>" name= "_template_meta[logo_url]" id="yith-wcet-logo-url">
            <input type="hidden" value="<?php echo $custom_logo_url ?>" id="yith-wcet-custom-logo-url">

            <div class="yith-wcet-section-container">
                <div class="yith-wcet-section-title"> <?php echo __('Header Logo', 'yith-woocommerce-email-templates') ?></div>
                <table class="yith-wcet-section-table">
                    <tr>
                        <td class="yith-wcet-table-title">
                            <label><?php echo __('Logo', 'yith-woocommerce-email-templates') ?></label>
                        </td>
                        <td class="yith-wcet-table-content">
                            <div class="yith-wcet-uploader_sect">
                                <?php yith_wcet_insert_image_uploader(); ?>
                                <div id="yith-wcet-logo-uploaded-image">
                                    <div id="yith-wcet-logo-and-del-container">
                                        <img <?php if (!isset($logo_url) || $logo_url == '') { echo 'style="display:none;"'; }?> id="yith-wcet-logo-image" src="<?php echo $logo_url ?>" />
                                        <span id="yith-wcet-remove-logo-btn" class="dashicons dashicons-no"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="yith-wcet-table-description"><?php echo __('[Upload a new logo, or select your default logo as you have set in "Email Template Settings"]', 'yith-woocommerce-email-templates') ?></div>
                        </td>
                    </tr>
                </table>        
            </div><!-- section-container -->

            <div class="yith-wcet-section-container">
                <div class="yith-wcet-section-title"> <?php echo __('Style Options', 'yith-woocommerce-email-templates') ?></div>
                <table class="yith-wcet-section-table">
                    <tr>
                        <td class="yith-wcet-table-title">
                            <label><?php echo __('Base Color', 'yith-woocommerce-email-templates') ?></label>
                        </td>
                        <td class="yith-wcet-table-content">
                            <input type="text" class="yith-wcet-color-picker" name= "_template_meta[base_color]" value="<?php echo $base_color ?>"
                            data-default-color="<?php echo $base_color_default; ?>" id="yith-wcet-base-color">
                        </td>
                    </tr>
                    <tr>
                        <td class="yith-wcet-table-title">
                            <label><?php echo __('Body Color', 'yith-woocommerce-email-templates') ?></label>
                        </td>
                        <td class="yith-wcet-table-content">
                            <input type="text" class="yith-wcet-color-picker" name= "_template_meta[body_color]" value="<?php echo $body_color ?>"
                            data-default-color="<?php echo $body_color_default; ?>" id="yith-wcet-body-color">
                        </td>
                    </tr>
                    <tr>
                        <td class="yith-wcet-table-title">
                            <label><?php echo __('Background Color', 'yith-woocommerce-email-templates') ?></label>
                        </td>
                        <td class="yith-wcet-table-content">
                            <input type="text" class="yith-wcet-color-picker" name= "_template_meta[bg_color]" value="<?php echo $bg_color ?>"
                            data-default-color="<?php echo $bg_color_default; ?>" id="yith-wcet-bg-color">
                        </td>
                    </tr>
                    <tr>
                        <td class="yith-wcet-table-title">
                            <label><?php echo __('Text Color', 'yith-woocommerce-email-templates') ?></label>
                        </td>
                        <td class="yith-wcet-table-content">
                            <input type="text" class="yith-wcet-color-picker" name= "_template_meta[txt_color]" value="<?php echo $txt_color ?>"
                            data-default-color="<?php echo $txt_color_default; ?>" id="yith-wcet-txt-color">
                        </td>
                    </tr>
                </table>        
            </div><!-- section-container -->
            <div class="yith-wcet-section-container">
                <div class="yith-wcet-section-title"> <?php echo __('Preview', 'yith-woocommerce-email-templates') ?></div>
                <table class="yith-wcet-section-table">
                    <tr>
                        <td class="yith-wcet-table-title">
                            <label><?php echo __('Preview', 'yith-woocommerce-email-templates') ?></label>
                        </td>
                        <td class="yith-wcet-table-content">
                            <a class="yith-wcet-nofocus" target="_blank" href="<?php echo admin_url('?yith_wcet_preview_mail=1&template_id=' . $post->ID)?>">
                                <input type="button" class="button-secondary" value="<?php echo __('Preview Template', 'yith-woocommerce-email-templates')?>">
                            </a>
                            <div class="yith-wcet-table-description"><?php echo __('[You need to publish or update the template before displaying the correct preview]', 'yith-woocommerce-email-templates') ?></div>
                        </td>
                    </tr>
                </table>        
            </div><!-- section-container -->
        <?php
	}
}


/**
 * Insert Uploader button
 *
 * @return   string
 * @since    1.0
 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
 */
if (! function_exists( 'yith_wcet_insert_image_uploader' )){
    function yith_wcet_insert_image_uploader(){
        wp_enqueue_script('jquery');
        // This will enqueue the Media Uploader script
        wp_enqueue_media();
        ?>
            <input type="button" name="upload-btn" id="yith-wcet-upload-btn" class="button-secondary" value="<?php echo __('Upload', 'yith-woocommerce-email-templates')?>">
            <input type="button" id="yith-wcet-custom-logo-btn" class="button-secondary" value="<?php echo __('My Default Logo', 'yith-woocommerce-email-templates')?>">
        <?php
    }
}


if ( ! function_exists( 'yith_wcet_get_template' ) ) {
    function yith_wcet_get_template( $template , $args ){
        extract( $args );
        include(YITH_WCET_TEMPLATE_PATH . '/' . $template);
    }
}



?>