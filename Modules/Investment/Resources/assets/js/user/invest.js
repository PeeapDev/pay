"use strict";

function restrictNumberToPrefdecimalOnInput(e, type) {
    var type = $("#investment-plan option:selected").data('currency-type');
    restrictNumberToPrefdecimal(e, type);
}

if ($(".main-containt").find("#invest_add").length) {
    // Get active payment method of selected plan
    function getActivePaymentMethod() {
        let promiseObj = new Promise(function(resolve, reject) {
            var planId = $('#investment-plan').val();
            var planCurrencyId = (investmentPlan ? $("#investment-plan").data('currency') : $("#investment-plan option:selected").data('currency'));
            if (planId != null) {
                $.ajax({
                    method: 'GET',
                    url: paymentMethodUrl,
                    data: {
                        'plan_id': planId,
                        'plan_currency_id': planCurrencyId,
                    },
                    dataType: "json",
                }).done(function (response) {
                    let options = '';
                    $.map(response.data.paymentMethods, function (value, key) {
                        options += `<option value="${value.id}">${value.name}</option>`;
                    });
                    if (response.data.paymentMethods != '') {
                        resolve(200);
                        $('#payment-method-div, #invest-money-btn').removeClass('d-none');
                        $('#payment-method').html(options);
                        $('#empty-payment').addClass('d-none');
                    } else {
                        resolve(404);
                        $('#empty-payment').removeClass('d-none');
                        $('#payment-method-div, #invest-money-btn').addClass('d-none');
                    }
                }).fail(function(error) {
                    Swal.fire(
                        failedText,
                        JSON.parse(error.responseText).message,
                        'error'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                    reject();
                });
            }
        });
        return promiseObj;
    }

    // Check currency type according to selected plan
    function checkPlanAmount()
    {
        var type = (investmentPlan ? $("#investment-plan").data('type') : $("#investment-plan option:selected").data('type'));
        var amount = (investmentPlan ? $("#investment-plan").data('amount') : $("#investment-plan option:selected").data('amount'));
        var currencyCode = (investmentPlan ? $("#investment-plan").data('currency-code') : $("#investment-plan option:selected").data('currency-code'));
        var currencyType = (investmentPlan ? $("#investment-plan").data('currency-type') : $("#investment-plan option:selected").data('currency-type'));
        var maximumAmount = (investmentPlan ? $("#investment-plan").data('maximum-amount') : $("#investment-plan option:selected").data('maximum-amount'));

        $.ajax({
            method: "GET",
            url: currencyTypeUrl,
            dataType: "json",
            data: {
                "currencyType": currencyType,
                "amount": amount,
                "maximum_amount": maximumAmount
            }
        }).done(function (response) {
            $('.currency').text('('+currencyCode+')').show();
            if (type == 'Fixed') {
                $('#amount-limit-div').addClass('d-none');
                $('#amount').val((response.data.amount)).prop("readonly", true).show();
                $('#amount-range, #maximum-amount').hide();
            } else {
                $('#amount-limit-div').removeClass('d-none');
                $('#amount').val('').show().prop("readonly", false);
                $('#amount-range').text(minAmount + ' - ' + response.data.amount +' '+ currencyCode).show();
                $('#maximum-amount').text(maxAmount + ' - ' + response.data.maximumAmount +' '+ currencyCode).show();
            }
        });
    }

    // Check invested amount limit
    function checkInvestAmountLimit()
    {
        var userAmount = $('#amount').val();
        var token = $("#token").val();
        var planType = (investmentPlan ? $("#investment-plan").data('type') : $("#investment-plan option:selected").data('type'));
        var amount = (investmentPlan ? $("#investment-plan").data('amount') : $("#investment-plan option:selected").data('amount'));
        var maximumAmount = (investmentPlan ? $("#investment-plan").data('maximum-amount') : $("#investment-plan option:selected").data('maximum-amount'));
        var planId = (investmentPlan ? $("#investment-plan").val() : $("#investment-plan option:selected").val())
        var pmText = $('#payment-method option:selected').text();
        var pm = (investmentPlan ? $("#investment-plan").data('payment-methods') : $("#investment-plan option:selected").data('payment-methods'));

        $.ajax({
            method: "POST",
            url: amountLimitUrl,
            dataType: "json",
            data: {
                "_token": token,
                'user_amount': userAmount,
                'amount': amount,
                'plan_type': planType,
                'plan_id': planId,
                'maximum_amount': maximumAmount,
                'payment_method': pmText,
                'payment_methods': pm,
                'transaction_type_id': transactionTypeId
            }
        }).done(function (response) {
            if (response.success.status == 200) {
                if (planType == 'Range') {
                    $('#amount-limit-div').removeClass('d-none');
                    $('#amount-range, #maximum-amount').show();
                } else {
                    $('#amount-limit-div').addClass('d-none');
                    $('#amount-range, #maximum-amount').hide();
                }
                $('.amountLimit').text('');
                $('#invest-money-btn').attr("disabled", false);
            } else {
                if (userAmount == '' && planType == 'Range') {
                    $('#amount-limit-div').removeClass('d-none');
                    $('.amountLimit').text('');
                    $('#amount-range, #maximum-amount').show();
                    $('#invest-money-btn').attr("disabled", false);
                } else {
                    $('#amount-limit-div').addClass('d-none');
                    $('#amount-range, #maximum-amount').hide();
                    $('.amountLimit').text(response.success.message);

                    if (pmText.includes("wallet")) {
                        $('.amountLimit').text(response.success.balanceLimitError);
                    }
                    $('#invest-money-btn').attr("disabled", true);
                }
            }
        });
    }

    // Get active payment method, check currency type and user amount limit check
    $(window).on('load', function () {
        repopulateInvestValue();
        checkPlanAmount();
        getActivePaymentMethod()
        .then((status) => {
            if (status == 200) {
                checkInvestAmountLimit();
            }
        }).catch(error => {
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

    });

    function repopulateInvestValue() {
        let previousUrl = localStorage.getItem("investConfirmPreviousUrl");
        let confirmationUrl = SITE_URL + '/invest/create';
        if (confirmationUrl == previousUrl) {
            let investPaymentMethodId = localStorage.getItem('investPaymentMethodId');
            let amount = localStorage.getItem('amount');
            let planId = localStorage.getItem('planId');

            if (investPaymentMethodId && amount && planId ) {
                swal(waitText.replace( /&#039;/g, "'"), loadingText, {
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                    buttons: false,
                });

                setTimeout(function(investPaymentMethodId, amount, planId) {
                    $('#investment-plan').val(planId);
                    $("#amount").val(amount);
                    $("#payment-method").val(investPaymentMethodId);
                    $("#payment-method").trigger('change');
                    swal.close();
                }, 1300, investPaymentMethodId, amount, planId);
                removeInvestLocalStorageValues();
            }
        } else {
            setTimeout(function()
            {
               removeInvestLocalStorageValues();
            }, 1300);
        }
    }

    function removeInvestLocalStorageValues()
    {
        localStorage.removeItem('investPaymentMethodId');
        localStorage.removeItem('amount');
        localStorage.removeItem('planId');
        localStorage.removeItem('investConfirmPreviousUrl');
    }

    // Maximum amount and minimum amount field show on plan type change on plan change
    $('#investment-plan').on('change', function () {
        checkPlanAmount();
        getActivePaymentMethod()
        .then((status) => {
            if (status == 200) {
                checkInvestAmountLimit();
            }
        }).catch(error => {
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
    });

    // User invested amount limit check
    $(document).on('keyup', '.amount', $.debounce(1000, function () {
        getActivePaymentMethod()
        .then((status) => {
            if (status == 200) {
                checkInvestAmountLimit();
            }
        }).catch(error => {
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
    }));

    // User amount limit check on payment method change
    $(document).on('change', '#payment-method', function () {
        checkInvestAmountLimit();
    });

    //invest add form submitting
    $(document).on('submit', '#invest-form', function() {
        $("#invest-money-btn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#invest-money-btn-text").text(investSubmitButtonText);

        var investPaymentMethodId = $('#payment-method').val();
        localStorage.setItem("investPaymentMethodId", investPaymentMethodId);

        var amount =  $("#amount").val();
        localStorage.setItem("amount", amount);

        var planId =  $("#investment-plan").val();
        localStorage.setItem("planId", planId);

        setTimeout(function () {
            $(".spinner").addClass('d-none');
            $("#invest-money-btn").attr("disabled", false);
            $("#invest-money-btn-text").text(pretext);
        }, 2000);
    });
}

if ($(".main-containt").find("#invest_confirm").length) {

    //Only go back by back button, if submit button is not clicked
    $(document).on('click', '.deposit-confirm-back-btn', function (e)
    {
        e.preventDefault();
        InvestBack();
    });

    function InvestBack()
    {
        window.localStorage.setItem("investConfirmPreviousUrl", SITE_URL + '/invest/create');
        window.history.back();
    }

    //invest confirm form submitting
    $(document).on('submit', '#confirm-form', function() {
        $("#confirm-button").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#confirm-button-text").text(continueButtonText);
    });
}

if ($(".main-containt").find("#invest_success").length) {
    $(document).ready(function() {
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };
    });

    //disabling F5
    function disable_f5(e) {
        if ((e.which || e.keyCode) == 116) {
            e.preventDefault();
        }
    }
    $(document).ready(function() {
        $(document).on("keydown", disable_f5);
    });

    //disabling ctrl+r
    function disable_ctrl_r(e) {
        if (e.keyCode == 82 && e.ctrlKey) {
            e.preventDefault();
        }
    }

    $(document).ready(function() {
        $(document).on("keydown", disable_ctrl_r);
    });
}

if ($(".main-containt").find("#invest_list").length) {
    $(document).on('change', '#status', function ()
    {
        let status = $(this).val();
        let url = SITE_URL + '/investment-list/' + status;
        window.location.href = url;
    });
}
