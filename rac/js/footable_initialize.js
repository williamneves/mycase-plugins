jQuery(document).ready(function () {
    jQuery('.rac_email_template_table').footable();
    jQuery('#rac_pagination').val(10);
    jQuery('#rac_pagination').on('change', function () {
        jQuery('.rac_email_template_table').data('page-size', this.value);
        jQuery('.rac_email_template_table').trigger('footable_initialized');
        console.log("later");
    });

//    jQuery('#rac_pagination').change(function (e) {
//                        e.preventDefault();
//                        var pageSize = jQuery(this).val();
//                        alert(pageSize);
//                        jQuery('.footable').data('page-size', pageSize);
//                        jQuery('.footable').trigger('footable_initialized');
//                         console.log("later");
//    });

//                    
    jQuery('.rac_email_template_table_abandon').footable();
    jQuery('#rac_pagination_cart').val(10);
    jQuery('#rac_pagination_cart').on('change', function () {
        jQuery('.rac_email_template_table_abandon').data('page-size', this.value);
        jQuery('.rac_email_template_table_abandon').trigger('footable_initialized');
        console.log("later");
    });
//
    jQuery('.rac_email_logs_table').footable();
    jQuery('#rac_pagination_logs').val(10);
    jQuery('#rac_pagination_logs').on('change', function () {
        jQuery('.rac_email_logs_table').data('page-size', this.value);
        jQuery('.rac_email_logs_table').trigger('footable_initialized');
        console.log("later");
    });
//
    //delete function for email template
    jQuery('.rac_email_template_table').on('click', '.rac_delete', function (e) {
        // var confirms = confirm("Are you sure want to delete? Cannot Undone this Operation once it delete! ");
       // if (confirms === true) {
        e.preventDefault();
        var row_id = jQuery(this).data('id');
        console.log(row_id);
        var footable = jQuery('.rac_email_template_table').data('footable');
        var row = jQuery(this).parents('tr:first');
        footable.removeRow(row);
        var data = {
            row_id: row_id,
            action: "rac_delete_email_template"
        }
        jQuery.ajax({type: "POST",
            url: ajaxurl,
            data: data}).done(function (res) {
//j
        });
  //  }
    });

     //adding page all/ page deselect all for cart list
        jQuery('#rac_page_select').click(function (e) {
            e.preventDefault();
             jQuery('.footable-visible>.rac_checkboxes').each(function() {
                 var parent_style = jQuery(this).parent().parent().css('display');
               if(parent_style!=='none') {
                jQuery(this).prop('checked', true);
                console.log(jQuery(this).attr('data-racid'));
               }
         });
        });
        jQuery('#rac_page_deselect').click(function (e) {
            e.preventDefault();
            //jQuery('.footable-visible>.rac_checkboxes').prop('checked', false);
            jQuery('.footable-visible>.rac_checkboxes').each(function() {
               var parent_style = jQuery(this).parent().parent().css('display');
               if(parent_style!=='none') {
                jQuery(this).prop('checked', false);
                console.log(jQuery(this).attr('data-racid'));
               }
         });
        });
    //

    //adding select all/deselect all for cart list
    jQuery('#rac_sel').click(function (e) {
        e.preventDefault();
        jQuery('.rac_checkboxes').prop('checked', true);
    });
    jQuery('#rac_desel').click(function (e) {
        e.preventDefault();
        jQuery('.rac_checkboxes').prop('checked', false);
    });
//
    jQuery('.rac_selected_del').click(function (e) {
        // var confirms = confirm("Are you sure want to delete? Cannot Undone this Operation once it delete! ");
       // if (confirms === true) {
      
        e.preventDefault();
        var selection_for_delete = new Array();
        jQuery('.rac_checkboxes').each(function (num) {
            if (jQuery(this).prop('checked')) {
                selection_for_delete.push(jQuery(this).data('racid'));
                jQuery(this).parents('tr:first').css('display', 'none');
            }
        });
        
      
        var data = ({
            action: 'deletecartlist',
            listids: selection_for_delete,
            deletion:jQuery(this).data('deletion'),
        });
        jQuery.post(ajaxurl, data,
                function (response) {
//location.reload(true); 
                });
        // console.log(jQuery('.bis_mas_checkboxes'));
        console.log(selection_for_delete);
   // }
    });
//
    //delete function for each cart
    jQuery('.rac_email_template_table_abandon').on('click', '.button.rac_check_indi', function (e) {
        
      //      var confirms = confirm("Are you sure want to delete? Cannot Undone this Operation once it delete! ");
       // if (confirms === true) {
        e.preventDefault();
        var row_id = jQuery(this).data('racdelid');

        console.log(row_id);
        var footable = jQuery('.rac_email_template_table_abandon').data('footable');
        var row = jQuery(this).parents('tr:first');
        footable.removeRow(row);
        var data = {
            row_id: row_id,
            action: "rac_delete_individual_list",
            deletion: jQuery(this).data('deletion'),
        }
        jQuery.ajax({type: "POST",
            url: ajaxurl,
            data: data}).done(function (res) {

        });
    //}
    });
//delete mail log
    jQuery('#rac_selected_del_log').click(function (e) {
            //var confirms = confirm("Are you sure want to delete? Cannot Undone this Operation once it delete! ");
       // if (confirms === true) {
        e.preventDefault();
        var selection_for_delete = new Array();
        jQuery('.rac_checkboxes').each(function (num) {
            if (jQuery(this).prop('checked')) {
                selection_for_delete.push(jQuery(this).data('raclogid'));
                jQuery(this).parents('tr:first').css('display', 'none');
            }
        });

        var data = ({
            action: 'deletemaillog',
            listids: selection_for_delete,
        });
        jQuery.post(ajaxurl, data,
                function (response) {

                });
        // console.log(jQuery('.bis_mas_checkboxes'));
        console.log(selection_for_delete);
   // }
    });
//
    //delete function for each log
    jQuery('.rac_email_logs_table').on('click', '.button.rac_check_indi', function (e) {

       // var confirms = confirm("Are you sure want to delete? Cannot Undone this Operation once it delete! ");
       // if (confirms === true) {

            e.preventDefault();
            var row_id = jQuery(this).data('raclogdelid');
            console.log(row_id);
            var footable = jQuery('.rac_email_logs_table').data('footable');
            var row = jQuery(this).parents('tr:first');
            footable.removeRow(row);
            var data = {
                row_id: row_id,
                action: "rac_delete_individual_log"
            }
            jQuery.ajax({type: "POST",
                url: ajaxurl,
                data: data}).done(function (res) {

            });
       // }
    });

    //Start/Stop Sending Mail
    jQuery('.rac_email_template_table_abandon').on('click', '.rac_mail_sending', function (e) {
        e.preventDefault();
        var row_id = jQuery(this).data('racmoptid');
        var obj = jQuery(this);
        console.log(row_id);
        jQuery(this).attr('disabled', true);
        var current_state = jQuery(this).data('currentsate');
        console.log(obj);
        var data = {
            row_id: row_id,
            action: "rac_start_stop_mail",
            current_state: current_state,
        }
        jQuery.ajax({type: "POST", url: ajaxurl, data: data}).done(function (response) {
            console.log(response);
            obj.data('currentsate', response);
            if (response == 'SEND') {
                obj.text('Stop Mailing');
            } else {
                obj.text('Start Mailing');
            }
            jQuery(obj).attr('disabled', false);
        });

    });
//
    //ACTIVATE/DEACTIVATE function for email template
    jQuery('.rac_email_template_table').on('click', '.rac_mail_active', function (e) {
        e.preventDefault();
        var row_id = jQuery(this).data('racmailid');
        var obj = jQuery(this);
        jQuery(obj).attr('disabled', true);
        var status = jQuery(this).data('currentstate');
        var data = {
            row_id: row_id,
            status: status,
            action: "rac_email_template_status"
        }
        jQuery.ajax({type: "POST",
            url: ajaxurl,
            data: data}).done(function (res) {
            obj.data('currentstate', res);
            if (res == "ACTIVE") {
                obj.text("Deactivate");
            } else {
                obj.text("Activate");
            }
            jQuery(obj).attr('disabled', false);

        });
    });

    jQuery('.rac_shortcodes_info').footable();

});