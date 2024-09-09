@extends('layouts.admin')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/slug.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{mix('/dist/js/admin.js', '..')}}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')
    <div class="row">
        <h2 class="title">{{__('labels.add')}}</h2>
    </div>
    <form id="restaurantForm" action="{{ route('admin.restaurant.store') }}" method="POST" class="add-item">
        <div class="row">
            @csrf
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.name')}}</label>
                    <input type="text" id="name" name="name" required
                           placeholder="{{__('labels.name')}}"
                           value="{{ old('name') }}">
                    @if($errors->has('name'))
                        <div class="error">{{ $errors->first('name') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.email')}}</label>
                    <input class="gray" type="email" id="email" name="email" placeholder="{{__('labels.email')}}"
                           value="{{ old('email') }}" required>
                    @if($errors->has('email'))
                        <div class="error">{{ $errors->first('email') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3 hidden">
                <div class="custom">
                    <label>{{__('labels.slug')}}</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" readonly="readonly" required
                           placeholder="{{__('labels.slug')}}">
                    @if($errors->has('slug'))
                        <div class="error">{{ $errors->first('slug') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.phone-number')}}</label>
                    <input class="gray" type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                           placeholder="{{__('labels.phone-number')}}">
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
                           value="{{ old('address') }}">
                    @if($errors->has('address'))
                        <div class="error">{{ $errors->first('address') }}</div>
                    @endif
                </div>
            </div>

            <div class="col-sm-3 hidden">
                <div class="custom">
                    <label>{{__('labels.vat')}}</label>
                    <input type="text" id="vat" name="vat" placeholder="{{__('labels.vat')}}"
                           value="{{ old('vat') }}">
                    @if($errors->has('vat'))
                        <div class="error">{{ $errors->first('vat') }}</div>
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
                           value="{{ old('company_registration') }}">
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
                           value="{{ old('contact_person') }}">
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
                           value="{{ old('payrexx_token') }}" required>
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
                           value="{{ old('payrexx_name') }}" required>
                    @if($errors->has('payrexx_name'))
                        <div class="error">{{ $errors->first('payrexx_name') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label for="dropzone-logo">@lang('labels.receipt-logo')</label>
                    <div 
                        data-url="{{ route('admin.restaurant.verify.logo') }}"
                        class="dropzone drop-grid drop" id="dropzone-logo"
                    ></div>
                    <p class="image-error" id="logo-error"></p>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label>{{__('labels.receipt-message')}}</label>
                    <input class="gray" type="text" id="receipt_message" name="receipt_message"
                           placeholder="{{__('labels.receipt-message')}}"
                           value="{{ old('receipt_message') }}">
                    @if($errors->has('receipt_message'))
                        <div class="error">{{ $errors->first('receipt_message') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="button-container">
                    <button class="primary-button" type="submit" name="submit" value="invite">
                        Send Invite
                    </button>
                    <a href="{{route('admin.restaurant.list')}}" class="secondary-button">
                        @lang('labels.cancel')
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection
