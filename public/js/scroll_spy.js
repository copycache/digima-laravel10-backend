(function($) 
{
    'use strict'; // Start of use strict

    /*------------------------------------------------------------------
    Navigation Hover effect
    ------------------------------------------------------------------*/
    // jQuery for page scrolling feature - requires jQuery Easing plugin

    $('.smoth-scroll').on('click', function(event) 
    {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top - 60)
        }, 1000, 'easeInOutExpo'); //1250
        
        event.preventDefault();
    });
    // Highlight the top nav as scrolling occurs

    $('body').scrollspy(
    {
        target: '.navbar-default',
        offset: 70
    });

})(jQuery);