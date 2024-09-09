<div class="staff-cart">
    @if ($products)
        @foreach ($products as $key => $product)
            <div class="staff-cart__item" data-product-id="{{ $product->id }}" data-key="{{ $key }}">
                <div class="staff-cart__name">
                    <div class="staff-cart__main-item">{{ $product->name }}</div>
                    @if ($product->bundleItems)
                        @foreach ($product->bundleItems as $item)
                            <div class="staff-cart__extra">{{ isset($item->title) ? $item->title : $item->name }}</div>
                        @endforeach
                    @endif
                    @if ($product->notes)
                        <div class="staff-cart__notes"><span class="staff-cart__notes-label">@lang('labels.note'):</span> <span class="staff-cart__notes-text">{{ $product->notes }}</span></div> 
                    @endif
                </div>
                <button class="staff-cart__button" data-action="decrease">
                    <span class="staff-cart__icon icon-minus"></span>
                </button>
                <div class="staff-cart__quantity">{{ $product->quantity }}</div>
                <button class="staff-cart__button" data-action="increase">
                    <span class="staff-cart__icon icon-plus"></span>
                </button>
                <div class="staff-cart__price">{{ priceFormat($product->price, '.', '\'') }}</div>
            </div>
        @endforeach
    @else
        <div class="staff-cart__empty">
            <div class="staff-cart__empty-text staff-cart__empty-text--large">@lang('labels.empty-cart')</div>
            <div class="staff-cart__empty-text">@lang('labels.open-menu-to-add-items')</div>
        </div>    
    @endif 
</div>
<div class="staff-cart-totals">
    <div class="staff-cart-totals__row staff-cart-totals__row--large">
        <div class="staff-cart-totals__label">@lang('labels.total')</div>
        <div class="staff-cart-totals__amount">@lang('labels.currency') {{ priceFormat($totals['gross'], '.', '\'') }}</div>
    </div>
</div>