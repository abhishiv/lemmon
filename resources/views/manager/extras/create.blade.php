@extends('layouts.manager')
@push('scripts')
    @include('manager.extras.form-validation.error-messages')
    <script type="text/javascript" src="{{ mix('/dist/js/slug.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{ mix('/dist/js/manager-extra.js', '..') }}" defer></script>
@endpush
@section('body_class', 'management-table full')
@section('content')
    <div class="row">
        <h2 class="title"> @lang('labels.add-extra')</h2>
    </div>
    <form id="extraForm" action="{{ route('manager.extra.store') }}" method="POST" class="add-item">
        <div class="row">
            @csrf
            <div class="col-sm-6">
                <div class="custom">
                    <label>{{__('labels.title')}}</label>
                    <input type="text" id="title" name="title"
                           value="{{ old('title') }}" required>
                    <div id="error-title" class="error" style="display:none;"></div>
                    @if($errors->has('title'))
                        <div class="error">{{ $errors->first('title') }}</div>
                    @endif
                </div>
            </div>

            <div class="col-sm-6">
                <div class="custom">
                    <label for="status">
                        @lang('labels.status')
                    </label>
                    <select id="status" name="status">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">@lang('labels.'.  $status)</option>
                        @endforeach
                    </select>
                    <div id="error-status" class="error" style="display:none;"></div>
                    @if($errors->has('status'))
                        <div class="error">{{ $errors->first('status') }}</div>
                    @endif
                </div>
            </div>
        </div>
       {{-- <div class="row">
            <div class="col-sm-6">
                <div class="custom">
                    <label>@lang('labels.description')</label>
                    <textarea name="description" id="description">{{ old('description') }}</textarea>
                    <div id="error-description" class="error" style="display:none;"></div>
                    @if($errors->has('description'))
                        <div class="error">{{ $errors->first('description') }}</div>
                    @endif
                </div>
            </div>
        </div>--}}
    </form>
    {{--<div class="row">
        <div class="col-sm-3">
            <div class="custom">
                <label for="weight"> @lang('labels.list-image') </label>
                <form action="{{ route('manager.extra.verify.image')}}"
                      data-url="{{ route('manager.extra.verify.image') }}"
                      class="dropzone drop-grid drop" id="dropzone-list">
                    @csrf
                </form>
                <p class="image-error" id="list-error"></p>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="custom">
                <label for="weight"> @lang('labels.single-image')</label>
                <form action="{{ route('manager.extra.verify.image')}}"
                      data-url="{{ route('manager.extra.verify.image') }}"
                      class="dropzone drop-grid drop" id="dropzone-single">
                    @csrf
                </form>
                <p class="image-error" id="single-error"></p>
            </div>
        </div>
    </div>--}}

    <div class="row">
        <div class="col-sm-3">
            <div class="button-container">
                {{--                @if($categories->isEmpty())--}}
                {{--                    <p> @lang('labels.extra-category-condition')</p>--}}
                {{--                    <a href="{{route('manager.extra.category.create')}}">--}}
                {{--                        @lang('labels.click-to-add-category')--}}
                {{--                    </a>--}}
                {{--                @else--}}
                <button class="primary-button" id="save-extra-submit">
                    @lang('labels.save')
                </button>
                <a href="{{route('manager.extra.list')}}" class="secondary-button">
                    @lang('labels.cancel')
                </a>
            </div>
        </div>
    </div>
@endsection
