jQuery(document).ready(function ($) {
    // Attach a handler to interrupt the form submission
    $("form[name=checkout_confirmation]").submit(function (e) {
        $("#btn_submit").hide();
        e.preventDefault();
        triggerSubmit();
    });

    //The Integration Code
    // Create a new `HPS` object with the necessary configuration
    //get current url
    var siteHref = window.location.href;
    siteHref = siteHref.substring(0, siteHref.lastIndexOf("/") + 1);

    // Create globalpayment configurations
    GlobalPayments.configure({
        publicApiKey: public_key
    });

    const hps = GlobalPayments.ui.form({
        fields: {
            "card-number": {
                placeholder: "•••• •••• •••• ••••",
                target: "#iframesCardNumber"
            },
            "card-expiration": {
                placeholder: "MM / YYYY",
                target: "#iframesCardExpiration"
            },
            "card-cvv": {
                placeholder: "•••",
                target: "#iframesCardCvv"
            }
        },
        styles: {
            'html' : {
                "-webkit-text-size-adjust": "100%"
            },
            'body' : {
                'width' : '100%'
            },
            '#secure-payment-field-wrapper' : {
                'position' : 'relative',
                'width' : '99%'
            },
            '#secure-payment-field' : {
                'background-color' : '#fff',
                'border'           : '1px solid #ccc',
                'display'          : 'block',
                'font-size'        : '14px',
                'height'           : '35px',
                'padding'          : '6px 12px',
                'width'            : '100%',
            },
            '#secure-payment-field-body' :{
                'width' : '99% !important',
                'position' : 'absolute'
            },
            '#secure-payment-field:focus' : {
                "border": "1px solid lightblue",
                "box-shadow": "0 1px 3px 0 #cecece",
                "outline": "none"
            },
            'button#secure-payment-field.submit' : {
                "border": "0",
                "border-radius": "0",
                "background": "none",
                "background-color": "#333333",
                "border-color": "#333333",
                "color": "#fff",
                "cursor": "pointer",
                "padding": ".6180469716em 1.41575em",
                "text-decoration": "none",
                "font-weight": "600",
                "text-shadow": "none",
                "display": "inline-block",
                "-webkit-appearance": "none",
                "height": "initial",
                "width": "100%",
                "flex": "auto",
                "position": "static",
                "margin": "0",
                "white-space": "pre-wrap",
                "margin-bottom": "0",
                "float": "none",
                "font": "600 1.41575em/1.618 Source Sans Pro,HelveticaNeue-Light,Helvetica Neue Light,\r\n                Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif !important"
            },
            '#secure-payment-field[type=button]' : {
                "width": "100%"
            },
            '#secure-payment-field[type=button]:focus' : {
                "color": "#fff",
                "background": "#000000",
                "width": "100%"
            },
            '#secure-payment-field[type=button]:hover' : {
                "color": "#fff",
                "background": "#000000"
            },
            '.card-cvv' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/cvv1.png) no-repeat right',
                'background-size' : '63px 40px'
            },
            '.card-cvv.card-type-amex' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-savedcards-amex@2x.png) no-repeat right top',
                'background-size' : '63px 40px'
            },
            '.card-number' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-inputcard-blank@2x.png) no-repeat right',
                'background-size' : '55px 35px'
            },
            '.card-number.invalid.card-type-amex' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-savedcards-amex@2x.png) no-repeat right',
                'background-position-y' : '-41px',
                'background-size' : '50px 90px'
            },
            '.card-number.invalid.card-type-discover' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-discover@2x.png) no-repeat right bottom',
                'background-position-y' : '-44px',
                'background-size' : '85px 90px'
            },
            '.card-number.invalid.card-type-jcb' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-jcb@2x.png) no-repeat right',
                'background-position-y' : '-44px',
                'background-size' : '55px 94px'
            },
            '.card-number.invalid.card-type-mastercard' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-mastercard.png) no-repeat right',
                'background-position-y' : '-41px',
                'background-size' : '82px 86px'
            },
            '.card-number.invalid.card-type-visa' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-visa@2x.png) no-repeat right',
                'background-position-y' : '-44px',
                'background-size' : '83px 88px',
            },
            '.card-number.valid.card-type-amex' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-discover@2x.png) no-repeat right top',
                'background-position-y' : '3px',
                'background-size' : '50px 90px',
            },
            '.card-number.valid.card-type-discover' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-discover@2x.png) no-repeat right top',
                'background-position-y' : '1px',
                'background-size' : '85px 90px'
            },
            '.card-number.valid.card-type-jcb' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-jcb@2x.png) no-repeat right top',
                'background-position-y' : '2px',
                'background-size' : '55px 94px'
            },
            '.card-number.valid.card-type-mastercard' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-mastercard.png) no-repeat right',
                'background-position-y' : '2px',
                'background-size' : '82px 86px'
            },
            '.card-number.valid.card-type-visa' : {
                'background': 'transparent url(' + siteHref + 'includes/templates/template_default/images/ss-saved-visa@2x.png) no-repeat right top',
                'background-size' : '82px 86px'
            },
            '.card-number::-ms-clear' : {
                'display' : 'none'
            },
            'input[placeholder]' : {
                'letter-spacing' : '.5px',
            },
        }
    });

    hps.on("token-success", function(resp) {
        secureSubmitResponseHandler(resp);
    });

    hps.on("token-error", function(resp) {
        secureSubmitResponseHandler(resp);
    });

    var triggerSubmit = function () {
        // manually include iframe submit button
        const fields = ['submit'];
        const target = hps.frames['card-number'];

        for (const type in hps.frames) {
            if (hps.frames.hasOwnProperty(type)) {
                fields.push(type);
            }
        }

        for (const type in hps.frames) {
            if (!hps.frames.hasOwnProperty(type)) {
                continue;
            }

            const frame = hps.frames[type];

            if (!frame) {
                continue;
            }

            GlobalPayments.internal.postMessage.post({
                data: {
                    fields: fields,
                    target: target.id
                },
                id: frame.id,
                type: 'ui:iframe-field:request-data'
            }, frame.id);
        }
    }

    function secureSubmitResponseHandler(response) {
        if (response.error !== undefined && response.error.message !== undefined) {
            $("#btn_submit").show();
            alert(response.error.message);
        } else {
            var form$ = $("form[name=checkout_confirmation]");
            var token = response.paymentReference;

            if ($('#securesubmit_token_field').length > 0)
                $('#securesubmit_token_field').val(token_value);
            else
                form$.append("<input type='hidden' id='securesubmit_token_field' name='securesubmit_token' value='" + token + "'/>");

            form$.attr('action', 'index.php?main_page=checkout_process');
            form$.get(0).submit();
        }
    }

});
