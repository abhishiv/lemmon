@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager.js', '../') }}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')
    <div class="row">
        <h2 class="title">@lang('labels.edit-course')</h2>
    </div>

    <form id="serviceForm" action="{{ route('manager.course.update', $foodType->id) }}" method="POST" class="add-item">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-6">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="custom">
                            <label> @lang('labels.name')</label>
                            <input type="text" id="name" name="name"
                                   placeholder="@lang('labels.name')" required=""
                                   value="{{ old('name') ?? $foodType->name }}">
                            @if($errors->has('name'))
                                <div class="error">{{ $errors->first('name') }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="button-container">
                            <button class="primary-button" type="submit">
                                @lang('labels.save')
                            </button>
                            <a href="{{route('manager.course.index')}}" class="secondary-button">
                                @lang('labels.cancel')
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
@endsection
