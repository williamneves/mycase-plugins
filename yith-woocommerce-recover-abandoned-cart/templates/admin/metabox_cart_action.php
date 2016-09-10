<?php
/**
 * YITH WooCommerce Recover Abandoned Cart Content metabox template
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */

?>
<table class="yith-ywrac-info-cart" cellspacing="20">
    <tbody>
        <tr>
            <th><?php _e('Email sent:','ywrac') ?></th>
            <td class="ywrac_email_status"><?php echo $email_sent ?></td>
        </tr>

        <tr>
            <th><?php _e('Email action:','ywrac') ?></th>
            <td><?php echo '<input type="button" id="sendemail" class="ywrac_send_email button action"  value="' . __( 'Send email', 'ywrac' ) . '" data-id="'.$cart_id.'">' ?></td>
        </tr>
    </tbody>
</table>