"use strict";

// Resctrict the decimal places based on currency type
function restrictNumberToPrefdecimalOnInput(e) {
    let type = $("#currency-id").find(':selected').attr('data-type');
    restrictNumberToPrefdecimal(e, type);
}

// Defined amount place holder with decimal places
function determineDecimalPoint() {
    let currencyType = $('select#currency-id').find(':selected').data('type')
    if (currencyType == 'fiat') {
        $("#amount").attr('placeholder', FIATDP);
        $("#maximum-amount").attr('placeholder', FIATDP);
    } else {
        $("#amount").attr('placeholder', CRYPTODP);
        $("#maximum-amount").attr('placeholder', CRYPTODP);
    } 
}

$(function () {
    $(".payment").select2({});
});


function getTermArray(termType) 
{
    let termsArray = [];
    termType == "Year" ? (termsArray = ["Yearly", "Monthly", "Weekly", "Daily", "Hourly"]) : "";
    termType == "Month" ? (termsArray = ["Monthly", "Weekly", "Daily", "Hourly"]) : "";
    termType == "Week" ? (termsArray = ["Weekly", "Daily", "Hourly"]) : "";
    termType == "Day" ? (termsArray = ["Daily", "Hourly"]) : "";
    termType == "Hour" ? (termsArray = ["Hourly"]) : "";

    return termsArray
}

// Change interest time frame on change of term type on change
function changeTerm() 
{
    $("#interest-time-frame").empty();
    let index;
    let termsArray = getTermArray($("#term-type").val());
    
    if (termsArray != "") {
        for (index in termsArray) {
            $("#interest-time-frame").append("<option>" + termsArray[index] + "</option>");
        }
    }
}

// Maximum-investors, Maximum-limit-for-investor, Term can not be a fractional value
$("#maximum-investors, #maximum-limit-for-investor, #term").keypress(function (event) {
    var key = event.which;
    if (!(key >= 48 && key <= 57)) event.preventDefault();
});

$("#name").on("input", function () {
    var name = $("#name").val();
    if (name != "") {
        $(".url").css("display", "none");
        var name = convertToSlug($(this).val());
        $(".url").text(url + '/' + name);
    } else {
        $(".url").css("display", "none");
    }
});

// Slug generate in real time based on name
function convertToSlug(text) {
    return text
        .toString()
        .toLowerCase()
        .replace(/\s+/g, "-")
        .replace(/[^\w\-]+/g, "")
        .replace(/\-\-+/g, "-")
        .replace(/^-+/, "")
        .replace(/-+$/, "");
}

// Maximum amount show based on plan type
function maximum_amount(investment_type) {
    if (investment_type == "Range") {
        $("#maximum-amount-div").show();        
    } else if (investment_type == "Fixed") {
        $("#maximum-amount-div").css("display", "none");
    }
}

function enableDisableBtn(className, errorMessage){
    $('.' + className).text(errorMessage);
    $("#investment-plan-add-submit-btn").attr("disabled", true);
    $("#investment-plan-edit-submit-btn").attr("disabled", true);
}

function checkAmountMaxAmountInterestRate() {
    var amount = $('#amount').val();
    var interestRate = $('#interest-rate').val();
    var maximumAmount = $('#maximum-amount').val();

    $('.interest-rate').text(' ');
    $('.maximum-amount').text(' ');
    $("#investment-plan-add-submit-btn").attr("disabled", false);
    $("#investment-plan-edit-submit-btn").attr("disabled", false);
    
    if (maximumAmount.length > 0) {

        if ((amount.length > 0 && interestRate.length > 0 && parseFloat(interestRate) > parseFloat(amount))) {
            enableDisableBtn('interest-rate' , interestRateError);
        } 

        if ((amount.length > 0 && maximumAmount.length > 0 && parseFloat(amount) >= parseFloat(maximumAmount))) {
            enableDisableBtn('maximum-amount' , maxAmountError);
        } 
    } else {
        if (amount.length > 0 && interestRate.length > 0 && parseFloat(interestRate) > parseFloat(amount)) {
            enableDisableBtn('interest-rate' , interestRateError);
        } 
    }
}

function checkMaxInvestLimitAndMaxLimitInvestor() {
    var maxInvestor = $('#maximum-investors').val();
    var maxInvestorLimit = $('#maximum-limit-for-investor').val();

    $('.investor-limit').text(' ');
    $("#investment-plan-add-submit-btn").attr("disabled", false);
    $("#investment-plan-edit-submit-btn").attr("disabled", false);
    
    if (maxInvestor.length > 0 && maxInvestorLimit.length > 0 && parseFloat(maxInvestorLimit) > parseFloat(maxInvestor)) {
        enableDisableBtn('investor-limit' , investorError);
    } 
    
}

$("#amount, #maximum-amount, #interest-rate").on("change", function () {
    checkAmountMaxAmountInterestRate();
});

$("#maximum-investors, #maximum-limit-for-investor").on("change", function () {
    checkMaxInvestLimitAndMaxLimitInvestor();
});

// Get active payment methods according to currency code
function getPaymentMethods() {
    
    var currencyId = $("#currency-id option:selected").val();
    $.ajax({
        method: "GET",
        url: paymentMethodUrl,
        dataType: "json",
        data: {
            'currency_id': currencyId
        }
    }).done(function (response) {
        var options = '';
        
        if (response.data.status == 200) {
            $.map(response.data.paymentMethods, function(value) {

                options += `<option value="${value.id}">${value.name == 'Mts' ? 'Wallet' : value.name}</option>`;
            });
            $('#payment-methods').html(options);
        } else {
            $('#payment-methods').html(options);
        }
    }); 
}

var currentCurrencyType, lastCurrencyType;
$(document).on('click', 'select', function()
{
    lastCurrencyType = $(this).find(':selected').data('type');
});

// Get active payment methods on currency change
$("#currency-id").on("change", function () {
   currentCurrencyType = $('#currency-id').find(':selected').data('type');
    if (lastCurrencyType !== currentCurrencyType) {
        $('#amount').val('');
        $('#maximum-amount').val('');
    }
    $('#currency-type').val(currentCurrencyType);
    determineDecimalPoint();
    getPaymentMethods();
});

$("#investment-type").on("change", function () {
    maximum_amount($(this).find("option:selected").val());
});

// investment plan add js 
if ($('.content').find('#plan_add').length) {

    // Get active payment methods on load
    $(window).on("load", function () {
        determineDecimalPoint();
        getPaymentMethods();

        let currencyType = $('#currency-id').find(':selected').data('type');
        $('#currency-type').val(currencyType);
        // Change interest time frame on change of term type on load
        let index;
        let termsArray = getTermArray($("#term-type").find("option:selected").val());

        for (index in termsArray) {
            $("#interest-time-frame").append("<option>" + termsArray[index] + "</option>");
        }

        // maximum amount show or hide on page load
        maximum_amount($("#investment-type").find("option:selected").val());
    });    

    $(document).on('submit', '#add-investment-plan-form', function() {
        $(".fa-spinner").removeClass('d-none');
        $("#investment-plan-add-submit-btn").attr("disabled", true);
        $("#investment-plan-add-submit-btn-text").text(submitButtonText);
    });
}

// investment plan edit js 
if ($('.content').find('#plan_edit').length) { 

    $(document).ready(function() {
        var selectedPaymentMethods = paymentMethods;
        var paymentMethod = selectedPaymentMethods.split(',');
        $("#payment-methods").select2({
            placeholder: selectedPaymentMethodText,
            allowClear: true
        }).select2().val(paymentMethod).trigger("change");
    });

    // Toggle switch of is-featured field on/off according to value
    let Featured = $("#is-featured").val();
    if (Featured == "Yes") {
        $("#featured").prop("checked", true).change();
    }

    // Toggle switch of withdraw-after-matured field on/off according to value
    let withdrawTerm = $("#withdraw-after-matured").val();
    if (withdrawTerm == "Yes") {
        $("#withdrawal-term").prop("checked", true).change();
    }

    // Maximum amount show based on plan type on load
    $(window).on("load", function () {
        determineDecimalPoint();
        let currencyType = $('#currency-id').find(':selected').data('type');
        $('#currency-type').val(currencyType);
        maximum_amount($("#investment-type").find("option:selected").val());
        $("#interest-time-frame").empty();
        let index;
        let termsArray = getTermArray($("#term-type").find("option:selected").val());
        for (index in termsArray) {
            $("#interest-time-frame").append("<option "+ (selectedProfitAdjust == termsArray[index] ? 'selected' : '') +">" + termsArray[index] + "</option>");
        }
    });

    $(document).on('submit', '#edit-investment-plan-form', function() {
        $("#investment-plan-edit-submit-btn").attr("disabled", true);
        $(".fa-spinner").removeClass('d-none');
        $("#investment-plan-edit-submit-btn-text").text(updateButtonText);
    });
}

// investment plan card js

//status
$(document).ready(function () {
    $(".planInactive, .planActive").on("click", function (event) {
        var id = $(this).attr("data-id");
        var status = $(this).attr("data-status");
        event.preventDefault();
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
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    data: {
                        'status': status,
                        'id': id,
                    },
                    url: statusChangeUrl,
                })
                .done(function(response)
                {
                    Swal.fire(
                        response.title,
                        response.message,
                        response.alert
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                })
                .fail(function(error)
                {
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

    $(".delete").on("click", function (event) {

        var id = $(this).attr("data-id");

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
            confirmButtonText: deleteConfirm,
            cancelButtonText: alertCancel,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "GET",
                    dataType: "JSON",
                    data:{
                        "ajax": true
                    },
                    url: ADMIN_URL + "/investment-plan/delete/" + id,
                })
                .done(function(response)
                {
                    Swal.fire(
                        response.title,
                        response.message,
                        response.alert
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                })
                .fail(function(error)
                {
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
    
    $("#list, #grid").on("click", function (event) {

        var view = $(this).attr("data-view");

        event.preventDefault();
        $.ajax({
            headers:
            {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: "POST",
            data: {
                'view':view
            },
            url: viewChangeUrl,
            beforeSend: function () {
                swal(waitText.replace( /&#039;/g, "'"), LoadingText, {
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                    buttons: false,
                });
            },
        })
        .done(function(response)
        {
            setTimeout(function() {
                swal.close();
                window.location.reload();
            }, 1000);
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
    });
});