@if($order)

    @if($order->items->isNotEmpty() || $order->isParent)
        <article
            class="order-card {{ $filteringClasses }}"
            data-id="{{$order->id}}"
            data-table="{{ $order->table?->id }}"
        >
            <section class="order-card__section order-card__header">
                <span class="order-card__service-method">
                    <span
                        class="order-card__service-icon order-card__service-icon--{{ $order->service_method }}"></span>
                    <span class="order-card__service-text">@lang('labels.'  . $order->service_method)</span>
                </span>
                @if(($order->status == \App\Models\Order::NEW || $order->status == \App\Models\Order::PREPARING) && !$hasOnlinePayment)
                    <div class="order-card__menu">
                        <button class="order-card__menu-toggle">
                            <span class="order-card__menu-icon icon-arrow-down"></span>
                        </button>
                        <ul class="order-card__menu-list">
                            <li class="order-card__menu-item">
                                <button class="order-card__menu-button order-card__cancel"
                                        data-url="{{ route('staff.order.cancel', ['order' => $order->id]) }}">@lang('labels.cancel-order-button')</button>
                            </li>
                        </ul>
                    </div>
                @endif
            </section>
            <section class="order-card__section order-card__body">
                @if($order->status == \App\Models\Order::READY)
                    <div class="order-card__actions">
                        <button class="order-card__control order-card__close"
                                data-new-status="{{ \App\Models\Order::CLOSED }}"
                                data-checkout="{{ $order->needsPayment() ? 'yes' : 'no' }}">@lang('labels.completed')</button>
                    </div>
                @endif

                <div class="order-card__details">
                    <div class="order-card__details-row">
                        <span class="order-card__number">#{{ $order->display_id }}</span>
                        @if ($order->service_method === \App\Models\Order::DINEIN)
                            <span class="order-card__table">{{ $order->table?->name }}</span>
                        @endif
                    </div>
                    <div class="order-card__details-row order-card__details-row--fade">
                        <span class="order-card__time">{{ $order->created_at->format('g:i A') }}</span>
                        @if(in_array($order->payment_method, [\App\Models\Order::CASH, \App\Models\Order::CARD]) || !$order->payment_method)
                            <span
                                class="order-card__amount">@lang('labels.currency') {{ priceFormat(0, '.', '\'') }}</span>
                        @endif
                    </div>
                </div>

                @if ($order->status == \App\Models\Order::CLOSED && $availablePrinters['receipt'])
                    <div class="order-card__actions">
                        <button class="order-card__button order-card__print" type="button">
                            <span class="order-card__button-text">@lang('labels.print-order')</span>
                            <span class="order-card__button-icon icon-printer"></span>
                        </button>
                    </div>
                @endif

                @if($order->isParent)
                    @include('staff.dashboard.parts.grouped-order')
                @else
                    @include('staff.dashboard.parts.order')
                @endif

                @if (isset($order->deliveryInformation))
                    <div class="order-card__delivery-info">
                        <div class="order-card__delivery-heading">@lang('labels.delivery-to')</div>
                        <div class="order-card__delivery-body">
                            <div
                                class="order-card__delivery-cell">{{ $order->deliveryInformation->first_name ?  ($order->deliveryInformation->first_name .' ' . $order->deliveryInformation->last_name) : $order->deliveryInformation->company_name }}</div>
                            <div
                                class="order-card__delivery-cell order-card__delivery-cell--gray">{{ $order->deliveryInformation->phone }}</div>
                            <div class="order-card__delivery-cell">{{ $order->deliveryInformation->street }}
                                , {{ $order->deliveryInformation->postal_code }}
                                , {{ $order->deliveryInformation->city }}</div>
                        </div>
                    </div>
                @endif

                @if(!empty($order->pickupDetails->time))
                    <div class="order-card__delivery-info">
                        <div class="order-card__delivery-heading">@lang('labels.pickup-details')</div>
                        <div class="order-card__delivery-body order-card__delivery-body--center">
                            <div
                                class="order-card__delivery-cell">{{ \Carbon\Carbon::parse($order->pickupDetails->day . ' ' . $order->pickupDetails->time)->format('l j M, H:i') }}</div>
                        </div>
                    </div>
                @endif
            </section>
        </article>
    @endif
@endif
