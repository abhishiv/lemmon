@extends('layouts.manager')
@push('scripts')
    <script src="{{mix('/dist/js/data-table.js', '../')}}" defer></script>
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '../') }}" defer></script>
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
            <div class="col-4"><h2 class="title"> @lang('labels.extra-items')<span class="summary"></span></h2></div>
            <div class="status offset-3 col-5">
                <div class="status-all">
                    <input type="text" class="data-search" placeholder="@lang('labels.search')">
                    @include('layouts.parts.icons.data-search')
                </div>
            </div>
        </div>
    </div>
    <table class="table {{ $extras->isEmpty() ? 'empty-table' : '' }}" id="extra-table"
           data-table-url="{{ route('manager.extra.data.table') }}">
        <thead>
        <tr>
            <th>
                @lang('labels.title')
            </th>
            <th>
                @lang('labels.description')
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
    <div class="table-footer {{ $extras->isEmpty() ? 'btn-empty-table' : '' }}">
        <div class="button-container">
            <a href="{{ route('manager.extra.create') }}" class="primary-button">
                @lang('labels.add-extra-item')
            </a>
        </div>
    </div>
@endsection
