"use strict";

let enabled = $("#isEnabled").val();
if (enabled == "on" && $("#kyc").val() == "Yes") {
    $("#isEnabled").prop("checked", true).change();
}

let approval = $("#invest-on-admin-approval").val();
if (approval == "on" && $("#invest-start-on-admin-approval").val() == "Yes") {
    $("#invest-on-admin-approval").prop("checked", true).change();
}

$(document).on('submit', '#investment_settings_form', function() {
    $("#investment-settings-submit-btn").attr("disabled", true);
    $(".fa-spinner").removeClass('d-none');
    $("#investment-settings-submit-btn-text").text(submitButtonText);
});
