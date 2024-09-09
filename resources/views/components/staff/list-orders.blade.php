@if(!empty($orders))
    @foreach ($orders as $order)
        <x-staff.order-card :order="$order" :food-types="$foodTypes" :available-printers="$availablePrinters" />
    @endforeach
@else
    <div class="dashboard__empty">
        <p>{{__('labels.no-pending')}}<br> {{__('labels.order-kitchen')}}</p>
        @include('layouts.parts.icons.lemmon-empty-screen')
    </div>
@endif