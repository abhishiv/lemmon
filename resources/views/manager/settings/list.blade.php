@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '..') }}" defer></script>
    <script src="{{mix('/dist/js/manager/settings.js', '..')}}" defer></script>
@endpush

@section('body_class', 'management-table full')

@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success">
            <p>{{ Session::get('success') }}</p>
            <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-danger">
            <p>{{ session()->get('error') }}</p>
            <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
        </div>
    @endif
    <div class="row">
        <h2 class="title"> @lang('labels.edit-restaurant-settings')</h2>
    </div>
    <form id="settingsForm" action="{{ route('manager.settings.update') }}" method="POST" class="add-item">
        @method('PATCH') @csrf
        <div class="row">
            <div class="col-sm-12">
                <h2 class="subtitle">@lang('manager/settings.restaurant-working-hours')</h2>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label> @lang('labels.start')</label>
                    <input class="gray" type="time" id="start" name="start_time" required=""
                           value="{{old('start_time') ?? $settings['start_time'] ?? null}}">
                    @if($errors->has('start_time'))
                        <div class="error">{{ $errors->first('start_time') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label> @lang('labels.end')</label>
                    <input class="gray" type="time" id="end" name="end_time" required=""
                           value="{{old('end_time') ?? $settings['end_time'] ?? null}}">
                    @if($errors->has('end_time'))
                        <div class="error">{{ $errors->first('end_time') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h2 class="subtitle">@lang('manager/settings.tip-settings')</h2>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label> @lang('labels.tip-recommended-amount-1')</label>
                    <input class="gray" type="number" step="0.01" id="tip_recommended_amount_1"
                           name="tip_recommended_amount_1"
                           value="{{old('tip_recommended_amount_1') ?? $settings['tip_recommended_amount_1'] ?? null}}">
                    @if($errors->has('tip_recommended_amount_1'))
                        <div class="error">{{ $errors->first('tip_recommended_amount_1') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label> @lang('labels.tip-recommended-amount-2')</label>
                    <input class="gray" type="number" step="0.01" id="tip_recommended_amount_2"
                           name="tip_recommended_amount_2"
                           value="{{old('tip_recommended_amount_2') ?? $settings['tip_recommended_amount_2'] ?? null}}">
                    @if($errors->has('tip_recommended_amount_2'))
                        <div class="error">{{ $errors->first('tip_recommended_amount_2') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label> @lang('labels.tip-recommended-amount-3')</label>
                    <input class="gray" type="number" step="0.01" id="tip_recommended_amount_3"
                           name="tip_recommended_amount_3"
                           value="{{old('tip_recommended_amount_3') ?? $settings['tip_recommended_amount_3'] ?? null}}">
                    @if($errors->has('tip_recommended_amount_3'))
                        <div class="error">{{ $errors->first('tip_recommended_amount_3') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h2 class="subtitle">@lang('manager/settings.group-order-settings')</h2>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label> @lang('labels.group-order-delay')</label>
                    <input class="gray" type="number" step="1" id="group-order-delay" name="group_order_delay"
                           value="{{old('group_order_delay') ?? $settings['group_order_delay'] ?? null}}">
                    @if($errors->has('group_order_delay'))
                        <div class="error">{{ $errors->first('group_order_delay') }}</div>
                    @endif
                </div>
            </div>
            {{-- <div class="col-sm-3">
                <div class="custom-field">
                    <label for="hide-unavailable"> @lang('labels.order-grouping-popup')</label>
                    <input type="checkbox" id="order-grouping-popup"
                           @checked(($settings['order_grouping_popup'] ?? false))
                           name="order_grouping_popup" value="true">
                    @if($errors->has('order_grouping_popup'))
                        <div class="error">{{ $errors->first('order_grouping_popup') }}</div>
                    @endif
                </div>
            </div> --}}
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h2 class="subtitle">@lang('manager/settings.takeaway-settings')</h2>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom-field">
                    <label for="take_away"> @lang('labels.takeawaylabel')</label>
                    <input type="checkbox" id="take_away"
                           @checked(($settings['take_away'] ?? false))
                           name="take_away" value="yes">
                    @if($errors->has('take_away'))
                        <div class="error">{{ $errors->first('take_away') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label for="takeaway-value">@lang('labels.takeaway-value-percentage-label')</label>
                    <input class="gray" type="number" step="1" id="takeaway-value" name="discount_takeaway"
                           value="{{old('discount_takeaway') ?? $settings['discount_takeaway'] ?? null}}">
                    @if($errors->has('discount_takeaway'))
                        <div class="error">{{ $errors->first('discount_takeaway') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom-field">
                    <label for="take_away_auto_close"> @lang('labels.take_away_auto_close')</label>
                    <input type="checkbox" id="take_away_auto_close"
                           @checked(($settings['take_away_auto_close'] ?? false))
                           name="take_away_auto_close" value="yes">
                    @if($errors->has('take_away_auto_close'))
                        <div class="error">{{ $errors->first('take_away_auto_close') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label for="take_away_auto_close_interval">@lang('labels.take_away_auto_close_interval')</label>
                    <input class="gray" type="number" min="5" step="1" id="take_away_auto_close_interval"
                           name="take_away_auto_close_interval"
                           value="{{old('take_away_auto_close_interval') ?? $settings['take_away_auto_close_interval'] ?? 5}}">
                    @if($errors->has('take_away_auto_close_interval'))
                        <div class="error">{{ $errors->first('take_away_auto_close_interval') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <h2 class="subtitle">@lang('manager/settings.delivery-settings')</h2>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom-field">
                    <label for="delivery"> @lang('manager/settings.activate-delivery')</label>
                    <input
                        type="checkbox"
                        id="delivery"
                        @checked(($settings['delivery'] ?? false))
                        name="delivery"
                        value="yes"
                    >
                    @if($errors->has('delivery'))
                        <div class="error">{{ $errors->first('delivery') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <h2 class="subtitle">@lang('manager/settings.delivery-cities')</h2>
                <hr>
            </div>
        </div>
        <div class="admin-delivery-cities">
            @php
                if (session('delivery_cities')) {
                    $settings['delivery_cities'] = session('delivery_cities');
                }
            @endphp
            @if (!empty($settings['delivery_cities']) && $settings['delivery_cities'] !== 'false')
                @foreach ($settings['delivery_cities'] as $key => $city)
                    <div class="admin-delivery-cities__item" data-city="{{ $key }}">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="custom">
                                    <label for="city-{{ $key }}">@lang('manager/settings.city')</label>
                                    <input
                                        type="text"
                                        id="city-{{ $key }}"
                                        name="delivery_cities[]"
                                        placeholder="@lang('manager/settings.city')"
                                        value="{{  old('delivery_cities.' . $key, $city->name) }}"
                                        required
                                    >
                                    @if($errors->has('delivery_cities.' . $key))
                                        <div class="error">{{ $errors->first('delivery_cities.' . $key) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="custom">
                                    <label for="delivery-fee-{{$key}}">{{__('labels.delivery-fee')}}</label>
                                    <input
                                        type="text"
                                        id="delivery-fee-{{$key}}"
                                        name="delivery_fees[]"
                                        placeholder="{{__('labels.delivery-fee')}}"
                                        value="{{  old('delivery_fees.' . $key, $city->fee) }}"
                                        required
                                    >
                                    @if($errors->has('delivery_fees.' . $key))
                                        <div class="error">{{ $errors->first('delivery_fees.' . $key) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="custom">
                                    <label></label>
                                    <div class="admin-delivery-cities__actions">
                                        <button class="admin-delivery-cities__delete btn" type="button">
                                            <svg style="display: inline;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="row">
                <div class="col-sm-6">
                    <div class="button-container">
                        <button id="add-city" class="primary-button" type="button">
                            @lang('manager/settings.add-city')
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <h2 class="subtitle">@lang('manager/settings.printers')</h2>
                <hr>
            </div>
        </div>
        <div class="printers">
            @php
                if (session('printers')) {
                    $settings['printers'] = session('printers');
                }
            @endphp
            @if (!empty($settings['printers']) && $settings['printers'] !== 'false')
                @foreach ($settings['printers'] as $key => $printer)
                    <div class="printers__item" data-printer="{{ $key }}">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="custom">
                                    <label for="ip-{{$key}}">{{__('labels.serial_number')}}</label>
                                    <input
                                        type="text"
                                        id="ip-{{$key}}"
                                        name="ip[]"
                                        placeholder="{{__('labels.serial_number')}}"
                                        value="{{  old('ip.' . $key, $printer->ip) }}"
                                        required
                                    >

                                    @if($errors->has('ip.' . $key))
                                        <div class="error">{{ $errors->first('ip.' . $key) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="custom">
                                    <label for="port-{{$key}}">{{__('labels.port')}}</label>
                                    <input
                                        type="text"
                                        id="port-{{$key}}"
                                        name="port[]"
                                        placeholder="{{__('labels.port')}}"
                                        value="{{  old('port.' . $key, $printer->port) }}"
                                        required
                                    >
                                    @if($errors->has('port.' . $key))
                                        <div class="error">{{ $errors->first('port.' . $key) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="custom">
                                    <label for="device-id-{{$key}}">{{__('labels.device-id')}}</label>
                                    <input
                                        type="text"
                                        id="device-id-{{$key}}"
                                        name="device-id[]"
                                        placeholder="{{__('labels.device-id')}}"
                                        value="{{  old('device-id.' . $key, $printer->device_id) }}"
                                        required
                                    >
                                    @if($errors->has('device-id.' . $key))
                                        <div class="error">{{ $errors->first('device-id.' . $key) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="custom">
                                    <label for="print-type-select-{{$key}}">{{__('labels.print')}}</label>
                                    <select class="printers__type-select multi-select" id="print-type-select-{{$key}}"
                                            required multiple size="3">
                                        @php
                                            $printTypeArray = explode(',', old('print-type.'  . $key, $printer->print_type));
                                        @endphp
                                        @foreach(\App\Models\Restaurant::PRINT_TYPE as $print_type)
                                            <option
                                                value="{{$print_type}}" @selected(in_array($print_type, $printTypeArray))>@lang('labels.' . $print_type)</option>
                                        @endforeach
                                    </select>
                                    <input
                                        type="hidden"
                                        class="printers__type"
                                        id="print-type-{{$key}}"
                                        name="print-type[]"
                                        value="{{  old('print-type.' . $key, $printer->print_type) }}"
                                    >
                                    @if($errors->has('print-type.' . $key))
                                        <div class="error">{{ $errors->first('print-type.' . $key) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="custom">
                                    <label></label>
                                    <div class="printers__actions">
                                        <button class="printers__delete btn" type="button">
                                            <svg style="display: inline;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="row">
                <div class="col-sm-6">
                    <div class="button-container">
                        <button id="add-printer" class="primary-button" type="button">
                            @lang('manager/settings.add-printer')
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <div class="button-container">
                    <button class="primary-button" id="save-settings-submit">
                        @lang('labels.save-changes')
                    </button>
                    <a href="{{route('manager.dashboard')}}" class="secondary-button">
                        @lang('labels.cancel')
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="empty-printer-row hidden">
        <div class="printers__item">
            <div class="row">
                <div class="col-sm-3">
                    <div class="custom">
                        <label for="ip-">{{__('labels.serial_number')}}</label>
                        <input
                            type="text"
                            id="ip-"
                            name="ip[]"
                            placeholder="{{__('labels.serial_number')}}"
                            value=""
                            required
                        >
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="custom">
                        <label for="port-">{{__('labels.port')}}</label>
                        <input
                            type="text"
                            id="port-"
                            name="port[]"
                            placeholder="{{__('labels.port')}}"
                            value=""
                            required
                        >
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="custom">
                        <label for="device-id-">{{__('labels.device-id')}}</label>
                        <input
                            type="text"
                            id="device-id-"
                            name="device-id[]"
                            placeholder="{{__('labels.device-id')}}"
                            value=""
                            required
                        >
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="custom">
                        <label for="print-type-select-">{{__('labels.print')}}</label>
                        <select class="printers__type-select multi-select" id="print-type-select-" required multiple
                                size="3">
                            @foreach(\App\Models\Restaurant::PRINT_TYPE as $print_type)
                                <option value="{{$print_type}}">@lang('labels.' . $print_type)</option>
                            @endforeach
                        </select>
                        <input
                            type="hidden"
                            class="printers__type"
                            id="print-type-"
                            name="print-type[]"
                            value=""
                        >
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="custom">
                        <label></label>
                        <div class="printers__actions">
                            <button class="printers__delete btn" type="button">
                                <svg style="display: inline;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="empty-cities-row hidden">
        <div class="admin-delivery-cities__item">
            <div class="row">
                <div class="col-sm-3">
                    <div class="custom">
                        <label for="city-">{{__('manager/settings.city')}}</label>
                        <input
                            type="text"
                            id="city-"
                            name="delivery_cities[]"
                            placeholder="{{__('manager/settings.city')}}"
                            value=""
                            required
                        >
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="custom">
                        <label for="delivery-fee-">{{__('labels.delivery-fee')}}</label>
                        <input
                            type="text"
                            id="delivery-fee-"
                            name="delivery_fees[]"
                            placeholder="{{__('labels.delivery-fee')}}"
                            value=""
                            required
                        >
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="custom">
                        <label></label>
                        <div class="admin-delivery-cities__actions">
                            <button class="admin-delivery-cities__delete btn" type="button">
                                <svg style="display: inline;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
