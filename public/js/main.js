$(document).ready(function(){
    $('.sidenav.sidebar').sidenav({ edge: 'left' });
    $('ul[class^="sidenav"]:not(.sidebar)').sidenav({ edge: 'right' });
    $('.tabs').tabs();
    $('.modal').modal();
    $('select').formSelect();
    $('.timepicker').timepicker({twelveHour: false});
    $('.dropdown-trigger').dropdown();
    $('.tooltip').tooltip();

    $(window).scroll(function(){
        if ($(window).scrollTop() < 2)
        {
            $('header.main nav').addClass('top');
        }else{
            $('header.main nav').removeClass('top');
        }
    });


    $('.quantity.input button.inc').click(function(){
        quantity = increaseQuantity(Number($('.quantity.input .quantity.count').attr('data-quantity')));

        $('.quantity.input .quantity.count').attr('data-quantity', quantity);
        $('.quantity.input .quantity.count').html(quantity);
    });

    $('.quantity.input button.dec').click(function(){
        quantity = decreaseQuantity(Number($('.quantity.input .quantity.count').attr('data-quantity')));
        $('.quantity.input .quantity.count').attr('data-quantity', quantity);
        $('.quantity.input .quantity.count').html(quantity);
    });


    $('.menu-card').click(function(){
        $('#item-modal .modal-title').html($(this).data('title'));
        $('#item-modal .modal-title').attr('data-title', $(this).data('title'));
        $('#item-modal p.description').html($(this).data('description'));
        $('#item-modal .price').html('EUR ' + Number($(this).data('price')).toFixed(2));
        $('#item-modal').attr('data-item-id', $(this).data('id'));
        $('.menu-display .quantity.count').html(1);
        $('.menu-display .quantity.count').attr('data-quantity',1);
    });

    if (Number($('#item-modal').attr('data-item-id')) !== 0)
    {
        $('#addtocart').click(function () {
            $.ajax({
                type: 'POST',
                url: '/user/cart/add',
                data: 'itemId='+ Number($('#item-modal').attr('data-item-id')) +'&quantity='+ Number($('#item-modal .quantity.input .quantity.count').attr('data-quantity')),
                success: function (data){
                    M.toast({ html: data.data.message });
                }
            });
        });
    }


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