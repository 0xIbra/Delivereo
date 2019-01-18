$(document).ready(function (){
    var stripe = Stripe($('meta#stripe-key').attr('content'));
    var elements = stripe.elements({
        'locale': 'fr'
    });

    var style = {
        base: {
            fontSize: '16px',
            color: '#32325d',
        }
    };

    var card = elements.create('card', { style: style });
    console.log(card);
    card.mount('#card-element');

    card.on('change', function (e) {
       var displayError = $('#card-errors');
       if (e.error)
       {
           displayError.textContent = e.error.message;
       }else
       {
           displayError.textContent = '';
       }
    });

    var form = $('#payment-form');
    form.on('submit', function (e){
        if ($('.credit-card-method').is(':checked'))
        {

            e.preventDefault();

            stripe.createToken(card).then(function (result){
                if (result.error)
                {
                    var errorElement = $('#card-errors');
                    errorElement.textContent = result.error.message;
                }else
                {
                    if (!$('#stripe-token').length)
                    {
                        stripeTokenHandler(result.token);
                    }
                }
            });
        }
    });
});

function stripeTokenHandler(token) {
    // Insert the token ID into the form so it gets submitted to the server
    var form = $('#payment-form');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    hiddenInput.setAttribute('id', 'stripe-token');
    form.append(hiddenInput);

    // Submit the form
    $('form#payment-form')[0].submit();
}