@extends('layouts.main')
@push('scripts')
    <script src="{{mix('/dist/js/staff.js', '..')}}" defer></script>
    <script src="{{mix('/dist/js/libraries/epos-2.27.0.js', '..')}}" defer></script>
    <script src="{{mix('/dist/js/printer.js', '..')}}" defer></script>
@endpush

@section('body_class', 'page-staff page-staff--active')
@section('content')
    <div class="left-side">
        <div class="dashboard__overview">
            <x-staff.orders-overview status="active"/>
        </div>
        <a href="{{ route('staff.tip.list') }}" class="tips-dashboard-button" target="_blank">
            Tips
        </a>
    </div>
    <div 
        id="dashboard-table"
        class="dashboard__table list-view-screen"
        data-put-url="{{route('staff.order.update')}}"
        data-get-url="{{route('staff.order.get')}}"
        data-print-ticket-url="{{ route('staff.order.print.ticket') }}"
        data-print-receipt-url="{{ $restaurant->hasPrinterForType([$restaurant::RECEIPT]) ? route('staff.order.print.receipt') : '' }}"
    >
        <x-staff.list-orders :grouped="$grouped"/>
    </div>

    <div class="modal-actions">
        <div class="overlay" id="overlay-modal">
            <div class="overlay-box">
                <div class="close-modal">
                    @include('layouts.parts.icons.close-modal')
                </div>
                <p>@lang('labels.cancel-order')</p>
                <div class="button-container">
                    <a class="secondary-button" id="close-popup-btn">@lang('labels.no')</a>
                    <button id="cancel-order-btn" type='submit' name='send'
                            class="primary-button">@lang('labels.yes')
                    </button>
                </div>
                <div class="action">@lang('labels.undone-action')</div>
            </div>
        </div>
    </div>
    <script src="{{ mix('/dist/js/libraries/OneSignalSDK.min.js', '..') }}" async></script>
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
@endsection
