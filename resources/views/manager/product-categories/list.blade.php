@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '../') }}" defer></script>
    <script src="{{ mix('/dist/js/data-table.js', '../') }}" defer></script>
    <script type="text/javascript" src="{{mix('/dist/js/general.js', '../')}}" defer></script>
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
            <div class="col-4"><h2 class="title"> @lang('labels.categories')<span class="summary"></span></h2></div>
            <div class="status offset-3 col-5">
                <div class="status-all">
                    <select autocomplete="off" name="status" class="status__change">
                        <option value="">{{__('labels.all_statuses')}}</option>
                        @foreach(\App\Models\ProductCategory::STATUSES as $status)
                            <option value="@lang('labels.' . $status)">@lang('labels.' . $status)</option>
                        @endforeach
                    </select>
                    <input type="text" class="data-search" placeholder="@lang('labels.search')">
                    @include('layouts.parts.icons.data-search')
                </div>
            </div>
        </div>
    </div>

    <table class="table" id="category-table" data-table-url="{{route('manager.product.category.data.table')}}" data-order-update-url="{{route('manager.product.category.update.order')}}">
        <thead>
        <tr>
            <th>
                @lang('labels.order')
            </th>
            <th>
                @lang('labels.name')
            </th>
            <th>
                @lang('labels.number-of-products')
            </th>
            <th>
                @lang('labels.status')
            </th>
            <th>
                @lang('labels.actions')
            </th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="table-footer">
        <div class="button-container">
            <a href="{{ route('manager.product.category.create') }}" class="primary-button"
               type="submit">
                @lang('labels.add-category')
            </a>
        </div>
    </div>

@endsection
