@foreach($orders as $order)
    <article class="order-summary">
        <header class="order-summary__header">
            <h3 class="order-summary__title">@lang('labels.order'): #{{ $order->display_id ?? $order->parent->display_id }}</h3>
            <span class="order-summary__status">{{ $order->status }}</span>
        </header>
        <section class="order-summary__totals">
            <div class="order-summary__totals-row">
                <span class="order-summary__totals-cell">{{ $order->items->sum('quantity') }} @lang('labels.items')</span>
            </div>
            @if($order->tips)
                <div class="order-summary__totals-row">
                    <span class="order-summary__totals-cell">@lang('labels.tips-order-summary')</span>
                    <span class="order-summary__totals-cell">@lang('labels.currency') {{ priceFormat($order->tips) }}</span>
                </div>
            @endif
            <div class="order-summary__totals-row order-summary__totals-row--large">
                <span class="order-summary__totals-cell">@lang('labels.total')</span>
                <span class="order-summary__totals-cell">@lang('labels.currency') {{ priceFormat($order->totalAmount) }}</span>
            </div>
        </section>
        <section class="order-summary__body">
            @foreach($order->items as $item)
                <div class="order-summary__item">
                    <div class="order-summary__name">{{ $item->quantity }}x {{ $item->products->name }}</div>
                    
                    @if($item->itemBundles)
                        @foreach($item->itemBundles as $bundledItem)
                            <div class="order-summary__bundle-item">
                                <span class="order-summary__extra-name">
                                    @if($bundledItem->entity_type == \App\Models\Product::class)
                                        {{ $bundledItem->entity->name }}
                                    @elseif($bundledItem->entity_type == \App\Models\Extra::class)
                                        {{ $bundledItem->entity->title }}
                                    @endif
                                </span>
                                <span class="order-summary__extra-price">@lang('labels.currency') {{ priceFormat($bundledItem->price) }}</span>
                            </div>
                        @endforeach
                    @endif

                    <div class="order-summary__price"> @lang('labels.currency') {{ priceFormat($item->price * $item->quantity) }}</div>
                </div>
            @endforeach
        </section>
    </article>
@endforeach
