$(document).ready(function (){
    if ($('.floating-alerts .floating-alert').length > 0)
    {
        $('.floating-alerts').css('opacity', 1);
        $('.floating-alerts').css('transform', 'translateX(0%)');
    }

    setTimeout(
        function (){
            $('.floating-alerts').css('transform', 'translateX(130%)');
            $('.floating-alerts').css('opacity', 0);
            setTimeout(function () {
                $('.floating-alerts').empty();
            }, 2000)
        },
        5000
    )
});