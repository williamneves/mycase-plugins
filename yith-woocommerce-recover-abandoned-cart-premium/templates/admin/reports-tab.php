<div class="wrap">
    <h2><?php _e('Reports', 'yith-woocommerce-recover-abandoned-cart') ?> </h2>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <table class="ywrac-reports" cellpadding="10" cellspacing="0">
                    <tbody>
                        <tr>
                            <th width="20%"><?php _e('Abandoned Carts','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo $abandoned_carts_counter ?></td>
                        </tr>

                        <tr>
                            <th><?php _e('Emails Sent','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo $email_sent_counter ?></td>
                        </tr>

                        <tr>
                            <th><?php _e('Clicks From Emails','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo $email_clicks_counter ?></td>
                        </tr>

                        <tr>
                            <th><?php _e('Recovered Carts','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo $recovered_carts ?></td>
                        </tr>

                        <tr>
                            <th><?php _e('Total Amount Recovered','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo wc_price($total_amount) ?></td>
                        </tr>

                        <tr>
                            <th><?php _e('Rate Conversion','yith-woocommerce-recover-abandoned-cart') ?></th>
                            <td><?php echo $rate_conversion ?> %</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
