<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

?>

<?php if ( $social_params['email'] == 'yes' ) : ?>

    <div class="ywsfd-social-button">
        <div class="ywsfd-email">
            <i></i>
            <?php _e( 'Send to a friend', 'yith-woocommerce-share-for-discounts' ) ?>
        </div>
    </div>
    <div class="ywsfd-email-form-wrapper">

            <p class="form-row form-row-wide ywsfd-validate-required ywsfd-validate-email">
                <label for="ywsfd_friend_email"><?php _e( 'Your friend email', 'yith-woocommerce-share-for-discounts' ); ?>
                    <span class="required">*</span>
                </label>
                <input type="text" class="input-text" name="ywsfd_friend_email" id="ywsfd_friend_email" />
            </p>

            <p class="form-row form-row-wide ywsfd-validate-required ywsfd-validate-email">
                <label for="ywsfd_user_email"><?php _e( 'Your email', 'yith-woocommerce-share-for-discounts' ); ?>
                    <span class="required">*</span>
                </label>
                <input type="text" class="input-text" name="ywsfd_user_email" id="ywsfd_user_email" />
            </p>

            <p class="form-row form-row-wide ywsfd-validate-required">
                <label for="ywsfd_message"><?php _e( 'Message', 'yith-woocommerce-share-for-discounts' ); ?>
                    <span class="required">*</span>
                </label>
                <textarea class="input-text input-textarea" name="ywsfd_message" id="ywsfd_message" rows="2" cols="5"><?php echo $social_params['sharing']['message'] ?></textarea>
            </p>

            <p class="form-row form-row-wide">
                <?php wp_nonce_field( 'ywsfd-send_friend_mail', 'ywsfd_wpnonce', false ); ?>
                <input type="hidden" name="ywsfd_post_id" id="ywsfd_post_id" value="<?php echo $social_params['sharing']['post_id'] ?>">
                <input type="hidden" name="ywsfd_sharing_url" id="ywsfd_sharing_url" value="<?php echo $social_params['sharing']['url'] ?>">
                <input type="button" class="button ywsfd-email" name="ywsfd_email" id="ywsfd_email" value="<?php _e( 'Send email', 'yith-woocommerce-share-for-discounts' ); ?>" />
            </p>

            <div class="clear"></div>

    </div>

<?php endif; ?>