@extends('layouts.manager')
@push('scripts')
    <script src="{{mix('/dist/js/manager.js', '..')}}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')
    <div class="row">
        <h2 class="title">{{__('labels.edit-restaurant')}}</h2>
    </div>
    <form id="restaurantForm" action="{{ route('manager.restaurant.update', $restaurant->id) }}" method="POST"
          class="add-item">
        <div class="row">
            @csrf @method('PUT')
            @if($restaurant->receipt_logo)
                <input class="old_logo" type="hidden" name="old_logo" value="{{ $restaurant->receipt_logo }}"/>
            @endif
            @if($restaurant->welcome_screen_image)
                <input class="old_welcome_screen_image" type="hidden" name="old_welcome_screen_image" value="{{ $restaurant->welcome_screen_image }}"/>
            @endif
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.name')}}</label>
                    <input type="text" id="name" name="name" required
                           placeholder="{{__('labels.name')}}"
                           value="{{  old('name',$restaurant->name) }}">
                    @if($errors->has('name'))
                        <div class="error">{{ $errors->first('name') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.email')}}</label>
                    <input class="gray" type="email" id="email" name="email" required
                           placeholder="{{__('labels.email')}}"
                           value="{{ old('email',$restaurant->email) }}">
                    @if($errors->has('email'))
                        <div class="error">{{ $errors->first('email') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.phone-number')}}</label>
                    <input class="gray" type="tel" id="phone" name="phone" value="{{ old('phone',$restaurant->phone) }}"
                           placeholder="{{__('labels.phone-number')}}"
                           required>
                    @if($errors->has('phone'))
                        <div class="error">{{ $errors->first('phone') }}</div>
                    @endif
                </div>
            </div>

            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.address')}}</label>
                    <input type="text" id="address" name="address" required
                           placeholder="{{__('labels.address')}}"
                           value="{{ old('address', $restaurant->address) }}">
                    @if($errors->has('address'))
                        <div class="error">{{ $errors->first('address') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label>Company Registration</label>
                    <input type="text" id="company-registration" name="company_registration"
                           placeholder="CHE 123.456.789"
                           value="{{ old('company_registration', $restaurant->company_registration) }}">
                    @if($errors->has('company_registration'))
                        <div class="error">{{ $errors->first('company_registration') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.contact')}} {{__('labels.name')}}</label>
                    <input type="text" id="contact_person" name="contact_person" required
                           placeholder="{{__('labels.contact')}} {{__('labels.name')}}"
                           value="{{ old('contact_person',$restaurant->contact_person) }}">
                    @if($errors->has('contact_person'))
                        <div class="error">{{ $errors->first('contact_person') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.payrexx-key')}}</label>
                    <input class="gray" type="text" id="payrexx-token" name="payrexx_token"
                           placeholder="{{__('labels.payrexx-key')}}"
                           value="{{ $restaurant->payrexx_token }}" disabled>
                    @if($errors->has('payrexx_token'))
                        <div class="error">{{ $errors->first('payrexx_token') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.payrexx-name')}}</label>
                    <input class="gray" type="text" id="payrexx-token" name="payrexx_name"
                           placeholder="{{__('labels.payrexx-name')}}"
                           value="{{ $restaurant->payrexx_name }}" disabled>
                    @if($errors->has('payrexx_name'))
                        <div class="error">{{ $errors->first('payrexx_name') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.receipt-phone')}}</label>
                    <input class="gray" type="text" id="receipt_phone" name="receipt_phone"
                           placeholder="{{__('labels.receipt-phone')}}"
                           value="{{ old('receipt_phone', $restaurant->receipt_phone) }}">
                    @if($errors->has('receipt_phone'))
                        <div class="error">{{ $errors->first('receipt_phone') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.receipt-message')}}</label>
                    <input class="gray" type="text" id="receipt_message" name="receipt_message"
                           placeholder="{{__('labels.receipt-message')}}"
                           value="{{ old('receipt_message', $restaurant->receipt_message) }}">
                    @if($errors->has('receipt_message'))
                        <div class="error">{{ $errors->first('receipt_message') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label for="dropzone-logo">@lang('labels.receipt-logo')</label>
                    <div
                        data-url="{{ route('manager.restaurant.verify.logo') }}"
                        data-logo="{{ route('manager.restaurant.logo', $restaurant->id )}}"
                        class="dropzone drop-grid drop" id="dropzone-logo"
                    ></div>
                    <p class="image-error" id="logo-error"></p>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label for="dropzone-welcome-screen">@lang('labels.app-welcome-screen')</label>
                    <div
                        data-url="{{ route('manager.restaurant.verify.welcome-screen-image') }}"
                        data-welcome-screen="{{ route('manager.restaurant.welcome-screen-image', $restaurant->id )}}"
                        class="dropzone drop-grid drop" id="dropzone-welcome-screen"
                    ></div>
                    <p class="image-error" id="welcome-screen-error"></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="button-container">
                    <button class="primary-button" type="submit" name="submit" value="save">
                        @lang('labels.save-changes')
                    </button>
                    <a href="{{route('manager.dashboard')}}" class="secondary-button" style="margin-right: 30px;">
                        @lang('labels.cancel')
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection
