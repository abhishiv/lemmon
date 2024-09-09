@extends('layouts.guest')
@push('scripts')
    <script src="{{mix('/dist/js/data-table.js', '../')}}" defer></script>
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '../') }}" defer></script>
@endpush
@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="account__logo">
            @include('layouts.parts.icons.logo')
        </div>
        <div class="account__form">
            <div class="row"><p>@lang('labels.forget-password')<span>@lang('labels.forget-password-text')</span></p>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="custom-field">
                        <input type="text" name="email" id="email" value="" placeholder="@lang('labels.enter-email')"
                               required>
                        @if($errors->has('email'))
                            <div class="error">{{ $errors->first('email') }}</div>
                        @endif
                    </div>
                    <span class="success-message"> {{session()->get('status')}}</span>
                </div>
            </div>
            <div class="row">
                <div class="button-container forgot-password">
                    <button class="primary-button" type="submit">
                        @lang('labels.reset-password')
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

