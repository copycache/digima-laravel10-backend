<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MLMHOUSE</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" type="text/css" href="/assets/plugins/00-bootstrap-4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/plugins/01-fontawesome-5.3.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/plugins/03-fancybox-master/dist/jquery.fancybox.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/plugins/05-swiper/css/swiper.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/plugins/04-wow/css/animate.css">
    <link rel="stylesheet" type="text/css" href="/assets/plugins/06-sidebars/css/normalize.css">
    <link rel="stylesheet" type="text/css" href="/assets/plugins/06-sidebars/css/icons.css">
    <link rel="stylesheet" type="text/css" href="/assets/plugins/06-sidebars/css/component.css">
    <link rel="stylesheet" type="text/css" href="/assets/plugins/07-input/dist/jquery.nice-number.css">
    <link rel="stylesheet" type="text/css" href="/css/main.css">
</head>
<body>
    <div class="mp-pusher" id="mp-pusher">

        @include("ecommerce.pages.sidebar")

        <div class="scroller"><!-- this is for emulating position fixed of the nav -->
            <div class="scroller-inner">
                <header>
                    <div class="header-conatiner">
                        <div class="header-content__top">
                            
                        </div>
                        <div class="header-content__bottom">
                            <div class="header-content__bottom-logo">
                                <a href="#" id="trigger" class="mobile-nav"><i class="fas fa-bars"></i></a>
                                <a href="/"><img class="img-fluid" src="/assets/img/logo-header.png" alt=""></a>
                                <a href="javascript:" class="mobile-cart my-cart-icon text-center text-orange popup" size="lg" link="/my-cart"><i class="fas fa-shopping-cart"></i><span class="badge badge-pill badge-danger">0</span></a>
                            </div>
                            <div class="header-content__bottom-search">
                                <div class="dropdown">
                                    <button class="btn-categories dropdown-toggle" href="http://example.com" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">All Categories</button>
                                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                        {{-- <li><a class="dropdown-item" href="#">Products</a></li> --}}
                                        <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="javascript:">Products</a>
                                            <ul class="dropdown-menu">
                                                @foreach($product as $item)
                                                <li><a class="dropdown-item" href="/products/view/{{$item->item_id}}">{{$item->item_sku}}</a></li>
                                                @endforeach
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="javascript:">Services</a>
                                            <ul class="dropdown-menu">
                                                @foreach($services as $item)
                                                <li><a class="dropdown-item" href="/products/view/{{$item->item_id}}">{{$item->item_sku}}</a></li>
                                                @endforeach
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="javascript:">Property</a>
                                            <ul class="dropdown-menu">
                                                @foreach($property as $item)
                                                <li><a class="dropdown-item" href="/products/view/{{$item->item_id}}">{{$item->item_sku}}</a></li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                                <input type="text" placeholder="Search product or services here">
                                <button class="btn-search"><i class="fas fa-search"></i></button>
                            </div>
                            <div class="header-content__bottom-feature">
                                {{-- <div class="header-content__bottom-feature--item">
                                    <img src="/assets/img/online-supp.png" alt="">
                                    <label for="">24/7 Online Support</label>
                                </div>
                                <div class="header-content__bottom-feature--item">
                                    <img src="/assets/img/safe-payment.png" alt="">
                                    <label for="">Safe Payment</label>
                                </div>
                                <div class="header-content__bottom-feature--item">
                                    <img src="/assets/img/trusted.png" alt="">
                                    <label for="">We are trusted</label>
                                </div> --}}

                                <nav class="header-content__top-nav__nav-links">
                                    <ul>
                                        <li><a href="/">Home</a></li>
                                        <li><a href="javascript:">About Us</a></li>
                                        {{-- <li><a href="javascript:">Support</a></li> --}}
                                        <li><a href="javascript:">Contact Us</a></li>
                                        <li class="ml-3"><a href="javascript:">Sign Up</a></li>
                                        <li><a href="javascript:">Login</a></li>
                                        <li class="ml-3">
                                            <a href="javascript:" class="my-cart-icon popup" size="lg" link="/cart_item"><i class="fas fa-shopping-cart"></i> My Cart</a>
                                            <span class="badge badge-pill badge-danger">{{$cart_count}}</span>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                   </div>
                </header>

                <main>
                    @yield("content")
                </main>

                <footer>
                    <div class="container">
                        <div class="footer__top">
                            <div class="footer__top-item">
                                <div class="footer_top-item__title">SHOP</div>
                                <nav>
                                    <ul>
                                        <li><a href="javascript:">Games</a></li>
                                        <li><a href="javascript:">General Consultancy</a></li>
                                        <li><a href="javascript:">General Supply Services</a></li>
                                        <li><a href="javascript:">Holding Services</a></li>
                                        <li><a href="javascript:">I.T. Services</a></li>
                                        <li><a href="javascript:">Import & Export Services</a></li>
                                    </ul>
                                    <ul>
                                        <li><a href="javascript:">Man Power Services</a></li>
                                        <li><a href="javascript:">Marketing</a></li>
                                        <li><a href="javascript:">Real State</a></li>
                                    </ul>
                                </nav>
                            </div>
                            <div class="footer__top-item">
                                <div class="footer_top-item__title">PAYMENT METHODS</div>
                                <div class="d-flex align-items-center justify-content-start">
                                    <img class="m-2" width="100" src="/assets/img/paymaya.jpg" alt="">
                                    <img class="m-2" width="100" src="/assets/img/dragonpay.jpg" alt="">
                                </div>
                            </div>
                            <div class="footer__top-item">
                                <div class="footer_top-item__title">FOLLOW US ON</div>
                                <div class="footer__top-item__social-icons">
                                    <div class="facebook"><a href="javascript:"><i class="fab fa-facebook-f"></i></a></div>
                                    <div class="twitter"><a href="javascript:"><i class="fab fa-twitter"></i></a></div>
                                    <div class="linkedin"><a href="javascript:"><i class="fab fa-linkedin-in"></i></a></div>
                                    <div class="google-plus"><a href="javascript:"><i class="fab fa-google-plus-g"></i></a></div>
                                </div>
                            </div>
                        </div>
                        <div class="footer__bottom">
                            <div class="footer__bottom-item">
                                &copy; 2018 MLMHOUSE. All Rights Reserved.
                            </div>
                            <div class="footer__bottom-item">
                                <ul>
                                    <li><a href="javascript:">Terms and Agreement</a></li>
                                    <li><a href="javascript:">Privacy Policy</a></li>
                                    <li>Powered By: Digima Web Solutions, Inc.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
    <div id="global_modal" class="modal fade" role="dialog" >
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content modal-content-global clearfix">
            </div>
        </div>
    </div>
    <div class="multiple_global_modal_container"></div>
    <script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
    {{-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script type="text/javascript" src="/assets/plugins/00-bootstrap-4.1.3/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/03-fancybox-master/dist/jquery.fancybox.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/05-swiper/js/swiper.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/04-wow/js/wow.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/06-sidebars/js/classie.js"></script>
    <script type="text/javascript" src="/assets/plugins/06-sidebars/js/mlpushmenu.js"></script>
    <script type="text/javascript" src="/assets/plugins/06-sidebars/js/modernizr.custom.js"></script>
    <script type="text/javascript" src="/assets/plugins/07-input/dist/jquery.nice-number.min.js"></script>
    {{-- <script type="text/javascript" src="js/global.js"></script> --}}
    <script type="text/javascript" src="/js/popup.js"></script>
    <script type="text/javascript" src="/js/mlm.js"></script>
    @yield("script")
    <script>
        new mlPushMenu( document.getElementById( 'mp-menu' ), document.getElementById( 'trigger' ), {
            type : 'cover'
        } );

        // $(document).on('show.bs.modal', '.modal', function ()
        // {
        //     var zIndex = 1040 + (10 * $('.modal:visible').length);
        //     $(this).css('z-index', zIndex);
        //     setTimeout(function() {
        //         $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        //     }, 0);
        // });

        // $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {

        //     if (!$(this).next().hasClass('show')) {
        //         $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
        //     }

        //     var $subMenu = $(this).next(".dropdown-menu");

        //     $subMenu.toggleClass('show')

        //     $(this).parents('div.dropdown.show').on('hidden.bs.dropdown', function(e) {
        //         $('.dropdown-submenu .show').removeClass("show");
        //     });

        //     return false;
        // });
    </script>
</body>
</html>