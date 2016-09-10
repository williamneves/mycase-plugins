<?php

defined( 'ABSPATH' ) or exit;

/*
 *	Settings
 */

global $wpdb;

if ( isset( $_POST['act'] ) && $_POST['act'] == 'save' ) {
    update_option( 'yith-wcch-default-sender-name', $_POST['default_sender_name'] );
    update_option( 'yith-wcch-default-sender-email', $_POST['default_sender_email'] );
    update_option( 'yith-wcch-default_save_admin_session', $_POST['default_save_admin_session'] );
    update_option( 'yith-wcch-hide_users_with_no_orders', $_POST['hide_users_with_no_orders'] );
    update_option( 'yith-wcch-results_per_page', $_POST['results_per_page'] );
}

?>

<div id="yith-woocommerce-customer-history">
	<div id="settings" class="wrap">

		<h1><?php echo __( 'Settings', 'yith-woocommerce-customer-history' ); ?></h1>
        <p><?php echo __( 'Configure the plugin options.', 'yith-woocommerce-customer-history' ); ?></p>

        <form id="group-form" action="admin.php?page=yith-wcch-settings.php" method="post">

            <input type="hidden" name="act" value="save">

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="results_per_page"><?php echo __( 'Results per page', 'yith-woocommerce-customer-history' ); ?></label></th>
                        <td><input name="results_per_page" type="number" class="small-text" placeholder="50" value="<?php echo get_option( 'yith-wcch-results_per_page' ); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_save_admin_session"><?php echo __( 'Save "admin" sessions?', 'yith-woocommerce-customer-history' ); ?></label></th>
                        <td>
                            <select name="default_save_admin_session">
                                <option value="0"><?php echo __( 'No', 'yith-woocommerce-customer-history' ); ?></option>
                                <option value="1"<?php echo get_option('yith-wcch-default_save_admin_session') ? ' selected="selected"' : ''; ?>><?php echo __( 'Yes', 'yith-woocommerce-customer-history' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hide_users_with_no_orders"><?php echo __( 'Hide users with no orders?', 'yith-woocommerce-customer-history' ); ?></label></th>
                        <td>
                            <select name="hide_users_with_no_orders">
                                <option value="0"><?php echo __( 'No', 'yith-woocommerce-customer-history' ); ?></option>
                                <option value="1"<?php echo get_option('yith-wcch-hide_users_with_no_orders') ? ' selected="selected"' : ''; ?>><?php echo __( 'Yes', 'yith-woocommerce-customer-history' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_sender_name"><?php echo __( 'Default Sender Name', 'yith-woocommerce-customer-history' ); ?></label></th>
                        <td><input name="default_sender_name" type="text" class="regular-text" placeholder="<?php echo get_bloginfo('name'); ?>" value="<?php echo get_option( 'yith-wcch-default-sender-name' ); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_sender_email"><?php echo __( 'Default Sender Email', 'yith-woocommerce-customer-history' ); ?></label></th>
                        <td><input name="default_sender_email" type="text" class="regular-text" placeholder="<?php echo get_bloginfo('admin_email'); ?>" value="<?php echo get_option( 'yith-wcch-default-sender-email' ); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><input type="submit" value="<?php echo __( 'Save', 'yith-woocommerce-customer-history' ); ?>" class="button button-primary button-large"></th>
                        <td></td>
                    </tr>
                </tbody>
            </table>

        </form>

	</div>
</div>