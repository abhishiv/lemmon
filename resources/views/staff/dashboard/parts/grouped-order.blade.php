{{-- Food --}}
@if(count($groupedFoodItems))
    @foreach($foodTypes as $foodTypeId => $foodTypeName)
        @if(!isset($groupedFoodItems[$foodTypeId]) || empty($groupedFoodItems[$foodTypeId]))
            @continue
        @endif
        <div class="order-card__group order-card__group--restaurant order-card__group--{{ $foodStatuses[$foodTypeId] }}">
            @if (
                    $order->status != App\Models\Order::CLOSED 
                    && $order->status != App\Models\Order::GROUP 
                    && isset($foodStatuses[$foodTypeId])
                )
                <div class="order-card__group-actions">
                    <button class="order-card__toggle order-card__control order-card__control--{{ $foodStatuses[$foodTypeId] }}"
                        data-items-type="restaurant" data-food-type-id="{{ $foodTypeId }}">
                        {{ __('labels.status-' . $foodStatuses[$foodTypeId]) }}
                    </button>
                </div>
            @endif
            <div class="order-card__group-header">
                <h4 class="order-card__group-type">{{ $foodTypeName }}</h4>
            </div>
            <div class="order-card__group-body">
                @foreach($groupedFoodItems[$foodTypeId] as $key => $item)
                    <div class="order-card__product">
                        <div class="order-card__quantity">{{$item['quantity']}}</div>
                        <div class="order-card__product-details">
                            <div class="order-card__product-name">{{$item['name']}}</div>
                            @if ($item['bundle'] && count($item['bundle']) > 0)
                                <div class="order-card__bundle-items">
                                    @foreach ($item['bundle'] as $bundle)
                                        <div class="order-card__bundle-item">{{ $bundle->entity_type == App\Models\Product::class ? $bundle->entity?->name : $bundle->entity?->title }}</div>
                                    @endforeach
                                </div>
                            @endif
                            @if ($item['notes'])
                                <div class="order-card__notes">
                                    <span class="order-card__notes-label">@lang('labels.note'): </span><span>{{ $item['notes'] }}</span>
                                </div>
                            @endif
                        </div>  
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif

{{-- Bars --}}
@if(count($groupedBarItems))
    <div class="order-card__group order-card__group--bar order-card__group--{{ $order->bar_status }}">
        @if ($order->status != App\Models\Order::CLOSED && $order->status != App\Models\Order::GROUP)
            <div class="order-card__group-actions">
                <button class="order-card__toggle order-card__control order-card__control--{{ $order->bar_status }}"
                    data-items-type="bar">
                    {{ $order->getNextStatusButtonText('bar') }}
                </button>
            </div>
        @endif
        <div class="order-card__group-header">
            <h4 class="order-card__group-type">Bar</h4>
        </div>
        <div class="order-card__group-body">
            @foreach($groupedBarItems as $item)
                <div class="order-card__product">
                    <div class="order-card__quantity">{{$item['quantity']}}</div>
                    <div class="order-card__product-details">
                        <div class="order-card__product-name">{{$item['name']}}</div>
                        @if ($item['bundle'] && count($item['bundle']) > 0)
                            <div class="order-card__bundle-items">
                                @foreach ($item['bundle'] as $bundle)
                                    <div class="order-card__bundle-item">{{ $bundle->entity_type == App\Models\Product::class ? $bundle->entity?->name : $bundle->entity?->title }}</div>
                                @endforeach
                            </div>
                        @endif
                        @if ($item['notes'])
                            <div class="order-card__notes">
                                <span class="order-card__notes-label">@lang('labels.note'): </span><span>{{ $item['notes'] }}</span>
                            </div>
                        @endif
                    </div>  
                </div>
            @endforeach
        </div>
    </div>
@endif
