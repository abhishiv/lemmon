@extends('layouts.guest')
@push('scripts')
    <script src="{{mix('/dist/js/data-table.js', '..')}}" defer></script>
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '..') }}" defer></script>
@endpush
@section('content')
    @if(Session::has('error'))
        <div class="alert alert-danger" style="display:block;">
            <p>{{ Session::get('error') }}</p>
{{--            <a class="close" data-dismiss="alert" aria-label="close">&times;</a>--}}
        </div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="account__logo">
            @include('layouts.parts.icons.logo')
        </div>
        <div class="account__form">
            <div class="row"><p> @lang('labels.login')<span> @lang('labels.login-text')</span></p></div>
            <div class="row">
                <div class="col-12">
                    <div class="custom-field">
                        <input type="text" name="email" id="email" value="{{old('email')}}"
                               placeholder="@lang('labels.enter-email')" required>
                        @if($errors->has('email'))
                            <div class="error">{{ $errors->first('email') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="custom-field">
                        <input type="password" id="password" name="password" placeholder="Enter Password"
                               minlength="8" required>
                        @if($errors->has('password'))
                            <div class="error">{{ $errors->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="button-container">
                        @if (Route::has('password.request'))
                            <a class="secondary-button" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="button-container">
                    <button class="primary-button" type="submit">
                        {{ __('Log in') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

