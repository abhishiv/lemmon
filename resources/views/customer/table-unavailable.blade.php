@extends('layouts.customer')
@section('class', 'main-menu')
@section('content')
<div class="success-payment full-content unavailable">
    <div class="success">
        <div class="success__inner">
            <div class="success__text">
                <h1 class="success__text__title">{{__('customer.table-unavailable')}}</h1>
                <div class="success__powered-by">
                    <div class="success__powered-by-text">@lang('labels.powered-by')</div>
                    <div class="success__powered-by-logo">
                        @include('layouts.parts.icons.logo')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
