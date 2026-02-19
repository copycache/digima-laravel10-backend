var theme_custom = new theme_custom();

function theme_custom()
{
	init();

	function init()
	{
        window_load();
		$(document).ready(function()
		{
			document_ready();
		});   
	}
    
    function window_load()
    {
        $(window).load(function()
        {
            $('.loader-container').fadeOut();
        });
    }

	function document_ready()
	{
        product_mobile_dropdown();
        company_mobile_dropdown();
        close_side_nav();
		action_facebook_widget();
		event_header_fix();
		event_action_click();
        event_mobile_sidenav();
        scroll_up();
        match_height();
        genealogy_mobile_dropdown();
        reports_mobile_dropdown();
	}

    function match_height()
    {
        $(".match-height").matchHeight();
    }

    function scroll_up()
    {
        /*scroll up*/
        $(window).scroll(function () {
            if ($(this).scrollTop() > 700) {
                $('.scroll-up').fadeIn();
            } else {
                $('.scroll-up').fadeOut();
            }
        });

        $('.scroll-up').click(function () {
            $("html, body").animate({
                scrollTop: 0
            }, 700);
            return false;
        });
    }

	function action_facebook_widget()
	{
		(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.10";
        fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
	}

	function event_header_fix()
	{
        $window = $(window);
        $window.scroll(function()
        {
            $scroll_position = $window.scrollTop();
            if ($scroll_position > 32.2167)
            {
                $('.header-container').addClass('header-fixed');
                $('.subheader-container').addClass('header-fixed');

                header_height = $('.your-header').innerHeight();
                $('body').css('padding-top', header_height);
            }
            else
            {
                $('body').css('padding-top', '0');
                $('.header-container').removeClass('header-fixed');
                $('.subheader-container').removeClass('header-fixed');
            }
        });
	}

	function event_action_click()
	{
        $('.slider3').diyslider(
        {
            width: "580px", // width of the slider
            height: "120px", // height of the slider
            display: 5, // number of slides you want it to display at once
            loop: false // disable looping on slides
        }); // this is all you need!

        // use buttons to change slide
        $('#gotoleft').bind("click", function()
        {
            // Go to the previous slide
            $('.slider3').diyslider("move", "back");
        });
        $('#gotoright').unbind("click")
        $('#gotoright').bind("click", function()
        {
            // Go to the previous slide
            $('.slider3').diyslider("move", "forth");
        });

        /*PRODUCT HOVER TOGGLE*/

        $('.product-hover').hover(function()
            {
                $('.product-dropdown').stop();
                $('.product-dropdown').fadeIn(400);
            },
            function()
            {
                $('.product-dropdown').stop();
                $('.product-dropdown').fadeOut(400);
            });

        $('.company-hover').hover(function()
            {
                $('.company-dropdown').stop();
                $('.company-dropdown').fadeIn(400);
            },
            function()
            {
                $('.company-dropdown').stop();
                $('.company-dropdown').fadeOut(400);
            });

        $('.cart-hover').hover(function()
            {
                $('.cart-dropdown').stop();
                $('.cart-dropdown').fadeIn(400);
            },
            function()
            {
                $('.cart-dropdown').stop();
                $('.cart-dropdown').fadeOut(400);
            });


        // NAVIRINO CLICK TOGGLE
        $(".menu-nav").click(function()
        {
            $(".navirino").toggle("slow");
        });


        // COMPANY CLICK TOGGLE
        $(".company-hover").click(function()
        {
            $(".minimize-cat-holder").toggle("slow");
        });

        // PRODUCT CLICK TOGGLE
        $(".product-hover").click(function()
        {
            $(".minimize-product-holder").toggle("slow");
        });
	}

    function event_mobile_sidenav()
    {
        $menuLeft = $('.pushmenu-left');
        $nav_list = $('#nav_list');
        
        $nav_list.on("click", function() 
        {
            $(this).toggleClass('active');
           /* $('.pushmenu-push').toggleClass('pushmenu-push-toright');*/
            $menuLeft.toggleClass('pushmenu-open');
        }); 
    }

    function close_side_nav()
    {
        /*$("body").click(function(e)
        {
            if(e.target.className == "pushmenu-open")
            { 
                alert("do't hide");  
            }

            else 
            {
                $(".pushmenu-open").hide();
            }
        });*/

/*        $(document).mouseup(function(e) 
        {
            var container = $("pushmenu-push");

            // if the target of the click isn't the container nor a descendant of the container
            if (!container.is(e.target) && container.has(e.target).length === 0) 
            {
                container.hide();
            }
        });*/
    }

    function genealogy_mobile_dropdown()
    {
        $(".genealogy-mobile-dropdown").on("click", function (e) 
        {
            $(e.currentTarget).siblings(".genealogy-mobile-dropdown-list").slideToggle();

            // $(this).find($(".fa-change")).removeClass('fa-angle-down').addClass('fa-angle-up');

            $(e.currentTarget).find(".fa-angle-down").toggleClass('fa-rotate-180');

        });
    }

    function reports_mobile_dropdown()
    {
        $(".reports-mobile-dropdown").on("click", function (e) 
        {
            $(e.currentTarget).siblings(".reports-mobile-dropdown-list").slideToggle();

            // $(this).find($(".fa-change")).removeClass('fa-angle-down').addClass('fa-angle-up');

            $(e.currentTarget).find(".fa-angle-down").toggleClass('fa-rotate-180');

        });
    }

    function product_mobile_dropdown()
    {
        $(".product-mobile-dropdown").on("click", function (e) 
        {
            $(e.currentTarget).siblings(".product-mobile-dropdown-list").slideToggle();

            // $(this).find($(".fa-change")).removeClass('fa-angle-down').addClass('fa-angle-up');

            $(e.currentTarget).find(".fa-angle-down").toggleClass('fa-rotate-180');

        });
    }

    function company_mobile_dropdown()
    {
        $(".company-mobile-dropdown").on("click", function (e) 
        {
            $(e.currentTarget).siblings(".company-mobile-dropdown-list").slideToggle();
            
            $(e.currentTarget).find(".fa-angle-down").toggleClass('fa-rotate-180');

        });
    }
}

/*JAVASCRIPT*/

/*function on() 
{
    var $body = $(document.body);

    document.getElementById("overlay").style.display = "block";
    $body.css("overflow", "hidden");
}

function off()
{
    var $body = $(document.body);
    document.getElementById("overlay").style.display = "none";
    $('.pushmenu').removeClass("pushmenu-open");
    $body.css("overflow", "auto");
}*/

function on() 
{
    document.getElementById("overlay").style.display = "block";
    $("body").css({"overflow": "hidden","position": "fixed","margin": "0","padding": "0","right": "0","left": "0"});
    // $(".blur-me").css({
    //     filter: 'blur(50px)'
    // });
}

function off()
{
    document.getElementById("overlay").style.display = "none";
    $('.pushmenu').removeClass("pushmenu-open");
    $("body").css({"overflow": "auto","position": "static"});
    // $(".blur-me").css({
    //     filter: 'blur(0)'
    // });
}