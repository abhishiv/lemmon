@extends('layouts.customer')
@push('scripts')
    {{--    <script src="{{mix('/dist/js/customer/order.js', '../')}}" defer></script>--}}
@endpush
@section('class', 'failed-payment')
@section('content')
    <div class="cart-list">
        <div class="empty-cart">
            <span class="empty-cart__text">@lang('customer.payment_issue-fr')</span>
            <div class="empty-cart__actions cancel-payment">
                <a href="{{ route('customer.order.newpayment', ['order' => $order]) }}"
                    class="customer-button payment"> @lang('customer.payment_tryagain')</a>
                @if($order->service_method == App\Models\Order::DINEIN)
                    <a href="{{ route('customer.order.paycash', ['order' => $order]) }}"
                       class="customer-button payment"> @lang('customer.payment_paycash')</a>
                @endif
                <a href="{{ route('customer.cart') }}"
                    class="customer-button payment">@lang('customer.return')</a>
            </div>
        </div>
    </div>
@endsection
