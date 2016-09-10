function set_exportation_mode(manual) {
    if (manual) {
        jQuery('.scheduled-exportation').hide();
        jQuery(".manual-exportation").show();
        jQuery("#ywqe_export_on_date").prop('required', false);
        jQuery("#ywqe_export_on_time").prop('required', false);

    }
    else {
        jQuery('.scheduled-exportation').show();
        jQuery(".manual-exportation").hide();
        jQuery("#ywqe_export_on_date").prop('required', true);
        jQuery("#ywqe_export_on_time").prop('required', true);

    }
}

function timeToSeconds(time) {
    time = time.split(/:/);
    return time[0] * 3600 + time[1] * 60;
}

function process(date) {
    var parts = date.split("-");
    return new Date(parts[2], parts[1] - 1, parts[0]);
}

jQuery(document).ready(function ($) {

    set_exportation_mode(1);

    $(document).on("click", "#cb-select", function () {
        $("input[type=checkbox]").prop("checked", $("#cb-select").prop("checked"));
    });

    $('div.exportation-job-settings .date-picker').datepicker({
        dateFormat: 'dd-mm-yy'
    });

    $(document).on('click', '#ywqe_schedule_exportation', function () {
        if ($(this).prop('checked')) {
            set_exportation_mode(0);
        }
        else {
            set_exportation_mode(1);
        }
    });

    $(document).on('click', '.export-items', function () {
        var count = $("input[type=checkbox].export-items:checked").length;
        $('#start-now').prop('disabled', count == 0);
    });

    /**
     * Check if start date and end date of the exportation interval is valid
     */
    $(document).on('click', '#start-now', function (e) {

        $("div.date-error").remove();
        $('#ywqe_export_start_date').css("background-color", "inherit");
        $('#ywqe_export_end_date').css("background-color", "inherit");
        $('#ywqe_export_on_date').css("background-color", "inherit");
        $('#ywqe_export_on_time').css("background-color", "inherit");

        if ($("#ywqe_schedule_exportation").prop("checked")) {
            //  date/time of scheduling must be in the future
            if ($('#ywqe_export_on_date').val() && $("#ywqe_export_on_time").val()) {

                //  ********************************************************
                var user_date = process($('#ywqe_export_on_date').val());
                var user_time = $("#ywqe_export_on_time").val();
                user_time = user_time.split(/:/);
                user_date.setHours(user_time[0], user_time[1], 0, 0);

                var currentDate = new Date();

                var user_total = user_date.getTime();
                var system_total = currentDate.getTime();

                if (user_total <= system_total) {
                    $("#ywqe_export_on_date").parent().prepend('<div class="date-error">' + messages.schedulation_time + '</span>');
                    $('#ywqe_export_on_date').css("background-color", "#f85454");
                    $('#ywqe_export_on_time').css("background-color", "#f85454");
                    return false;
                }
            }
        }
        else {
            if ($('#ywqe_export_start_date').val() && $("#ywqe_export_end_date").val()) {

                if ($.datepicker.parseDate("dd-mm-yy", $("#ywqe_export_start_date").val()) > $.datepicker.parseDate("dd-mm-yy", $("#ywqe_export_end_date").val())) {
                    $("#ywqe_export_start_date").parent().prepend('<div class="date-error">' + messages.valid_interval + '</div>');
                    $('#ywqe_export_start_date').css("background-color", "#f85454");
                    $('#ywqe_export_end_date').css("background-color", "#f85454");

                    return false;
                }
            }
        }
    });
});