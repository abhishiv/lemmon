<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('/dist/css/app.css', '..') }}"/>
    <link rel="stylesheet" href="{{ mix('/scss/app.css', '..') }}">

    <!-- Scripts -->
    <script src="{{ mix('/dist/js/libraries/jquery.min.js', '..') }}"></script>

    <script>$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});</script>

    <script src="{{ mix('/dist/js/app.js', '..') }}" defer></script>

    {{-- ############### JQUERY MOBILE ###############--}}


    {{-- ############### OVERLAY SCROLLBAR ###############--}}
    <script src="{{ mix('/dist/js/libraries/OverlayScrollbars.min.js', '..') }}"></script>
    <link rel="stylesheet" href="{{ mix('/dist/css/libraries/OverlayScrollbars.min.css', '..') }}"/>

    <script src="{{ mix('/dist/js/libraries/OneSignalSDK.min.js', '..') }}" async></script>
    @if(App::environment(['production']))
        <script>
            window.OneSignal = window.OneSignal || [];
            OneSignal.push(function () {
                OneSignal.init({
                    appId: "{{env('ONESIGNAL_APP_ID')}}",
                    allowLocalhostAsSecureOrigin: true,
                    notifyButton: {
                        enable: false,
                    },
                    promptOptions: {
                        slidedown: {
                            prompts: [
                                {
                                    type: "push", // current types are "push" & "category"
                                    text: {
                                        /* limited to 90 characters */
                                        actionMessage: "We'd like to notify you about the status of your order.",
                                        /* acceptButton limited to 15 characters */
                                        acceptButton: "Allow",
                                        /* cancelButton limited to 15 characters */
                                        cancelButton: "Cancel"
                                    },
                                }
                            ]
                        }
                    }
                });
                OneSignal.on('subscriptionChange', function (isSubscribed) {
                    OneSignal.getUserId().then((deviceId) => {
                        $.ajax({
                            type: 'POST',
                            url: "{{route('customer.onesignal.store')}}",
                            data: {deviceId: deviceId},
                            success: function (data) {
                                console.log(data)
                            }
                        })
                    })
                });

                OneSignal.isPushNotificationsEnabled(function (isEnabled) {
                    if (!isEnabled) {
                        OneSignal.showSlidedownPrompt()
                    }
                })
            });
        </script>
    @endif
    @livewireStyles

    @stack('scripts')
</head>
<body class="@yield('body_class') {{ App::environment(['production']) ? '' : 'development-environment' }}">


<div class="page-container">

    @if(Route::currentRouteName() === 'customer.menu')
        @yield('menu-header')
    @endif
    @if(Route::currentRouteName() === 'customer.product.show')
        @yield('prod-header')
    @endif
    @if(Route::currentRouteName() === 'customer.cart')
        @yield('cart-header')
    @endif

</div>


@if(request()->headers->get('referer') === null && request()->routeIs('customer.menu'))
    <div class="success-payment full-content welcome-screen">
        <div class="success" {!! $restaurant->welcome_screen_image ? 'style="background-image: url(' . asset('storage/uploads/' . $restaurant->id . '/images/' . $restaurant->welcome_screen_image) . ');"' : '' !!}>
            <div class="success__inner">
                <div class="success__text">
                    <h1 class="success__text__title">
                        <span class="success__text-message">{{__('customer.welcome-to')}}</span>
                        <span class="success__text-restaurant">{{ $restaurant->name }}</span>
                    </h1>
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
@endif

<main class="@yield('class')">
    @yield('content')
</main>
@livewireScripts

</body>
</html>
