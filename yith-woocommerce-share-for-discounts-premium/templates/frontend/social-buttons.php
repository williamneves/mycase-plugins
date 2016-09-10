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

<?php if ( $social_params['facebook'] == 'yes' ) : ?>

    <div id="fb-root"></div>

    <?php if ( $social_params['facebook_type'] == 'like' || $social_params['facebook_type'] == 'both' ) : ?>
        <div class="ywsfd-social-button ywsfd-facebook">
            <div class="fb-like" data-layout="button" data-action="like" data-show-faces="false" data-share="false" data-href="<?php echo $social_params['sharing']['url'] ?>"></div>
        </div>
    <?php endif; ?>

    <?php if ( $social_params['facebook_type'] == 'share' || $social_params['facebook_type'] == 'both' ) : ?>
        <div class="ywsfd-social-button">
            <a href="javascript:fbShare('<?php echo $social_params['sharing']['url'] ?>')" class="ywsfd-facebook-share">
                <span class="fb-image"></span><span class="fb-text"><?php echo apply_filters( 'ywsfd_facebook_share_label', __( 'Share', 'yith-woocommerce-share-for-discounts' ) ) ?></span>
            </a>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php if ( $social_params['twitter'] == 'yes' ) : ?>

    <div class="ywsfd-social-button ywsfd-twitter">
        <a href="https://twitter.com/share" class="twitter-share-button" data-count="none" data-url="<?php echo $social_params['sharing']['url'] ?>" data-text="<?php echo $social_params['sharing']['message'] ?>" data-via="<?php echo $social_params['sharing']['twitter_username'] ?>">Tweet</a>
    </div>

<?php endif; ?>

<?php if ( $social_params['google'] == 'yes' ) : ?>

    <?php if ( $social_params['google_type'] == 'plusone' || $social_params['google_type'] == 'both' ) : ?>
        <div class="ywsfd-social-button ywsfd-google">
            <div class="g-plusone" data-size="medium" data-annotation="none" data-callback="gpCallback" data-href="<?php echo esc_url( $social_params['sharing']['url'] ) ?>"></div>
        </div>
    <?php endif; ?>

    <?php if ( $social_params['google_type'] == 'share' || $social_params['google_type'] == 'both' ) : ?>
        <div class="ywsfd-social-button ywsfd-google-share">
            <div class="g-plus" data-action="share" data-size="medium" data-annotation="none" data-href="<?php echo $social_params['sharing']['url'] ?>" data-onstartinteraction="gpShareCallback" data-onendinteraction="gpStopShareCallback"></div>
        </div>
    <?php endif; ?>

<?php endif; ?>