$(document).ready(function () {
    $('.item-remove').click(function () {
        var cartItem = $(this).closest('div.cart-item-container');
        $.ajax({
            type: 'DELETE',
            url: '/user/cart/remove',
            data: 'itemId='+ $(this).attr('data-item-id'),
            success: function (response) {
                M.toast({ html: response.data.message });
                if (response.data.status) {
                    cartItem.remove();
                }
            }
        });
    });
});