'use strict';

$(document).on('submit', '#WoocommerceConfigureForm', function() {
    $('.submit-btn').attr('disabled', true);
    $('.cancel-btn').attr('disabled',true);
    $('.fa-spin').removeClass('d-none');
    $('.submit-btn-text').text(submitText);
});