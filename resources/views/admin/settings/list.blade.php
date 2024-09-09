@extends('layouts.admin')
@push('scripts')
    <script src="{{ mix('/dist/js/data-table.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{mix('/dist/js/admin.js', '..')}}" defer></script>
    <script type="text/javascript" src="{{mix('/dist/js/general.js', '..')}}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')
    @if(session()->has('success'))
        <div class="alert alert-success">
            <p>{{ session()->get('success') }}</p>
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
        <h2 class="title">{{ trans_choice('labels.setting', 2) }}</h2>
    </div>
    <form id="settingsForm" action="{{ route('admin.settings.update') }}" method="POST" class="add-item">
        @method('PATCH') 
        @csrf
        <div class="row">
            <div class="col-sm-12">
                <h2 class="subtitle">@lang('labels.accounting')</h2>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label for="vat">{{__('labels.vat')}}</label>
                    <input 
                        type="text" 
                        id="vat" 
                        name="vat" 
                        required
                        placeholder="{{__('labels.vat')}}"
                        value="{{ old('vat', $settings['vat'] ?? null) }}"
                    >
                    @if($errors->has('vat'))
                        <div class="error">{{ $errors->first('vat') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label for="takeaway_vat">{{__('labels.takeaway-vat')}}</label>
                    <input 
                        class="gray" 
                        type="text" 
                        id="takeaway_vat" 
                        name="takeaway_vat" 
                        required
                        placeholder="{{__('labels.takeaway-vat')}}"
                        value="{{ old('takeaway_vat',  $settings['takeaway_vat'] ?? null ) }}"
                    >
                    @if($errors->has('takeaway_vat'))
                        <div class="error">{{ $errors->first('takeaway_vat') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="button-container">
                    <button class="primary-button">
                        @lang('labels.save-changes')
                    </button>
                    <a href="{{route('admin.dashboard')}}" class="secondary-button">
                        @lang('labels.cancel')
                    </a>
                </div>
            </div>
        </div>
    </form>

@endsection
