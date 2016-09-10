<?php
/**
 * MY ACCOUNT ADMIN NEW ENDPOINT
 */
if ( ! defined( 'YITH_WCMAP' ) ) {
    exit;
} // Exit if accessed directly

?>

<div class="new-endpoint-form" style="display: none;">
    <table class="form-table">
        <tbody>
            <tr>
                <th>
                    <label for="yith-wcmap-new-endpoint"><?php _e( 'Endpoint Name', 'yith-woocommerce-customize-myaccount-page' ); ?></label>
                </th>
                <td>
                    <input type="text" id="yith-wcmap-new-endpoint" name="yith-wcmap-new-endpoint" value="">
                    <span class="checking"></span>
                    <p class="error-msg"></p>
                </td>
            </tr>
        </tbody>
    </table>
    <input type="hidden" name="yith-wcmap-order-endpoints" class="yith-wcmap-order-endpoints" value="">
    <input type="submit" class="button-secondary add-endpoint-button" value="<?php _e( 'Add Endpoint' ) ?>" disabled="disabled">
</div>
