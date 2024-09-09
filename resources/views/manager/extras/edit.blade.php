@extends('layouts.manager')
@push('scripts')
    @include('manager.extras.form-validation.error-messages')
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{ mix('/dist/js/manager-extra.js', '..') }}" defer></script>
@endpush

@section('body_class', 'management-table full')

@section('content')

    <div class="row">
        <h2 class="title"> @lang('labels.edit-extra-item')</h2>
    </div>

    <form id="extraForm" action="{{ route('manager.extra.update', $extra->id) }}" method="POST" class="add-item">
        @foreach($extra->images as $image)
            <input class="hidden_images" type="hidden" name="images[]" value="{{ $image->filename }}"/>
        @endforeach
        @method('PUT') @csrf
        <div class="row">
            @csrf
            <div class="col-sm-6">
                <div class="custom">
                    <label>{{__('labels.title')}}</label>
                    <input type="text" id="title" name="title" required=""
                           value="{{  old('title',$extra->title) }}">
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
                            <option @selected($status == $extra->status) value="{{ $status }}">@lang('labels.'.  $status)</option>
                        @endforeach
                    </select>
                    <div id="error-status" class="error" style="display:none;"></div>
                    @if($errors->has('status'))
                        <div class="error">{{ $errors->first('status') }}</div>
                    @endif
                </div>
            </div>
        </div>
        {{--<div class="row">
            <div class="col-sm-6">
                <div class="custom">
                    <label>  @lang('labels.description')</label>
                    <textarea id="description" name="description" required="" name="description">
                        {{ old('description',$extra->description) }}
                    </textarea>
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
                <label for="weight">@lang('labels.list-image') </label>
                <form action="{{ route('manager.extra.verify.image')}}"
                      data-url="{{ route('manager.extra.verify.image') }}"
                      data-gallery="{{ route('manager.extra.images', $extra->id )}}"
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
                      data-gallery="{{ route('manager.extra.images', $extra->id )}}"
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
                <button class="primary-button" id="save-extra-submit">
                    @lang('labels.save-changes')
                </button>
                <a href="{{route('manager.extra.list')}}" class="secondary-button">
                    @lang('labels.cancel')
                </a>
            </div>
        </div>
    </div>

@endsection
