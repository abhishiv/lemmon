@extends('layouts.guest')
@push('scripts')
    <script src="{{mix('/dist/js/data-table.js', '..')}}" defer></script>
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '..') }}" defer></script>
@endpush
@section('content')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="account__logo">
            @include('layouts.parts.icons.logo')
        </div>
        <div class="account__form">
            <div class="row"><p>Activate Account<span>Please enter your new password</span></p></div>
            <div class="row">
                <div class="col-12">
                    <div class="custom-field">
                        <input id="email" type="hidden" name="email"
                               value="{{old('email', $request->email)}}" required autofocus/>
                        <input type="password" id="password" name="password" placeholder="Enter Password" value=""
                               minlength="8" required>
                        @if($errors->has('password'))
                            <div class="error">{{ $errors->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="custom-field">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               placeholder="Confirm Password" value=""
                               minlength="8" required>
                        @if($errors->has('password_confirmation'))
                            <div class="error">{{ $errors->first('password_confirmation') }}</div>
                        @endif
                    </div>
                </div>
                <span class="success-message"> {{session()->get('status')}}</span>
            </div>
            <div class="row">
                <div class="button-container forgot-password">
                    <button class="primary-button" type="submit">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
