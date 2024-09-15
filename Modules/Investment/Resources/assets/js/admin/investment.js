'use strict';

$(".select2").select2({});

if ($('.content').find('#investment_list').length) {
    var sDate;
    var eDate;

    //Date range as a button
    $("#daterange-btn").daterangepicker(
        {
            ranges: {
                Today: [moment(), moment()],
                Yesterday: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                ],
                "Last 7 Days": [moment().subtract(6, "days"), moment()],
                "Last 30 Days": [moment().subtract(29, "days"), moment()],
                "This Month": [moment().startOf("month"), moment().endOf("month")],
                "Last Month": [
                    moment().subtract(1, "month").startOf("month"),
                    moment().subtract(1, "month").endOf("month"),
                ],
            },
            startDate: moment().subtract(29, "days"),
            endDate: moment(),
        },

        function (start, end) {
            var sessionDateFinal = sessionDate.toUpperCase();
            sDate = moment(start, "MMMM D, YYYY").format(sessionDateFinal);
            eDate = moment(end, "MMMM D, YYYY").format(sessionDateFinal);

            $("#startfrom").val(sDate);
            $("#endto").val(eDate);
            $("#daterange-btn span").html( "&nbsp;" + sDate +
                " - " + eDate
            );
        }
    );

    $(document).ready(function () {
        $("#daterange-btn").mouseover(function () {
            $(this).css("background-color", "white");
            $(this).css("border-color", "grey !important");
        });

        if (startDate == "") {
            $("#daterange-btn span").html(
                '<i class="fa fa-calendar"></i> &nbsp;&nbsp;' +
                dateRange
                
            );
        } else {
            $("#daterange-btn span").html(
                startDate +
                " - " +
                endDate
            );
        }

        $("#user_input").on("keyup keypress", function (e) {
            if (e.type == "keyup" || e.type == "keypress") {
                var user_input = $("form").find("input[type='text']").val();
                if (user_input.length === 0) {
                    $("#user_id").val("");
                    $("#error-user").html("");
                    $("form").find("button[type='submit']").prop("disabled", false);
                }
            }
        });

        $("#user_input").autocomplete({
            source: function (req, res) {
                if (req.term.length > 0) {
                    $.ajax({
                        url: url,
                        dataType: "json",
                        type: "get",
                        data: {
                            search: req.term,
                        },
                        success: function (response) {
                            $("form")
                                .find("button[type='submit']")
                                .prop("disabled", true);
                            if (response.status == "success") {
                                res(
                                    $.map(response.data, function (item) {
                                        return {
                                            id: item.user_id,
                                            first_name: item.first_name, 
                                            last_name: item.last_name, 
                                            value: item.first_name +
                                                " " + item.last_name, 
                                        };
                                    })
                                );
                            } else if (response.status == "fail") {
                                $("#error-user")
                                    .addClass("text-danger")
                                    .html(userError);
                            }
                        },
                    });
                } else {
                    $("#user_id").val("");
                }
            },
            select: function (event, ui) {
                var e = ui.item;

                $("#error-user").html("");
                $("#user_id").val(e.id);
                $("form").find("button[type='submit']").prop("disabled", false);
            },
            minLength: 0,
            autoFocus: true,
        });
    });

    $(document).ready(function () {
        // CSV
        $("#csv").on("click", function (event) {
            event.preventDefault();
            window.location = ADMIN_URL + "/investments/csv?startfrom=" + $("#startfrom").val() + "&endto=" + $("#endto").val() + "&status=" + $("#status").val() + "&currency=" + $("#currency").val() + "&payment_methods=" + $("#payment_methods").val() + "&user_id=" + $("#user_id").val();;
        });

        // PDF
        $("#pdf").on("click", function (event) {
            event.preventDefault();
            window.location = ADMIN_URL + "/investments/pdf?startfrom=" +
                $("#startfrom").val() + "&endto=" + $("#endto").val() + "&status=" + $("#status").val() + "&currency=" + $("#currency").val() + "&payment_methods=" + $("#payment_methods").val() + "&user_id=" + $("#user_id").val();
        });
    
        // Profit approve
        $("#approve").on("click", function (event) {
            event.preventDefault();
            Swal.fire({
                title: alertTitle,
                text: alertText,
                icon: 'warning',
                width: 600,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: alertConfirm,
                cancelButtonText: alertCancel,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        headers:
                        {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: "POST",
                        url: approveUrl,
                        beforeSend: function () {
                            swal(waitText.replace( /&#039;/g, "'"), waitingText, {
                                closeOnClickOutside: false,
                                closeOnEsc: false,
                                buttons: false,
                            });
                        },
                    })
                    .done(function(response)
                    {
                        swal.close();
                        Swal.fire(
                            response.title,
                            response.message,
                            response.alert
                        ).then((result) => {
                            if (result.isConfirmed) {
                                if (response.alert == 'verification needed') {
                                    window.location.href = ADMIN_URL + "/addon/verify/SU5WRVNUTUVOVF9TRUNSRVQ=";
                                } else {
                                    window.location.reload();
                                }
                            }
                        });
                    })
                    .fail(function(error)
                    {
                        swal.close();
                        Swal.fire(
                            failedText,
                            JSON.parse(error.responseText).message,
                            'error'
                        ).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    });
                }
            })
        });

    });

}

if ($('.content').find('#investment_detail').length) {
    $("#approve, #cancel").on("click", function (event) {
        event.preventDefault();
        var id = $(this).attr("data-id");
        var investmentStatus = $(this).attr("data-status");
        var transactionType = 'Investment';
        Swal.fire({
            title: alertTitle,
            icon: 'warning',
            width: 600,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: alertConfirm,
            cancelButtonText: alertCancel,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    headers:
                    {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: url,
                    data: {
                        'id':id,
                        'transaction_type': transactionType,
                        'status': investmentStatus,
                        'user_id': userId,
                        'currency_id': currencyId,
                        "ajax": true
                    },
                    beforeSend: function () {
                        swal(waitText.replace( /&#039;/g, "'"), waitingText, {
                            closeOnClickOutside: false,
                            closeOnEsc: false,
                            buttons: false,
                        });
                    },
                })
                .done(function(response)
                {
                    swal.close();
                    Swal.fire(
                        response.title,
                        response.message,
                        response.alert
                    ).then((result) => {
                        if (result.isConfirmed) {
                            if (response.alert == 'verification needed') {
                                    window.location.href = ADMIN_URL + "/addon/verify/SU5WRVNUTUVOVF9TRUNSRVQ=";
                            } else {
                                window.location.reload();
                            }
                        }
                    });
                })
                .fail(function(error)
                {
                    swal.close();
                    Swal.fire(
                        failedText,
                        JSON.parse(error.responseText).message,
                        'error'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                });
            }
        })
    });
}
