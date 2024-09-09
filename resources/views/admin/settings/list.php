@extends('layouts.admin')
@push('scripts')
    <script src="{{ mix('/dist/js/data-table.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{mix('/dist/js/admin.js', '..')}}" defer></script>
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
            <div class="col-4"><h2 class="title"> {{__('labels.restaurants')}}<span class="summary"></span></h2></div>
            <div class="status offset-3 col-5">
                <div class="status-all">
                    <select autocomplete="off" name="status" class="status__change">
                        <option value="">{{__('labels.all_statuses')}}</option>
                        @foreach(\App\Models\Restaurant::STATUSES as $status)
                            <option value="{{trans('labels.' . $status)}}">@lang('labels.' . $status)</option>
                        @endforeach
                    </select>
                    <input type="text" class="data-search" placeholder="@lang('labels.search')">
                    @include('layouts.parts.icons.data-search')
                </div>
            </div>
        </div>
    </div>
    <table class="table" id="restaurant-table"
           data-table-url="{{route('admin.restaurant.data.table')}}">
        <thead>
        <tr>
            <th>
                @lang('labels.name')
            </th>
            <th>
                @lang('labels.onboard-at')
            </th>
            <th>
                @lang('labels.onboard-by')
            </th>
            <th>
                @lang('labels.status')
            </th>
            <th>
                @lang('labels.actions')
            </th>
        </tr>
        </thead>
    </table>
    <div class="table-footer">
        <div class="button-container">
            <a href="{{ route('admin.restaurant.create') }}" class="primary-button"
               type="submit">
                @lang('labels.add')
            </a>
        </div>
    </div>

@endsection
