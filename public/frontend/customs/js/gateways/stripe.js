"use strict";

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return (charCode > 31 && (charCode < 48 || charCode > 57)) ? false : true;
}

function stripeMonutdiv(paymentElement) {
    return new Promise((resolve, reject) => {
        paymentElement.mount('#payment-element');
        // Assuming mounting is synchronous. If it's asynchronous, call resolve() accordingly.
        resolve();
    });
}


const stripe = Stripe(publishableKey, {
    locale: lang
});


var mode = localStorage.getItem('theme');

var theme, bgcolor;

if (localStorage.getItem('theme') === 'dark') {
    theme = 'stripe';
    bgcolor = '#F5F6FA';
} else {
    theme = 'night';
    bgcolor = '#30313d';
}

$("#switch").on('click', function () {
    location.reload();
});

const options = {
    clientSecret: paymentIntendKey,
    appearance: {
        theme: theme,

        variables: {
            colorBackground: bgcolor,
            spacingUnit: '6.61px',
            borderRadius: '5px',
        }
    }
};

const elements = stripe.elements(options);
const paymentElement = elements.create('payment');


stripeMonutdiv(paymentElement).then(() => {
    return new Promise((resolve) => {
        setTimeout(resolve, 3000);
    });
}).then(() => {
    $('#stripe_btn').prop('disabled', false);
});



const form = document.getElementById('payment-form');
form.addEventListener('submit', async (event) => {

    event.preventDefault();

    $("#stripe_btn").attr("disabled", true);
    $(".spinner").removeClass('d-none');
    $("#stripeSubmitBtnText").text(stripeSubmitBtnText);

    const {error} = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: verifyUrl + '?params=' + params,
        }
    });


    if (error) {
        $("#stripe_btn").attr("disabled", false);
        $(".spinner").addClass('d-none');
        $("#stripeSubmitBtnText").text(confirmText);
    }

    

});




