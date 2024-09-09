<div class="table-summary" data-table-id="{{ $tableId }}">
    <div class="table-summary__items">
        <div class="delete-button-container">
            <button class="lemmon-modal__delete button button--red d-none button--icon"
                    type="button"><span class="button__icon icon-trash"></span></button>
        </div>

        @if($mergedOrders['totals']['net'] != '0.00')

            @foreach ($mergedOrders['items'] as $type => $taxonomies)

                @foreach ($taxonomies as $taxonomy => $items)

                    @foreach ($items as $k => $item)

                        <div
                            class="table-summary__item {{ $item['online_payment'] ? 'table-summary__item--paid' : 'table-summary__item--unpaid' }}">

                            <div class="table-summary__qty">
                                <div class="table-summary__checkbox d-none">
                                    @if (!$item['online_payment'])
                                        <input type="checkbox" class="table-summary__partial-checkbox"
                                               data-initial-value="{{ $item['quantity'] }}"
                                               name="{{ $item['order_item_id'] }}-{{ $k }}"
                                               value="">
                                    @else
                                        <span class="checkbox-replacement"></span>
                                    @endif
                                </div>
                                <button class="staff-cart__button d-none" data-action="decrease">
                                    <span class="staff-cart__icon icon-minus"></span>
                                </button>
                                <div class="staff-cart__quantity"
                                     data-initial-value="{{ $item['quantity'] }}">{{ $item['quantity'] }}</div>
                                <button class="staff-cart__button d-none" data-action="increase">
                                    <span class="staff-cart__icon icon-plus"></span>
                                </button>
                            </div>

                            <div class="table-summary__product">
                                <div class="table-summary__main">{{ $item['name'] }}</div>
                                @foreach ($item['bundleItems'] as $bundleItem)
                                    <div class="table-summary__bundle-item">
                                        {{ $bundleItem['name'] }}
                                        @if(!$item['online_payment'])
                                            (+ @lang('labels.currency') {{ $bundleItem['price'] }})
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="table-summary__price">
                                @if ($item['online_payment'])
                                    @lang('labels.paid')
                                @else
                                    @lang('labels.currency') {{ priceFormat($item['price'], '.', '\'') }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            @endforeach
        @else
            All the items are paid, please close the orders by pressing the Pay button
        @endif
    </div>
    <div class="table-summary__totals">
        <div class="table-summary__row" data-table="">
            <div class="table-summary__column table-summary__column--highlight">@lang('labels.total')</div>
            <div class="table-summary__column">@lang('labels.currency') {{ priceFormat($mergedOrders['totals']['gross'], '.', '\'') }}</div>
        </div>
    </div>
</div>
