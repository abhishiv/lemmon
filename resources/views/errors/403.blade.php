@extends('layouts.error')
@section('content')
    <div class="error-page">
        <div class="error-page__inner">
            <div class="error-page__text">
                <h1 class="error-page__title">403</h1>
                <p class="error-page__subtitle">@lang('labels.forbidden-text')</p>
            </div>
            <div class="error-page__actions">
                <a class="customer-button" href="{{ url('/') }}">@lang('labels.menu')</a>
            </div>
            <div class="error-page__powered-by">
                <div class="error-page__powered-by-text">@lang('labels.powered-by')</div>
                <div class="error-page__powered-by-logo">
                    @include('layouts.parts.icons.logo')
                </div>
            </div>
        </div>
    </div>
@endsection