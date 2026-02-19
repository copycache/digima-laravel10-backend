@extends("ecommerce.pages.layout")
@section("content")

<section class="py-5 product-view">
    <input type="hidden" id="item_id" name="item_id" value="{{$item->item_id}}"/>
    <div class="container">
        <div class="product-view__content">
            <div class="product-view__content-item">
                <div class="product-view__content-item__img">
                    <div class="product-view__content-item__img__item--big">
                        <a href="/assets/img/item-big.jpg" data-fancybox="product"><img class="img-fluid" src="{{$item->item_thumbnail}}" alt=""></a>
                    </div>
                    <div class="mt-3 product-view__content-item__img__item--small">
                        <!-- Swiper -->
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide"><a href="{{$item->item_thumbnail}}" data-fancybox="product"><img class="img-fluid" src="{{$item->item_thumbnail}}" alt=""></a></div>
                                <div class="swiper-slide"><a href="{{$item->item_thumbnail}}" data-fancybox="product"><img class="img-fluid" src="{{$item->item_thumbnail}}" alt=""></a></div>
                                <div class="swiper-slide"><a href="{{$item->item_thumbnail}}" data-fancybox="product"><img class="img-fluid" src="{{$item->item_thumbnail}}" alt=""></a></div>
                                <div class="swiper-slide"><a href="{{$item->item_thumbnail}}" data-fancybox="product"><img class="img-fluid" src="{{$item->item_thumbnail}}" alt=""></a></div>
                                <div class="swiper-slide"><a href="{{$item->item_thumbnail}}" data-fancybox="product"><img class="img-fluid" src="{{$item->item_thumbnail}}" alt=""></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product-view__content-item">
                {{-- This is for services--}}
                {{-- <h4>Manpower Services</h4>  --}}

                <h4 class="mb-0">{{$item->item_sku}}</h4>
                <div class="d-flex justify-content-start align-items-center">
                    <fieldset class="rating">
                        <input type="radio" id="star5" name="rating" value="5" /><label class = "full" for="star5" title="Awesome - 5 stars"></label>
                        <input type="radio" id="star4half" name="rating" value="4 and a half" /><label class="half" for="star4half" title="Pretty good - 4.5 stars"></label>
                        <input type="radio" id="star4" name="rating" value="4" /><label class = "full" for="star4" title="Pretty good - 4 stars"></label>
                        <input type="radio" id="star3half" name="rating" value="3 and a half" /><label class="half" for="star3half" title="Meh - 3.5 stars"></label>
                        <input type="radio" id="star3" name="rating" value="3" /><label class = "full" for="star3" title="Meh - 3 stars"></label>
                        <input type="radio" id="star2half" name="rating" value="2 and a half" /><label class="half" for="star2half" title="Kinda bad - 2.5 stars"></label>
                        <input type="radio" id="star2" name="rating" value="2" /><label class = "full" for="star2" title="Kinda bad - 2 stars"></label>
                        <input type="radio" id="star1half" name="rating" value="1 and a half" /><label class="half" for="star1half" title="Meh - 1.5 stars"></label>
                        <input type="radio" id="star1" name="rating" value="1" /><label class = "full" for="star1" title="Sucks big time - 1 star"></label>
                        <input type="radio" id="starhalf" name="rating" value="half" /><label class="half" for="starhalf" title="Sucks big time - 0.5 stars"></label>
                    </fieldset>
                    <label for="rating" class="text-orange ml-1"><small>4.0 Rating</small></label>
                </div>

                <h5 class="mt-4">Overview</h5>
                <p>{{$item->item_description}}</p>
                @if($item->item_type != 'product')
                

                {{-- This is for services--}}
                <div class="mt-5 py-5 px-2 product-view__content-item__process">
                    <div class="product-view__content-item__process-item">
                        <div class="icon"><img src="/assets/img/request-qoute-icon.png" alt=""></div>
                        <label for="RequestQoute">Request a Qoute</label>
                        <p>Lorem ipsum dolor sit amet consectetur.</p>
                    </div>
                    <div class="product-view__content-item__process-item">
                        <div class="icon"><img src="/assets/img/fillup-icon.png" alt=""></div>
                        <label for="RequestQoute">Fill Up</label>
                        <p>Lorem ipsum dolor sit amet consectetur.</p>
                    </div>
                    <div class="product-view__content-item__process-item">
                        <div class="icon"><img src="/assets/img/submit-icon.png" alt=""></div>
                        <label for="RequestQoute">Submit Request</label>
                        <p>Lorem ipsum dolor sit amet consectetur.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-orange popup add-to-cart" link="/products/view/request/qoute"><i class="fas fa-envelope"></i> Request a Qoute</button>
                </div>
                @else
                <label for="price">Price</label>
                <h1 class="text-red m-0">{{$currency}} {{$item->item_price}}</h1>
                <div class="mt-5 mb-1"><label for="Quantity">Quantity</label></div>
                <div class="nice-number">
                    <input type="number" value="1" id="view_quantity" style="width: 4ch;">
                </div>
                <small class="text-muted"><br/>99 Set Available</small>

                
                <div class="mt-4">
                    <button class="btn btn-orange popup" link="/cart_item?item_id={{$item->item_id}}&quantity=1"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- <section class="pb-5 product-description">
    <div class="container">
        <div class="product-description__content">
            <h4>Description</h4>

            <div class="pt-3 pb-4 product-description__content-img">
                <img class="img-fluid" src="/assets/img/desc-img.jpg" alt="">
            </div>

            <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Adipisci id magni, doloremque nostrum eveniet nihil voluptatem omnis commodi laudantium perferendis dignissimos accusamus dolore quam exercitationem ullam quae eos necessitatibus incidunt asperiores cum soluta dicta placeat itaque. Adipisci animi obcaecati saepe quam? Praesentium corporis aspernatur asperiores id, labore quis necessitatibus commodi.</p>

            <div class="pt-3 pb-5 product-description__content-list">
                <ul>
                    <li>Lorem ipsum dolor sit amet.</li>
                    <li>Lorem ipsum dolor sit amet.</li>
                    <li>Lorem ipsum dolor sit amet.</li>
                    <li>Lorem ipsum dolor sit amet.</li>
                    <li>Lorem ipsum dolor sit amet.</li>
                </ul>
                <ul>
                    <li>Lorem ipsum dolor sit amet.</li>
                    <li>Lorem ipsum dolor sit amet.</li>
                    <li>Lorem ipsum dolor sit amet.</li>
                    <li>Lorem ipsum dolor sit amet.</li>
                    <li>Lorem ipsum dolor sit amet.</li>
                </ul>
            </div>

            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Harum assumenda odit, officiis exercitationem rerum nesciunt doloribus et magnam, labore saepe ipsum sunt nisi tenetur molestias optio voluptatem cum voluptas a?</p>

        </div>
    </div>
</section>
 --}}

<section class="product-you-may-like">
    <div class="container pb-5">
        <div class="product-you-may-like__content">
            <h4>You may also like</h4>
            <!-- Swiper -->
            <div class="swiper-real-state-container--sub">
                <div class="swiper-wrapper">
                    @foreach($product as $item)
                    <a href="/products/view/{{$item->item_id}}">
                    <div class="swiper-slide">
                        <div clasc="swiper-product-item">
                            <div class="swiper-product-item__img">
                                <img src="{{$item->item_thumbnail}}" alt="">
                            </div>
                            <div class="slide-item__info">
                                <div class="slide-item__info-name">{{$item->item_sku}}</div>
                                <div class="slide-item__info-desc">{{$item->item_description}}</div>
                                <div class="slide-item__info-price">{{$currency}} {{$item->item_price}}</div>
                            </div>
                        </div>
                    </div>
                    </a>
                    @endforeach
                </div>
                <!-- Add Arrows -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>
</section>

@endsection

@section("script")
<script>
    var swiper = new Swiper('.swiper-container', {
        slidesPerView: 4,
        spaceBetween: 16
    });

    // $(function(){
    //     $('input[type="number"]').niceNumber();
    // });

    $('input[type="number"]').niceNumber({
        // auto resize the number input
        autoSize: true,

        // the number of extra character
        autoSizeBuffer: 1,

        // custom button text
        buttonDecrement: '-',
        buttonIncrement: "+",

        // 'around', 'left', or 'right'
        buttonPosition: 'around'
    });

    var swiper = new Swiper('.swiper-real-state-container--sub', {
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
</script>
@endsection