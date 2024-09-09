@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager/food-types.js', '../') }}" defer></script>
    <script src="{{ mix('/dist/js/data-table.js', '../') }}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success">
            <p>{{ Session::get('success') }}</p>
            <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
        </div>
    @endif
    @if(Session::has('error'))
        <div class="alert alert-danger">
            <p>{{ Session::get('error') }}</p>
            <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
        </div>
    @endif
    <div class="table-header">
        <div class="row">
            <div class="col-4"><h2 class="title">  {{ trans_choice('labels.course', 2) }}</h2></div>
        </div>
    </div>
    <table class="table" id="food-types-table" data-table-url="{{route('manager.course.data.table')}}" data-update-url="{{ route('manager.course.data.table.reorder') }}">
        <thead>
        <tr>
            <th>
                @lang('labels.order')
            </th>
            <th>
                @lang('labels.name')
            </th>
            <th>
                @lang('labels.date-time')
            </th>
            <th>
                @lang('labels.actions')
            </th>
        </tr>
        </thead>
    </table>
    <div class="table-footer">
        <div class="button-container">
            <a href="{{ route('manager.course.create') }}" class="primary-button">
                @lang('labels.add-course')
            </a>
        </div>
    </div>
@endsection
