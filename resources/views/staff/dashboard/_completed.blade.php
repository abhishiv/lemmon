@extends('layouts.main')
@push('scripts')
    <script src="{{mix('/dist/js/staff.js', '../')}}" defer></script>
    <script src="{{mix('/dist/js/libraries/epos-2.27.0.js', '../')}}" defer></script>
    <script src="{{mix('/dist/js/printer.js', '../')}}" defer></script>
@endpush
@section('body_class', 'page-staff page-staff--closed')
@section('content')
    <div class="left-side">
        <div class="dashboard__overview">
            <x-staff.orders-overview status="closed"/>
        </div>
        <a href="{{ route('staff.tip.list') }}" class="tips-dashboard-button" target="_blank">
            Tips
        </a>
    </div>
    <div 
        id="dashboard-table"
        class="dashboard__table grid-view-screen"
        data-put-url="{{route('staff.order.update')}}"
        data-get-url="{{route('staff.order.get')}}"
        data-print-ticket-url="{{ route('staff.order.print.ticket') }}"
        data-print-receipt-url="{{ $restaurant->hasPrinterForType([$restaurant::RECEIPT]) ? route('staff.order.print.receipt') : '' }}"
    >
        @if(\App\Models\Order::all()->isEmpty())
            <div class="dashboard__empty">
                <p>{{__('labels.no-pending')}}<br> {{__('labels.order-kitchen')}}</p>
                @include('layouts.parts.icons.lemmon-empty-screen')
            </div>
        @else
            <x-staff.list-orders :orders="$orders"/>
        @endif
    </div>
@endsection
