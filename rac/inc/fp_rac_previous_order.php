<?php
add_action('admin_head', 'fp_rac_previous_order_abandon_cart');

function fp_rac_previous_order_abandon_cart() {
    ?>
    <script type="text/javascript">
        jQuery(function () {
            jQuery("#update_order").click(function () {
                jQuery('.perloader_image').show();
                jQuery("#update_order").prop("disabled", true);
                var rac_order_status = Array();
                jQuery('input[name="order_status[]"]:checked').each(function (index) {
                    rac_order_status.push(jQuery(this).val());
                });
                
                var mycount;
                var previous_count;
                var order_time = jQuery("#order_time").val();
                var from_time = jQuery("#from_time").val();
                var to_time = jQuery("#to_time").val();

                var dataparam = ({
                    action: 'rac_add_old_order',
                    rac_order_time: order_time,
                    rac_from_time: from_time,
                    rac_to_time: to_time,
                    rac_order_status: rac_order_status

                });
                function getDataRAC(id) {

                    return jQuery.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        data: ({
                            action: 'rac_chunk_previous_order_list',
                            ids: id,
                            rac_order_status: rac_order_status,
                        }),
                        success: function (response) {
                            console.log(response);
                            if (response) {
                                previous_count = response.count;
                                jQuery('.perloader_image').hide();
                                if (previous_count > 0) {
                                    jQuery("#update_response").append(previous_count + " Orders found and added to Abandon List <br>");
                                    setTimeout(function () {
                                        location.reload()
                                    }, '3500');
                                }
                                else {
                                    jQuery("#update_response").append("No Orders found <br>");
                                    setTimeout(function () {
                                        location.reload()
                                    }, '3500');
                                }
                            }
                        },
                        dataType: 'json',
                        async: false
                    });

                }


                jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", dataparam,
                        function (response) {
                            console.log(response);
                            if (response !== 'success') {
                                var j = 1;
                                var i, j, temparray, chunk = "<?php echo get_option('rac_chunk_count_per_ajax',true); ?>";
                                for (i = 0, j = response.length; i < j; i += chunk) {
                                    temparray = response.slice(i, i + chunk);
                                    //console.log(temparray.length);
                                    getDataRAC(temparray);

                                    //
                                }
                                jQuery.when(getDataRAC('')).done(function (a1) {
                                    //                                    jQuery(getDataRAC().getXml());
                                    jQuery("#update_order").prop("disabled", false);
                                });
                            } else {
                                var newresponse = response.replace(/\s/g, '');
                                if (newresponse === 'success') {
                                    jQuery('.submit .button-primary').trigger('click');
                                }
                            }
                        }, 'json');
            });
        });
    </script>
    <?php
}

function fp_rac_get_list_of_ids_by_query() {
    if (isset($_POST['rac_order_time'])) {
        $order_statuses = $_POST['rac_order_status'];
        $from_time_array = explode("/", $_POST['rac_from_time']);
        $to_time_array = explode("/", $_POST['rac_to_time']);
        if ("all" != $_POST['rac_order_time']) {
            $date_query = array(
                array(
                    'after' => array(
                        'year' => $from_time_array[2],
                        'month' => $from_time_array[0],
                        'day' => $from_time_array[1],
                    ),
                    'before' => array(
                        'year' => $to_time_array[2],
                        'month' => $to_time_array[0],
                        'day' => $to_time_array[1],
                    ),
                    'inclusive' => true,
                ),
            );
        }

        if ("all" == $_POST['rac_order_time']) {
            $args = array('post_type' => 'shop_order', 'posts_per_page' => '-1', "post_status" => $order_statuses, 'fields' => 'ids', 'cache_results' => false);
        } else {
            $args = array('post_type' => 'shop_order', 'posts_per_page' => '-1', 'date_query' => $date_query, "post_status" => $order_statuses, 'fields' => 'ids', 'cache_results' => false);
        }
        $products = get_posts($args);
        delete_option('rac_abandon_previous_count');
        echo json_encode($products);
    }
    exit();
}

add_action('wp_ajax_rac_add_old_order', 'fp_rac_get_list_of_ids_by_query');

add_action('wp_ajax_rac_chunk_previous_order_list', 'fp_rac_add_old_order_byupdate');

function fp_rac_add_old_order_byupdate() {
    if (isset($_POST['rac_order_status']) && !empty($_POST['ids'])) {

        $looking_order_status = $_POST['rac_order_status'];
        global $wpdb;
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        global $woocommerce;
        $check_previous_data = get_option('rac_abandon_previous_count');
        $udated_count = 0;
        $the_query = $_POST['ids'];
        if (is_array($the_query) && !empty($the_query)) {
            foreach ($the_query as $each_query) {
                $order = new WC_Order($each_query);


                if ($order->user_id != '') {

                    $user_details = get_userdata($order->user_id);
                    $user_email = $order->billing_email;
                } else {
                    $user_email = $order->billing_email;
                }
                /*
                  if (get_option('rac_remove_carts') == 'yes') {


                  if (get_option('rac_remove_new') == 'yes') {

                  $wpdb->delete($table_name, array('email_id' => $user_email, 'cart_status' => 'NEW'));
                  }

                  if (get_option('rac_remove_abandon') == 'yes') {

                  $wpdb->delete($table_name, array('email_id' => $user_email, 'cart_status' => 'ABANDON'));
                  }
                  }

                 */
                $rac_order_place = get_post_meta($order->id, "rac_order_placed", true);
                $guest_cart = get_post_meta($order->id, "guest_cart", true);


                if (empty($rac_order_place) && empty($guest_cart)) {
//check to, not importing order whic are recovered and captured on place order
                    if (in_array($order->post->post_status, (array) $looking_order_status)) {

                        $already_added = get_post_meta($order->id, "old_order_updated", true);
                        if (empty($already_added)) {

                            $cart_details = maybe_serialize($order);
                            $user_id = "old_order";
                            $order_modified_time = strtotime($order->modified_date); //convert as unix timestamp, so it can be used in comparing even though it is dead old
                            $insert_data = $wpdb->insert($table_name, array('cart_details' => $cart_details, 'user_id' => $user_id, 'email_id' => $user_email, 'cart_abandon_time' => $order_modified_time, 'cart_status' => 'ABANDON'), array('%s'));
                            if ($insert_data) {
                                update_post_meta($order->id, "old_order_updated", "yes"); // this makes sure for no duplication
                                $udated_count++;
                                //$wpdb->flush();
                            }
                        }
                    }
                }
            }
        }

        update_option('rac_abandon_previous_count', $check_previous_data + $udated_count);
        //echo json_encode(array('count' => $udated_count));
        // echo "$udated_count orders are added to cart list";
    } else {
        echo json_encode(array('count' => get_option('rac_abandon_previous_count')));
    }
    exit();
}
