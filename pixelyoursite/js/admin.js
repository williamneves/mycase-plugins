jQuery(function($){

    /* Tabs Management */
    $('.pys-menu li').on('click', function(event) {

        event.preventDefault();
        var tab = $(this).attr('id').replace('pys-menu-', '');
        $('.pys-menu li').removeClass('nav-tab-active selected');
        $(this).addClass('nav-tab-active selected');

        $('.pys-panel').hide();
        $('#pys-panel-'+tab).show();

        // remember active tab
        $('input[name=active_tab]').val(tab);

    });

    /* Bulk delete Std events */
    $('.btn-delete-std-events').on('click', function(e){
        e.preventDefault();
        $(this).addClass('disabled');

        // add all selected rows to ids array
        var ids = [];
        $.each( $('.std-event-check'), function(index, check) {
            if ( $(check).prop('checked') == true ) {
                ids.push( $(check).data('id') );
            }
        });

        $.ajax({
            url: pys.ajax,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'pys_delete_std_event',
                ids: ids
            }
        })
            .done(function(data) {
                //console.log(data);
                location.reload(true);
            });

    });

    /* Delete single Std Event */
    $('.btn-delete-std-event').on('click', function(e){
        e.preventDefault();
        $(this).addClass('disabled');

        var ids = [];
        ids.push( $(this).data('id') );

        $.ajax({
            url: pys.ajax,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'pys_delete_std_event',
                ids: ids
            }
        })
            .done(function(data) {
                //console.log(data);
                location.reload(true);
            });

    });

    /* Woo events toggle */
    // set main switcher state on page load
    if($('.woo-option').length == $('.woo-option:checked').length) {
        $('.woo-events-toggle').prop('checked', 'checked');
    }

    /* Woo events toggle */
    // add multiple select / deselect functionality
    $('.woo-events-toggle').click(function(){
        var options = $('.woo-option');
        options.prop('checked', this.checked);
    });

    /* Woo events toggle */
    // if all checkbox are selected, check the selectall checkbox and viceversa
    $('.woo-option').click(function(){

        if($('.woo-option').length == $('.woo-option:checked').length) {
            $('.woo-events-toggle').prop('checked', 'checked');
        } else {
            $('.woo-events-toggle').removeAttr('checked');
        }

    });

}); /* Dom Loaded */