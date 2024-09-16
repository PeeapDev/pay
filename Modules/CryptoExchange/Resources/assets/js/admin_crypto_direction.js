"use strict";

function restrictNumberToPrefdecimalOnInput(e) {
    let type = 'crypto';
    let directionType = $("#direction_type").val();
    if (directionType == 'crypto_buy') {
        type = 'fiat';
    }
    restrictNumberToPrefdecimal(e, type);
}

function exchangeRateControll() {
    if ($('#exchange_from').val() == 'api') {
        $('#exchange_rate_div').hide();
        $("#exchange_rate").prop("disabled", true);
    } else {
        $('#exchange_rate_div').show();
        $("#exchange_rate").prop("disabled", false);
    }
}

function determineDecimalPoint() {
    let directionType = $("#direction_type").val();
    if (directionType == 'crypto_swap') {
        $(".min_amount, .max_amount, .fees_fixed, .fees_percentage, .exchange_rate, .total_fees").attr('placeholder', CRYPTODP);
    } else if (directionType == 'crypto_buy') {
        $(".min_amount, .max_amount, .fees_fixed, .fees_percentage, .total_fees").attr('placeholder', FIATDP);
        $(".exchange_rate").attr('placeholder', CRYPTODP);
    } else {
        $(".min_amount, .max_amount, .fees_fixed, .fees_percentage, .total_fees").attr('placeholder', CRYPTODP);
        $(".exchange_rate").attr('placeholder', FIATDP);
    }
}

function restrictNumberToPrefdecimalOnInputExchange(e) {
    let type = 'crypto';
    let directionType = $("#direction_type").val();
    if (directionType == 'crypto_sell') {
        type = 'fiat';
    }
    restrictNumberToPrefdecimal(e, type);
}

function formatNumberToPrefDecimal(num = 0) {
    let decimalFormat = decimalPreferrence;
    num = ((Math.abs(num)).toFixed(decimalFormat))
    return num;
}

function getPaymentMethod() {
    let token = $("#token").val();
    let directionType = $("#direction_type").val();
    let fromCurrencyId = $("#from_currency_id option:selected").val();
    if (directionType == 'crypto_buy') {
        $('#payment_method_direction').show();
        $.ajax({
            method: "GET",
            url: gatewayListUrl,
            dataType: "json",
            cache: false,
            data: {
                "_token": token,
                'from_currency_id': fromCurrencyId,
            }
        })
        .done(function (response) {
            let paymentOptions = '';
            if (response.status == 200) {
                $.map(response.paymentMethod, function (value, index) {
                    paymentOptions += `<option value="${value.id}">${value.name}</option>`
                });
            }
            $('#payment_method').html(paymentOptions);
        });
    } else {
        $('#payment_method_direction').hide();
    }
}

if ($('.content').find('#crypto_direction_create').length) {
    $(function () {
        $(".select2").select2({});
        $("#payment_method").select2({
            placeholder: selectGatewayText
        });
    });
    
    //on load
    $(window).on('load', function () {
        determineDecimalPoint();
        getCurrenciesExceptFromCurrencyType();
        getPaymentMethod();
        exchangeRateControll();
    });

    // Direction Type Change
    $(document).on('change', '#direction_type', function () {
        determineDecimalPoint();
        getCurrenciesExceptFromCurrencyType();
        getPaymentMethod();
        $('#fees_percentage').val('');
        $('#fees_fixed').val('');
        $('#min_amount').val('');
        $('#max_amount').val('');
        $('#exchange_rate').val('');
    });

    // From Wallet Change
    $(document).on('change', '#from_currency_id', function () {
        getCurrenciesExceptFromCurrencyType();
        getPaymentMethod();
    });

    $(document).on('change', '#exchange_from', function () {
        exchangeRateControll();

        var exchangeFrom = $(this).find('option:selected').val();
        if (exchangeFrom == 'api') {
            if (cryptoCompareEnabledApi != 'Disabled' && cryptoCompareApiKey != '') {
                $('#direction_create').attr('disabled', false);
            } else {
                $('#direction_create').attr('disabled', true);
                $('.rate-setup-text').removeClass('d-none');
            }
        } else if (exchangeFrom == 'local') {
            $('.rate-setup-text').addClass('d-none');
            $('#direction_create').attr('disabled', false);
        }
    });

    $(document).on('input', '#min_amount, #max_amount', function () {
        let minAmount = parseFloat($('#min_amount').val());
        let maxAamount = parseFloat($('#max_amount').val());
        if (minAmount && maxAamount && minAmount > maxAamount) {
            $('#max_error').addClass('error').text(maximumAmounText);
            $("#direction_create").attr("disabled", true);
        } else {
            $('#max_error').text('');
            $("#direction_create").attr("disabled", false);
        }
    });

    $(document).on('submit', '#exchange_direction_form', function () {
        exchangeRateControll();
        $("#direction_create").attr("disabled", true);
        $(".fa-spin").removeClass("d-none");
        $("#direction_create_text").text(createText);
        $('#users_cancel').attr("disabled", true);
    });

  

    function getCurrenciesExceptFromCurrencyType() {
        let token = $("#token").val();
        let directionType = $("#direction_type").val();
        let fromCurrencyId = $("#from_currency_id option:selected").val();
        $.ajax({
            method: "GET",
            url: getCurrencyUrl,
            dataType: "json",
            cache: false,
            data: {
                "_token": token,
                'direction_type': directionType,
                'from_currency_id': fromCurrencyId,
            }
        })
        .done(function (response) {
            let fromOptions = '';
            fromOptions += `<option value="">${selectOneText}</option>`;
            $.each(response.fromCurrencies, function (key, value) {
                fromOptions += `<option value="${value.id}" ${(value.id == fromCurrencyId) ? 'selected' : ''} data-type="${value.type}" >${value.code}</option>`;
            });
            $('#from_currency_id').html(fromOptions);
            let toOptions = '';
            toOptions += `<option value="">${selectOneText}</option>`;
            $.each(response.toCurrencies, function (key, value) {
                toOptions += `<option value="${value.id}" data-type="${value.type}" >${value.code}</option>`;
            });
            $('#to_currency_id').html(toOptions);
        });
    }
}

// Crypto Direction Edit
if ($('.content').find('#crypto_direction_edit').length) {
    
    $(function () {
        $(".select2").select2({});
    });

    $(document).ready(function () {
        var selectedGateway = gateways;
        var gateway = selectedGateway.split(',').map(Number);
        $('#payment_method').select2({
            placeholder: selectGatewayText,
            allowClear: true
        }).select2().val(gateway).trigger("change");
    });

    $(document).on('submit', '#exchange_direction_form', function () {
        exchangeRateControll();
        $("#direction_create").attr("disabled", true);
        $(".fa-spin").removeClass("d-none");
        $("#direction_create_text").text(updateText);
        $('#users_cancel').attr("disabled", true);
    });

    //on load
    $(window).on('load', function () {
        determineDecimalPoint()
        var fromCurrency = $('#from_currency_id').val();
        getCurrenciesExceptFromCurrencyType(fromCurrency);
        exchangeRateControll();
    });

    $(document).on('change', '#exchange_from', function (e) {
        exchangeRateControll();

        var exchangeFrom = $(this).find('option:selected').val();
        if (exchangeFrom == 'api') {
            if (cryptoCompareEnabledApi != 'Disabled' && cryptoCompareApiKey != '') {
                $('#direction_create').attr('disabled', false);
            } else {
                $('#direction_create').attr('disabled', true);
                $('.rate-setup-text').removeClass('d-none');
            }
        } else if (exchangeFrom == 'local') {
            $('.rate-setup-text').addClass('d-none');
            $('#direction_create').attr('disabled', false);
        }
    });

    //From Wallet
    $(document).on('change', '#from_currency_id', function (e) {
        let fromCurrency = $('#from_currency_id').val();
        getCurrenciesExceptFromCurrencyType(fromCurrency);
        getPaymentMethod();
    });

    function getCurrenciesExceptFromCurrencyType(fromCurrency) {
        let token = $("#token").val();
        let currencyId = fromCurrency;
        if (currencyId) {
            $.ajax({
                method: "GET",
                url: getCurrencyUrl,
                dataType: "json",
                cache: false,
                data: {
                    "_token": token,
                    'to_currency_id': toCurrencyId,
                    'type': "edit",
                    'direction_type': directionType,
                }
            })
            .done(function (response) {
                var options = '';
                options += `<option value="${toCurrencyId}" >${toCurrencyCode}</option>`;
                $.each(response.toCurrencies, function (key, value) {
                    if (value.id != toCurrencyId) {
                        options += `<option value="${value.id}" >${value.code}</option>`;
                    }
                });
                $('#to_currency_id').html(options);
            });
        }
    }

    $(document).on('input', '#min_amount, #max_amount', function () {
        let minAmount = parseFloat($('#min_amount').val());
        let maxAmount = parseFloat($('#max_amount').val());
        if (minAmount && maxAmount && minAmount > maxAmount) {
            $('#max_error').addClass('error').text(maximumAmounText);
            $("#direction_create").attr("disabled", true);
        } else {
            $('#max_error').text('');
            $("#direction_create").attr("disabled", false);
        }
    });
}