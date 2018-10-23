$(document).ready(function(){
    $('.sidenav').sidenav({ edge: 'right' });
    $('.tabs').tabs();
    $('.modal').modal();
    $('select').formSelect();

    $(window).scroll(function(){
        if ($(window).scrollTop() < 2)
        {
            $('header.main nav').addClass('top');
        }else{
            $('header.main nav').removeClass('top');
        }
    });


    $('.quantity.input button.inc').click(function(){
        quantity = increaseQuantity($('.quantity.input .quantity.count').data('quantity'));

        $('.quantity.input .quantity.count').data('quantity', quantity);
        $('.quantity.input .quantity.count').html(quantity);
    });

    $('.quantity.input button.dec').click(function(){
        quantity = decreaseQuantity($('.quantity.input .quantity.count').data('quantity'));
        $('.quantity.input .quantity.count').data('quantity', quantity);
        $('.quantity.input .quantity.count').html(quantity);
    });

});

function increaseQuantity(quantity)
{
    return quantity + 1;
}

function decreaseQuantity(quantity)
{
    if (quantity <= 1){
        return 1;
    }
    return quantity - 1;
}