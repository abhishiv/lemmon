@extends('layouts.manager')
@push('scripts')
    <script src="{{ mix('/dist/js/libraries/jquery.timepicker.min.js', '../') }}"></script>
    <script src="{{mix('dist/js/data-table.js', '../')}}" defer></script>
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '../') }}" defer></script>
    <script type="text/javascript" src="{{mix('dist/js/general.js', '../')}}" defer></script>
    <script src="{{mix('dist/js/manager/dashboard.js', '../')}}" type="module" defer></script>
@endpush
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ mix('/dist/css/libraries/jquery.timepicker.min.css', '../') }}"/>
@endpush
@section('body_class')
    {{ 'management-table no-content dashboard-role-' . auth()->user()->getRoleNames()[0] }}
@endsection
@section('content')

    @if(!$restaurant->productCategories()->has('products')->count())
        <div class="dashboard__empty">
            <p class="wrap"><span>{{__('customer.welcome')}}!</span><br>{{__('labels.manager-welcome-text')}}</p>
            @include('layouts.parts.icons.lemmon-empty-screen')
        </div>
    @else
        <div class="dashboard-background">
            @if($message && $message['type'] == 'error')
                <div class="alert alert-danger">
                    <p>{{$message['message'] }}</p>
                    <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </div>
            @endif
            <div class="row">
                <h2 class="title">@lang('labels.dashboard')</h2>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <div class="custom date">
                        <label for="date">@lang('labels.date-from')</label>
                        <input class="gray" readonly="readonly" {{ auth()->user()->hasRole('staff') ? 'disabled' : '' }} type="text" id="date_from" name="date"
                               autocomplete="off"
                               value="{{ now()->startOfDay()->format('Y/m/d H:i') }}">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="custom date">
                        <label>@lang('labels.date-to')</label>
                        <input class="gray" readonly="readonly" type="text" id="date_to" name="date"
                               placeholder="dd/mm/yy"
                               autocomplete="off"
                               {{ auth()->user()->hasRole('staff') ? 'disabled' : '' }}
                               value="{{ now()->format('Y/m/d H:i') }}">
                    </div>
                </div>
                <div class="col-sm-6 action-buttons custom">
                    <div class="row">
                        @if(auth()->user()->hasRole('manager'))
                            <div class="col-sm-3 mt-5">
                                <div class="btn" id="btn-run-stats">@lang('labels.run')</div>
                            </div>
                            <div class="col-sm-3 mt-5">
                                <div class="btn danger-button" id="btn-reset-stats">@lang('labels.reset')</div>
                            </div>
                        @endif
                        <div class="col-sm-3 mt-5">
                            <div class="btn" id="btn-export-stats">@lang('labels.export-csv')</div>
                        </div>
                        <div class="col-sm-3 mt-5">
                            <a target="_blank" href="#" class="btn d-none"
                               id="btn-download-stats">@lang('labels.download-csv')</a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row" id="statistics-container" data-statistics-url="{{ route('manager.statistics') }}"
                 data-export-url="{{ route('manager.statistics.export') }}"
                 data-export-get-url="{{ route('manager.statistics.get') }}">
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
                                <div class="info divider">
                                    <div
                                        id="order_terminal_money">@lang('labels.currency') {{ priceFormat($stats['order_terminal_money']) }}</div>
                                    <p>@lang('labels.terminal')</p>
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
                        <div class="text-stats">@lang('labels.total-orders-for') <span
                                class="stats-text stats-restaurant-name">{{ $stats['restaurant_name'] }}</span> @lang('labels.between')
                            <span
                                class="stats-text stats-dates"> {{ $stats['date_from'] }} - {{ $stats['date_to'] }}</span>.
                        </div>
                        <div class="stats">
                            <div class="top">
                                <div class="value">
                                    <div
                                        id="order_total_count">{{ number_format($stats['order_total_count'], 0) }}</div>
                                    <p>@lang('labels.total-orders')</p>
                                </div>
                            </div>
                            <div class="stats-overview">
                                <div class="info divider">
                                    <div
                                        id="order_cash_count">{{ number_format($stats['order_cash_count'], 0) }}</div>
                                    <p>@lang('labels.cash')</p>
                                </div>
                                <div class="info divider">
                                    <div
                                        id="order_card_count">{{ number_format($stats['order_card_count'], 0) }}</div>
                                    <p>@lang('labels.card')</p>
                                </div>
                                <div class="info">
                                    <div
                                        id="order_terminal_count">{{ number_format($stats['order_terminal_count'], 0) }}</div>
                                    <p>@lang('labels.terminal')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection


