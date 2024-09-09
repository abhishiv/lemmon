@extends('layouts.customer')
@push('scripts')
    <script src="{{mix('/dist/js/customer/order.js', '..')}}" defer></script>
@endpush
@section('body_class', 'order-overview-screen')
@section('class', 'success-payment')
@section('content')
    <div class="success" data-get-url="{{route('customer.order.get')}}">
        <div class="success__inner">
            <div class="success__text" id="card-payment"
                style="display: {{($order->status == \App\Models\Order::NEW || $order->status == \App\Models\Order::GROUP) ? 'block' : 'none'}}">
                <h1 class="success__text__title">{{__('customer.order-received')}}</h1>
                <h3 class="success__text__subtitle">{{__('customer.preparing')}}</h3>
                <p class="success__order-number">@lang('labels.order-number'): #{{$order->getDisplayId()}}</p>
            </div>
            <div class="success__text" id="order-preparing"
                style="display: {{$order->status == \App\Models\Order::PREPARING ? 'block' : 'none'}}">
                <h1 class="success__text__title">{{__('customer.preparing')}}</h1>
                <h3 class="success__text__subtitle">{{__('customer.almost_there')}}</h3>
                <p class="success__order-number">@lang('labels.order-number'): #{{$order->getDisplayId()}}</p>
            </div>
            <div class="success__text" id="order-enjoy"
                style="display: {{$order->status == \App\Models\Order::READY ? 'block' : 'none'}}">
                <h1 class="success__text__title">{{__('customer.order-delivered')}}</h1>
                <h3 class="success__text__subtitle">{{__('customer.enjoy')}}</h3>
                <p class="success__order-number">@lang('labels.order-number'): #{{$order->getDisplayId()}}</p>
            </div>
            <div class="success__actions">
                <a class="customer-button customer-button--secondary" href="{{ session('table.url') ?? '' }}">@lang('labels.menu')</a>
                <button class="customer-button review" data-url="{{ route('customer.orders.summary') }}">@lang('customer.view-order')</button>
            </div>
            <div id="order-review-modal" class="customer-modal">
                <div class="customer-modal__section customer-modal__header">
                    <button class="customer-modal__close icon-close"></button>
                </div>
                
                <div class="customer-modal__section customer-modal__body">
                    <div class="order-overview" data-id="{{$order->id}}">
                        <div class="scrollable-list"></div>
                    </div>
                </div>

                <div class="customer-modal__section customer-modal__footer">
                    <h3 class="customer-modal__heading">@lang('labels.receipt')</h3>
                    <p class="customer-modal__text">@lang('customer.enter-email-text')</p>
                    <form class="send-email customer-modal__form" method="POST" action="{{route('customer.order.receipt')}}"
                        data-url="{{route('customer.order.receipt')}}">
                        @csrf
                        <label for="email" class="customer-label">@lang('labels.email')</label>
                        <input 
                            class="customer-input email" 
                            type="email" 
                            name="email" 
                            id="email"
                            placeholder="@lang('customer.enter-email')"
                        >
                        <div id="email-error" class="customer-error"></div>
                        <div class="message hidden">@lang('customer.success-send-email')</div>
                        <div class="customer-button-wrapper">
                            <button class="customer-button customer-button--secondary" name="type" type="submit" value="email">
                                @lang('customer.send-receipt')
                            </button>
                            <button class="customer-button" name="type" type="submit" value="download">
                                @lang('customer.download-receipt')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="cancel-action">
                <div class="overlay-content">
                    <div class="content">
                        <div class="close-modal">
                            @include('layouts.parts.icons.close-modal')
                        </div>
                        <p class="modal-text">@lang('customer.cancel-number') #<span
                                id="canceled-order-number"></span> @lang('customer.cancelled-order') @lang('customer.cancelled-info')
                        </p>
                        <div class="button-container">
                            <a class="customer-button" href="{{ session('table.url') ?? '' }}">@lang('labels.menu')</a>
                        </div>
                    </div>
                </div>
            </div>
            @if(session()->has('receipt-email-sent'))
                <div class="overlay-content">
                    <div class="content">
                        <div class="close-modal">
                            @include('layouts.parts.icons.close-modal')
                        </div>
                        <p class="modal-text">@lang('customer.success-send-email')</p>
                        <div class="button-container center-btn">
                            <button class="customer-button close-modal">
                                @lang('labels.close')
                            </button>
                        </div>

                    </div>
                </div>
            @endif
            <div class="success__powered-by">
                <div class="success__powered-by-text">@lang('labels.powered-by')</div>
                <div class="success__powered-by-logo">
                    @include('layouts.parts.icons.logo')
                </div>
            </div>
        </div>
    </div>
@endsection
