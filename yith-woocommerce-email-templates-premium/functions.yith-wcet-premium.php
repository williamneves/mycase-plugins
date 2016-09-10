<?php
/**
 * Functions Premium
 *
 * @author  Yithemes
 * @package YITH WooCommerce Badge Management
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCET_PREMIUM' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Print the content of metabox options [PREMIUM]
 *
 * @return   void
 * @since    1.0
 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
 */
if ( !function_exists( 'yith_wcet_metabox_options_content_premium' ) ) {
    function yith_wcet_metabox_options_content_premium( $args ) {
        extract( $args );
        global $post;
        ?>

        <input type="hidden" value="<?php echo $footer_logo_url ?>" name="_template_meta[footer_logo_url]" id="yith-wcet-logo-url-footer">
        <input type="hidden" value="<?php echo $logo_url ?>" name="_template_meta[logo_url]" id="yith-wcet-logo-url">
        <input type="hidden" value="<?php echo $custom_logo_url ?>" id="yith-wcet-custom-logo-url">


        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Advanced Style', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table">
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Advanced Style', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <select name="_template_meta[premium_mail_style]" id="yith-wcet-premium-mail-style">
                            <option value="default" <?php echo selected( $premium_mail_style, 'default', false ) ?>><?php _e( 'Default', 'yith-woocommerce-email-templates' ) ?></option>
                            ;
                            <option value="1" <?php echo selected( $premium_mail_style, '1', false ) ?>><?php _e( 'Informal', 'yith-woocommerce-email-templates' ) ?></option>
                            ;
                            <option value="2" <?php echo selected( $premium_mail_style, '2', false ) ?>><?php _e( 'Elegant', 'yith-wcbm' ) ?></option>
                            ;
                            <option value="3" <?php echo selected( $premium_mail_style, '3', false ) ?>><?php _e( 'Casual', 'yith-wcbm' ) ?></option>
                            ;
                        </select>
                    </td>
                    <td>
                        <?php $style_id             = ( $premium_mail_style == 'default' ) ? 1 : intval( $premium_mail_style ) + 1;
                        $image_preview_template_url = YITH_WCET_ASSETS_URL . '/images/preview-emails/template-' . $style_id . '.png';
                        ?>
                        <img id="yith-wcet-image-preview-template" height="100px" src="<?php echo $image_preview_template_url; ?>"/>
                    </td>
                </tr>
            </table>
        </div><!-- yith-wcet-section-container -->

        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Header', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table">
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Logo', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <div class="yith-wcet-uploader_sect">
                            <?php yith_wcet_insert_image_uploader_premium(); ?>
                            <div id="yith-wcet-logo-uploaded-image">
                                <div id="yith-wcet-logo-and-del-container">
                                    <img <?php if ( !isset( $logo_url ) || $logo_url == '' ) {
                                        echo 'style="display:none;"';
                                    } ?> id="yith-wcet-logo-image" src="<?php echo $logo_url ?>"/>
                                    <span id="yith-wcet-remove-logo-btn" class="dashicons dashicons-no"></span>
                                </div>
                            </div>
                        </div>
                        <div
                            class="yith-wcet-table-description"><?php echo __( '[Upload a new logo, or select the default logo you have set in "', 'yith-woocommerce-email-templates' ) . '<a href="admin.php?page=yith_wcet_panel" target="_blank">' . __( 'Email Templates Settings', 'yith-woocommerce-email-templates' ) . '</a>"]' ?></div>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Position', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <select name="_template_meta[header_position]" id="yith-wcet-header-position">
                            <option value="left" <?php echo selected( $header_position, 'left', false ) ?>><?php _e( 'Left', 'yith-woocommerce-email-templates' ) ?></option>
                            ;
                            <option value="right" <?php echo selected( $header_position, 'right', false ) ?>><?php _e( 'Right', 'yith-woocommerce-email-templates' ) ?></option>
                            ;
                            <option value="center" <?php echo selected( $header_position, 'center', false ) ?>><?php _e( 'Center', 'yith-wcbm' ) ?></option>
                            ;
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Logo Height', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $logo_height ?>" name="_template_meta[logo_height]" id="yith-wcet-logo-height">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Header Padding', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <table class="yith-wcet-padding-table">
                            <tr>
                                <td><input type="number" size="4" value="<?php echo $header_padding[ 0 ] ?>" name="_template_meta[header_padding][0]" id="yith-wcet-header-padding-top" class="yith-wcet-mini-input"></td>
                                <td><input type="number" size="4" value="<?php echo $header_padding[ 1 ] ?>" name="_template_meta[header_padding][1]" id="yith-wcet-header-padding-right" class="yith-wcet-mini-input"></td>
                                <td><input type="number" size="4" value="<?php echo $header_padding[ 2 ] ?>" name="_template_meta[header_padding][2]" id="yith-wcet-header-padding-bottom" class="yith-wcet-mini-input"></td>
                                <td><input type="number" size="4" value="<?php echo $header_padding[ 3 ] ?>" name="_template_meta[header_padding][3]" id="yith-wcet-header-padding-left" class="yith-wcet-mini-input"></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Top', 'yith-woocommerce-email-templates' ) ?></th>
                                <th><?php _e( 'Right', 'yith-woocommerce-email-templates' ) ?></th>
                                <th><?php _e( 'Bottom', 'yith-woocommerce-email-templates' ) ?></th>
                                <th><?php _e( 'Left', 'yith-woocommerce-email-templates' ) ?></th>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div><!-- yith-wcet-section-container -->

        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Appearance', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table">
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Page Width', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $page_width ?>" name="_template_meta[page_width]" id="yith-wcet-page-width">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Page Border Radius', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $page_border_radius ?>" name="_template_meta[page_border_radius]" id="yith-wcet-page-border-radius">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Base Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[base_color]" value="<?php echo $base_color ?>"
                               data-default-color="<?php echo $base_color_default; ?>" id="yith-wcet-base-color">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Body Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[body_color]" value="<?php echo $body_color ?>"
                               data-default-color="<?php echo $body_color_default; ?>" id="yith-wcet-body-color">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Background Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[bg_color]" value="<?php echo $bg_color ?>"
                               data-default-color="<?php echo $bg_color_default; ?>" id="yith-wcet-bg-color">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Text Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[txt_color]" value="<?php echo $txt_color ?>"
                               data-default-color="<?php echo $txt_color_default; ?>" id="yith-wcet-txt-color">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Header Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[header_color]" value="<?php echo $header_color ?>"
                               data-default-color="<?php echo $header_color_default; ?>" id="yith-wcet-header-color">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Footer Text Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[footer_text_color]" value="<?php echo $footer_text_color ?>"
                               data-default-color="<?php echo $footer_text_color_default; ?>" id="yith-wcet-footer-text-color">
                    </td>
                </tr>
            </table>
        </div><!-- yith-wcet-section-container -->

        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Typography', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table">
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Heading 1 Font Size', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $h1_size ?>" name="_template_meta[h1_size]" id="yith-wcet-h1-size">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Heading 2 Font Size', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $h2_size ?>" name="_template_meta[h2_size]" id="yith-wcet-h2-size">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Heading 3 Font Size', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $h3_size ?>" name="_template_meta[h3_size]" id="yith-wcet-h3-size">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Body Font Size', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $body_size ?>" name="_template_meta[body_size]" id="yith-wcet-body-size">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Body Line Height', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $body_line_height ?>" name="_template_meta[body_line_height]" id="yith-wcet-line-height">
                    </td>
                </tr>
            </table>
        </div><!-- yith-wcet-section-container -->

        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Custom Links', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table" id="yith-wcet-custom-links-table">
                <tr>
                    <td>
                        <input type="button" class="button-secondary yith-wcet-nofocus" value="<?php _ex( 'Add Custom Link', 'Text for Add Custom Link button', 'yith-woocommerce-email-templates' ) ?>"
                               id="yith-wcet-custom-links-add-btn">
                    </td>
                </tr>
                <tr id="yith-wcet-custom-link-row-default" style="visible:hidden; display:none;">
                    <td class="yith-wcet-table-title yith-wcet-table-title-cl">
                        <label><?php _ex( 'Link Text', 'Text for the custom link', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content yith-wcet-table-content-cl">
                        <input type="text" value="" name="_template_meta[custom_links][INDEX][text]" id="yith-wcet-custom-links-textINDEX">
                    </td>
                    <td class="yith-wcet-table-title yith-wcet-table-title-cl">
                        <label><?php _ex( 'Link URL', 'Url for the custom link', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content yith-wcet-table-content-cl">
                        <input type="text" value="" name="_template_meta[custom_links][INDEX][url]" id="yith-wcet-custom-links-urlINDEX">
                    </td>
                    <td class="yith-wcet-table-content yith-wcet-table-content-btn-remove">
                        <input type="button" class="yith-wcet-custom-links-remove-btn button-secondary yith-wcet-nofocus" value="<?php _ex( 'Remove', 'Text for Remove Custom Link button', 'yith-woocommerce-email-templates' ) ?>"
                               custom-link-index="INDEX">
                    </td>
                </tr>
                <?php
                $i = 0;
                //for($i = 0; $i < count($custom_links); $i++) {
                foreach ( $custom_links as $cl ) {
                    ?>
                    <tr id="yith-wcet-custom-link-row<?php echo $i ?>">
                        <td class="yith-wcet-table-title yith-wcet-table-title-cl">
                            <label><?php _ex( 'Link Text', 'Text for the custom link', 'yith-woocommerce-email-templates' ) ?></label>
                        </td>
                        <td class="yith-wcet-table-content yith-wcet-table-content-cl">
                            <input type="text" value="<?php echo $cl[ 'text' ] ?>" name="_template_meta[custom_links][<?php echo $i ?>][text]" id="yith-wcet-custom-links-text<?php echo $i ?>">
                        </td>
                        <td class="yith-wcet-table-title yith-wcet-table-title-cl">
                            <label><?php _ex( 'Link URL', 'Url for the custom link', 'yith-woocommerce-email-templates' ) ?></label>
                        </td>
                        <td class="yith-wcet-table-content yith-wcet-table-content-cl">
                            <input type="text" value="<?php echo $cl[ 'url' ] ?>" name="_template_meta[custom_links][<?php echo $i ?>][url]" id="yith-wcet-custom-links-url<?php echo $i ?>">
                        </td>
                        <td class="yith-wcet-table-content yith-wcet-table-content-btn-remove">
                            <input type="button" class="yith-wcet-custom-links-remove-btn button-secondary yith-wcet-nofocus"
                                   value="<?php _ex( 'Remove', 'Text for Remove Custom Link button', 'yith-woocommerce-email-templates' ) ?>" custom-link-index="<?php echo $i ?>">
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
                <input type="hidden" data-custom-links-count="<?php echo $i ?>" id="yith-wcet-custom-links-count">
            </table>
        </div><!-- yith-wcet-section-container -->

        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Order detail table', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table">
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Border Width', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="number" size="4" value="<?php echo $table_border_width ?>" name="_template_meta[table_border_width]" id="yith-wcet-table-border-width">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Border Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[table_border_color]" value="<?php echo $table_border_color ?>"
                               data-default-color="<?php echo $table_border_color_default; ?>" id="yith-wcet-table-border-color">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Background Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[table_bg_color]" value="<?php echo $table_bg_color ?>"
                               data-default-color="<?php echo $table_bg_color_default; ?>" id="yith-wcet-table-bg-color">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Price Title Background Color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="text" class="yith-wcet-color-picker" name="_template_meta[price_title_bg_color]" value="<?php echo $price_title_bg_color ?>"
                               data-default-color="<?php echo $price_title_bg_color_default; ?>" id="yith-wcet-price-title-bg-color">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Show product thumbnails', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="checkbox" <?php checked( $show_prod_thumb, 1 ); ?> name="_template_meta[show_prod_thumb]" id="yith-wcet-show-prod-thumb">
                    </td>
                </tr>
            </table>
        </div><!-- yith-wcet-section-container -->


        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Social Network', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table">
                <tr>
                    <td style="width:100%;">
                        <?php _e( 'You can set your social network links in ' ); ?><a href="admin.php?page=yith_wcet_panel&tab=socials"
                                                                                      target="_blank"><?php _e( 'YIT Plugins -> Email Templates -> Social Network' ); ?></a>
                    </td>
                </tr>
            </table>
            <table class="yith-wcet-section-table">
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Social network buttons in the header', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="checkbox" <?php checked( $socials_on_header, 1 ); ?> name="_template_meta[socials_on_header]" id="yith-wcet-socials-on-header">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Social network buttons in the footer', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <input type="checkbox" <?php checked( $socials_on_footer, 1 ); ?> name="_template_meta[socials_on_footer]" id="yith-wcet-socials-on-footer">
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Social network buttons color', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <select class="yith-wcet-enhanced-select" name="_template_meta[socials_color]" id="yith-wcet-socials-color">
                            <option <?php selected( $socials_color, 'black' ); ?> value="black"><?php _ex( 'Black', 'Black color for Socials Icons', 'yith-woocommerce-email-templates' ) ?></option>
                            <option <?php selected( $socials_color, 'white' ); ?> value="white"><?php _ex( 'White', 'White color for Socials Icons', 'yith-woocommerce-email-templates' ) ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </div><!-- yith-wcet-section-container -->

        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Footer', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table">
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Left Logo', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <div class="yith-wcet-uploader_sect">
                            <?php yith_wcet_insert_image_uploader_premium( 'footer' ); ?>
                            <div id="yith-wcet-logo-uploaded-image">
                                <div id="yith-wcet-logo-and-del-container-footer">
                                    <img <?php if ( !isset( $footer_logo_url ) || $footer_logo_url == '' ) {
                                        echo 'style="display:none;"';
                                    } ?> id="yith-wcet-logo-image-footer" src="<?php echo $footer_logo_url ?>"/>
                                    <span id="yith-wcet-remove-logo-btn-footer" class="dashicons dashicons-no"></span>
                                </div>
                            </div>
                        </div>
                        <div class="yith-wcet-table-description"><?php _e( '[Upload a new logo, or select the default logo you have set in "Email Templates Settings"]', 'yith-woocommerce-email-templates' ) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Footer Text', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <textarea name="_template_meta[footer_text]" id="yith-wcet-footer-text"><?php echo $footer_text; ?></textarea>
                    </td>
                </tr>
            </table>
        </div><!-- yith-wcet-section-container -->

        <div class="yith-wcet-section-container">
            <div class="yith-wcet-section-title"> <?php _e( 'Preview', 'yith-woocommerce-email-templates' ) ?></div>
            <table class="yith-wcet-section-table">
                <tr>
                    <td class="yith-wcet-table-title">
                        <label><?php _e( 'Preview', 'yith-woocommerce-email-templates' ) ?></label>
                    </td>
                    <td class="yith-wcet-table-content">
                        <a class="yith-wcet-nofocus" target="_blank" href="<?php echo admin_url( '?yith_wcet_preview_mail=1&template_id=' . $post->ID ) ?>">
                            <input type="button" class="button-secondary yith-wcet-nofocus" value="<?php _e( 'Preview Template', 'yith-woocommerce-email-templates' ) ?>">
                        </a>

                        <div class="yith-wcet-table-description"><strong><?php _e( '[You need to publish or update the template before displaying the correct preview]', 'yith-woocommerce-email-templates' ) ?></strong></div>
                    </td>
                </tr>
            </table>
        </div><!-- yith-wcet-section-container -->
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
if ( !function_exists( 'yith_wcet_insert_image_uploader_premium' ) ) {
    function yith_wcet_insert_image_uploader_premium( $text = "" ) {
        wp_enqueue_script( 'jquery' );
        // This will enqueue the Media Uploader script
        wp_enqueue_media();
        $txt = "";
        if ( strlen( $text ) > 0 ) {
            $txt = "-" . $text;
        }
        ?>
        <input type="button" name="upload-btn" id="yith-wcet-upload-btn<?php echo $txt; ?>" class="button-secondary yith-wcet-nofocus" value="<?php _e( 'Upload', 'yith-woocommerce-email-templates' ) ?>">
        <input type="button" id="yith-wcet-custom-logo-btn<?php echo $txt; ?>" class="button-secondary yith-wcet-nofocus" value="<?php _e( 'My Default Logo', 'yith-woocommerce-email-templates' ) ?>">
        <?php
        if ( $text == 'footer' ) {
            ?>
            <input type="button" id="yith-wcet-custom-logo-btn-footer-add-himg" class="button-secondary yith-wcet-nofocus" value="<?php _e( 'Use Header Logo', 'yith-woocommerce-email-templates' ) ?>">
            <?php
        }
    }
}


if ( !function_exists( 'yith_wcet_get_template' ) ) {
    function yith_wcet_get_template( $template, $args ) {
        extract( $args );
        include( YITH_WCET_TEMPLATE_PATH . '/' . $template );
    }
}