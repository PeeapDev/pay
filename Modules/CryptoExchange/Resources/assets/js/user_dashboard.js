"use strict";

function restrictNumberToPrefdecimalOnSendInput(e)
{
    var  transactionType = $("#from_type").val();

    var type = (transactionType == 'crypto_buy') ? 'fiat' : 'crypto';

    restrictNumberToPrefdecimal(e, type);
}

function restrictNumberToPrefdecimalOnReceiveInput(e)
{
    var  transactionType = $("#from_type").val();

    var type = (transactionType == 'crypto_sell') ? 'fiat' : 'crypto';

    restrictNumberToPrefdecimal(e, type);
}

function formatNumberToPrefDecimal(num = 0)
{
    let decimalFormat = decimalPreferrence;
    num = ((Math.abs(num)).toFixed(decimalFormat))
    return num;
}

if ($('.main-containt').find('#crypto_exchange_user').length) {

    $(window).on('load', function () {
        beforeLoad();
        let previousUrl = localStorage.getItem("previousUrl");
        let confirmationUrl = cryptoBuySellConfirmUrl;
        if (confirmationUrl == previousUrl) {
            var exchangeType = localStorage.getItem("exchangeType");
            var sendAmount = localStorage.getItem("defaultAmnt");
            $("#send_amount").val(sendAmount)
            $('.' + exchangeType).trigger('click');
            localStorage.removeItem("previousUrl");
        }
        let fromCurrencyId = $("#from_currency").val();
        let toCurrencyId = $("#to_currency").val();
        var sendAmount = $("#send_amount").val();
        getDirectionTabAmount(fromCurrencyId, toCurrencyId, sendAmount);
    });

    $(document).on('change', "#from_currency", function () {
        beforeLoad();
        let fromCurrencyId = $("#from_currency").val();
        let type = $("#from_type").val();
        if (fromCurrencyId && type) {
            getCurrenciesExceptFromCurrencyType(fromCurrencyId, type);
        }
    });

    $(document).on('change', "#to_currency", function () {
        beforeLoad();
        let fromCurrencyId = $("#from_currency").val();
        let toCurrencyId = $("#to_currency").val();
        let sendAmount = $("#send_amount").val();
        if (fromCurrencyId && toCurrencyId && sendAmount) {
            getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount);
        }
    });

    $(document).on('keyup', '#send_amount', $.debounce(700, function() {
        beforeLoad();
        let fromCurrencyId = $("#from_currency").val();
        let toCurrencyId = $("#to_currency").val();
        let sendAmount = $("#send_amount").val();
        if (fromCurrencyId && toCurrencyId && sendAmount) {
            getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount);
        }
    }));

    $(document).on('keyup', '#get_amount', $.debounce(700, function() {
        let fromCurrencyId = $("#from_currency").val();
        let toCurrencyId = $("#to_currency").val();
        let getAmount = $("#get_amount").val();
        beforeLoad(getAmount);
        if (fromCurrencyId && toCurrencyId && getAmount) {
            getDirectionAmount(fromCurrencyId, toCurrencyId, null, getAmount);
        }
    }));

    function beforeLoad(getAmount = null) {
        $('.rate').text('');
        $('.exchange_fee').text('');
        $('.direction').text('');
        $("#crypto_buy_sell_button").attr("disabled", true);
        (getAmount) ? $("#send_amount").val('-') : $("#get_amount").val('-');
    }

    function getDirectionTabAmount(fromCurrencyId, toCurrencyId, sendAmount = null, getAmount = null) {
        let token = $("#token").val();
        if (fromCurrencyId && toCurrencyId) {
            $.ajax({
                method: "GET",
                url: directionAmountUrl,
                dataType: "json",
                cache: false,
                data: {
                    "_token": token,
                    'from_currency_id': fromCurrencyId,
                    'to_currency_id': toCurrencyId,
                    'send_amount': sendAmount,
                    'get_amount': getAmount,
                }
            })
            .done(function (response) {
                $('#send_amount').val(response.success.send_amount);
                $('#get_amount').val(response.success.get_amount);
                $('.rate').text(response.success.exchange_rate);
                $('.exchange_fee').text(response.success.exchange_fee);
                if (response.success.status == 200) {
                    $('.send_amount').text('');
                    $('#crypto_buy_sell_button').attr('disabled', false);
                } else {
                    $('.send_amount').text(response.success.message);
                    $('#crypto_buy_sell_button').attr('disabled', true);
                }
                $("input").prop('disabled', false);
            });
        } else {
            $('#crypto_buy_sell_button').attr('disabled', true);
            $('.direction').addClass('error').text(directionNotAvaillable);
            $('#get_amount').val(0);
        }
    }

    function getCurrenciesExceptFromCurrencyType(fromCurrencyId, type)
    {
        let token = $("#token").val();
        if (fromCurrencyId && type) {
            $.ajax({
                method: "GET",
                url: directionListUrl,
                dataType: "json",
                cache: false,
                data: {
                    "_token": token,
                    'type': type,
                    'from_currency_id': fromCurrencyId,
                }
            })
            .done(function (response)
            {
                let toOptions = '';
                $.each(response.directionCurrencies, function(key, value)
                {
                    toOptions += `<option value="${value.id}" >${value.code}</option>`
                });
                $('#to_currency').html(toOptions);
                let fromCurrencyId = $("#from_currency").val();
                let toCurrencyId = $("#to_currency").val();
                let sendAmount = $("#send_amount").val();
                if (fromCurrencyId && toCurrencyId && sendAmount) {
                    getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount);
                }
            });
        }
    }

    function getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount = null, getAmount = null)
    {
        let token = $("#token").val();
        if (fromCurrencyId && toCurrencyId ) {
            $.ajax({
                method: "GET",
                url: directionAmountUrl,
                dataType: "json",
                cache: false,
                data: {
                    "_token": token,
                    'from_currency_id': fromCurrencyId,
                    'to_currency_id': toCurrencyId,
                    'send_amount': sendAmount,
                    'get_amount': getAmount,
                },
            })
            .done(function (response)
            {
                $('#send_amount').val(response.success.send_amount);
                $('#get_amount').val(response.success.get_amount);
                $('.rate').text(response.success.exchange_rate);
                $('.exchange_fee').text(response.success.exchange_fee);
                $('.flash-container').hide();
                if (response.success.status == 200) {
                    $('.send_amount').text('');
                    $('#crypto_buy_sell_button').attr('disabled', false);
                } else {
                    $('.send_amount').text(response.success.message);
                    $('#crypto_buy_sell_button').attr('disabled', true);
                }
                $("input").prop('disabled', false);
            });
        }
    }

    $(document).on('click', ".crypto", function ()
    {
        beforeLoad();
        let type = $(this).attr('data-type');
        $('.crypto').removeClass('tabactive');
        $(this).addClass('tabactive');
        $('.send_amount').text('');
        $("#from_type").val(type);
        getCurrenciesByType(type);
    });

    function getCurrenciesByType(directionType) {
        let token = $("#token").val();
        if (directionType) {
            $.ajax({
                method: "GET",
                url: directionTypeUrl,
                dataType: "json",
                cache: false,
                data: {
                    "_token": token,
                    'direction_type': directionType,
                }
            })
            .done(function (response) {
                let fromOptions = '';
                $.each(response.fromCurrencies, function (key, value) {
                    fromOptions += `<option value="${value.id}" >${value.code}</option>`;
                });
                $('#from_currency').html(fromOptions);
                $("#send_amount").val(response.min_amount);


                let toOptions = '';
                $.each(response.toCurrencies, function (key, value) {
                    toOptions += `<option value="${value.id}" >${value.code}</option>`;
                });
                $('#to_currency').html(toOptions);

                let text = (response.status == '401') ? directionNotAvaillable : '';
                $('.direction').addClass('error').text(text);
                if (localStorage.getItem("from_currency") && localStorage.getItem("to_currency")) {
                    $("#from_currency").val(localStorage.getItem("from_currency"));
                    $("#to_currency").val(localStorage.getItem("to_currency"));
                    localStorage.removeItem("from_currency");
                    localStorage.removeItem("to_currency");
                }
                let fromCurrencyId = $("#from_currency").val();
                let toCurrencyId = $("#to_currency").val();
                let sendAmount = $("#send_amount").val();
                if (fromCurrencyId && toCurrencyId && sendAmount) {
                    getDirectionTabAmount(fromCurrencyId, toCurrencyId, sendAmount);
                }
            });
        }
    }

    $(document).on('submit', '#crypto-send-form', function() {
        $("#crypto_buy_sell_button").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#rightAngleSvgIcon").addClass('d-none');
        $('#rp_text').text(submitText);
    });
}

if ($('.main-containt').find('#crypto_exchange_details').length) {

    function exchangeBack() {
        localStorage.setItem("previousUrl", document.URL);
        localStorage.setItem("exchangeType", exchangeTypeValue);
        localStorage.setItem("from_currency", fromCurrencyValue);
        localStorage.setItem("to_currency", toCurrencyValue);
        localStorage.setItem("defaultAmnt", defaultAmnt);
        history.back();
    }

    $(window).on('load', function()
    {
        paymentOption();
    });

    $(document).on('change', '#pay_with', function() {
        paymentOption();
    });

    $(document).on('change', '#file', function() {
        var ext = $('#file').val().replace(/^.*\./, '');
        let fileInput = document.getElementById('file');
        const fileTypes = extensions;
        if (!fileTypes.includes(ext)) {
            fileInput.value = '';
            $('.file-error').addClass('error').text(invalidFileText);
            $('#fileSpan').fadeIn('slow').delay(2000).fadeOut('slow');
            return false;
        } else {
            $('.file-error').removeClass('error').text('');
            return true;
        }
    })

    function paymentOption()
    {
        if ($('#pay_with').val() == 'others') {
            $("#payment_details").show();
            $('.payment_details').attr("disabled", false);
        } else {
            $("#payment_details").hide();
            $('.payment_details').attr("disabled", true);
        }
    }

    $(document).on('change', '#receive_with', function() {
        receiveOption();
    });

    function receiveOption()
    {
        if ( $('#receive_with').val() == 'address') {
            $("#crypto_address_section").css('display','block');
            $('.crypto_address').attr("disabled", false);
        } else {
            $("#crypto_address_section").css('display','none');
            $('.crypto_address').attr("disabled", true);
        }
    }

    $(document).on('submit', '#crypto_buy_sell_from', function() {
        $("#exchange-confirm-submit-btn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#rightAngleSvgIcon").addClass('d-none');
        $('#cryptoExchangeConfirmBtnText').text(confirmText);
        localStorage.removeItem("previousUrl");
        localStorage.removeItem("from_currency");
        localStorage.removeItem("to_currency");

        setTimeout(function () {
            $(".spinner").addClass('d-none');
            $("#exchange-confirm-submit-btn").attr("disabled", false);
            $("#cryptoExchangeConfirmBtnText").text(pretext);
            $("#rightAngleSvgIcon").removeClass('d-none');
        }, 2000);


    });

    //Only go back by back button, if submit button is not clicked
    $(document).on('click', '.exchange-confirm-back-btn', function(e) {
        e.preventDefault();
        exchangeBack();
    });

    $('#copyButton, #merchantAddress').on('click', function () {
		let address = $('#merchantAddress').val();
        let elem = document.createElement("textarea");
        document.body.appendChild(elem);
        elem.value = address;
        elem.select();
        document.execCommand("copy");
        document.body.removeChild(elem);
        $('#copy-parent-div').addClass('show-copied');
        setInterval(show_copy, 5000);
	});

    function show_copy() {
        $('#copy-parent-div').removeClass('show-copied');
    }
}

if ($('.main-containt').find('#cryptoExchangeList').length) {

    $(function() {
        var sDate;
        var eDate;

        $('#daterange-btn').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment(),

            }, function (start, end) {
                sDate = moment(start, 'MMMM D, YYYY').format('DD-MM-YYYY');
                $('#startfrom').val(sDate);
                eDate = moment(end, 'MMMM D, YYYY').format('DD-MM-YYYY');
                $('#endto').val(eDate);
                $('#daterange-btn p').html(sDate + ' - ' + eDate);
            }
        )

        if (startDate == '') {
            $('#daterange-btn p').html(dateRangePickerText);
        } else {
            $('#daterange-btn p').html(startDate + ' - ' + endDate);
        }
    });

    $(document).ready(function () {
        let status = $('#status').val();
        let currency = $('#currency').val();
        let type = $('#type').val();

        if (startDate != '' || status != 'all' || currency != 'all' || type != 'all') {
            $(".filter-panel").css('display', 'block');
        }

        $(".fil-btn").on('click', function () {
            $(this).find('img').toggle();
            $(".filter-panel").slideToggle(300);
        });
    });
}
