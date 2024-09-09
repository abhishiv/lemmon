@extends('layouts.staff')

@section('body_class', 'page-tables')

@section('filters')
    <div class="header__filters">
        <div class="header__filter-group">
            <div class="header__filter-wrapper">
                <label for="tables-room-filter" class="label">@lang('labels.rooms')</label>
                <select id="tables-room-filter" class="dropdown">
                    <option value="">@lang('labels.all')</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room }}">{{ $room }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="header__filter-group">
            <div class="header__filter-wrapper">
                <label class="label">Status</label>
                <ul id="table-filters" class="filters-nav">
                    <li class="filters-nav__item">
                        <button class="filters-nav__button" data-status="">@lang('labels.all')</button>
                    </li>
                    <li class="filters-nav__item filters-nav__item--active">
                        <button class="filters-nav__button" data-status="busy"
                                data-no-records="{{ __('labels.no-records-table-busy') }}">@lang('labels.busy')</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('content')

    <section class="section">
        <div class="section__inner">
            <table id="restaurant-tables" class="restaurant-tables lemmon-table"
                   data-list-url="{{ route('staff.tables.list') }}"
                   data-update-status-url="{{ route('staff.tables.update-status') }}">
                <thead>
                <tr>
                    <th class="restaurant-tables__heading lemmon-table__heading lemmon-table__heading--ordered lemmon-table__heading--highlight">
                        <button id="refresh-button" style="display: none;">Refresh Table</button>
                        @lang('labels.tables')
                    </th>
                    <th class="restaurant-tables__heading lemmon-table__heading">@lang('labels.room')</th>
                    <th class="restaurant-tables__heading lemmon-table__heading">@lang('labels.status')</th>
                    <th class="restaurant-tables__heading restaurant-tables__heading--actions lemmon-table__heading">@lang('labels.actions')</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
@endsection

@section('footer')
    <div id="table-orders-summary" class="table-orders-summary lemmon-modal lemmon-modal--improved"
         data-url="{{ route('staff.tables.summary') }}">
        <div class="lemmon-modal__inner">
            <div class="lemmon-modal__section lemmon-modal__header">
                <h2 class="lemmon-modal__header-text"></h2>
                <button class="lemmon-modal__close" type="button">
                    <span class="lemmon-modal__close-icon icon-close"></span>
                </button>
            </div>
            <div class="lemmon-modal__section lemmon-modal__body"></div>
            <div class="lemmon-modal__section lemmon-modal__footer">
                <div class="lemmon-modal__buttons">
                    <div class="lemmon-modal__button-wrapper">
                        <button class="lemmon-modal__cancel button button--secondary"
                                type="button">@lang('labels.back')</button>
                    </div>
                    <form class="lemmon-modal__button-wrapper" action="{{ route('staff.menu') }}" method="post">
                        @csrf
                        <input type="hidden" name="table_id" value="">
                        <button class="button button--secondary" type="submit">@lang('labels.add-order')</button>
                    </form>
                    <div class="lemmon-modal__button-wrapper">
                        <button class="lemmon-modal__edit button"
                                type="button">@lang('labels.edit')</button>
                        <button class="lemmon-modal__back button d-none"
                                type="button">@lang('labels.cancel')</button>
                    </div>
                    <div class="lemmon-modal__button-wrapper lemmon-modal__button-wrapper-pay">
                        <button class="lemmon-modal__action lemmon-modal__confirm button"
                                type="button">@lang('labels.pay')</button>
                        <button class="lemmon-modal__action lemmon-modal__close-orders button"
                                type="button">@lang('labels.close-orders')</button>
                    </div>
                </div>
                {{--<div class="lemmon-modal__messages">
                    <p class="table-orders-summary__payment-disabled lemmon-modal__message lemmon-modal__message--error">@lang('labels.complete-before-payment')
                        .</p>
                </div>--}}
            </div>
        </div>
    </div>
    @include('staff.menu.parts.amount-modal')
    @include('staff.menu.parts.discount-modal')
    @include('staff.menu.parts.delete-modal')
    @include('staff.menu.parts.change-table-modal')
    @include('staff.menu.parts.checkout-modal', [
        'checkoutModalId' => 'lemmon-table-checkout',
        'displayedButton' => 'print',
        'totalsUrl' => route('staff.tables.payment-totals'),
        'optionsUrl' => route('staff.tables.payment-options'),
        'payUrl' => route('staff.tables.update-payment'),
        'closeOrdersUrl' => route('staff.tables.close-orders'),
    ])
@endsection
