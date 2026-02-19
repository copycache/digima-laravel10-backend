@extends("ecommerce.pages.layout")
@section("content")

<section class="splash">
    <div class="container">
        <div class="splash-grid">
            <div class="splash-grid__products-and-services">
                <nav>
                    <div class="nav-header" data-toggle="collapse" href="#collapseServices" role="button" aria-expanded="false" aria-controls="collapseServices"><i class="fas fa-list-ul"></i> Our Products and Services</div>
                    <div class="nav-content" id="">
                        <ul id="accordion" class="accordion">
                            <li class="default open">
                                <div class="link"><i class="fa fa-paint-brush"></i>Products<i class="fa fa-chevron-down"></i></div>
                                <ul class="submenu">
                                    @foreach($product as $item)
                                    <li><a href="/products/view/{{$item->item_id}}">{{$item->item_sku}}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            <li >
                                <div class="link"><i class="fa fa-code"></i>Services<i class="fa fa-chevron-down"></i></div>
                                <ul class="submenu">
                                    @foreach($services as $item)
                                    <li><a href="/products/view/{{$item->item_id}}">{{$item->item_sku}}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            <li>
                                <div class="link"><i class="fa fa-mobile"></i>Property<i class="fa fa-chevron-down"></i></div>
                                <ul class="submenu">
                                    @foreach($property as $item)
                                    <li><a href="/products/view/{{$item->item_id}}">{{$item->item_sku}}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            <li><div class="link"><i class="fa fa-globe"></i>Others<i class="fa fa-chevron-down"></i></div>
                                <ul class="submenu">
                                    @foreach($other_product as $item)
                                    <li><a href="/products/view/{{$item->item_id}}">{{$item->item_sku}}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            <div class="splash-grid__slider">
                <!-- Swiper -->
                <div class="swiper-splash-container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            {{-- <div style="background-image: url('assets/img/main-slider.jpg')"></div> --}}
                            <img src="assets/img/real-img-1.jpg" alt="">
                        </div>
                        <div class="swiper-slide">
                            {{-- <div style="background-image: url('assets/img/main-slider.jpg')"></div> --}}
                            <img src="assets/img/real-img-3.jpg" alt="">
                        </div>
                        <div class="swiper-slide">
                            {{-- <div style="background-image: url('assets/img/main-slider.jpg')"></div> --}}
                            <img src="assets/img/real-img-4.jpg" alt="">
                        </div>
                    </div>
                    <!-- Add Arrows -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <!-- Add Pagination -->
                    <div class="swiper-pagination"></div>
                </div>
            </div>
            {{-- <div class="splash-grid__a-d-d">
                <div class="splash__a-d-d__item"><img class="img-fluid" src="assets/img/home-ads-1.jpg" alt=""></div>
                <div class="splash__a-d-d__item"><img class="img-fluid" src="assets/img/home-ads-2.jpg" alt=""></div>
            </div> --}}
        </div>
    </div>
</section>

<section class="featured-services">
    <div class="container">
        {{-- <div class="section-title">
            <div class="section-title__text">FEATURED SERVICES</div>
            <div class="section-title__line"></div>
        </div> --}}

        <div class="featured-services__content">
            <div class="featured-services__content-item">
                <div class="featured-services__content-item__img-deals-holder">
                    <img class="img-fluid" src="assets/img/featured-main-img.jpg" alt="">
                    <button class="btn btn-orange">REQUEST A QOUTE NOW!</button>
                </div>
            </div>
            <div class="featured-services__content-item">
                <!-- Swiper -->
                <div class="featured-services-swiper-container">
                    <div class="swiper-wrapper">
                        @foreach($services as $item)
                        <div class="swiper-slide">
                            <div class="featured-services__swiper-slide__item">
                                {{-- <div class="featured-services__swiper-slide__item-img" style="background-image: url('assets/img/featured-img-1.jpg')">
                                </div> --}}
                                <div class="featured-services__swiper-slide__item-img">
                                    <img src="{{$item->item_thumbnail}}" alt="">
                                </div>
                                <div class="featured-services__swiper-slide__item-img-info">
                                    <div class="title">{{$item->item_sku}}</div>
                                    <div class="desc">{{$item->item_description}}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="request-qoutation">
    <div class="container">
        <div class="request-qoutation__grid">
            <div class="request-qoutation__grid-item">
                <img class="img-fluid" src="assets/img/wrapper-4-bg.jpg" alt="">
            </div>
            <div class="request-qoutation__grid-item">
                <form action="">
                    <div class="form-title">SINGLE/MULTIPLE QUOTATION REQUEST</div>
                    <div class="request-qoutation__grid-item__input-fields">
                        <div class="input-fields__left">
                            <input type="text" placeholder="Full Name *">
                            <input type="text" placeholder="Email Address *">
                            <input type="text" placeholder="Contact Number *">
                        </div>
                        <div class="input-fields__right">
                            <textarea name="" id="" cols="" rows="5" placeholder="Type your message here..."></textarea>
                        </div>
                    </div>
                    <button class="btn btn-orange">Request for Qoutation</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="info-section">
    <div class="container">
        <div class="info-section__title">mlmhouse</div>
        <div class="info-section__content">
            <div class="info-section__content-paragraph">
                <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Sed atque perspiciatis alias nostrum dolores iusto, at quasi laborum quaerat numquam magnam voluptatibus. Distinctio unde laborum ipsam sequi, expedita doloribus adipisci earum perspiciatis illum cupiditate sint. Corrupti explicabo nobis tenetur, recusandae asperiores, aspernatur iste cupiditate officia labore porro fuga a quae minus veritatis deserunt. Earum cum quisquam ad soluta repudiandae nobis iure nostrum libero? Consequatur, ad. Vel reprehenderit cum aut harum. Debitis, excepturi et, quasi, perferendis at voluptate dignissimos expedita quis sint aliquam consequatur.</p>

                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Architecto reprehenderit, porro saepe, culpa qui placeat nihil dolor neque facere earum quos voluptas quam illo iste suscipit cupiditate similique laudantium quidem deleniti. Esse, id, vel ipsam tempore tenetur voluptatibus, ea ex hic animi expedita repellat provident porro! Dicta illum magni quasi.</p>
            </div>
            <div class="info-section__content-image">
                <img class="img-fluid" src="assets/img/logo-about.png" alt="">
            </div>
        </div>
    </div>
</section>

<section class="contact">
    <div class="container">
        <div class="contact__grid">
            <div class="contact__grid-item-map">
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.2249159821213!2d121.06014061435678!3d14.586255889811579!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c81732bb3087%3A0x24a54621a7ea5a71!2sRaffles+Corporate+Center!5e0!3m2!1sen!2sph!4v1539842081887" frameborder="0" style="border:0" allowfullscreen></iframe>
                </div>
            </div>
            <div class="contact__grid-item-info">
                <div class="contact__grid-info__title">Contact Us</div>

                <div class="contact__grid-item-info__content">
                    <div class="__content-wrapper">
                        <div class="icon"><img src="assets/img/location-icon.png" alt=""></div>
                        <div class="info">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequuntur recusandae doloremque amet, veritatis consectetur error animi. Tempore dolorem unde, omnis quos officia sint, recusandae, vitae iste ab labore dolore eveniet.</div>
                    </div>
                    <div class="__content-wrapper">
                        <div class="icon"><img src="assets/img/phone-icon.png" alt=""></div>
                        <div class="info">000 - 00 - 00 / 0000 - 000 - 0000</div>
                    </div>
                    <div class="__content-wrapper">
                        <div class="icon"><img src="assets/img/mail-icon.png" alt=""></div>
                        <div class="info">ecommerce@email.com</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section("script")



<script type="text/javascript">

    $(function() {
        var Accordion = function(el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

        // Variables privadas
        var links = this.el.find('.link');
        // Evento
        links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
    }

    Accordion.prototype.dropdown = function(e) {
        var $el = e.data.el;
        $this = $(this),
        $next = $this.next();

        $next.slideToggle();
        $this.parent().toggleClass('open');

        if (!e.data.multiple) {
            $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
        };
    }   

    var accordion = new Accordion($('#accordion'), false);
});
</script>
<script>
    var swiper = new Swiper('.swiper-splash-container', {
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
    });
    var swiper = new Swiper('.featured-services-swiper-container', {
        slidesPerView: 3,
        slidesPerColumn: 2,
        spaceBetween: 5,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            320: {
              slidesPerView: 1,
          }
      },
  });
    var swiper = new Swiper('.swiper-real-state-container', {
        slidesPerView: 4,
        spaceBetween: 5,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        breakpoints: {
            640: {
              slidesPerView: 2,
          },
          320: {
              slidesPerView: 1,
          }
      },
  });
    var swiper = new Swiper('.swiper-real-state-container--sub', {
        slidesPerView: 3,
        spaceBetween: 5,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        breakpoints: {
            640: {
              slidesPerView: 2,
          },
          320: {
              slidesPerView: 1,
          }
      },
  });

    $(window).bind("resize", function () {
        console.log($(this).width())
        if ($(this).width() < 605) {
            $('.nav-content').addClass('collapse')
            $('.nav-content').attr('id', 'collapseServices')
        } else {
            $('.nav-content').removeClass('collapse')
            $('.nav-content').attr('id', '')
        }
    }).trigger('resize');
</script>
@endsection