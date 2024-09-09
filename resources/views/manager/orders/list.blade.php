@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager.js', '../') }}" defer></script>
    <script src="{{ mix('/dist/js/data-table.js', '../') }}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')
    <div class="table-header">
        <div class="row">
            <div class="col-4"><h2 class="title">  {{ trans_choice('labels.orders', 2) }} <span class="summary"></span></h2></div>
            <div class="status offset-3 col-5">
                <div class="status-all">
                    <select autocomplete="off" name="status" class="status__change">
                        <option value="">{{__('labels.all_statuses')}}</option>
                        @foreach(\App\Models\Order::CHOOSESTATUS as $status)
                            <option value="{{trans('labels.' . $status)}}">@lang('labels.' . $status)</option>
                        @endforeach
                    </select>
                    <input type="text" class="data-search" placeholder="@lang('labels.search')">
                    @include('layouts.parts.icons.data-search')
                </div>
            </div>
        </div>
    </div>
    <table class="table" id="orders-table" data-table-url="{{route('manager.order.data.table')}}">
        <thead>
        <tr>
            <th>
                @lang('labels.id')
            </th>
            <th>
                @lang('labels.internal-id')
            </th>
            <th>
                @lang('labels.table')
            </th>
            <th>
                @lang('labels.amount')
            </th>
            <th>
                @lang('labels.tips')
            </th>
            <th>
                @lang('labels.date-time')
            </th>
            <th>
                @lang('labels.status')
            </th>
            <th>
                @lang('labels.transaction-id')
            </th>
        </tr>
        </thead>
    </table>
    <div class="table-footer">

    </div>
@endsection
