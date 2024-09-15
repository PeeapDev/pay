if ($(".main-containt").find("#invest_stripe").length) {
    var paymentIntendId = null;
    var paymentMethodId = null;
    function isNumber(evt) 
    {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    function investBack()
    {
        window.localStorage.setItem("investConfirmPreviousUrl",document.URL);
        window.history.back();
    }

    //Only go back by back button, if submit button is not clicked
    $(document).on('click', '.invest-confirm-back-btn', function (e)
    {
        e.preventDefault();
        investBack();
    });

    function makePayment()
    {
        var promiseObj = new Promise(function(resolve, reject)
        {
            var cardNumber = $("#cardNumber").val().trim();
            var month      = $("#month").val().trim();
            var year       = $("#year").val().trim();
            var cvc        = $("#cvc").val().trim();
            var token      = $("#token").val().trim();
            $("#stripeError").html('');
            if (cardNumber && month && year && cvc) {
                $.ajax({
                    type: "POST",
                    url: makePaymentUrl,
                    data:
                    {
                        "_token": token,
                        'cardNumber': cardNumber,
                        'month': month,
                        'year': year,
                        'cvc': cvc
                    },
                    dataType: "json",
                    beforeSend: function (xhr) {
                        $("#investment-stripe-submit-btn").attr("disabled", true);
                    },
                }).done(function(response)
                {   
                    if (response.data.status != 200) {
                        $("#stripeError").html(response.data.message);
                        $("#investment-stripe-submit-btn").attr("disabled", true);
                        reject(response.data.status);
                        return false;    
                    } else {
                        resolve(response.data);
                        $("#investment-stripe-submit-btn").attr("disabled", false);
                    }
                });
            }
        });
        return promiseObj;
    }

    function confirmPayment()
    {
        makePayment().then(function(result) {
            var token      = $("#token").val().trim();
            $.ajax({
                type: "POST",
                url: confirmPaymentUrl,
                data:
                {
                    "_token": token,
                    'paymentIntendId': result.paymentIntendId,
                    'paymentMethodId': result.paymentMethodId,
                },
                dataType: "json",
                beforeSend: function (xhr) {
                    $("#investment-stripe-submit-btn").attr("disabled", true);
                    $(".fa-spin").show();
                    $("#investment-stripe-submit-btn-txt").text(submitButtonText);
                },
            }).done(function(response)
            {   
                $("#investment-stripe-submit-btn-txt").text(pretext);
                $(".fa-spin").hide();
                if (response.data.status != 200) {
                    $("#investment-stripe-submit-btn").attr("disabled", true);
                    $("#stripeError").html(response.data.message);
                    if (typeof response.data.investment !== 'undefined' || response.data.investment !== null) {
                        setTimeout(function() {
                            window.location.replace(investUrl);
                        }, 3000);
                    }
                    return false;    
                } else {
                    $("#investment-stripe-submit-btn").attr("disabled", false);
                }
                window.location.replace(SITE_URL + '/investment/success/' + response.data.investId);
            });
        });
    }

    $("#month").change(function() { 
        $("#investment-stripe-submit-btn").attr("disabled", true);
        makePayment();
    });

    $("#year, #cvc").on('keyup', $.debounce(500, function() {
        $("#investment-stripe-submit-btn").attr("disabled", true);
        makePayment();
    }));

    $("#cardNumber").on('keyup', $.debounce(1000, function() {
        $("#investment-stripe-submit-btn").attr("disabled", true);
        makePayment();
    }));
    // For card number design
    document.getElementById('cardNumber').addEventListener('input', function (e) {
        var target = e.target, position = target.selectionEnd, length = target.value.length;
        target.value = target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
        target.selectionEnd = position += ((target.value.charAt(position - 1) === ' ' && target.value.charAt(length - 1) === ' ' && length !== target.value.length) ? 1 : 0);
    });

    $(document).ready(function() {
        $("#investment-stripe-submit-btn").attr("disabled", true);

        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };
    });

    //invest stripe form submitting
    $(document).on('submit', '#payment-form', function(event) {
        event.preventDefault();
        $(".spinner").removeClass('d-none');
        $("#invest-money-text").text(submitButtonText);
        confirmPayment();
    });
}

if ($(".main-containt").find("#invest_paypal").length) {
    paypal.Buttons({
        createOrder: function (data, actions) {
            // This function sets up the details of the transaction, including the amount and line item details.
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: amount
                    }
                }]
            });
        },
        onApprove: function (data, actions) {
            // This function captures the funds from the transaction.
            return actions.order.capture().then(function (details) {
                // This function shows a transaction success message to your buyer.
                window.location.replace(successUrl);
            });
        }
    }).render('#paypal-button-container');
}

if ($(".main-content").find("#invest_payeer").length) {
    $('#payeer-submit-button').trigger('click');
}

if ($(".main-content").find("#invest_payumoney").length) {
    $('#payumoney-submit-button').trigger('click');
}
