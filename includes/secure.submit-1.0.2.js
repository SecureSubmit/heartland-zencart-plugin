//$('head').append('<link rel="stylesheet" href='+siteHref+'"includes/modules/payment/securesubmit/assets/css/styles.css" type="text/css" />');

jQuery(document).ready(function ($) {
    $("form[name=checkout_confirmation]").submit(function (e) {
        $("#btn_submit").hide();        
        hps.Messages.post(
                {
                    accumulateData: true,
                    action: 'tokenize',
                    message: public_key
                },
                'cardNumber'
                );
        return false;
    });
  
        //The Integration Code  
       // Create a new `HPS` object with the necessary configuration
       //get current url
       var siteHref = window.location.href;
       siteHref = siteHref.substring(0, siteHref.lastIndexOf("/") + 1);
       

       var hps = new Heartland.HPS({
         publicKey: 'pkapi_cert_jKc1FtuyAydZhZfbB3',
         type:      'iframe',
         class: 'ss',
         // Configure the iframe fields to tell the library where
         // the iframe should be inserted into the DOM and some
         // basic options
         fields: {
           cardNumber: {
             target:      'iframesCardNumber',
             placeholder: '•••• •••• •••• ••••'
           },
           cardExpiration: {
             target:      'iframesCardExpiration',
             placeholder: 'MM / YYYY'
           },
           cardCvv: {
             target:      'iframesCardCvv',
             placeholder: 'CVV'
           }
         },
         // Collection of CSS to inject into the iframes.
         // These properties can match the site's styles
         // to create a seamless experience.
         style: {
           '#iframesCardExpiration' : {
             'height':'50px'
           },
           'input#heartland-field': {
               'box-sizing':'border-box',
               'display': 'block',
               'height': '44px',
               'padding': '6px 12px',
               'font-size': '14px',
               'line-height': '1.42857143',
               'color': '#555',
               'background-color': '#fff',
               'border': '1px solid #ccc',
               'border-radius': '0px',
               '-webkit-box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
               'box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
               '-webkit-transition': 'border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s',
               '-o-transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
               'transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
               'width':'100%',
               'height':'40px'
           },
           'input#heartland-field:hover' :{
               'border':'1px solid #3989e3'
           },
           'input#heartland-field:focus' : {
               'border':'1px solid #3989e3',
               'outline':'none',
               'box-shadow':'none'
           },
           'input[type=submit]' : {
               'box-sizing':'border-box',
               'display': 'inline-block',
               'padding': '6px 12px',
               'margin-bottom': '0',
               'font-size': '14px',
               'font-weight': '400',
               'line-height': '1.42857143',
               'text-align': 'center',
               'white-space': 'nowrap',
               'vertical-align': 'middle',
               '-ms-touch-action': 'manipulation',
               'touch-action': 'manipulation',
               'cursor': 'pointer',
               '-webkit-user-select': 'none',
               '-moz-user-select': 'none',
               '-ms-user-select': 'none',
               'user-select': 'none',
               'background-image': 'none',
               'border': '1px solid transparent',
               'border-radius': '4px',
               'color': '#fff',
               'background-color': '#337ab7',
               'border-color': '#2e6da4'
           },
           '#heartland-field[placeholder]' :{
               'letter-spacing':'3px'
           },
           '#heartland-field[name=cardCvv]' : {
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/cvv1.png) no-repeat right',
               'background-size' :'63px 40px'
           },
           '#heartland-field[name=cardNumber]' : {
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-inputcard-blank@2x.png) no-repeat right',
               'background-size' :'55px 35px'
           },
           'input#heartland-field[name=cardNumber].invalid.card-type-visa' :{
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-saved-visa@2x.png) no-repeat right',
               'background-size' :'83px 88px',
               'background-position-y':'-44px'
           },
           '#heartland-field.valid.card-type-visa' :{
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-saved-visa@2x.png) no-repeat right top',
               'background-size' :'82px 86px'
           },
           '#heartland-field.invalid.card-type-discover' :{
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-saved-discover@2x.png) no-repeat right bottom',
               'background-size' :'85px 85px'
           },
           '#heartland-field.valid.card-type-discover' :{
               'background':'transparent url('+siteHref+'"includes/templates/template_default/images/ss-saved-discover@2x.png) no-repeat right top',
               'background-size' :'85px 83px'
           },
           '#heartland-field.invalid.card-type-amex' :{
               'background':'transparent url('+siteHref+'"includes/templates/template_default/images/ss-savedcards-amex@2x.png") no-repeat right',
               'background-size' :'50px 90px',
               'background-position-y':'-44'
           },
           '#heartland-field.valid.card-type-amex' :{
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-savedcards-amex@2x.png) no-repeat right top',
               'background-size' :'50px 90px'
           },
           '#heartland-field.invalid.card-type-mastercard' :{
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-saved-mastercard.png) no-repeat right',
               'background-size' :'85px 81px',
               'background-position-y':'-55px'
           },
           '#heartland-field.valid.card-type-mastercard' :{
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-saved-mastercard.png) no-repeat right',
               'background-size' :'62px 105px',
               'background-position-y':'-4px'
           },
           '#heartland-field.invalid.card-type-jcb' :{
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-saved-jcb@2x.png) no-repeat right',
               'background-size' :'75px 78px',
               'background-position-y':'-38px'
           },
           '#heartland-field.valid.card-type-jcb' :{
               'background':'transparent url('+siteHref+'includes/templates/template_default/images/ss-saved-jcb@2x.png) no-repeat right top',
               'background-size' :'75px 78px',
               'background-position-y':'1px'
           },
           'input#heartland-field[name=cardNumber]::-ms-clear' : {
               'display':'none'
           }
         },

         // Callback when a token is received from the service
         onTokenSuccess: function (resp) {
           alert('Here is a single-use token: ' + resp.token_value);
         },
         // Callback when an error is received from the service
         onTokenError: function (resp) {
           alert('There was an error: ' + resp.error.message);
         },

        // Callback when a token is received from the service
        onTokenSuccess: function (resp) {            
            secureSubmitResponseHandler(resp);
        },
        // Callback when an error is received from the service
        onTokenError: function (resp) {            
            secureSubmitResponseHandler(resp);
        }
    });    

    function secureSubmitResponseHandler(response) {
        if (response.error.message) {
            $("#btn_submit").show();
            alert(response.error.message);
        } else {
            var form$ = $("form[name=checkout_confirmation]");
            var token = response.token_value;
            
            if ($('#securesubmit_token_field').length > 0)
                $('#securesubmit_token_field').val(token_value);
            else
                form$.append("<input type='hidden' id='securesubmit_token_field' name='securesubmit_token' value='" + token + "'/>");
            
            form$.attr('action', 'index.php?main_page=checkout_process');
            form$.get(0).submit();
        }
    }

});
