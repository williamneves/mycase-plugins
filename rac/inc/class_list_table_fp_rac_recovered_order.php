<?php

// Integrate WP List Table for Recover Abandon Cart

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class FP_List_Table_RAC extends WP_List_Table {

    // Prepare Items
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $from_date = '';
        $to_date = '';

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();
        
        if(isset($_POST['filter_by_date_recovered_order_ids'])){
            $url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracrecoveredorderids', 'from_date' => $_POST['recovered_order_ids_from_date'], 'to_date' => $_POST['recovered_order_ids_to_date']), admin_url('admin.php')));
            wp_safe_redirect($url);
        }
        if(isset($_GET['from_date']) && isset($_GET['to_date'])){
            $from_date = $_GET['from_date'];
            $to_date = $_GET['to_date'];
        }
        
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="fromDate">From Date:</label> <input type="text"  class="rac_date" id="recovered_order_ids_from_date"  name="recovered_order_ids_from_date" value="'.$from_date.'" />';

        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="toDate">To Date:</label> <input type="text" class="rac_date" id="recovered_order_ids_to_date" name="recovered_order_ids_to_date" value="'.$to_date.'" />';

        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="filter_by_date_recovered_order_ids button-primary" id="filter_by_date_recovered_order_ids" name="filter_by_date_recovered_order_ids" value=" Filter " />';

        $data = $this->table_data();
        

        if (isset($_REQUEST['s'])) {
            $searchvalue = $_REQUEST['s'];
            $keyword = "/$searchvalue/";

            $newdata = array();
            foreach ($data as $eacharray => $value) {
                $searchfunction = preg_grep($keyword, $value);
                if (!empty($searchfunction)) {
                    $newdata[] = $data[$eacharray];
                }
            }
            usort($newdata, array(&$this, 'sort_data'));

            $perPage = 10;
            $currentPage = $this->get_pagenum();
            $totalItems = count($newdata);

            $this->set_pagination_args(array(
                'total_items' => $totalItems,
                'per_page' => $perPage
            ));

            $newdata = array_slice($newdata, (($currentPage - 1) * $perPage), $perPage);

            $this->_column_headers = array($columns, $hidden, $sortable);

            $this->items = $newdata;
        } else {
            usort($data, array(&$this, 'sort_data'));

            $perPage = 10;
            $currentPage = $this->get_pagenum();
            $totalItems = count($data);

            $this->set_pagination_args(array(
                'total_items' => $totalItems,
                'per_page' => $perPage
            ));

            $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

            $this->_column_headers = array($columns, $hidden, $sortable);

            $this->items = $data;
        }
    }

    public function get_columns() {
        $columns = array(
            // 'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'sno' => __('S.No', 'recoverabandoncart'),
            'orderid' => __('Order ID', 'recoverabandoncart'),
            'amount' => __('Recover Sale Total', 'recoverabandoncart'),
            'date' => __('Date', 'recoverabandoncart'),
        );

        return $columns;
    }

    public function get_hidden_columns() {
        return array();
    }

    public function get_sortable_columns() {
        return array(
            'amount' => array('amount', false),
            'sno' => array('sno', false),
            'date' => array('date', false),
        );
    }

    private function table_data() {
        $data = array();
        $i = 1;
        if(isset($_GET['from_date']) && isset($_GET['to_date'])){
            $from_date = $_GET['from_date'];
            $to_date = $_GET['to_date'];
            $fromdate = strtotime($from_date . ' 00:00:00');
            $todate = strtotime($to_date . ' 23:59:59');
        }

        $get_list_orderids = (array) array_filter(get_option('fp_rac_recovered_order_ids') ? get_option('fp_rac_recovered_order_ids') : array());
        if (is_array($get_list_orderids) && (!empty($get_list_orderids))) {
            foreach ($get_list_orderids as $key => $value) {
                if(isset($_GET['from_date']) && isset($_GET['to_date'])){
                    $entry_date = strtotime($value['date']);
                    if (($from_date != "") && ($to_date != "")) {
                        if($entry_date >= $fromdate && $entry_date <=$todate){
                            $data[] = array(
                                'sno' => $i,
                                'orderid' => "<a href=" . admin_url('post.php?post=' . $value["order_id"] . '&action=edit') . ">#" . $value['order_id'] . "</a>",
                                'amount' => self::format_price($value['order_total']),
                                'date' => $value['date'],
                            );
                            $i++;
                        }
                    }
                    elseif (($from_date != "") && ($to_date == "")) {
                        if($entry_date >= $fromdate){
                            $data[] = array(
                                'sno' => $i,
                                'orderid' => "<a href=" . admin_url('post.php?post=' . $value["order_id"] . '&action=edit') . ">#" . $value['order_id'] . "</a>",
                                'amount' => self::format_price($value['order_total']),
                                'date' => $value['date'],
                            );
                            $i++;
                        }
                    }
                    elseif (($from_date == "") && ($to_date != "")) {
                        if($entry_date <=$todate){
                            $data[] = array(
                                'sno' => $i,
                                'orderid' => "<a href=" . admin_url('post.php?post=' . $value["order_id"] . '&action=edit') . ">#" . $value['order_id'] . "</a>",
                                'amount' => self::format_price($value['order_total']),
                                'date' => $value['date'],
                            );
                            $i++;
                        }
                    }
                    else{
                        $data[] = array(
                            'sno' => $i,
                            'orderid' => "<a href=" . admin_url('post.php?post=' . $value["order_id"] . '&action=edit') . ">#" . $value['order_id'] . "</a>",
                            'amount' => self::format_price($value['order_total']),
                            'date' => $value['date'],
                        );
                        $i++;
                    }
                }else{
                    $data[] = array(
                        'sno' => $i,
                        'orderid' => "<a href=" . admin_url('post.php?post=' . $value["order_id"] . '&action=edit') . ">#" . $value['order_id'] . "</a>",
                        'amount' => self::format_price($value['order_total']),
                        'date' => $value['date'],
                    );
                    $i++;
                }
            }
        }

        return $data;
    }

    public static function format_price($price) {
        if (function_exists('woocommerce_price')) {
            return woocommerce_price($price);
        } else {
            return wc_price($price);
        }
    }

    public function column_id($item) {
        return $item['sno'];
    }

    public function column_default($item, $column_name) {

        switch ($column_name) {

            default:
                return $item[$column_name];
        }
    }

    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="id[]" value="%s" />', $item['orderid']
        );
    }

    private function sort_data($a, $b) {

        $orderby = 'sno';
        $order = 'asc';

        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strnatcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }

}
