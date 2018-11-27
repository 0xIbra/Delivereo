$(document).ready(function () {
    $('.on-card button.increase').click(function (){
        var id = $(this).data('item-id');
        $.ajax({
            type: 'PUT',
            url: '/user/cart/increase',
            data: 'itemId='+ id,
            success: function (response) {
                M.toast({ html: response.data.message });
                if (response.data.status)
                {
                    $('.menu-card-'+ id +' .card-title-quantity').html('x'+ response.data.quantity);
                    $('.menu-card-'+ id +' .card-title-quantity').attr('data-quantity', response.data.quantity);
                    $('.menu-card-'+ id +' .quantity.count').attr('data-quantity', response.data.quantity);
                    $('.menu-card-'+ id +' .quantity.count').html(response.data.quantity);
                    $('.cost-card-'+ id +' .menu-name .quantity').html('x'+ response.data.quantity);
                    $('.cost-card-'+ id +' .menu-price').html(Number(response.data.newPrice).toFixed(2) +' EUR');
                    $('#checkout .total-price').html(Number(response.data.totalPrice).toFixed(2) +' EUR');
                }
            }
        });
    });

    $('.on-card button.decrease').click(function (){
        var id = $(this).data('item-id');
        $.ajax({
            type: 'PUT',
            url: '/user/cart/decrease',
            data: 'itemId='+ id,
            success: function (response) {
                M.toast({ html: response.data.message });
                if (response.data.status)
                {
                    $('.menu-card-'+ id +' .card-title-quantity').html('x'+ response.data.quantity);
                    $('.menu-card-'+ id +' .card-title-quantity').attr('data-quantity', response.data.quantity);
                    $('.menu-card-'+ id +' .quantity.count').attr('data-quantity', response.data.quantity);
                    $('.menu-card-'+ id +' .quantity.count').html(response.data.quantity);
                    $('.cost-card-'+ id +' .menu-name .quantity').html('x'+ response.data.quantity);
                    $('.cost-card-'+ id +' .menu-price').html(Number(response.data.newPrice).toFixed(2) +' EUR');
                    $('#checkout .total-price').html(Number(response.data.totalPrice).toFixed(2) +' EUR');
                }
            }
        });
    });

    $('.item-remove').click(function () {
        var id = $(this).attr('data-item-id');
        var cartItem = $('.cart-item-container-'+ id);
        $.ajax({
            type: 'DELETE',
            url: '/user/cart/remove',
            data: 'itemId='+ id,
            success: function (response) {
                M.toast({ html: response.data.message });
                if (response.data.status) {
                    cartItem.remove();
                    $('.cost-card-'+ id).remove();
                    $('#checkout .total-price').html(Number(response.data.totalPrice).toFixed(2) +' EUR');
                }
            }
        });
    });
});