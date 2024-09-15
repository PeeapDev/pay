var hasPhoneError = false;
var hasEmailError = false;

function enableDisableButton() {
    if (!hasPhoneError && !hasEmailError) {
        $('form').find(".common_button").prop('disabled', false);
    } else {
        $('form').find(".common_button").prop('disabled', true);
    }
}

$("#phone").intlTelInput({
    separateDialCode: true,
    nationalMode: true,
    preferredCountries: [countryShortCode],
    autoPlaceholder: "polite",
    placeholderNumberType: "MOBILE",
    utilsScript: utilsScriptLoadingPath
});

if (formattedPhoneNumber !== null && defaultCountry !== null && carrierCode !== null) {
    $("#phone").intlTelInput("setNumber", formattedPhoneNumber);
    $('#defaultCountry').val(defaultCountry);
    $('#carrierCode').val(carrierCode);
    $('#formattedPhone').val(formattedPhoneNumber);
}

function updatePhoneInfo() {
    let promiseObj = new Promise(function (resolve, reject) {
        hasPhoneError = true;
        enableDisableButton();
        $('#defaultCountry').val($('#phone').intlTelInput('getSelectedCountryData').iso2);
        $('#carrierCode').val($('#phone').intlTelInput('getSelectedCountryData').dialCode);

        if ($('#phone').val != '') {
            $("#formattedPhone").val($('#phone').intlTelInput("getNumber").replace(/-|\s/g, ""));
        }
        resolve();
    });
    hasPhoneError = false;
    enableDisableButton();
    return promiseObj;
}

function checkDuplicatePhoneNumber() {
    $.post({
        url: duplicatePhoneCheckUrl,
        dataType: 'json',
        data: {
            '_token': csrfToken,
            'phone': $.trim($('#phone').val()),
            'carrierCode': $.trim($('#phone').intlTelInput('getSelectedCountryData').dialCode),
            'id': userId,
        }
    })
    .done(function (response) {
        if (response.status) {
            $('#phone-error').show().addClass('error').html(response.fail);
            hasPhoneError = true;
            enableDisableButton();
        } else {
            $('#phone-error').html('');
            hasPhoneError = false;
            enableDisableButton();
        }
    });
}

function validateInternaltionalPhoneNumber() {
    let promiseObj = new Promise(function (resolve, reject) {
        let resolveStatus = false;
        if ($.trim($('#phone').val()) !== '') {
            if (!$('#phone').intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($('#phone').val()))) {
                $('#phone-error').html('');
                $('#tel-error').addClass('error').html(validPhoneNumberErrorText);
                hasPhoneError = true;
                enableDisableButton();
            } else {
                resolveStatus = true;
                $('#tel-error').html('');
                hasPhoneError = false;
                enableDisableButton();
            }
        } else {
            $('#tel-error').html('');
            hasPhoneError = false;
            enableDisableButton();
        }
        resolve(resolveStatus);
    });
    return promiseObj;
}

//is_sms_env_enabled and phone verification check
$(document).ready(function()
{
    let is_sms_env_enabled = $('#is_sms_env_enabled').val();
    let checkPhoneVerification = $('#checkPhoneVerification').val();

    if ((! is_sms_env_enabled && checkPhoneVerification != "Enabled") || checkPhoneVerification != "Enabled") {
        
        if (formattedPhoneNumber) {
            $('.next').removeClass("next").addClass('edit_form_submit').html(updatePhoneText);
        } else {
            $('.next').removeClass("next").addClass('form_submit').html(submitText);
        }
    } else {   
        $('.next').removeClass("form_submit").addClass('next').html(nextText);
    }
});

// next
$(document).on('click', '.next', function()
{
    let phone = $("input[name=phone]");
    if (phone.val() == '') {
        $('#phone-number-error').addClass('error').html(fieldRequiredText).css({
            'color' : '#f50000 !important',
            'font-size' : '14px',
            'font-weight' : '400',
            'padding-top' : '5px',
        });
        return false;
    } else if(phone.hasClass('error')) {
        return false;
    }
    else
    {
        $('.modal-title').html(getCodeText);
        $('#subheader_text').html(verificationCodeText);
        $('#static_phone_show').show();
        $('.edit').show();
        $(this).removeClass("next").addClass("get_code").html(getCodeText);
        let fullPhone = $("#phone").intlTelInput("getNumber");
        $('#static_phone_show').html(fullPhone + '&nbsp;&nbsp;');
        return true;
    }
});

//get_code
$(document).on('click', '.get_code', function()
{
    let pluginCarrierCode = $('#phone').intlTelInput('getSelectedCountryData').dialCode;
    let pluginPhone = $("#phone").intlTelInput("getNumber");

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        url: getVerificationCodeUrl,
        dataType: "json",
        cache: false,
        data: {
            'phone': pluginPhone,
            'carrierCode': pluginCarrierCode,
        }
    })
    .done(function(response)
    {
        if (response.status) {
            $('.modal-title').html(verifyPhoneText);
            $('.phone_group').hide();
            $('.static_phone_show').html('');
            $('#subheader_text').html(smsCodeSentText+ '<br><br>' + smsCodeSubmitText);
            $('.edit').hide();
            $('#phone_verification_code').val('');
            $('.phone_verification_section').removeClass('d-none');
            $('.get_code').removeClass("get_code").addClass("verify").html(verifyText);
            $('#hasVerificationCode').val(response.message);
        } else {
            $('#message').removeClass('d-none');
            $('#message').html('SMS not send');
        }
    });
});

//verify
$(document).on('click', '.verify', function()
{
    let phone_verification_code = $("#phone_verification_code").val();

    let pluginPhone = $("#phone").intlTelInput("getNumber");
    let pluginCarrierCode = $('#phone').intlTelInput('getSelectedCountryData').dialCode;
    let pluginDefaultCountry = $('#phone').intlTelInput('getSelectedCountryData').iso2;

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        url: completeVerificationCodeUrl,
        dataType: "json",
        cache: false,
        data: {
            'phone': pluginPhone,
            'defaultCountry': pluginDefaultCountry,
            'carrierCode': pluginCarrierCode,
            'phone_verification_code': phone_verification_code,
        }
    })
    .done(function(data)
    {
        if (!data.status || data.status == 500) {
            $('#message').removeClass('d-none');
            $('#message').html(data.message);
            $('#message').addClass(data.error);
        } else {
   
            $('#message').removeClass('error');
            $('#message').addClass('d-none');
            $('#message').html();
            $('#subheader_text').html();
            $('.phone-text').html(pluginPhone);
            $('.phone_verification_section').addClass('d-none');
            $('#subheader_text').html('');
            $('#close').hide();
            $('.common_button').removeClass("verify").addClass("next").html(nextText);
            $('.modal-title').html(updatePhoneText);
            $('.phone_group').show();
            $('#exampleModal-4').modal('toggle');
            swal({
                title: successText,
                text:  data.message,
                icon: "success",
                closeOnClickOutside: false,
                closeOnEsc: false,
            });
        }
    });
    
});

//form_submit
$(document).on('click', '.form_submit', function()
{
    let pluginPhone = $("#phone").intlTelInput("getNumber");
    let pluginDefaultCountry = $('#phone').intlTelInput('getSelectedCountryData').iso2;
    let pluginCarrierCode = $('#phone').intlTelInput('getSelectedCountryData').dialCode;

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        url: phoneNumberAddUrl,
        dataType: "json",
        cache: false,
        data: {
            'phone': pluginPhone,
            'defaultCountry': pluginDefaultCountry,
            'carrierCode': pluginCarrierCode,
        }
    })
    .done(function(data)
    {
        if (data.status) {
            $('.phone-text').html(pluginPhone);
            $('#exampleModal-4').modal('toggle');

            swal({
                title: successText,
                text:  data.message,
                icon: "success",
                closeOnClickOutside: false,
                closeOnEsc: false,
            });

            $('.form_submit').removeClass("form_submit").addClass('edit_form_submit').html(submitText);
            $('.modal-title').html(updatePhoneText);
        }
    });
    
});


//clear inputs on close - edit modal
$('#editModal').on('hidden.bs.modal', function () {
    if ($("#edit_phone").val() != '') {
        $("#edit_phone").val(`+${OrginalUsercarrierCode}${OrginalUserphone}`)
        //need to reload - or validation message still exists.
        window.location.reload(); 
    }
});

$(document).ready(function()
{
    $("#edit_phone").intlTelInput({
        separateDialCode: true,
        nationalMode: true,
        preferredCountries: ["us"],
        autoPlaceholder: "polite",
        placeholderNumberType: "MOBILE",
        formatOnDisplay: false,
        utilsScript: utilsScriptLoadingPath

    })
});

//when phone verificaiton is disabled
$(document).on('click', '.edit_form_submit', function()
{
    let pluginPhone = $("#phone").intlTelInput("getNumber");
    let pluginDefaultCountry = $('#phone').intlTelInput('getSelectedCountryData').iso2;
    let pluginCarrierCode = $('#phone').intlTelInput('getSelectedCountryData').dialCode;

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        url: phoneNumberUpdateUrl,
        dataType: "json",
        cache: false,
        data: {
            'phone': pluginPhone,
            'flag': pluginDefaultCountry,
            'code': pluginCarrierCode,
        }
    })
    .done(function(data)
    {
        if (data.status) {
            $('.phone-text').html(pluginPhone);
            $('#exampleModal-4').modal('toggle');


            swal({
                title: successText,
                text:  data.message,
                icon: "success",
                closeOnClickOutside: false,
                closeOnEsc: false,
            });
            $('#message').css('display', 'block');
            $('#message').html(data.message);
            $('#message').addClass(data.class_name);
            $('#close').hide();
        }
    });
    
});
