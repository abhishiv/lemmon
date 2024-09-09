@extends('layouts.staff')

@section('body_class', 'page-products')

@section('content')
    <section class="section">
        <div class="section__inner">
            <table id="product-table" class="product-table lemmon-table"  data-update-url="{{ route('staff.products.update') }}">
                <thead>
                    <tr>
                        <th class="product-table__heading lemmon-table__heading lemmon-table__heading--highlight">@lang('labels.availability')</th>
                        <th class="product-table__heading product-table__heading--name lemmon-table__heading lemmon-table__heading--ordered">@lang('labels.item-name')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="lemmon-table__row" data-type="product" data-id="{{ $product->id }}">
                            <td class="lemmon-table__cell lemmon-table__cell--highlight">
                                <div class="checkbox">
                                    <input id="product-table-{{ $product->id }}" type="checkbox" class="checkbox__input" @checked($product->status === \App\Models\Product::AVAILABLE)>
                                    <label for="product-table-{{ $product->id }}" class="checkbox__label"></label>
                                </div>
                            </td>
                            <td class="lemmon-table__cell product-table__name">{{ $product->name }}</td>
                        </tr>
                    @endforeach
                    @foreach ($extras as $extra)
                        <tr class="lemmon-table__row" data-type="extra" data-id="{{ $extra->id }}">
                            <td class="lemmon-table__cell lemmon-table__cell--highlight">
                                <div class="checkbox">
                                    <input id="extra-table-{{ $extra->id }}" type="checkbox" class="checkbox__input" @checked($extra->status === \App\Models\Extra::AVAILABLE)>
                                    <label for="extra-table-{{ $extra->id }}" class="checkbox__label"></label>
                                </div>
                            </td>
                            <td class="lemmon-table__cell product-table__name">@lang('labels.extra'): {{ $extra->title }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection

@section('footer')
    <div id="product-status-confirmation" class="lemmon-modal lemmon-modal--improved product-status-confirmation">
        <div class="lemmon-modal__inner">
            <div class="lemmon-modal__section lemmon-modal__header">
                <h2 class="lemmon-modal__header-text"><span class="product-status-confirmation__placeholder"></span> - @lang('labels.availability')</h2>
                <button class="lemmon-modal__close" type="button">
                    <span class="lemmon-modal__close-icon icon-close"></span>
                </button>
            </div>
            <div class="lemmon-modal__section lemmon-modal__body">
                <div class="product-status-confirmation__message product-status-confirmation__enable">
                    @lang('labels.product-enable-confirmation', ['product' => '<span class="product-status-confirmation__placeholder"></span>'])
                </div>
                <div class="product-status-confirmation__message product-status-confirmation__disable">
                    @lang('labels.product-disable-confirmation', ['product' => '<span class="product-status-confirmation__placeholder"></span>'])
                </div>
            </div>
            <div class="lemmon-modal__section lemmon-modal__footer">
                <button class="lemmon-modal__cancel button button--secondary" type="button">@lang('labels.cancel')</button>
                <button class="lemmon-modal__confirm button" type="button">@lang('labels.confirm')</button>
            </div>
        </div>
    </div>
    <div id="product-status-updated-notification" class="lemmon-notification">
        <span class="lemmon-notification__text">@lang('labels.product-status-updated')</span>
        <span class="lemmon-notification__icon icon-checkmark-circle"></span>
    </div>
@endsection