"use strict";

if ($('.content').find('#crypto_exchange_list').length) {

    $(".select2").select2({});

    var sDate;
    var eDate;

    //Date range as a button
    $('#daterange-btn').daterangepicker({
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                    'month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        },
        function(start, end) {
            var sessionDate = dateFormateType;
            var sessionDateFinal = sessionDate.toUpperCase();

            sDate = moment(start, 'MMMM D, YYYY').format(sessionDateFinal);
            $('#startfrom').val(sDate);

            eDate = moment(end, 'MMMM D, YYYY').format(sessionDateFinal);
            $('#endto').val(eDate);

            $('#daterange-btn span').html('&nbsp;' + sDate + ' - ' + eDate +
                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        }
    )

    $(document).ready(function() {
        
        var startDate = formDate;
        var endDate = toDate;

        if (startDate == '') {
            $('#daterange-btn span').html('<i class="fa fa-calendar"></i>'+ ' ' + pickDateRange);
        } else {
            $('#daterange-btn span').html(startDate + ' - ' + endDate + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        }

        $("#user_input").on('keyup keypress', function(e) {
            if (e.type == "keyup" || e.type == "keypress") {
                var userInput = $('form').find("input[type='text']").val();
                if (userInput.length === 0) {
                    $('#user_id').val('');
                    $('#error-user').html('');
                    $('form').find("button[type='submit']").prop('disabled', false);
                }
            }
        });

        $('#user').autocomplete({
            source: function(req, res) {
                if (req.term.length > 0) {
                    $.ajax({
                        url: ajaxUrl,
                        dataType: 'json',
                        type: 'get',
                        data: {
                            search: req.term
                        },
                        success: function(response) {

                            $('form').find("button[type='submit']").prop('disabled',
                                true);

                            if (response.status == 'success') {
                                res($.map(response.data, function(item) {
                                    return {
                                        user_id: item
                                        .user_id,
                                        first_name: item
                                        .first_name,
                                        last_name: item
                                        .last_name,
                                        value: item.first_name + ' ' + item
                                            .last_name
                                    }
                                }));
                            } else if (response.status == 'fail') {
                                $('#error-user').addClass('text-danger').html(
                                    userDoesntExist);
                            }
                        }
                    })
                } else {
                    $('#user_id').val('');
                }
            },
            select: function(event, ui) {
                var e = ui.item;

                $('#error-user').html('');

                $('#user_id').val(e.user_id);

                $('form').find("button[type='submit']").prop('disabled', false);
            },
            minLength: 0,
            autoFocus: true
        });
    });

    // csv
    $(document).ready(function() {
        $('#csv').on('click', function(event) {
            event.preventDefault();

            var startfrom = $('#startfrom').val();
            var endto = $('#endto').val();

            var status = $('#status').val();

            var currency = $('#currency').val();

            var useId = $('#user_id').val();

            window.location = csvUrl + "?startfrom=" + startfrom +
                "&endto=" + endto +
                "&status=" + status +
                "&currency=" + currency +
                "&user_id=" + useId;
        });
    });

    // pdf
    $(document).ready(function() {
        $('#pdf').on('click', function(event) {
            event.preventDefault();

            var startfrom = $('#startfrom').val();
            var endto = $('#endto').val();

            var status = $('#status').val();

            var currency = $('#currency').val();

            var userId = $('#user_id').val();

            window.location = pdfUrl + "?startfrom=" + startfrom +
                "&endto=" + endto +
                "&status=" + status +
                "&currency=" + currency +
                "&user_id=" + userId;
        });
    });

}

if ($('.content').find('#transaction_edit').length) {
    $(".select2").select2({});
    $(document).on('submit', '#exchange_form', function() {
        $("#exchange_edit").attr("disabled", true);
        $('#cancel_anchor').attr("disabled", "disabled");
        $(".fa-spin").removeClass("d-none");
        $("#exchange_edit_text").text(updateText);    
    });
}

if ($('.content').find('#crypto_module_settings').length) {
    $(document).on('submit', '#crypto_settings', function() {
        $(".fa-spin").removeClass("d-none");
        $("#preference-submit-text").text(updateText);
    });
}



