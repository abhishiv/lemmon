@extends('layouts.manager')
@push('scripts')
    <script src="{{mix('/dist/js/data-table.js', '..')}}" defer></script>
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{mix('/dist/js/general.js', '..')}}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')
    @if(session()->has('success'))
        <div class="alert alert-success">
            <p>{{ session()->get('success') }}</p>
            <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-danger">
            <p>{{ session()->get('error') }}</p>
            <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
        </div>
    @endif
    <div class="table-header">
        <div class="row">
            <div class="col-4"><h2 class="title"> @lang('labels.menu-items')<span class="summary"></span></h2></div>
            <div class="status offset-3 col-5">
                <div class="status-all">
                    <select autocomplete="off" name="status" class="status__change" id="list_product_status">
                        <option value="">{{__('labels.all_statuses')}}</option>
                        @foreach(\App\Models\Product::STATUSES as $status)
                            <option value="@lang('labels.' . $status)">@lang('labels.' . $status)</option>
                        @endforeach
                    </select>
                    <input type="text" class="data-search" placeholder="@lang('labels.search')">
                    @include('layouts.parts.icons.data-search')
                </div>
            </div>
        </div>
    </div>
    <table class="table {{ $products->isEmpty() ? 'empty-table' : '' }}" id="product-table"
           data-table-url="{{ route('manager.product.data.table') }}">
        <thead>
        <tr>
            <th>
                @lang('labels.service')
            </th>
            <th>
                @lang('labels.category')
            </th>
            <th>
                {{ trans_choice('labels.course', 1) }}
            </th>
            <th>
                @lang('labels.name')
            </th>
            <th class="first-th">
                @lang('labels.price')
            </th>
            <th>
                @lang('labels.promo-price')
            </th>
           {{-- <th>
                @lang('labels.description')
            </th>--}}
            <th>
                @lang('labels.status')
            </th>
            <th>
                @lang('labels.actions')
            </th>
        </tr>
        </thead>
    </table>
    <div class="table-footer {{ $products->isEmpty() ? 'btn-empty-table' : '' }}">
        <div class="button-container">
            <a href="{{ route('manager.product.create') }}" class="primary-button">
                @lang('labels.add-menu-item')
            </a>
            <a href="{{ route('manager.product.create.bundle') }}" class="primary-button">
                @lang('labels.add-bundle-menu-item')
            </a>
        </div>
    </div>
@endsection
