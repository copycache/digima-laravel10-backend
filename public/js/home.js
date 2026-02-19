var home = new home();

function home()
{
	init();

	function init()
	{
		document_ready();
	}

	function document_ready()
	{
		$(document).ready(function()
		{
			featured_swiper();
			real_estate_swiper();
			ahm_games_swiper();
		});
	}

	function featured_swiper()
	{
		var swiper = new Swiper('.featured-swiper-container', {
	    slidesPerView: 3,
	    slidesPerColumn: 2,
	    spaceBetween: 2,
	    autoplay:
	    {
       		delay: 2500,
       		disableOnInteraction: false
	    },
	    });
	}

	function real_estate_swiper()
	{
		var swiper = new Swiper('.real-estate-container', {
        slidesPerView: 4,
        spaceBetween: 5,
        loop: true,
        loopFillGroupWithBlank: true,
	    autoplay:
	    {
       		delay: 2500,
       		disableOnInteraction: false
	    },
        navigation: {
	        nextEl: '.swiper-state-next',
	        prevEl: '.swiper-state-prev',
	    },
	    });
	}

	function ahm_games_swiper()
	{
		var swiper = new Swiper('.ahm-games-container', {
        slidesPerView: 3,
        spaceBetween: 5,
        loop: true,
        loopFillGroupWithBlank: true,
	    autoplay:
	    {
       		delay: 2500,
       		disableOnInteraction: false
	    },
        navigation: {
	        nextEl: '.swiper-games-next',
	        prevEl: '.swiper-games-prev',
	    },
	    });
	}
}