@extends('layouts.customer')
@push('scripts')
    <script type="module" src="{{ mix('/dist/js/customer/menu.js', '../') }}" defer></script>
@endpush
@section('class', 'main-menu')
@section('menu-header')
    <div class="header" id="header" data-restaurant-id="{{ $restaurant->id }}">
        <div class="header-top">
            <div class="header-top__name">
                <div class="menu-title">{{ $restaurant->name }}</div>
                <span style="display: none;" class="search-title">@lang('labels.search')</span>
            </div>
            <div class="header-top__toggles">
                <div class="header-top__icons">
                    @if(!empty(session()->get('orders')))
                        <a href="{{ route('customer.order.list') }}">
                            <div class="header-top__review">
                                <div class="review-order">
                                    @include('layouts.parts.icons.review-order')
                                </div>
                            </div>
                        </a>
                    @endif
                    <div class="header-top__close">
                        <button class="header-top__close__btn">
                            <span class="header-top__close__icon icon-close"></span>
                        </button>
                    </div>
                    <div class="header-top__icons__search icon-magnifier-2"></div>
                    <div class="header-top__cart">
                        <x-customer.shopping-cart />
                    </div>
                </div>
            </div>
        </div>
        <div class="header__search">
            <div class="header__search__label">
                <input
                    class="header__search__label__input"
                    placeholder="{{__('customer.search-for-dishes')}}"
                    name="search"
                    id="filter"
                >
            </div>
        </div>
        @if($services->isNotEmpty())
            <div class="menu menu--services" id="service">
                <ul class="menu__list" style=" display: flex;">
                    @foreach($services as $service)
                        @if($service->productsByCategories() && !(!$service->isAvailable() && $service->hide_unavailable))
                            <li class="menu__list__item">
                                <a
                                    class="menu__button"
                                    data-menu-id="{{$service->id}}"
                                    data-item-id="{{$service->id}}"
                                    {!! $service->shown ? 'style="color: rgb(255, 255, 255);"' : null !!}
                                >{{$service->name}}</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="menu menu--categories" id="subservice">
                <ul class="menu__list" style=" display: flex;">
                    @foreach($services as $service)
                        @if($service->products->isNotEmpty() && !(!$service->isAvailable() && $service->hide_unavailable))
                            @foreach($service->productsByCategories(true) as $category => $products)
                                @if($category && is_array($products['products']))
                                    <li class="menu__list__item {{ !$service->shown ? 'hidden' : null }}"
                                        data-subservice-id="{{$service->id}}">
                                        <a
                                            class="menu__button"
                                            data-item-id="{{$service->id}}"
                                            data-category-name="{{$service->id . '-' . $category}}"
                                            data-category-id="{{ $category }}"
                                            {!! array_key_first($service->productsByCategories()) == $category ? 'style="color: rgb(255, 255, 255);"' : null !!}
                                        >{{$products['category']->name}}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
@section('content')
    <section id="menu-list"
                class="list {{ $services->isNotEmpty() ? 'large-header' : ''}}">
        <div class="empty">
            <span class="empty__search"> {{__('customer.nothing_search')}} </span>
        </div>

        @foreach($services as $service)
            @if($service->productsByCategories() && !(!$service->isAvailable() && $service->hide_unavailable))
                <div class="products" id="service-{{$service->id}}" data-service-id="{{$service->id}}"
                        data-count="{{ count($service->products) }}">
                    <h3 @class(['products__service', 'out-of-stock' => !$service->isAvailable()])>{{$service->name}}</h3>
                    @if($service->description)
                        <div class="service-info">{{$service->description}}</div>
                    @endif
                    @foreach($service->productsByCategories(true) as $category => $products)

                        @if($category && is_array($products))
                            <div class="category-container" data-subservice-id="{{ (string) $category }}">
                                <h4 @class(['products__category', 'out-of-stock' => !$service->isAvailable()]) id="subservice-{{$service->id . '-' . $category}}">{{$products['category']->name}}</h4>
                                @foreach($products['products'] as $product)
                                    @if($product->is_custom)
                                        @continue
                                    @endif
                                    <a 
                                        href="{{ $product->menuProductUrl }}"
                                        data-id="{{$product->id}}"
                                        data-href="{{route('customer.cart.add')}}" @class([
                                            'products__link',
                                            'disabled' => $product->status == \App\Models\Product::OUTOFSTOCK || !$service->isAvailable()
                                        ])
                                    >
                                        <div class="products__list">
                                            <div class="products__list__item">
                                                <div class="flex justify-content-between">
                                                    <h4 @class(['products__list__item__title'])>{{$product->name}}</h4>
                                                </div>
                                                <p class="products__list__item__description">{{Str::limit($product->description, 90)}}</p>
                                                <div class="products__list__item__price">
                                                    <div class="left-side">
                                                        @if(!empty($product->special_price))
                                                            <p class="special-price"> {{__('labels.currency')}} {{priceFormat($product->special_price)}}</p>
                                                            <span class="stroke-price"> {{__('labels.currency')}} {{priceFormat($product->price)}} </span>
                                                        @else
                                                            <span class="price">{{__('labels.currency')}} {{priceFormat($product->price)}}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @if($product->featured_image)
                                                <div class="products__list__item-img">
                                                    <img alt=" " src="{{ $product->featured_image }}"/>
                                                </div>
                                            @endif
                                            @if(array_key_exists($product->id, session('quantity') ?? []))
                                                <div class="quantity-counter">{{session('quantity')[$product->id]}}</div>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @endforeach
    </section>
@endsection
