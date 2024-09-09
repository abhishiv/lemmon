@extends('layouts.customer')
@push('scripts')
    <script type="module" src="{{ mix('/dist/js/customer/menu.js', '..') }}" defer></script>
@endpush
@section('body_class', 'single-product-page')
@section('class', 'product-menu')
@section('prod-header')
    <div class="product-header {{ $product->singleImage ? 'product-header--transparent' : '' }}">
        <a href="{{ session('table.url') }}" class="product-header__close icon-close"></a>
        <div class="header-top__cart">
            <x-customer.shopping-cart />
        </div>
    </div>
@endsection
@section('content')
    <div class="single" data-product-id="{{ $product->id }}">
        <div class="single__top">

        </div>
        <div class="single__product">
            @if ($product->singleImage)
                <div class="single__product__image-container">
                    <img class="single__product__image" src="{{ $product->singleImage }}" alt="" />
                </div>
            @endif
            <div class="single__product__info">
                <div class="single__product__info--top">
                    <div class="information">
                        <div class="single-product__name">{{ $product->name }}</div>
                        <div class="product-price" data-price="{{ !empty($product->special_price) ? $product->special_price : $product->price }}">
                            @if (!empty($product->special_price))
                                <span class="product-price__wrap">
                                    <span class="product-price__currency">{{ __('labels.currency') }}</span>
                                    <span class="product-price__amount">{{ priceFormat($product->special_price) }}</span>
                                </span>
                                <span class="product-price__wrap product-price__wrap--stroke">
                                    <span class="product-price__currency">{{ __('labels.currency') }}</span>
                                    <span class="product-price__initial-price">{{ priceFormat($product->price) }}</span>
                                </span>
                            @else
                                <span class="product-price__wrap">
                                    <span class="product-price__currency">{{ __('labels.currency') }}</span>
                                    <span class="product-price__amount">{{ priceFormat($product->price) }}</span>
                                </span>
                            @endif
                        </div>
                        @if ($product->additional_info)
                            <div class="single-product__additional-info">{{ $product->additional_info }}</div>
                        @endif
                    </div>
                </div>
                <div class="single-product__description">{{ $product->description }}</div>
                <div class="single-product__bundles">
                    @foreach ($product->bundles as $bundle)
                        @if ($bundle->extraProducts->count())
                            <div class="extras-box">
                                <h3 class="extra-title">
                                    <span class="extra-title__text">{{ $bundle->name }}</span>
                                    @if ($bundle->min_limit > 0)
                                        <span class="badge">Required</span>
                                    @endif
                                </h3>
                
                                <div class="extra-items-box" data-min-limit="{{ $bundle->min_limit }}"
                                    data-max-limit="{{ $bundle->limit }}">
                                    @foreach ($bundle->extraProducts->sortBy('pivot.order') as $key => $bundleProduct)
                                        <div class="extra-item">
                                            <div class="checkbox-container2">
                                                <input 
                                                    class="extra-item__checkbox inp-cbx extra-product"
                                                    name="products.{{ $bundle->id }}.{{ $bundleProduct->id }}.{{ $bundleProduct->pivot->price }}"
                                                    data-price="{{ $bundleProduct->pivot->price }}"
                                                    id="cbx.product.{{ $bundle->id }}.{{ $loop->iteration }}.{{ $bundleProduct->id }}" 
                                                    type="checkbox"
                                                    style="display: none"
                                                />
                                                <label class="cbx" for="cbx.product.{{ $bundle->id }}.{{ $loop->iteration }}.{{ $bundleProduct->id }}">
                                                    <span class="cbx__mark"></span>
                                                    <span class="cbx__text">{{ $bundleProduct->name }}</span>
                                                </label>
                                            </div>
                                            <div class="extra-price">@lang('labels.currency') {{ $bundleProduct->pivot->price }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($bundle->extras->count())
                            <div class="extras-box">
                                <h3 class="extra-title">
                                    <span class="extra-title__text">{{ $bundle->name }}</span>
                                    @if ($bundle->min_limit > 0)
                                        <span class="badge">Required</span>
                                    @endif
                                </h3>
                                <div class="extra-items-box" data-min-limit="{{ $bundle->min_limit }}"
                                    data-max-limit="{{ $bundle->limit }}">
                                    @foreach ($bundle->extras->sortBy('pivot.order') as $key => $bundleExtra)
                                        <div class="extra-item">
                                            <div class="checkbox-container2">
                                                <input 
                                                    class="extra-item__checkbox inp-cbx extra" 
                                                    name="extras.{{ $bundle->id }}.{{ $bundleExtra->id }}.{{ $bundleExtra->pivot->price }}"
                                                    data-price="{{ $bundleExtra->pivot->price }}"
                                                    id="cbx.extra.{{ $bundle->id }}.{{ $loop->iteration }}"
                                                    type="checkbox"
                                                    style="display: none"
                                                />
                                                <label class="cbx" for="cbx.extra.{{ $bundle->id }}.{{ $loop->iteration }}">
                                                    <span class="cbx__mark"></span>
                                                    <span class="cbx__text">{{ $bundleExtra->title }}</span>
                                                </label>
                                            </div>
                                            <div class="extra-price">@lang('labels.currency') {{ $bundleExtra->pivot->price }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif       
                    @endforeach
                </div>
                
                <div class="single-product__quantity">
                    <button class="single-product__quantity-button icon-minus remove" type="button"></button>
                    <input id="qty" class="single-product__quantity-input quantity" inputmode="numeric" value="1">
                    <button class="single-product__quantity-button icon-plus add" type="button"></button>
                </div>

                <div class="single__product__info--bottom">
                    <textarea 
                        type="text" 
                        class="product-mentions" 
                        placeholder="{{ __('customer.add_notes') }}" 
                        maxlength="254"
                    ></textarea>
                </div>
            </div>
        </div>
        <div class="product-cart">
            @if ($product->status == \App\Models\Product::AVAILABLE && $product->availableService())
                <div class="customer-button-wrapper">
                    <button class="customer-button customer-button--split add-to-cart-button product-cart--bottom" data-href="{{ route('customer.cart.add') }}">
                        <span class="customer-button__text">@lang('labels.add-quantity-for-price', [
                            'quantity' => '<span class="add-to-cart-button__quantity"></span>',
                            'currency' => __('labels.currency'),
                            'price' => '<span class="add-to-cart-button__price"></span>'
                        ])</span>
                        <span class="customer-button__icon customer-button__icon--small icon-plus"></span>
                    </button>
                    <button class="customer-button customer-button--secondary btn-animation">Added</button>
                </div>
            @else
                <div>Product Unavailable</div>
            @endif
        </div>
    </div>
@endsection
