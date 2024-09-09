@extends('layouts.admin')
@push('scripts')
    <script src="{{mix('/dist/js/admin.js', '../')}}" defer></script>
@endpush
@section('body_class', 'page-admin')
@section('content')
    <div class="dashboard__empty">
        <p class="wrap"><span>{{__('customer.welcome')}}!</span><br>{{__('customer.dashboard-entry')}}</p>
        @include('layouts.parts.icons.lemmon-empty-screen')
        <div class="button-container absolute">
            <a href="{{ route('admin.restaurant.create') }}" class="primary-button first">{{__('labels.onboard')}}</a>
        </div>
    </div>
@endsection
