
//Get merchant network address, merchant network balance and user network address
function getMerchantAndUserNetworkAddressWithMerchantBalance(userId) {
    $.ajax({
        url: addressBalanceUrl,
        type: "get",
        dataType: "json",
        data: {
            network: network,
            user_id: userId,
        },
        beforeSend: function () {
            swal(pleaseWait, loading, {
                closeOnClickOutside: false,
                closeOnEsc: false,
                buttons: false,
            });
        },
    })
    .done(function (res) {
        if (res.status == 401) {
            $(".amount-validation-error").text(res.message);
            userAddressErrorFlag = true;
            amountErrorFlag = true;
            swal({
                title: errorText,
                text: res.message,
                icon: "error",
                closeOnClickOutside: false,
                closeOnEsc: false,
            });
        } else {
            //user-address-div
            $("#user-div").after(`<div class="form-group row" id="user-address-div">
                <label class="col-sm-3 control-label f-14 fw-bold text-end" for="user-address">${userCryptoAddress}</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control f-14" name="userAddress" id="user-address" value="${res.userAddress}" />
                </div>
            </div>`);

            $("#user-address-div").after(`<div class="form-group row" id="submit-anchor-div">
                <label class="col-sm-3"></label>
                <div class="col-sm-6">
                    <a href="${backButtonUrl}" class="btn btn-theme-danger pull-left"><span><i class="fa fa-angle-left"></i>&nbsp;${backButton}</span></a>
                    <button type="submit" class="btn btn-theme pull-right" id="admin-crypto-send-submit-btn">
                        <i class="fa fa-spinner fa-spin d-none"></i>
                        <span id="admin-crypto-send-submit-btn-text">${confirmButton}&nbsp;<i class="fa fa-angle-right"></i></span>
                    </button>
                </div>
            </div>`);

            $("#merchant-address, #merchant-balance, #user-address").attr("readonly", true);

            $(".amount-validation-error").text("");
            userAddressErrorFlag = false;
            amountErrorFlag = false;

            $("#priority").select2({});


            let currencyType = $("#network").data("type");
            if (currencyType == "crypto" || currencyType == "crypto_asset") {
                $("#amount").attr("placeholder", CRYPTODP);
            }

            swal.close();
        }
    })
    .fail(function (error) {
        swal({
            title: errorText,
            text: JSON.parse(error.responseText).exception,
            icon: "error",
            closeOnClickOutside: false,
            closeOnEsc: false,
        });
    });
}

//Get merchant network address, merchant network balance and user network address
$(document).on("change", "#user_id", function (e) {
    //Remove merchant address, merchant balance and amount div on change of network
    $("#merchant-address-div, #merchant-balance-div, #user-address-div, #amount-div, #submit-anchor-div, #priority-div").remove();

    userId = $(this).val();
    let userName = $("#user_id option:selected").text();
    $(".user-full-name").text(userName);

    if (userId) {
        getMerchantAndUserNetworkAddressWithMerchantBalance(userId);
    }
});