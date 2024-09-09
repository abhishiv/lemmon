<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <base href="{{ url('') }}"/>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="manifest" href="manifest.json"/>
<meta name="mobile-web-app-capable" content="yes">
    <title>{{ config('app.name', 'Lemmon') }}</title>

    {{-- ############### JQUERY ###############--}}
    <script src="{{ mix('dist/js/libraries/jquery.min.js', '../') }}"></script>
    <script>$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});</script>

    {{-- ############### SELECT ############### --}}
    <link href="{{ mix('dist/css/libraries/select2.min.css', '../') }}" rel="stylesheet"/>
    <script src="{{ mix('dist/js/libraries/select2.min.js', '../') }}"></script>

    {{-- ############### DataTable  ###############--}}
    <link rel="stylesheet" type="text/css" href="{{ mix('dist/css/libraries/datatables.min.css', '../') }}"/>
    <script type="text/javascript" src="{{ mix('dist/js/libraries/datatables.min.js', '../') }}"></script>

    {{--    Local styles--}}
    <link rel="stylesheet" href="{{ mix('/scss/new-staff.css', '../') }}">

    @stack('styles')

    <script src="{{mix('dist/js/libraries/epos-2.27.0.js', '../')}}" defer></script>
</head>

<body class="@yield('body_class') {{ App::environment(['production']) ? '' : 'development-environment' }}">
    <header class="header section">
        <div class="section__inner">
            <div class="header__top">
                <a class="header__logo" href="{{ route('staff.menu') }}">
                    @include('layouts.parts.icons.logo')
                </a>

                <nav class="header__nav">
                    <ul class="header__nav-list">
                        <li class="header__nav-item {{ Route::is('staff.tables') ? 'header__nav-item--active' : '' }}">
                            <a href="{{ route('staff.tables') }}" class="header__link">@lang('labels.tables')</a>
                        </li>
                        <li class="header__nav-item {{ Route::is('staff.menu') ? 'header__nav-item--active' : '' }}">
                            <a href="{{ route('staff.menu') }}" class="header__link">@lang('labels.pos')</a>
                        </li>
                        <li class="header__nav-item {{ Route::is('staff.dashboard') ? 'header__nav-item--active' : '' }}">
                            <a href="{{ route('staff.dashboard') }}" class="header__link">{{ trans_choice('labels.orders', 2) }}</a>
                        </li>
                        <li class="header__nav-item {{ Route::is('staff.products') ? 'header__nav-item--active' : '' }}">
                            <a href="{{ route('staff.products') }}" class="header__link">@lang('labels.products')</a>
                        </li>
                        <li class="header__nav-item {{ Route::is('staff.tip.list') ? 'header__nav-item--active' : '' }}">
                            <a href="{{ route('staff.tip.list') }}" class="header__link">@lang('labels.tips')</a>
                        </li>
                        <li class="header__nav-item {{ Route::is('staff.tip.list') ? 'header__nav-item--active' : '' }}">
                            <a href="{{ route('manager.dashboard') }}" class="header__link">Reports</a>
                        </li>
                    </ul>
                </nav>

                <div class="header__account">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="header__button" type="submit">
                            {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
            @yield('filters')
        </div>
    </header>
    <main
        class="main"
        data-orders-to-print-url="{{ route('staff.order.to-print') }}"
        data-printers-list="{{ route('staff.printers.list') }}"
        data-print-receipt-url="{{ $availablePrinters['receipt'] ? route('staff.order.print.receipt') : '' }}"
    >
        @yield('content')
    </main>
    <footer>
        @yield('footer')
        <div class="printer-bubble">
            <span class="printer-bubble__default-message">@lang('staff/printer.default-message')</span>
            <span class="printer-bubble__text"></span>
            <span class="printer-bubble__icon icon-printer"></span>
        </div>
    </footer>
    <script src="{{mix('dist/js/printer.js', '../')}}" defer></script>
    <script src="{{mix('dist/js/new-staff.js', '../')}}" defer></script>
    <script src="{{ mix('/dist/js/libraries/OneSignalSDK.min.js', '../') }}" async></script>
    @if(App::environment(['production']) || App::environment(['staging']))
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
                        console.log('onesignal store');
                        $.ajax({
                            type: 'POST',
                            url: "{{route('staff.onesignal.store')}}",
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
</body>
</html>
