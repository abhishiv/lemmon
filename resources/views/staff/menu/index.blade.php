@extends('layouts.staff')

@section('body_class', 'page-menu')

@section('content')
    <section class="section staff-new-order">
        <div class="section__inner">
            <div class="staff-order" data-options-url="{{ route('staff.cart.options') }}">
                <div class="staff-order__section staff-order__top">
                    <div class="staff-order__controls">
                        <div class="staff-order__table"
                             data-table="{{ $table?->id ?? '' }}">{{ $table?->name ?? '' }}</div>
                        <button type="button" class="staff-order__empty button button--icon button--red"
                                data-url="{{ route('staff.cart.empty') }}" title="Empty cart">
                            <span class="button__icon icon-trash"></span>
                        </button>
                    </div>
                </div>
                <div class="staff-order__cart" data-update-url="{{ route('staff.cart.update') }}"
                     data-store-url="{{ route('staff.cart.store') }}">
                    <x-staff.cart/>
                </div>
                <div class="staff-order__section staff-order__bottom">
                    <div class="staff-order__actions">
                        <button class="staff-order__button staff-order__discount button button--dark" disabled>
                            Discount
                        </button>
                        <button class="staff-order__button staff-order__add-note button button--dark">
                            <span class="button__text button__text--desktop">@lang('labels.add-note')</span>
                            <span class="button__text button__text--mobile">@lang('labels.note')</span>
                        </button>
                        @if ($restaurant->valid_working_hours)
                            <button class="staff-order__button staff-order__takeaway button button--secondary"
                                    data-service-method="{{ \App\Models\Order::TAKEAWAY }}">@lang('labels.takeaway')</button>
                            <button class="staff-order__button staff-order__send button"
                                    data-service-method="{{ \App\Models\Order::DINEIN }}">@lang('labels.send-to-table')</button>
                            <button
                                class="staff-order__button staff-order__menu button button--secondary">@lang('labels.menu')</button>
                        @else
                            <div class="staff-order__message">@lang('labels.outside-of-working-hours')</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="staff-menu" data-add-url="{{ route('staff.cart.add') }}">
                <div class="staff-menu__header common-header">
                    <button class="staff-menu__back common-header__button icon-back"></button>
                    <span class="common-header__text">@lang('labels.menu')</span>
                    <button
                        class="staff-menu__toggle-search common-header__button common-header__button--gray icon-magnifier"></button>
                </div>
                <div class="staff-menu__search">
                    <input type="text" id="menu-search" class="input" placeholder="@lang('labels.search-menu')">
                    <button class="staff-menu__close-search icon-close"></button>
                </div>
                <div class="staff-menu__services">
                    <h2 class="staff-menu__heading">
                        <span class="staff-menu__step">Step 1</span>
                        <span class="staff-menu__instructions">Select a type of service</span>
                    </h2>
                    @foreach ($services as $service)
                        <button class="staff-menu__button" data-id="{{ $service->id }}">{{ $service->name }}</button>
                    @endforeach
                </div>
                <div class="staff-menu__categories">
                    <h2 class="staff-menu__heading">
                        <span class="staff-menu__step">Step 2</span>
                        <span class="staff-menu__instructions">Select a type of category</span>
                    </h2>
                    @foreach ($services as $service)
                        @foreach ($service->categories as $item)
                            <button class="staff-menu__button" data-service-id="{{ $service->id }}"
                                    data-category-id={{ $item['category']->id }}>{{ $item['category']->name }}</button>
                        @endforeach
                    @endforeach
                </div>
                <div class="staff-menu__products">
                    <h2 class="staff-menu__heading">
                        <span class="staff-menu__step">Step 3</span>
                        <span class="staff-menu__instructions">Select one or multiple products</span>
                    </h2>
                    @foreach ($services as $service)
                        @foreach ($service->categories as $item)
                            @foreach ($item['products'] as $product)
                                @if ($product->status == \App\Models\Product::AVAILABLE)
                                    <div class="staff-menu__item"
                                         data-service-id={{ $service->id }} data-category-id={{ $item['category']->id }}  data-product-id="{{ $product->id }}"
                                         data-product-name="{{ Str::ascii($product->name) }}">
                                        <button
                                            class="staff-menu__button {{ !empty($product->is_custom) ? 'staff-menu__button--is-custom' : '' }} {{ isset($menuQuantities[$product->id]) ? 'staff-menu__button--selected' : '' }} {{ $product->bundles->count() ? 'staff-menu__button--bundle' : '' }}">
                                            <span class="staff-menu__button-text">{{ $product->name }}</span>
                                            <span
                                                class="staff-menu__button-quantity">{{ isset($menuQuantities[$product->id]) ? $menuQuantities[$product->id] : '' }}</span>
                                        </button>
                                        @if ($product->bundles->count())
                                            <div class="staff-menu__composite-item lemmon-modal">
                                                <div class="lemmon-modal__inner">
                                                    <div class="lemmon-modal__section lemmon-modal__header">
                                                        <h2 class="lemmon-modal__header-text">@lang('labels.select-supplements')</h2>
                                                        <button class="lemmon-modal__close" type="button">
                                                            <span class="lemmon-modal__close-icon icon-close"></span>
                                                        </button>
                                                    </div>
                                                    <div class="lemmon-modal__section lemmon-modal__body">

                                                        <h3 class="staff-menu__composite-item-instructions">@lang('labels.select-suplements-instructions')</h3>

                                                        @foreach ($product->bundles as $bundle)
                                                            <div class="staff-menu-bundle"
                                                                 data-min="{{ $bundle->min_limit }}"
                                                                 data-max="{{ $bundle->limit }}"
                                                            >
                                                                <h3 class="staff-menu-bundle__heading">
                                                                    <span
                                                                        class="staff-menu-bundle__name">{{ $bundle->name }}</span>
                                                                    @if ($bundle->min_limit > 0)
                                                                        <span class="staff-menu-bundle__required">(@lang('labels.required'))</span>
                                                                    @endif
                                                                </h3>
                                                                <div class="staff-menu-bundle__items">
                                                                    @foreach ($bundle->extraProducts->sortByDesc('pivot.price') as $key => $bundleProduct)
                                                                        <x-staff.bundle-item :item="$bundleProduct"
                                                                                             entity-type="products"
                                                                                             :bundle-id="$bundle->id"/>
                                                                    @endforeach
                                                                    @foreach ($bundle->extras->sortByDesc('pivot.price') as $key => $bundleExtra)
                                                                        <x-staff.bundle-item :item="$bundleExtra"
                                                                                             entity-type="extras"
                                                                                             :bundle-id="$bundle->id"/>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="lemmon-modal__section lemmon-modal__footer">
                                                        <button class="lemmon-modal__cancel button button--secondary"
                                                                type="button">@lang('labels.cancel')</button>
                                                        <button class="staff-menu__add button"
                                                                type="button">@lang('labels.add-button')</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
                <div class="staff-menu__footer">
                    <button id="staff-menu-footer-back" class="button">@lang('labels.back')</button>
                    <button id="staff-menu-footer-cart" class="button button--secondary">@lang('labels.cart')</button>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer')
    <div id="send-to-table" class="lemmon-modal select-table-modal">
        <div class="lemmon-modal__inner">
            <div class="lemmon-modal__section lemmon-modal__header">
                <h2 class="lemmon-modal__header-text">@lang('labels.send-to-table')</h2>
                <button class="lemmon-modal__close" type="button">
                    <span class="lemmon-modal__close-icon icon-close"></span>
                </button>
            </div>
            <div class="common-header">
                <button class="select-table-modal__back common-header__button icon-back"></button>
                <span class="common-header__text">@lang('labels.tables')</span>
                <button
                    class="select-table-modal__toggle-search common-header__button common-header__button--gray icon-magnifier"></button>
                <div class="select-table-modal__search">
                    <input type="text" id="table-search" class="input" placeholder="@lang('labels.search-table')">
                    <button class="select-table-modal__close-search icon-close"></button>
                </div>
            </div>
            <div class="lemmon-modal__section lemmon-modal__body">
                <div class="select-table-modal__dropdown">
                    <label for="select-table" class="label">@lang('labels.table-number')</label>
                    <select id="select-table" class="dropdown">
                        <option value="none">@lang('labels.select-table')</option>
                        @foreach ($tables as $currentTable)
                            <option
                                value="{{ $currentTable->id }}" @selected($table?->id == $currentTable->id)>{{ $currentTable->name }}</option>
                        @endforeach
                    </select>
                </div>
                <h3 class="select-table-modal__heading">@lang('labels.select-a-table-number')</h3>
                <div class="select-table-modal__buttons">
                    @foreach ($tables as $currentTable)
                        <button
                            type="button"
                            class="select-table-modal__button {{ $table?->id == $currentTable->id ? 'select-table-modal__button--selected' : '' }} button"
                            data-table={{ $currentTable->id }}
                                data-table-name="{{ Str::ascii($currentTable->name) }}"
                        >{{ $currentTable->name }}</button>
                    @endforeach
                </div>
            </div>
            <div class="lemmon-modal__section lemmon-modal__footer">
                <button class="lemmon-modal__cancel button button--secondary"
                        type="button">@lang('labels.cancel')</button>
                <button class="lemmon-modal__confirm button"
                        type="button" @disabled(!$table)>@lang('labels.send')</button>
            </div>
        </div>
    </div>
    <div id="add-notes" class="add-note-modal lemmon-modal" data-notes-url="{{ route('staff.cart.notes') }}">
        <div class="lemmon-modal__inner">
            <div class="lemmon-modal__section lemmon-modal__header">
                <h2 class="lemmon-modal__header-text">@lang('labels.add-note-to-order')</h2>
                <button class="lemmon-modal__close" type="button">
                    <span class="lemmon-modal__close-icon icon-close"></span>
                </button>
            </div>
            <div class="lemmon-modal__section lemmon-modal__body"></div>
            <div class="lemmon-modal__section lemmon-modal__footer">
                <button class="lemmon-modal__cancel button button--secondary"
                        type="button">@lang('labels.cancel')</button>
                <button class="lemmon-modal__confirm button" type="button">@lang('labels.add-button')</button>
            </div>
        </div>
    </div>
    @include('staff.menu.parts.tips-modal')
    @include('staff.menu.parts.open-amount-modal')
    @include('staff.menu.parts.discount-modal')
    @include('staff.menu.parts.checkout-modal', [
        'checkoutModalId' => 'lemmon-order-checkout',
        'displayedButton' => 'tips',
        'totalsUrl' => route('staff.cart.totals'),
        'optionsUrl' => route('staff.cart.options'),
    ])
    <div id="order-sent-notification" class="lemmon-notification">
        <span class="lemmon-notification__text">@lang('labels.order-sent')</span>
        <span class="lemmon-notification__icon icon-checkmark-circle"></span>
    </div>
@endsection
