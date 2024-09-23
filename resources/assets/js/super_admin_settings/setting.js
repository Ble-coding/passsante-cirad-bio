document.addEventListener('turbo:load', loadSuperAdminSettingDate)

function loadSuperAdminSettingDate() {

    let CaptchaCheckbox = $('#captchaEnableAdmin').is(':checked')
    // let stripeCheckbox = $('#stripeEnableAdmin').is(':checked')
    // let paypalCheckbox = $('#paypalEnableAdmin').is(':checked')
    // let razorpayCheckbox = $('#razorpayEnableAdmin').is(':checked')

    if (CaptchaCheckbox) {
        $('.captcha-div').removeClass('d-none')
    } else {
        $('.captcha-div').addClass('d-none')
    }
    // if (stripeCheckbox) {
    //     $('.stripe-div-admin').removeClass('d-none')
    // } else {
    //     $('.stripe-div-admin').addClass('d-none')
    // }
    // if (paypalCheckbox) {
    //     $('.paypal-div-admin').removeClass('d-none')
    // } else {
    //     $('.paypal-div-admin').addClass('d-none')
    // }
    // if (razorpayCheckbox) {
    //     $('.razorpay-div-admin').removeClass('d-none')
    // } else {
    //     $('.razorpay-div-admin').addClass('d-none')
    // }
    if (typeof $('#footerSettingPhoneNumber').val() != 'undefined' &&
        $('#footerSettingPhoneNumber').val() !== '')
        $('.phoneNumber').trigger('blur')
    if ($('#defaultCountryCode').length) {
        let input = document.querySelector('#defaultCountryData')
        let intl = window.intlTelInput(input, {
            initialCountry: defaultCountryCodeValue,
            separateDialCode: true,
            geoIpLookup: function (success, failure) {
                $.get('https://ipinfo.io', function () {
                }, 'jsonp').always(function (resp) {
                    var countryCode = (resp && resp.country)
                        ? resp.country
                        : ''
                    success(countryCode)
                })
            },
            utilsScript: '../../public/assets/js/inttel/js/utils.min.js',
        })
        let getCode = intl.selectedCountryData['name'] + '+' +
            intl.selectedCountryData['dialCode']
        $('#defaultCountryData').val(getCode)
        // $('.iti__flag').attr('class').split(' ')[1]

    }
    $('#defaultLanguage').select2({
        width: '100%',
    })
    if (!$('#footerSettingPhoneNumber').length) {
        return
    }

}

listenChange('#appLogo', function () {
    $('#validationErrorsBox').addClass('d-none');
    if (isValidLogo($(this), '#validationErrorsBox')) {
        displayLogo(this, '#previewImage');
    }
})

listenSubmit('#createSetting', function (event) {
    event.preventDefault();
    let captchaCheckbox = $('#captchaEnableAdmin').is(':checked')

    if (captchaCheckbox && $('#captchaKey').val().trim() == '') {
        displayErrorMessage(Lang.get('messages.new_change.captcha_key'))
        return false
    }
    if (captchaCheckbox && $('#captchaSecret').val().trim() == '') {
        displayErrorMessage(Lang.get('messages.new_change.captcha_secret'))
        return false
    }
    $('#createSetting')[0].submit();

    return true;
})
listenSubmit('#adminPaymentForm',function (event) {
    event.preventDefault();

    if ($('#StripeAdminKey').val().trim() != '' && $('#StripeAdminSecret').val().trim() == '') {
        displayErrorMessage(Lang.get('messages.new_change.stripe_secret'))
        return false
    }
    if ( $('#StripeAdminKey').val().trim() == '' && $('#StripeAdminSecret').val().trim() != '') {
        displayErrorMessage(Lang.get('messages.new_change.stripe_secret'))
        return false
    }

    if ($('#paypalKeyAdmin').val().trim() == '' && $('#paypalSecretAdmin').val().trim() != '') {
        displayErrorMessage(Lang.get('messages.new_change.paypal_client_id'))
        return false
    }
    if ( $('#paypalSecretAdmin').val().trim() != '' && $('#paypalSecretAdmin').val().trim() == '') {
        displayErrorMessage(Lang.get('messages.new_change.paypal_secret'))
        return false
    }
    if ($('#paypalModeAdmin').val().trim() == '' && $('#paypalSecretAdmin').val().trim() != '' && $('#paypalSecretAdmin').val().trim() != '') {
        displayErrorMessage(Lang.get('messages.new_change.paypal_mode'))
        return false
    }


    if ($('#razorpayKeyAdmin').val().trim() != '' && $('#razorpaySecretAdmin').val().trim() == '') {
        displayErrorMessage(Lang.get('messages.new_change.razor_pay_secret'))
        return false
    }
    if ($('#razorpaySecretAdmin').val().trim() != '' && $('#razorpayKeyAdmin').val().trim() == '') {
        displayErrorMessage(Lang.get('messages.new_change.razorpay_key'))
        return false
    }
    $('#adminPaymentForm')[0].submit();

    return true;
})

window.isValidLogo = function (inputSelector, validationMessageSelector) {
    let ext = $(inputSelector).val().split('.').pop().toLowerCase();
    if ($.inArray(ext, ['jpg', 'png', 'jpeg']) == -1) {
        $(inputSelector).val('');
        $(validationMessageSelector).removeClass('d-none');
        $(validationMessageSelector).
            html(Lang.get('messages.new_change.image_must_be')).
            show();
        return false;
    }
    $(validationMessageSelector).hide();
    return true;
};

window.displayLogo = function (input, selector) {
    let displayPreview = true;
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function (e) {
            let image = new Image();
            image.src = e.target.result;
            image.onload = function () {
                /*
                if (image.height != 60 && image.width != 90) {
                    $(selector).val('');
                    $('#validationErrorsBox').removeClass('d-none');
                    $('#validationErrorsBox').
                        html($('#imageValidation').val()).
                        show();
                    return false;
                }
                 */
                $(selector).attr('src', e.target.result)
                displayPreview = true
            };
        };
        if (displayPreview) {
            reader.readAsDataURL(input.files[0])
            $(selector).show()
        }
    }
};
listenClick('.iti__standard', function () {
    $('#defaultCountryData').val($(this).text())
    $(this).attr('data-country-code')
    $('#defaultCountryCode').val($(this).attr('data-country-code'))
})

listenClick('#resetFilter', function () {
    $('#filter_status').val('2').trigger('change');
    hideDropdownManually('.dropdown-menu,#dropdownMenuButton1')
})

listenSubmit('#superAdminFooterSettingForm', function (event) {
    event.preventDefault();

    if ($('.error-msg').text() !== '') {
        $('.phoneNumber').focus();
        return false;
    }

    let facebookUrl = $('#facebookUrl').val();
    let twitterUrl = $('#twitterUrl').val();
    let instagramUrl = $('#instagramUrl').val();
    let linkedInUrl = $('#linkedInUrl').val();

    let facebookExp = new RegExp(
        /^(https?:\/\/)?((m{1}\.)?)?((w{2,3}\.)?)facebook.[a-z]{2,3}\/?.*/i);
    let twitterExp = new RegExp(
        /^(https?:\/\/)?((m{1}\.)?)?((w{2,3}\.)?)twitter\.[a-z]{2,3}\/?.*/i);
    let instagramUrlExp = new RegExp(
        /^(https?:\/\/)?((w{2,3}\.)?)instagram.[a-z]{2,3}\/?.*/i);
    let linkedInExp = new RegExp(
        /^(https?:\/\/)?((w{2,3}\.)?)linkedin\.[a-z]{2,3}\/?.*/i);

    let facebookCheck = (facebookUrl == '' ? true : (facebookUrl.match(
        facebookExp) ? true : false));
    if (!facebookCheck) {
        displayErrorMessage(Lang.get('messages.common.please_enter_valid_facebook_url'));
        return false;
    }
    let twitterCheck = (twitterUrl == '' ? true : (twitterUrl.match(twitterExp)
        ? true
        : false));
    if (!twitterCheck) {
        displayErrorMessage(Lang.get('messages.common.please_enter_valid_twitter_url'));
        return false;
    }
    let instagramCheck = (instagramUrl == '' ? true : (instagramUrl.match(
        instagramUrlExp) ? true : false));
    if (!instagramCheck) {
        displayErrorMessage(Lang.get('messages.common.please_enter_valid_Instagram_url'));
        return false;
    }
    let linkedInCheck = (linkedInUrl == '' ? true : (linkedInUrl.match(
        linkedInExp) ? true : false));
    if (!linkedInCheck) {
        displayErrorMessage(
            Lang.get('messages.common.please_enter_valid_Instagram_url'))
        return false
    }
    $('#superAdminFooterSettingForm')[0].submit()

    return true
})

listenClick('.iti__standard,.iti__preferred', function () {
    $('#defaultCountryData').val($(this).text())
    $('#defaultCountryCode').val($(this).attr('data-country-code'))
})

listen('change', '#captchaEnableAdmin', function () {
    let StripeCheckbox = $('#captchaEnableAdmin').is(':checked')
    if (StripeCheckbox) {
        $('.captcha-div').removeClass('d-none')
    } else {
        $('.captcha-div').addClass('d-none')
    }
})

// listen('change', '#stripeEnableAdmin', function () {
//     let StripeCheckbox = $('#stripeEnableAdmin').is(':checked')
//     if (StripeCheckbox) {
//         $('.stripe-div-admin').removeClass('d-none')
//     } else {
//         $('.stripe-div-admin').addClass('d-none')
//     }
// })
// listen('change', '#paypalEnableAdmin', function () {
//     let paypalEnableAdmin = $('#paypalEnableAdmin').is(':checked')
//     if (paypalEnableAdmin) {
//         $('.paypal-div-admin').removeClass('d-none')
//     } else {
//         $('.paypal-div-admin').addClass('d-none')
//     }
// })
// listen('change', '#razorpayEnableAdmin', function () {
//     let razorpayEnableAdmin = $('#razorpayEnableAdmin').is(':checked')
//     if (razorpayEnableAdmin) {
//         $('.razorpay-div-admin').removeClass('d-none')
//     } else {
//         $('.razorpay-div-admin').addClass('d-none')
//     }
// })
