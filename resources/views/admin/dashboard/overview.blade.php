@extends('layouts.admin')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ mix('/dist/css/libraries/jquery.timepicker.min.css', '../') }}"/>
@endpush

@push('scripts')
    <script src="{{ mix('/dist/js/libraries/jquery.timepicker.min.js', '../') }}"></script>
    <script src="{{mix('/dist/js/admin.js', '../')}}" defer></script>
    <script src="{{mix('/dist/js/admin/dashboard.js', '../')}}" type="module" defer></script>
@endpush
@section('body_class', 'management-table statistics-overview')
@section('content')
    <div class="row">
        <h2 class="title">@lang('labels.dashboard')</h2>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <div class="custom restaurant-select">
                <label for="restaurant">@lang('labels.restaurant')</label>
                <select name="restaurant" id="restaurant" autocomplete="off">
                    <option value="restaurant">@lang('labels.all')</option>
                    @foreach($restaurants as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>

            </div>
        </div>
        <div class="col-sm-2">
            <div class="custom date">
                <label>@lang('labels.date-from')</label>
                <input class="gray" readonly="readonly" type="text" id="date_from" name="date"
                       autocomplete="off"
                       value="{{ now()->startOfMonth()->startOfDay()->format('Y/m/d H:i') }}">
            </div>
        </div>
        <div class="col-sm-2">
            <div class="custom date">
                <label>@lang('labels.date-to')</label>
                <input class="gray" readonly="readonly" type="text" id="date_to" name="date"
                       autocomplete="off"
                       value="{{ now()->format('Y/m/d H:i') }}">
            </div>
        </div>
        <div class="col-sm-6 action-buttons custom">
            <div class="row">
                <div class="col-sm-3 mt-5">
                    <div class="btn" id="btn-run-stats">@lang('labels.run')</div>
                </div>
                <div class="col-sm-3 mt-5">
                    <div class="btn danger-button" id="btn-reset-stats">@lang('labels.reset')</div>
                </div>
                <div class="col-sm-3 mt-5">
                    <div class="btn" id="btn-export-stats">@lang('labels.export-csv')</div>
                </div>
                <div class="col-sm-3 mt-5">
                    <a target="_blank" href="#" class="btn d-none" id="btn-download-stats">@lang('labels.download-csv')</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="statistics-container" data-statistics-url="{{ route('admin.statistics') }}" data-export-url="{{ route('admin.statistics.export') }}"
    data-export-get-url="{{ route('admin.statistics.get') }}">
        <div class="col-sm-6">
            <div class="custom">
                <div class="text-stats">@lang('labels.total-amount-for') <span
                        class="stats-text stats-restaurant-name">{{ $stats['restaurant_name'] }}</span> @lang('labels.between')
                    <span
                        class="stats-text stats-dates"> {{ $stats['date_from'] }} - {{ $stats['date_to'] }}</span>.
                </div>
                <div class="stats">
                    <div class="top">
                        <div class="value">
                            <div
                                id="order_total_money">@lang('labels.currency') {{ priceFormat($stats['order_total_money']) }}</div>
                            <p>@lang('labels.total-amount')</p>
                        </div>
                    </div>
                    <div class="stats-overview">
                        <div class="info divider">
                            <div
                                id="order_cash_money">@lang('labels.currency') {{ priceFormat($stats['order_cash_money']) }}</div>
                            <p>@lang('labels.cash')</p>
                        </div>
                        <div class="info divider">
                            <div
                                id="order_card_money">@lang('labels.currency') {{ priceFormat($stats['order_card_money']) }}</div>
                            <p>@lang('labels.card')</p>
                        </div>
                        <div class="info">
                            <div
                                id="order_tip_money">@lang('labels.currency') {{ priceFormat($stats['order_tip_money']) }}</div>
                            <p>@lang('labels.tips')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="custom">
                <div class="text-stats">@lang('labels.total-orders-for')<span
                        class="stats-text stats-restaurant-name">{{ $stats['restaurant_name'] }}</span> @lang('labels.between')
                    <span
                        class="stats-text stats-dates"> {{ $stats['date_from'] }} - {{ $stats['date_to'] }}</span>.
                </div>
                <div class="stats">
                    <div class="top">
                        <div class="value">
                            <div id="order_total_count">{{ number_format($stats['order_total_count'], 0) }}</div>
                            <p>@lang('labels.total-orders')</p>
                        </div>
                    </div>
                    <div class="stats-overview">
                        <div class="info divider">
                            <div id="order_cash_count">{{ number_format($stats['order_cash_count'], 0) }}</div>
                            <p>@lang('labels.cash')</p>
                        </div>
                        <div class="info">
                            <div id="order_card_count">{{ number_format($stats['order_card_count'], 0) }}</div>
                            <p>@lang('labels.card')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
