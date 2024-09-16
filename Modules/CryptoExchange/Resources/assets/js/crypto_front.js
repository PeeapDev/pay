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

function formatNumberToPrefDecimal(num = 0) {
    let decimalFormat = decimalPreferrence;
    return ((Math.abs(num)).toFixed(decimalFormat));
}

function isNumber(evt) {
    var evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

$('#copyButton, #merchantAddress').on('click', function () {
    let address = $('#merchantAddress').text();
    let elem = document.createElement("textarea");
    document.body.appendChild(elem);
    elem.value = address;
    elem.select();
    document.execCommand("copy");
    document.body.removeChild(elem);
    $('#copyButton').addClass('d-none');
    $('.copyText').removeClass('d-none');
});

if ($('#crypto-front-initiate').length) {

    var logoUrl = currencyLogoUrl + '/';

    $(document).on('click', ".from-currency", function () {
        let id = $(this).attr('id');
        let text = $("#from-code-" + id).text();
        let imgsrc = $("#from-image-" + id).attr('src');
        $('#from-selected').text(text);
        $('img#seleceted-from-image').attr('src', imgsrc);
        let type = $("#from_type").val();
        $("#fromCurrencyId").val(id);
        getCurrenciesExceptFromCurrencyType(id, type);
    });

    $(document).on('click', ".to-currency", function () {
        let id = $(this).attr('id');
        let text = $("#to-code-" + id).text();
        let imgsrc = $("#to-image-" + id).attr('src');
        $('#to-selected').text(text);
        $('img#seleceted-to-image').attr('src', imgsrc);
        $('.bd-example-modal-sm-to').modal('hide');
        $("#toCurrencyId").val(id);
        let fromCurrencyId = $("#fromCurrencyId").val();
        let toCurrencyId = $("#toCurrencyId").val();
        let sendAmount = $("#send_amount").val();
        getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount);
    });

    $(document).on('keyup', '#send_amount', $.debounce(1200, function (e) {
        beforeLoad();
        var fromCurrencyId = $("#fromCurrencyId").val();
        var toCurrencyId = $("#toCurrencyId").val();
        var sendAmount = $("#send_amount").val();
        getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount);
    }));

    $(document).on('keyup', '#get_amount', $.debounce(1200, function (e) {
        var fromCurrencyId = $("#fromCurrencyId").val();
        var toCurrencyId = $("#toCurrencyId").val();
        var getAmount = $("#get_amount").val();
        beforeLoad(getAmount);
        getDirectionAmount(fromCurrencyId, toCurrencyId, null, getAmount);
    }));

    $(window).on('load', function (e) {
        beforeLoad();
        let fromType = $('#from_type').val();
        if (fromType == 'crypto_swap') {
            $(".switch-box").addClass('display-hide');
        } else {
            $(".switch-box").removeClass('display-hide');
        }
        var fromCurrencyId = $("#fromCurrencyId").val();
        var type = $("#from_type").val();
        var toCurrencyId = $("#toCurrencyId").val();
        var sendAmount = $("#send_amount").val();

        getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount);
        submitText();

    });

    $(document).on('click', '.switch-box', function () {
        beforeLoad();
        var type = $("#from_type").val();
        if (type == 'crypto_buy') {
            var exchangeType = 'crypto_sell';
        } else {
            var exchangeType = 'crypto_buy';
        }
        $("#from_type").val(exchangeType);
        getCurrenciesByType(exchangeType);
    });

    $(document).on('click', '.btn-toggle', function () {
        $(this).find('.btns').toggleClass('active');
        if ($(this).find('.btn-swich').length > 0) {
            $(this).find('.btns').toggleClass('btn-swich');
        }
        $(this).find('.btns').toggleClass('btn-defaults');
        $('.send_amount_error').text('');
        var type = $('.btn-swich').attr('data-type');
        $("#from_type").val(type);
        getCurrenciesByType(type);
    });

    $(document).on('submit', '#crypto-send-form', function () {
        $("#crypto_buy_sell_button").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#rightAngleSvgIcon").addClass('d-none');
        $("#rp_text").text(submitBtnText);
    });

    $(document).on("keyup", '#fromInput', function () {
        var value = $(this).val().toLowerCase();
        $("#from-currency-table tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $(document).on("keyup", '#toInput', function () {
        var value = $(this).val().toLowerCase();
        $("#to-currency-table tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $(document).on('click', ".crypto", function () {
        beforeLoad();
        var type = $(this).attr('data-type');
        $('#crypto_address-error').hide();
        $('.send_amount_error').text('');
        if (type == 'crypto_swap') {
            $(".crypto_swap").addClass('active');
            $(".crypto_buy").removeClass('active');
            $(".crypto_swap_svg").addClass('active-svg');
            $(".crypto_buy_svg").removeClass('active-svg');
            $(".switch-box").addClass('display-hide');
        } else {
            $(".crypto_swap").removeClass('active');
            $(".crypto_buy").addClass('active');
            $(".switch-box").removeClass('display-hide');
            $(".crypto_swap_svg").removeClass('active-svg');
            $(".crypto_buy_svg").removeClass('active-svg');
        }
        $("#from_type").val(type);
        $("#crypto_address").val('');
        getCurrenciesByType(type);
    });

    function beforeLoad(getAmount = null) {
        $('.rate').text('');
        $('.exchange_fee').text('');
        $('.send_amount_error').text('');
        $('.dot').addClass('display-hide');
        $('.dot-message').removeClass('h-9p');
        $("#crypto_buy_sell_button").attr("disabled", true);
        (getAmount) ? $("#send_amount").val('-') : $("#get_amount").val('-');
        $(".spinner").addClass('d-none');
    }

    function getCurrenciesExceptFromCurrencyType(fromCurrencyId, type) {
        var token = $("#token").val();
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
            .done(function (response) {
                setToCurrencies(response);
                var fromCurrencyId = $("#fromCurrencyId").val();
                var toCurrencyId = $("#toCurrencyId").val();
                var sendAmount = $("#send_amount").val();
                getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount);
            });
        }
    }

    function setToCurrencies(response) {
        let toOptions = '';
        let selectedToCurrency = '';

        if (response && (response.length || response.directionCurrencies)) {
            const first = response.directionCurrencies[0];
            toOptions = response.directionCurrencies.map(value => `<tr>
                <td class="text-left cursor-pointer to-currency" id="${value.id}">
                    <div class="d-flex px-3 align-items-center">
                        <img
                            id="to-image-${value.id}"
                            class="img-fluid currency-img"
                            src="${logoUrl}${value.logo}"
                            alt=""
                        >
                        <div class="px-3 coin-list">
                            <p class="coin-tag" id="to-code-${value.id}">
                            ${value.code}
                            </p>
                            <span class="coin-name" >${value.name}</span>
                        </div>
                    </div>
                </td>
            </tr>`).join('');

            selectedToCurrency = `<button type="button" class="btn btn-primary md:ms-2" >
                <img id="seleceted-to-image" class="img-fluid rounded-icon set-imgTo"
                src="${logoUrl}${first.logo}" alt="">
                <span id="to-selected" class="set-coinnameTo px-2">${first.code}</span>
            </button>`;

            $('#toCurrencyId').val(first.id);
        } else {
            $('#toCurrencyId').val('');
        }

        $('#to-currency-tr').html(toOptions);
        $('#selected-to-currency').html(selectedToCurrency);
        $('.bd-example-modal-sm').modal('hide');
    }

    function setToCurrenciesType(response) {
        let toOptions = '';
        let selectedToCurrency = '';

        if (response) {
            let first = response[0];
            $.each(response, function (key, value) {
                toOptions += `<tr>
                <td class="text-left cursor-pointer to-currency" id="${value.id}">
                    <div class="d-flex px-3 align-items-center">
                        <img
                            id="to-image-${value.id}"
                            class="img-fluid currency-img"
                            src="${logoUrl}${value.logo}"
                            alt=""
                        >
                        <div class="px-3 coin-list">
                            <p class="coin-tag" id="to-code-${value.id}">
                            ${value.code}
                            </p>
                            <span class="coin-name" >${value.name}</span>
                        </div>
                    </div>
                </td>
            </tr>`});

            selectedToCurrency = `<button type="button" class="btn btn-primary md:ms-2" >
                    <img id="seleceted-to-image" class="img-fluid rounded-icon set-imgTo"
                        src="${logoUrl}${first.logo}"
                        alt=""> <span id="to-selected" class="set-coinnameTo px-2">${first.code}</span>
                </button>`;

            $("#toCurrencyId").val(first.id)
        } else {
            $("#toCurrencyId").val('');
        }

        $('#to-currency-tr').html(toOptions);
        $('#selected-to-currency').html(selectedToCurrency);
        $('.bd-example-modal-sm').modal('hide');
    }

    function setFromCurrenciesType(response) {
        let fromOptions = '';
        let selectedFromCurrency = '';
        if (response) {
            let first = response[0];

            $.each(response, function (key, value) {
                fromOptions += `<tr>
                <td class="text-left cursor-pointer from-currency" id="${value.id}">
                    <div class="d-flex px-3 align-items-center">
                        <img
                            id="from-image-${value.id}"
                            class="img-fluid currency-img"
                            src="${logoUrl}${value.logo}"
                            alt="${value.code}"
                        >
                        <div class="px-3 coin-list">
                            <p class="coin-tag"  id="from-code-${value.id}">
                                ${value.code}
                            </p>
                            <span class="coin-name">${value.name}</span>
                        </div>
                    </div>
                </td>
            </tr>`});

            selectedFromCurrency = `<button type="button" class="btn btn-primary md:ms-2">
                    <img id="seleceted-from-image" class="img-fluid rounded-icon set-img" src="${logoUrl}${first.logo}"
                        alt=""><span id="from-selected" class="set-coinname px-2">${first.code}</span>
                </button>`;
            $("#fromCurrencyId").val(first.id)
        } else {
            $("#fromCurrencyId").val('')
        }

        $('#from-currency-tr').html(fromOptions);
        $('#selected-from-currency').html(selectedFromCurrency);
        $('.bd-example-modal-sm').modal('hide')
    }

    function getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount = null, getAmount = null) {
        var token = $("#token").val();
        $('#fromInput').val('');
        $('#toInput').val('');
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
                },
                beforeSend: function (xhr) {
                    $("#crypto_buy_sell_button").attr("disabled", true);
                },
            })
            .done(function (response) {
                $('.send_amount_error').text('');
                $('#send_amount').val(response.success.send_amount);
                $('#get_amount').val(response.success.get_amount);
                $('.rate').text(response.success.exchange_rate);
                $('.exchange_fee').text(response.success.exchange_fee);
                if (response.success.status == 200) {
                    $('#crypto_buy_sell_button').attr('disabled', false);
                    $('.dot').addClass('display-hide');
                    $('.dot-message').removeClass('h-9p');
                } else {
                    $('.send_amount_error').addClass('error').text(response.success.message);
                    $('.dot').removeClass('display-hide');
                    $('.dot-message').addClass('h-9p');
                    $('#crypto_buy_sell_button').attr('disabled', true);
                }
            });
        } else {
            $('.send_amount_error').addClass('error').text(directionNotAvaillable);
            $('.dot').removeClass('display-hide');
            $('.dot-message').addClass('h-9p');
            $('#crypto_buy_sell_button').attr('disabled', true);
            $('#get_amount').val(0);
            $('.rate').text('');
            $('.exchange_fee').text('');
        }
    }

    function getCurrenciesByType(directionType) {
        var token = $("#token").val();
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
                $("#send_amount").val(response.min_amount);
                setFromCurrenciesType(response.fromCurrencies);
                setToCurrenciesType(response.toCurrencies);
                let text = (response.status == '401') ? directionNotAvaillable : '';
                $('.send_amount_error').addClass('error').text(text);
                let fromCurrencyId = $("#fromCurrencyId").val();
                let toCurrencyId = $("#toCurrencyId").val();
                let sendAmount = $("#send_amount").val();
                getDirectionAmount(fromCurrencyId, toCurrencyId, sendAmount);
                submitText();
            });
        }
    }

    function submitText() {
        var type = $("#from_type").val();
        if (type == 'crypto_buy') {
            $('#rp_text').text(buyText);
        } else if (type == 'crypto_sell') {
            $('#rp_text').text(sellText);
        } else {
            $('#rp_text').text(exchangeText);
        }
    }
}

if ($('#crypto-exchange-verification').length) {

    function exchangeBack() {
        localStorage.setItem("previousUrl", document.URL);
        localStorage.setItem("exchangeType", from_type);
        localStorage.setItem("fromCurrency", fromCurrencyId);
        localStorage.setItem("toCurrency", toCurrencyId);
        history.back();
    }

    function enableDisableButton() {
        if (!hasPhoneError) {
            $('form').find("button[type='submit']").prop('disabled', false);
        } else {
            $('form').find("button[type='submit']").prop('disabled', true);
        }
    }

    $("#phone").intlTelInput({
        separateDialCode: true,
        nationalMode: true,
        preferredCountries: [defaultCountry],
        autoPlaceholder: "polite",
        placeholderNumberType: "MOBILE",
        utilsScript: utilsScriptFile
    });

    function updatePhoneInfo() {
        let promiseObj = new Promise(function (resolve, reject) {
            $('#defaultCountry').val($('#phone').intlTelInput('getSelectedCountryData').iso2);
            $('#carrierCode').val($('#phone').intlTelInput('getSelectedCountryData').dialCode);

            if ($('#phone').val != '') {
                $("#formattedPhone").val($('#phone').intlTelInput("getNumber").replace(/-|\s/g, ""));
            }
            resolve();
        });
        return promiseObj;
    }

    function validateInternaltionalPhoneNumber() {
        let promiseObj = new Promise(function (resolve, reject) {
            let resolveStatus = false;
            if ($.trim($('#phone').val()) !== '') {
                if (!$('#phone').intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($('#phone').val()))) {
                    $('#duplicate-phone-error').html('');
                    $('#tel-error').addClass('error').html(validPhoneText);
                    hasPhoneError = true;
                    enableDisableButton();
                } else {
                    resolveStatus = true;
                    $('#tel-error').html('');
                    hasPhoneError = false;
                    enableDisableButton();
                }
            } else {
                $('#tel-error').addClass('error').html(validPhoneText);
                hasPhoneError = false;
                enableDisableButton();
            }
            resolve(resolveStatus);
        });
        return promiseObj;
    }

    function phoneValidityCheck() {
        updatePhoneInfo()
        .then(() => {
            validateInternaltionalPhoneNumber()
        });
    }

    function sendSms() {
        var phone = $("#phone").val();
        var carrierCode = $("#carrierCode").val();
        var token = $("#token").val();
        if (phone && carrierCode) {
            $.ajax({
                method: "GET",
                url: phoneVerificationUrl,
                dataType: "json",
                cache: false,
                data: {
                    "_token": token,
                    'phone': phone,
                    'carrierCode': carrierCode,
                }
            })
            .done(function (response) {
                if (response.data.status) {
                    $('#phone').attr('readonly', true);
                    $("#otp_details").show();
                    $("#verification_field").hide();
                    $("#submit_field").show();
                } else {
                    $('#tel-error').html('');
                    $("#phone-config-error").text(phoneConfigText);
                    $('.phone_spinner').addClass('displaynone');
                    $('#phone_verification_next_text').text(nextText);
                }
            });
        }
    }

    $("#phone").on("countrychange", function () {
        phoneValidityCheck();
    });

    $("#phone").on('blur', function () {
        phoneValidityCheck();
    });

    $("#verify_phone").on('click', function () {
        phoneValidityCheck();
        if (!hasPhoneError) {
            sendSms();
        }
    });

    function validateEmail(email) {
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,})+$/;
        if (!regex.test(email)) {
            return false;
        } else {
            return true;
        }
    }

    $(document).on('click', "#verify_email", function () {

        $('#verify_email').attr('disabled', true);
        let email = $('#email').val();
        let token = $("#token").val();
        let checkEmail = validateEmail(email);
        if (checkEmail) {
            $("#email-error").text('');
            $('.email_spinner').removeClass('displaynone');
            $('#emil_verification_button_text').text(otpText);
            $.ajax({
                method: "GET",
                url: emailVerificationUrl,
                dataType: "json",
                cache: false,
                data: {
                    "_token": token,
                    'email': email,
                },
                beforeSend: function (xhr) {
                    $("#verify_email").attr("disabled", true);
                    $(".spinner").removeClass('d-none');
                    $("#rightAngleSvgIcon").addClass('d-none');
                    $("#emil_verification_button_text").text(otpText);
                },
            })
            .done(function (response) {
                if (response.data.status) {
                    $("#otp_details").show();
                    $("#verification_field").hide();
                    $('#code-text').text(response.data.code);
                    $("#submit_field").show();
                    $("#email").prop("disabled", true);
                    $(".spinner").addClass('d-none');
                    $("#rightAngleSvgIcon").removeClass('d-none');
                } else {
                    $("#email-error").text(emailConfigText);
                    $("#verify_email").attr('disabled', false);
                    $(".spinner").addClass('d-none');
                    $("#rightAngleSvgIcon").removeClass('d-none');
                    $('#emil_verification_button_text').text(nextText);
                }
            });
        } else {
            $("#verify_email").attr('disabled', false)
            $("#email-error").text(validEmailText);
        }
    });

    $(document).on('click', "#instant", function () {
        $("#phone_section").show();
        $("#verification_field").show();
        $("#unregistered").hide();
    });

    $(document).on('click', "#phone_verification_button", function () {
        var phone = $("#phone").val();
        var carrierCode = $("#carrierCode").val();
        var code = $("#phone_verification_code").val();
        var token = $("#token").val();
        $.ajax({
            method: "GET",
            url: phoneVerificationSuccessUrl,
            dataType: "json",
            cache: false,
            data: {
                "_token": token,
                'phone': phone,
                'carrierCode': carrierCode,
                'code': code,
            },
            beforeSend: function (xhr) {
                $("#phone_verification_button").attr("disabled", true);
                $(".spinner").removeClass('d-none');
                $("#otpRightAngleSvgIcon").addClass('d-none');
                $("#phone_verification_button_text").text(verifyingText);
            },
        })
        .done(function (response) {
            if (!response.status) {
                $('#code-error').addClass('error').html(response.message);
                $('#phone_verification_button_text').text(verify);
                $('.verify_confirm').addClass('displaynone');
            }
            if (response.status) {
                window.location.href = receivingInforUrl;
            }
            if (response.status == 500) {
                $('#code-error').addClass('error').html(otpRequiredText);
                $('#phone_verification_button_text').text(verify);
                $('.verify_confirm').addClass('displaynone');
            }
        });
    });

    $(document).on('click', "#email_verification_button", function () {
        var phone = $("#email").val();
        var code = $("#phone_verification_code").val();
        var token = $("#token").val();
        $.ajax({
            method: "GET",
            url: emailVerificationSuccessUrl,
            dataType: "json",
            cache: false,
            data: {
                "_token": token,
                'email': phone,
                'code': code,
            },
            beforeSend: function (xhr) {
                $("#email_verification_button").attr("disabled", true);
                $(".spinner").removeClass('d-none');
                $("#otpRightAngleSvgIcon").addClass('d-none');
                $("#phone_verification_button_text").text(verifyingText);
            },
        })
        .done(function (response) {
            if (!response.status) {
                $('#code-error').addClass('error').html(response.message);
                $(".spinner").addClass('d-none');
                $("#otpRightAngleSvgIcon").removeClass('d-none');
                $('#phone_verification_button_text').text(verify);
                $("#email_verification_button").attr("disabled", false);
            }

            if (response.status == 500) {
                $(".spinner").addClass('d-none');
                $("#otpRightAngleSvgIcon").removeClass('d-none');
                $('#phone_verification_button_text').text(verify);
                $('#code-error').addClass('error').html(otpRequiredText);
                $("#email_verification_button").attr("disabled", false);
            }

            if (response.status == true) {
                window.location.href = receivingInforUrl;
            }

        });
    });

    $(document).ready(function () {
        new Fingerprint2().get(function (result, components) {
            $('#browser_fingerprint').val(result);
        });
    });

    $(document).on('click', '.exchange-confirm-back-btn', function (e) {
        $(".spinner").addClass('d-none');
        e.preventDefault();
        exchangeBack();
    });
}

if ($('#crypto-receiving-info').length) {

    function exchangeBack() {
        localStorage.setItem("previousUrl", document.URL);
        window.history.back();
    }

    $(document).on('click', '.exchange-confirm-back-btn', function (e) {
        $(".spinner").addClass('d-none');
        e.preventDefault();
        exchangeBack();
    });

    $(document).on('submit', '#crypto_buy_sell_from', function () {
        $("#crypto_buy_sell_button").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#rightAngleSvgIcon").addClass('d-none');
        $("#crypto_buy_sell_button_text").text(nextText);
    });
}

if ($('#crypto-transaction-gateway').length) {

    function exchangeBack() {
        localStorage.setItem("previousUrl", document.URL);
        window.history.back();
    }

    $(document).on('submit', '#crypto_buy_sell_from', function () {
        $("#verify_phone").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#rightAngleSvgIcon").addClass('d-none');
        $("#phone_verification_button_text").text(submitText);
    });

    $(document).ready(function () {
        new Fingerprint2().get(function (result, components) {
            $('#browser_fingerprint').val(result);
        });
    });

    $(document).on('click', '.exchange-confirm-back-btn', function (e) {
        $(".spinner").addClass('d-none');
        e.preventDefault();
        exchangeBack();
    });

    $(document).on('click', '.payment_method', function () {
        var checkboxes = $(this).closest('form').find(':checkbox');
        checkboxes.prop('checked', $(this).is(':checked'));
    });

    $(document).on('click', '.gateway', function () {
        $(".gateway").removeClass("g5");
        localStorage.setItem("gateway", this.id);
        $(this).addClass("g5");
        $('#payment_method_id').val(this.id);
    });

    $(window).on('load', function () {
        if ($('#copyTarget').length) {
            var textWidth = $('#copyTarget').val().length;
            $("#copyTarget").width(textWidth * 10);
        }
        var previousUrl = localStorage.getItem("previousUrl");
        var gateway = localStorage.getItem("gateway");
        var confirmationUrl = SITE_URL + '/crypto-exchange/payment';
        if (previousUrl == confirmationUrl) {
            $('#' + gateway).on('click');
            localStorage.removeItem("previousUrl");
            localStorage.removeItem("gateway");
        }
    });

    $(document).on('change', '#file', function () {
        let ext = $('#file').val().replace(/^.*\./, '');
        let fileInput = document.getElementById('file');
        const fileTypes = extensions;
        if (!fileTypes.includes(ext)) {
            fileInput.value = '';
            $('.file-error').addClass('error').text(invalidFileText);
            $('#fileSpan').fadeIn('slow').delay(2000).fadeOut('slow');
            return false;
        } else {
            $('.file-error').text('');
            return true;
        }
    })

}

if ($('#crypto-bank-payment-method').length) {
    function exchangeBack() {
        localStorage.setItem("previousUrl", document.URL);
        window.history.back();
    }

    function getBanks() {
        var bank = $('#bank').val();
        if (bank) {
            $.ajax({
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: bankDetailsUrl,
                dataType: "json",
                cache: false,
                data: {
                    'bank': bank,
                }
            })
                .done(function (response) {
                    if (response.status) {
                        $('#bank_name').html(response.bank.bank_name);
                        $('#account_name').html(response.bank.account_name);
                        $('#account_number').html(response.bank.account_number);

                        if (response.bank_logo) {
                            $("#bank_logo").html(`<img class="stripe-image" src="${SITE_URL}/public/uploads/files/bank_logos/${response.bank_logo}" class="w-120p" width="120" height="80"/>`);
                        } else {
                            $("#bank_logo").html(`<img class="stripe-image" src="${SITE_URL}/public/images/payment_gateway/bank.jpg" class="w-120p" width="120" height="80"/>`);
                        }
                    } else {

                        $('#bank_name').html('');
                        $('#bank_branch_name').html('');
                        $('#bank_branch_city').html('');
                        $('#bank_branch_address').html('');
                        $('#swift_code').html('');
                        $('#account_name').html('');
                        $('#account_number').html('');
                    }
                });
        }
    }

    $(window).on('load', function () {
        getBanks();
    });

    $(document).on('change', '#bank', function () {
        getBanks();
    });

    $(document).on('submit', '#bank_deposit_form', function () {

        $("#bank_payment").attr("disabled", true);
        $("#spinner").show();
        var pretext = $("#deposit-money-text").text();
        $("#deposit-money-text").text(confirmText);

        //Make back button disabled and prevent click
        $('.deposit-bank-confirm-back-btn').attr("disabled", true).on('click', function (e) {
            e.preventDefault();
        });
        //Make back anchor prevent click
        $('.deposit-bank-confirm-back-link').on('click', function (e) {
            e.preventDefault();
        });
        setTimeout(function () {
            $("#bank_payment").attr("disabled", false);
            $("#spinner").hide();
            $("#deposit-money-text").text(pretext);
        }, 1000);
    });

    $(document).on('click', '.exchange-confirm-back-btn', function (e) {
        $(".spinner").addClass('d-none');
        e.preventDefault();
        exchangeBack();
    });

    $(document).on('change', '#file', function () {
        let ext = $('#file').val().replace(/^.*\./, '');
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


}

if ($('#stripe-payment-gateway').length) {

    var forms = document.querySelectorAll('form');
    if (forms.length != 0) {
        forms[0].addEventListener("click", function (e) {
            if (e.target && e.target.nodeName == "INPUT") {
                hideFormsButFirst();
            }
        });
        function hideFormsButFirst() {
            for (var i = 0; i < forms.length; ++i) {
                forms[i].style.display = 'none';
            }
            forms[0].style.display = 'block';
        }
        function init() {
            hideFormsButFirst();
        }
        init();
    }

    $('#Stripe').on('submit', function (e) {
        e.preventDefault();
        confirmPayment();
    });

    function exchangeBack() {
        localStorage.setItem("previousUrl", document.URL);
        window.history.back();
    }

    function makePayment() {
        var promiseObj = new Promise(function (resolve, reject) {
            var cardNumber = $("#cardNumber").val().trim();
            var month = $("#month").val().trim();
            var year = $("#year").val().trim();
            var cvc = $("#cvc").val().trim();
            var currency = $('#Stripe').find('input[name="currency"]').val().trim();
            var amount = totalAmount;

            $("#stripeError").html('');
            if (cardNumber && month && year && cvc) {
                $.ajax({
                    type: "POST",
                    url: stripePaymentUrl,
                    data:
                    {
                        "_token": csrfToken,
                        'cardNumber': cardNumber,
                        'month': month,
                        'year': year,
                        'cvc': cvc,
                        'currency': currency,
                        'amount': amount,
                    },
                    dataType: "json",
                    beforeSend: function (xhr) {
                        $(".standard-payment-submit-btn").attr("disabled", true);
                    },
                }).done(function (response) {
                    if (response.data.status != 200) {
                        $("#stripeError").html(response.data.message);
                        $(".standard-payment-submit-btn").attr("disabled", true);
                        reject(response.data.status);
                        return false;
                    } else {
                        $(".standard-payment-submit-btn").attr("disabled", false);
                        resolve(response.data);
                    }
                });
            }
        });
        return promiseObj;
    }

    function confirmPayment() {
        makePayment().then(function (result) {
            var form = $('#Stripe')[0];
            var formData = new FormData(form);
            formData.append('_token', csrfToken);
            formData.append('paymentIntendId', result.paymentIntendId);
            formData.append('paymentMethodId', result.paymentMethodId);

            $.ajax({
                type: "POST",
                url: stripeUrl,
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                beforeSend: function (xhr) {
                    $(".standard-payment-submit-btn").attr("disabled", true);
                    $(".fa-spin").show();
                    $('.standard-payment-submit-btn').text(submitting);

                },
            }).done(function (response) {

                if (response.data.status != 200) {
                    $(".fa-spin").hide();
                    $(".standard-payment-submit-btn").attr("disabled", true);
                    $("#stripeError").html(response.data.message);
                    return false;
                } else {
                    window.location.replace(SITE_URL + '/crypto-exchange/success');
                }
            });
        });
    }

    $("#month").on('change', function () {
        makePayment();
    });

    $("#year, #cvc").on('keyup', $.debounce(800, function () {
        makePayment();
    }));

    $("#cardNumber").on('keyup', $.debounce(800, function () {
        makePayment();
    }));

    // For card number design
    document.getElementById('cardNumber').addEventListener('input', function (e) {
        var target = e.target, position = target.selectionEnd, length = target.value.length;
        target.value = target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
        target.selectionEnd = position += ((target.value.charAt(position - 1) === ' ' && target.value.charAt(length - 1) === ' ' && length !== target.value.length) ? 1 : 0);
    });

    $(document).on('click', '.exchange-confirm-back-btn', function (e) {
        $(".spinner").addClass('d-none');
        e.preventDefault();
        exchangeBack();
    });

    $(document).on('submit', '#crypto_buy_sell_from', function () {
        $(".standard-payment-submit-btn").attr("disabled", true);
        $(".fa-spin").show();
        setTimeout(function () {
            $(".fa-spin").hide();
            $(".standard-payment-submit-btn").attr("disabled", true);
        }, 1000);
    });
}

if ($('#paypal-payment-gateway').length) {
    paypal.Buttons({
        createOrder: function (data, actions) {
            // This function sets up the details of the transaction, including the amount and line item details.
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: totoalAmount
                    }
                }]
            });
        },
        onApprove: function (data, actions) {
            // This function captures the funds from the transaction.
            return actions.order.capture().then(function (details) {
                // This function shows a transaction success message to your buyer.
                window.location.replace(SITE_URL + "/crypto-exchange/paypal-payment/success/" + btoa(details.purchase_units[0].amount.value));
            });
        }
    }).render('#paypal-button-container');

    function exchangeBack() {
        localStorage.setItem("previousUrl", document.URL);
        window.history.back();
    }

    $(document).on('click', '.exchange-confirm-back-btn', function (e) {
        e.preventDefault();
        exchangeBack();
    });
}

$(document).on("click", '.from-currency', function () {
    var value1 = $(this).find(".coin-tag").text();
    $(".set-coinname").text(value1);
    var value2 = $(this).find(".currency-img").attr("src");
    $(".set-img").attr("src", value2);
    $('.modal').modal('hide');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();

});

$(document).on("click", '.to-currency', function () {
    var value3 = $(this).find(".coin-tag").text();
    $(".set-coinnameTo").text(value3);
    var value4 = $(this).find(".currency-img").attr("src");
    $(".set-imgTo").attr("src", value4);
    $('.modal').modal('hide');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});

$(document).on("keyup", '#fromInput', function () {
    var value = $(this).val().toLowerCase();
    $("#from-currency-table tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
});

$(document).on("keyup", '#toInput', function () {
    var value = $(this).val().toLowerCase();
    $("#to-currency-table tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
});
