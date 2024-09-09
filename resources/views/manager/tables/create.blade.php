@extends('layouts.manager')
@section('body_class', 'management-table')
@section('content')

    <div class="row">
        <h2 class="title">@lang('labels.add-table')</h2>
    </div>

    <form id="tableForm" action="{{ route('manager.table.store') }}" method="POST" class="add-item">
        <div class="row">
            @csrf
            <div class="col-sm-3">
                <div class="custom">
                    <label>@lang('labels.name')</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required=""
                           placeholder="@lang('labels.name')">
                    @if($errors->has('name'))
                        <div class="error">{{ $errors->first('name') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label for="status">
                        @lang('labels.status')
                    </label>
                    <select
                        id="status" name="status">
                        @foreach(\App\Models\RestaurantTable::STATUSES as $status)
                            <option value="{{$status}}">@lang('labels.' . $status)</option>
                        @endforeach
                    </select>
                    @if($errors->has('status'))
                        <div class="error">{{ $errors->first('status') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label for="table-room">
                        @lang('labels.room')
                    </label>
                    <input type="text" name="room" id="table-room" placeholder="@lang('labels.room')" value="{{ old('room') }}">
                    @if($errors->has('room'))
                        <div class="error">{{ $errors->first('room') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-3">
                <div class="custom">
                    <label for="type">
                        @lang('labels.type')
                    </label>
                    <select
                        id="type" name="type">
                        @foreach(\App\Models\RestaurantTable::TYPES as $type)
                            <option @selected($latestTable && !empty($latestTable) && $latestTable->type == $type) value="{{$type}}">@lang('labels.' . $type)</option>
                        @endforeach
                    </select>
                    @if($errors->has('type'))
                        <div class="error">{{ $errors->first('type') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <div class="custom">
                    <label for="optional">
                        @lang('labels.optional')
                    </label>
                    <textarea name="optional" id="optional" placeholder=" @lang('labels.optional-info')">{{ old('optional') }}</textarea>
                    @if($errors->has('optional'))
                        <div class="error">{{ $errors->first('optional') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="button-container">
                    <button type="submit" class="primary-button">
                        @lang('labels.save')
                    </button>
                    <a href="{{route('manager.table.list')}}" class="secondary-button">
                        @lang('labels.cancel')
                    </a>
                </div>
            </div>
        </div>
    </form>

@endsection
