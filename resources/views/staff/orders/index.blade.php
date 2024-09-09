@extends('layouts.staff')

@section('body_class', 'page-orders')

@section('filters')
    <div class="header__filters">
        <div class="header__filter-group">
            <div class="header__filter-wrapper">
                <label for="staff-orders-views-filter" class="label">@lang('labels.view')</label>
                <select id="staff-orders-views-filter" class="dropdown">
                    <option value="all">@lang('labels.all')</option>
                    <option value="{{ \App\Models\Product::BAR }}">@lang('labels.bar')</option>
                    <option value="{{ \App\Models\Product::RESTAURANT }}">@lang('labels.restaurant')</option>
                    <option value="{{ \App\Models\Order::TAKEAWAY }}">@lang('labels.takeaway-and-delivery')</option>
                </select>
            </div>
            
            <div class="header__filter-wrapper">
                <label for="staff-orders-day-filter" class="label">@lang('labels.date')</label>
                <select id="staff-orders-day-filter" class="dropdown">
                    <option value="now">@lang('labels.today')</option>
                    <option value="later">@lang('labels.later')</option>
                </select>
            </div>

            <div class="header__filter-wrapper">
                <label for="staff-orders-status-filter" class="label">@lang('labels.status')</label>
                <select id="staff-orders-status-filter" class="dropdown">
                    <option value="{{ \App\Models\Order::NEW }}">@lang('labels.all')</option>
                    <option value="{{ \App\Models\Order::READY }}">@lang('labels.ready')</option>
                    <option value="{{ \App\Models\Order::CLOSED }}">@lang('labels.completed')</option>
                </select>
            </div>

            <div class="header__filter-wrapper">
                <label for="staff-orders-table-filter" class="label">@lang('labels.table')</label>
                <select id="staff-orders-table-filter" class="dropdown">
                    <option value="all">@lang('labels.all')</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}" @selected($table->id == $selectedTable)>{{ $table->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="header__filter-group header__filter-group--align-right">
            <a href="{{ route('staff.menu') }}" class="button">
                <span class="button__text">New order</span>
                <span class="button__icon icon-plus"></span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <section class="section dashboard" data-get-url="{{route('staff.order.get')}}" data-put-url="{{route('staff.order.update')}}">
        <div class="section__inner">
            <x-staff.list-orders :orders="$orders" :restaurant="$restaurant" :available-printers="$availablePrinters" />
        </div>
    </section>
    <div id="order-cancel-confirmation" class="lemmon-modal">
        <div class="lemmon-modal__inner">
            <div class="lemmon-modal__section lemmon-modal__header">
                <h2 class="lemmon-modal__header-text">Confirm action</h2>
                <button class="lemmon-modal__close" type="button">
                    <span class="lemmon-modal__close-icon icon-close"></span>
                </button>
            </div>
            <div class="lemmon-modal__section lemmon-modal__body">
                <p>@lang('labels.cancel-order')</p>
            </div>
            <div class="lemmon-modal__section lemmon-modal__footer">
                <button class="lemmon-modal__cancel button button--secondary" type="button">@lang('labels.cancel')</button>
                <button class="lemmon-modal__confirm button" type="button">@lang('labels.send')</button>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('staff.menu.parts.amount-modal')
    @include('staff.menu.parts.discount-modal')
    @include('staff.menu.parts.checkout-modal', [
        'checkoutModalId' => 'lemmon-order-checkout',
        'displayedButton' => 'print',
        'totalsUrl' => route('staff.dashboard.payment-totals'),
        'optionsUrl' => route('staff.dashboard.payment-options'),
        'payUrl' => route('staff.dashboard.update-payment'),
    ])
@endsection