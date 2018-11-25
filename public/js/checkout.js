$(document).ready(function () {
    $('.card-number').on('keyup', function(){
        var foo = $(this).val().split(" ").join("");
        if (foo.length > 0) {
            foo = foo.match(new RegExp('.{1,4}', 'g')).join(" ");
        }
        $(this).val(foo);
    });

    $('.expiration').on('keyup', function (e){
        console.log(e.keyCode);
        var val = $(this).val();
        if (val.length === 2 && e.keyCode !== 8 && e.keyCode !== 46)
        {
            if (val.substr(0, 2) > 12)
            {
                val = '01';
            }
            val = val.concat('/');
            $(this).val(val);
        }
    });

    $('form').submit(function (e) {

        if ($('.credit-card-method:checked'))
        {
            var number = $('.card-number').val();
            number = number.replace(/ /g,'');

            var expiration = $('.expiration').val();
            expiration = expiration.replace('/', '');

            var cvc = $('.cvc').val();

            if ($.isNumeric(number) && $.isNumeric(expiration) && $.isNumeric(cvc))
            {
                $('form').submit();
            }else
            {
                e.preventDefault();
                M.toast({ html: 'Carte bancaire n\'est pas valide' });
            }
        }
    });

});