@extends('layouts.customer')
@push('scripts')
    <script type="module" src="{{ mix('/dist/js/libraries/bootstrap.bundle.min.js', '../') }}"></script>
    <script type="module" src="{{ mix('/dist/js/customer/cart.js', '../') }}" defer></script>
@endpush
@section('class', 'main-cart')
@section('cart-header')
    <div class="cart-header" id="header">
        <div class="header-top">
            <div class="header-top__icons">
                <a class="redirect" href="{{ session('table.url') }}">
                    <div class="header-top__back">
                        <button class="header-top__back__btn icon-back"></button>
                    </div>
                </a>
            </div>
            <div class="header-top__name">
                <span class="menu-title">@lang('labels.order-summary')</span>
                <span class="menu-title menu-title--delivery">@lang('labels.delivery')</span>
                <span class="menu-title menu-title--takeaway">@lang('labels.takeaway')</span>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @if ($products)
        <form id="cart-form" action="{{ route('customer.order.store') }}" method="POST"
              data-refresh-url={{ route('customer.cart.refresh') }}>
            @csrf
            {{-- Tips input --}}
            <input name="tips" value="" style="display:none">
            <div class="cart-list">
                <section class="customer-cart list">
                    <div class="customer-cart-totals">
                        <div class="customer-cart-totals__row">
                            <span class="customer-cart-totals__cell">{{ $totalItems }} @lang('labels.items')</span>
                        </div>
                        <div class="customer-cart-totals__row customer-cart-totals__row--large">
                            <span class="customer-cart-totals__cell">@lang('labels.total')</span>
                            <span class="customer-cart-totals__cell">@lang('labels.currency') <span
                                    id="total">{{ priceFormat($total) }}</span></span>
                        </div>
                        <div id="takeaway-discount" style="display:{{ session('cart.takeaway') ? 'flex' : 'none' }};"
                             class="customer-cart-totals__row">
                            <span class="customer-cart-totals__cell">@lang('labels.discount-takeaway')</span>
                            <span class="customer-cart-totals__cell"><span
                                    id="takeaway-discount-value">{{ session('cart.takeaway_discount') }}</span>%</span>
                        </div>
                        <div id="company-discount" class="customer-cart-totals__row customer-cart-totals__row--hidden">
                            <span class="customer-cart-totals__cell">@lang('labels.company-discount')</span>
                            <span class="customer-cart-totals__cell"><span id="company-discount-value"></span>%</span>
                        </div>
                        <div id="delivery-fee" class="customer-cart-totals__row customer-cart-totals__row--hidden">
                            <span class="customer-cart-totals__cell">@lang('labels.delivery-fee')</span>
                            <span class="customer-cart-totals__cell">@lang('labels.currency') <span
                                    id="delivery-fee-value"></span></span>
                        </div>
                    </div>

                    @foreach ($products as $index => $item)
                        <input type="hidden" name="items[{{ $index }}]" value="{{ $item->id }}">
                        <input type="hidden" name="itemNotes[{{ $index }}]" value="{{ $item->notes }}">
                        <div
                            class="customer-cart-item products"
                            data-product-id="{{ $index }}"
                            data-quantity="{{ $item->quantity }}"
                        >
                            <a data-id="{{ $index }}" data-href="{{ route('customer.cart.update') }}"
                               class="swipe-remove">
                                <div class="products__list">
                                    <div class="products__list__item">
                                        <div class="customer-cart-item__header">
                                            <h4 @class(['products__list__item__title'])>{{ $item->name }}</h4>
                                            <button
                                                type="button" class="products__remove icon-trash"
                                                data-href="{{ route('customer.cart.delete') }}"
                                            ></button>
                                        </div>
                                        <p class="products__list__item__description">
                                            @if (!empty($item->bundles->products) || !empty($item->bundles->extras))
                                                Extras:
                                                @if (isset($item->bundles->products) && !empty($item->bundles->products))
                                                    @foreach ($item->bundles->products as $key => $extraProducts)
                                                        @foreach ($extraProducts as $extraProduct)
                                                            <input type="text"
                                                                   name="bundle[{{$index}}][{{ $item->id }}][Product][{{ $key }}][{{ $loop->iteration }}][id]"
                                                                   hidden value="{{ $extraProduct->id }}">
                                                            <input type="text"
                                                                   name="bundle[{{$index}}][{{ $item->id }}][Product][{{ $key }}][{{ $loop->iteration }}][price]"
                                                                   hidden value="{{ $extraProduct->price }}">
                                                        @endforeach
                                                    @endforeach
                                                @endif
                                                @if (isset($item->bundles->extras) && !empty($item->bundles->extras))
                                                    @foreach ($item->bundles->extras as $key => $extras)
                                                        @foreach ($extras as $extra)
                                                            <input type="text"
                                                                   name="bundle[{{ $index }}][{{ $item->id }}][Extra][{{ $key }}][{{ $loop->iteration }}][id]"
                                                                   hidden value="{{ $extra->id }}">
                                                            <input type="text"
                                                                   name="bundle[{{ $index }}][{{ $item->id }}][Extra][{{ $key }}][{{ $loop->iteration }}][price]"
                                                                   hidden value="{{ $extra->price }}">
                                                        @endforeach
                                                    @endforeach
                                                @endif
                                                @foreach ($item->bundles->all as $bundledItem)
                                                    @if ($loop->iteration > 3)
                                                        and {{ count($item->bundles->all) - 3 }} more)
                                                        @break
                                                    @else
                                                        {{ $loop->first ? '(' : '' }}{{ class_basename($bundledItem) == 'Product' ? $bundledItem->name : $bundledItem->title }}{{ $loop->last ? ')' : ($loop->iteration < 3 ? ' /' : '') }}
                                                    @endif
                                                @endforeach
                                            @else
                                                {{ Str::limit($item->description, 50) }}
                                            @endif
                                            <br>
                                        </p>
                                        <div class="customer-cart-item__footer">
                                            <div class="products__list__item__price">
                                                <div class="left-side">
                                                    <p class="price">{{ __('labels.currency') }}
                                                        {{ priceFormat($item->price) }} </p>
                                                </div>
                                                <div class="customer-cart-item__quantity">
                                                    <button
                                                        class="customer-cart-item__quantity-button icon-minus remove update {{ $item->quantity == 1 ? 'customer-cart-item__quantity-button--disabled' : ''}}"></button>
                                                    <input
                                                        type="text"
                                                        class="customer-cart-item__quantity-input quantity"
                                                        name="quantity[{{ $index }}]"
                                                        value="{{ $item->quantity }}"
                                                        inputmode="numeric"
                                                    >
                                                    <button
                                                        class="customer-cart-item__quantity-button icon-plus add update"></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach

                    <div class="customer-cart__actions">
                        <button type="button"
                                class="customer-button customer-button--light customer-button--split cart-remove">
                            <span class="customer-button__text">@lang('customer.remove')</span>
                            <span class="customer-button__icon customer-button__icon--small icon-minus-outline"></span>
                        </button>
                    </div>
                    <div class="overlay-btn">
                        <a href="#" class="customer-button remove-btn"
                           data-href="{{ route('customer.cart.destroy') }}">{{ __('customer.delete') }}</a>
                    </div>

                    @if (Session::has('error'))
                        <div class="overlay-content">
                            <div class="content">
                                <div class="close-modal">
                                    @include('layouts.parts.icons.close-modal')
                                </div>
                                <p class="error-message">{{ Session::get('error') }}</p>
                            </div>
                        </div>
                    @endif

                    <input id="service-method" type="hidden" name="service_method" value="">

                    @if ($table->type == \App\Models\RestaurantTable::OFFSITE && $takeaway['active'])
                        <div class="cart-form__group cart-form__group--takeaway">
                            <div class="form-control-group">
                                <div class="form-control__heading">@lang('labels.pickup-details')</div>

                                <div class="form-control-wrapper">
                                    <input
                                        class="cart-form__pickup customer-radio-button"
                                        type="radio"
                                        name="pickup"
                                        id="right-away"
                                        value="now"
                                        required
                                    >
                                    <label for="right-away"
                                           class="customer-label customer-label--radio">@lang('labels.right-away')</label>
                                </div>

                                <div class="form-control-wrapper">
                                    <input
                                        class="cart-form__pickup customer-radio-button"
                                        type="radio"
                                        name="pickup"
                                        id="pick-a-day"
                                        value="later"
                                        required
                                    >
                                    <label for="pick-a-day"
                                           class="customer-label customer-label--radio">@lang('labels.pick-a-day')</label>
                                </div>
                                @if($errors->has('pickup'))
                                    <div class="error">{{ $errors->first('pickup') }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($table->type == \App\Models\RestaurantTable::OFFSITE && ($takeaway['active'] || $delivery['active']))
                        <div class="cart-form__group cart-form__group--offsite">
                            <div class="form-control-wrapper form-control-wrapper--hidden">
                                <input
                                    class="inp-cbx"
                                    name="customer_type"
                                    id="company-order"
                                    type="checkbox"
                                    value="{{ old('customer_type') }}"
                                    @checked(old('customer_type') === 'company')
                                />
                                <label class="cbx cbx--cart" for="company-order">
                                    <span class="cbx__text">@lang('labels.company-order-fr')</span>
                                    <span class="cbx__mark"></span>
                                </label>
                            </div>

                            <div class="form-control-wrapper">
                                <label for="first-name" class="customer-label">@lang('labels.first-name')</label>
                                <input
                                    id="first-name"
                                    name="first_name"
                                    type="text"
                                    class="customer-input"
                                    placeholder="@lang('labels.first-name')"
                                    value="{{ old('first_name') }}"
                                    required
                                >
                                @if($errors->has('first_name'))
                                    <div class="error">{{ $errors->first('first_name') }}</div>
                                @endif
                            </div>

                            <div class="form-control-wrapper">
                                <label for="last-name" class="customer-label">@lang('labels.last-name')</label>
                                <input
                                    id="last-name"
                                    name="last_name"
                                    type="text"
                                    class="customer-input"
                                    placeholder="@lang('labels.last-name')"
                                    value="{{ old('last_name') }}"
                                    required
                                >
                                @if($errors->has('last_name'))
                                    <div class="error">{{ $errors->first('last_name') }}</div>
                                @endif
                            </div>

                            <div class="form-control-wrapper form-control-wrapper--hidden">
                                <label for="company-name" class="customer-label">@lang('labels.company-name')</label>
                                <input
                                    id="company-name"
                                    name="company_name"
                                    type="text"
                                    class="customer-input"
                                    placeholder="@lang('labels.company-name')"
                                    value="{{ old('company_name') }}"
                                >
                                @if($errors->has('company_name'))
                                    <div class="error">{{ $errors->first('company_name') }}</div>
                                @endif
                            </div>

                            <div class="form-control-wrapper">
                                <label for="phone-number" class="customer-label">@lang('labels.phone-number')</label>
                                <input
                                    id="phone-number"
                                    name="phone"
                                    type="text"
                                    class="customer-input"
                                    placeholder="@lang('labels.phone-number')"
                                    value="{{ old('phone') }}"
                                    required
                                >
                                @if($errors->has('phone'))
                                    <div class="error">{{ $errors->first('phone') }}</div>
                                @endif
                            </div>

                            <div class="form-control-wrapper">
                                <label for="email" class="customer-label">@lang('labels.email')</label>
                                <input
                                    class="customer-input email"
                                    type="email"
                                    name="email"
                                    id="email"
                                    placeholder="@lang('customer.enter-email')"
                                    value="{{ old('email') }}"
                                    required
                                >
                                @if($errors->has('email'))
                                    <div class="error">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($table->type == \App\Models\RestaurantTable::OFFSITE && $delivery['active'])
                        <div class="cart-form__group cart-form__group--delivery">
                            <div class="form-control-wrapper">
                                <label for="street" class="customer-label">@lang('labels.delivery-address')</label>
                                <input
                                    id="street"
                                    name="street"
                                    type="text"
                                    class="customer-input"
                                    placeholder="@lang('labels.delivery-address')"
                                    value="{{ old('street') }}"
                                    required
                                >
                                @if($errors->has('street'))
                                    <div class="error">{{ $errors->first('street') }}</div>
                                @endif
                            </div>

                            <div class="form-control-wrapper">
                                <label for="postal_code" class="customer-label">@lang('labels.postal-code')</label>
                                <input
                                    id="postal_code"
                                    name="postal_code"
                                    type="text"
                                    class="customer-input"
                                    placeholder="@lang('labels.postal-code')"
                                    value="{{ old('postal_code') }}"
                                    required
                                >
                                @if($errors->has('postal_code'))
                                    <div class="error">{{ $errors->first('postal_code') }}</div>
                                @endif
                            </div>

                            <div class="form-control-wrapper">
                                <label for="delivery-city" class="customer-label">@lang('labels.select-city')</label>
                                <select id="delivery-city" name="city" class="customer-select" required>
                                    <option value="">@lang('labels.select-city')</option>
                                    @if ($delivery['cities'])
                                        @foreach($delivery['cities'] as $city)
                                            <option value="{{ $city->name }}"
                                                    @selected(old('city') === $city->name) data-amount="{{ priceFormat($city->fee) }}">{{ $city->name }}
                                                ({{ $city->fee == 0 ? __('labels.free') : '+' . __('labels.currency') . ' ' . priceFormat($city->fee) }}
                                                )
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @if($errors->has('city'))
                                    <div class="error">{{ $errors->first('city') }}</div>
                                @endif
                            </div>
                            <div class="form-control-wrapper">
                                <label for="delivery_notes"
                                       class="customer-label">@lang('labels.delivery_notes')</label>
                                <textarea style="min-height: 95px;"
                                    id="delivery_notes"
                                    name="delivery_notes"
                                    class="customer-input"
                                    placeholder="@lang('labels.delivery_notes_placeholder')"
                                >{{ old('delivery_notes') }}</textarea>
                                @if($errors->has('delivery_notes'))
                                    <div class="error">{{ $errors->first('postal_code') }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($table->type == \App\Models\RestaurantTable::OFFSITE && $takeaway['active'])
                        <div class="cart-form__group cart-form__group--takeaway">
                            <div class="form-control-group form-control-group--pickup-later">
                                <div class="form-control-wrapper">
                                    <label for="pickup-day" class="customer-label">@lang('labels.day')</label>
                                    <select id="pickup-day" name="pickup_day" class="customer-select">
                                        <option value="">@lang('labels.choose-day')</option>
                                        @if ($takeaway['pickup_options']['days'])
                                            @foreach($takeaway['pickup_options']['days'] as $key => $pickupDay)
                                                <option value="{{ $pickupDay }}"
                                                        @selected(old('pickup_day') === $pickupDay) data-day="{{$key === 0 ? 'today' : 'other'}}">{{ $key === 0 ? 'Today, ' : ($key === 1 ? 'Tomorrow, ' : '') }}{{ $pickupDay }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if($errors->has('pickup_day'))
                                        <div class="error">{{ $errors->first('pickup_day') }}</div>
                                    @endif
                                </div>

                                <div class="form-control-wrapper">
                                    <span class="customer-label">@lang('labels.time-slots')</span>
                                    <div id="pickup-time-grid" class="form-control-grid">

                                        @if ($takeaway['pickup_options']['times'])
                                            @foreach ($takeaway['pickup_options']['times'] as $key => $time)
                                                <div
                                                    class="form-control-wrapper {{  \Carbon\Carbon::createFromFormat('H:i', $time)->gt($takeaway['pickup_options']['currentTime']) ? 'form-control-wrapper--later' : '' }}">
                                                    <input
                                                        class="customer-radio-button"
                                                        type="radio"
                                                        name="pickup_time"
                                                        id="pickup-time-{{ $key }}"
                                                        value="{{ $time }}"
                                                    >
                                                    <label for="pickup-time-{{ $key }}"
                                                           class="customer-label customer-label--button"
                                                           tabindex="0">{{ $time }}</label>
                                                </div>
                                            @endforeach
                                        @endif

                                        <button class="form-control-toggle" type="button">
                                            <span
                                                class="form-control-toggle__expand">@lang('labels.show-more-time-slots')</span>
                                            <span
                                                class="form-control-toggle__contract">@lang('labels.show-fewer-time-slots')</span>
                                        </button>

                                        @if($errors->has('pickup_time'))
                                            <div
                                                class="form-control-grid__error error">{{ $errors->first('pickup_time') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="cart-form__group cart-form__group--payment">
                        <div class="customer-button-wrapper">
                            @if ($table->type === \App\Models\RestaurantTable::SERVE)
                                <div class="customer-button-wrapper__group">
                                    <button
                                        type="submit"
                                        name="payment_method"
                                        value="{{ \App\Models\Order::CASH }}"
                                        class="customer-button payment"
                                    >{{ __('labels.cash') }}</button>
                                </div>
                            @endif
                            <div class="customer-button-wrapper__group">
                                <button
                                    type="submit"
                                    name="payment_method"
                                    value="{{ \App\Models\Order::ONLINE }}"
                                    class="customer-button payment"
                                >
                                    <span class="payment__card">{{ __('labels.card') }}</span>
                                    <span class="payment__pay">{{ __('labels.pay') }}</span>
                                </button>
                                <p class="cart-form__disclaimer">By paying with card, you accept <a
                                        class="cart-form__disclaimer-link"
                                        href="https://thelemmon.ch/termes-et-conditions/"
                                        target="_blank"
                                    >Lemmon's terms & conditions</a>.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="dining-container">
                <div class="dining-cart">
                    <div class="customer-button-wrapper">
                        @if ($table->type == \App\Models\RestaurantTable::SERVE)
                            <button
                                type="button"
                                name="take_away"
                                data-takeaway=""
                                data-href="{{ route('customer.cart.dine') }}"
                                value=""
                                class="cart-form__service-method customer-button"
                                data-service-method="{{ \App\Models\Order::DINEIN }}"
                            >
                                <span class="customer-button__text">{{ __('labels.dine-in') }}</span>
                                <span class="customer-button__icon customer-button__icon--small icon-food"></span>
                            </button>
                        @endif

                        @if ($takeaway['active'])
                            <button
                                type="button"
                                name="take_away"
                                data-takeaway="yes"
                                data-href="{{ route('customer.cart.dine') }}"
                                value="yes"
                                class="cart-form__service-method customer-button"
                                data-show-details-form="{{ $table->type == \App\Models\RestaurantTable::OFFSITE  ? 'true' : '' }}"
                                data-service-method="{{ \App\Models\Order::TAKEAWAY }}"
                            >
                                <span class="customer-button__text">{{ __('labels.takeaway') }}</span>
                                <span class="customer-button__icon customer-button__icon--small icon-car"></span>
                            </button>
                        @endif

                        @if ($table->type == \App\Models\RestaurantTable::OFFSITE && $delivery['active'])
                            <button
                                type="button"
                                data-href="{{ route('customer.cart.dine') }}"
                                data-service-method="{{ \App\Models\Order::DELIVERY }}"
                                class="cart-form__service-method customer-button"
                            >
                                <span class="customer-button__text">{{ __('labels.delivery') }}</span>
                                <span class="customer-button__icon customer-button__icon--small icon-scooter"></span>
                            </button>
                        @endif

                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="empty-cart">
            <span class="empty-cart__text">{{ __('customer.empty_order_list') }}</span>
            <div class="empty-cart__actions">
                <a href="{{ session('table.url') }}" class="payment customer-button">{{ __('customer.view_menu') }}</a>
            </div>
        </div>
    @endif

    {{-- Tips popup --}}
    @include('customer.cart.tips-popup')

    <div id="payment-overlay" style="display: none;">
        <div id="payment-overlay-message">@lang('customer.payment-wait-message')</div>
    </div>
@endsection
