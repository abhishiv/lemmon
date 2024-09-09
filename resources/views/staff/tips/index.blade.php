@extends('layouts.manager')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ mix('/dist/css/libraries/jquery.timepicker.min.css', '..') }}"/>
@endpush
@push('scripts')
    <meta name="tips-list-get-url" content="{{ route('staff.tip.get') }}">
    <script src="{{ mix('/dist/js/libraries/jquery.timepicker.min.js', '..') }}"></script>
    <script type="text/javascript" src="{{ mix('/dist/js/dashboard-header.js', '..') }}" defer></script>
    <script type="module" src="{{ mix('/dist/js/staff/tip-list.js', '..') }}" defer></script>
@endpush
@section('body_class', 'management-table staff-tips')
@section('content')

    <div class="row">
        <h2 class="title">  @lang('labels.title-tips-list') </h2>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="custom date">
                <label for="date">@lang('labels.date-from')</label>
                <input class="gray" readonly="readonly" type="text" id="date_from" name="date"
                       placeholder="dd/mm/yy hh:mm"
                       autocomplete="off"
                       value="{{ now()->startOfMonth()->startOfDay()->format('Y/m/d H:i') }}">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="custom date">
                <label>@lang('labels.date-to')</label>
                <input class="gray" readonly="readonly" type="text" id="date_to" name="date"
                       placeholder="dd/mm/yy hh:mm"
                       autocomplete="off"
                       value="{{ now()->format('Y/m/d H:i') }}">
            </div>
        </div>
        <div class="col-sm-6 action-buttons custom">
            <div class="row">
                <div class="col-sm-3 mt-5">
                    <div class="btn" id="btn-run-tips">@lang('labels.run')</div>
                </div>
                <div class="col-sm-3 mt-5">
                    <div class="btn danger-button" id="btn-reset-tips">@lang('labels.reset')</div>
                </div>
            </div>
        </div>
    </div>

    <table class="table dataTable tips-table" data-table-url="">
        <thead>
        <tr>
            <th>
                @lang('labels.table-name')
            </th>
            <th>
                @lang('labels.amount')
            </th>
        </tr>
        </thead>
        <tbody id="tips-table-body">
        @foreach($tablesData as $table)
            @if($table['tips'])
                <tr>
                    <td>{{ $table['name'] }}</td>
                    <td>@lang('labels.currency') {{ isset($table['tips']) ? priceFormat($table['tips']) : '0.00' }}</td>
                </tr>
            @endif
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td>@lang('customer.total'):</td>
            <td id="tips-table-total">@lang('labels.currency') {{ priceFormat($totalTips) }}</td>
        </tr>
        </tfoot>
    </table>

@endsection
