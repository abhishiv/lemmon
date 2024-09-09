@extends('layouts.manager')

@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager.js', '../') }}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')

    <div class="row">
        <h2 class="title">  @lang('labels.edit-table')</h2>
    </div>

    <div class="row">
        <form id="tableForm" action="{{ route('manager.table.update', $table->id) }}" method="POST"
              class="add-item">
            <div class="col-6">
                <div class="row">
                    @method('PUT') @csrf
                    <div class="col-sm-6">
                        <div class="custom">
                            <label>@lang('labels.name')</label>
                            <input type="text" name="name" id="name" value="{{  old('name',$table->name) }}"
                                   placeholder="@lang('labels.name')">
                            @if($errors->has('name'))
                                <div class="error">{{ $errors->first('name') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="status">
                                @lang('labels.status')
                            </label>
                            <select
                                id="status" name="status">
                                @foreach(\App\Models\RestaurantTable::STATUSES as $status)
                                    <option
                                        @selected($status === $table->status) value="{{$status}}">@lang('labels.' . $status)</option>
                                @endforeach
                            </select>
                            @if($errors->has('status'))
                                <div class="error">{{ $errors->first('status') }}</div>
                            @endif

                        </div>
                    </div><div class="col-sm-6">
                        <div class="custom">
                            <label for="table-room">
                                @lang('labels.room')
                            </label>
                            <input type="text" name="room" id="table-room" placeholder="@lang('labels.room')" value="{{ old('room', $table->room) }}">
                            @if($errors->has('room'))
                                <div class="error">{{ $errors->first('room') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="type">
                                @lang('labels.type')
                            </label>
                            <select
                                id="type" name="type">
                                @foreach(\App\Models\RestaurantTable::TYPES as $type)
                                    <option
                                        @selected($type=== $table->type) value="{{$type}}
                                        ">@lang('labels.' . $type)</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="error">{{ $errors->first('type') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="optional">
                                @lang('labels.optional')
                            </label>
                            <textarea name="optional" id="optional" placeholder=" @lang('labels.optional-info')">{{ old('optional', $table->optional) }}</textarea>
                            @if($errors->has('optional'))
                                <div class="error">{{ $errors->first('optional') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <div class="custom">
                <label> @lang('labels.qr-code')</label>
                <a class="qr" href="{{$table->menuUrl}}" target="_blank">
                    <img id="qr-code" src="{{$table->codeUrl}}?v={{ rand(1,1000) }}">
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="button-container">
                <a class="primary-button" id="save-table-submit">
                    @lang('labels.save-changes')
                </a>
                <a href="{{route('manager.table.list')}}" class="secondary-button">
                    @lang('labels.cancel')
                </a>
                <form action="{{ route('manager.table.regenerate.qr', $table->id)}}" method="POST"
                      style="display: flex;">
                    @method('PUT') @csrf
                    <button class="secondary-button" type="submit">
                        @lang('labels.generate-qr')
                    </button>
                    <a href="{{$table->codeUrl}}" download class="secondary-button"> @lang('labels.export')
                        @include('layouts.parts.icons.export')
                    </a>
{{--                    <a href="{{$table->codeUrl}}" download class="secondary-button"> Print--}}
{{--                    @include('layouts.parts.icons.print')
{{--                    </a>--}}
                </form>
            </div>
        </div>
    </div>

@endsection
