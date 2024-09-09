@extends('layouts.manager')
@section('body_class', 'management-table')
@section('content')

    <div class="row">
        <h2 class="title"> @lang('labels.add-staff')</h2>
    </div>

    <div class="row">
        <form id="staffForm" action="{{ route('manager.staff.store') }}" method="POST" class="add-item">
            @csrf
            <input type="hidden" name="submit" value="invite">
            <div class="col-6">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="custom">
                            <label>@lang('labels.name')</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required=""
                                   placeholder="@lang('labels.name')">
                            @if($errors->has('name'))
                                <div class="error">{{ $errors->first('name') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6 d-none">
                        <div class="custom">
                            <label for="user_id">
                                @lang('labels.type')
                            </label>
                            <select
                                required id="staff_type" name="staff_type">
                                @foreach(\App\Models\User::STAFFTYPES as $type)
                                    <option value="{{ $type }}">@lang('labels.' . $type)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="Phone">
                                @lang('labels.phone-number')</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="+41787641099">
                            @if($errors->has('phone'))
                                <div class="error">{{ $errors->first('phone') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="email">
                                @lang('labels.email')</label>
                            <input type="text" name="email" id="email" value="{{ old('email') }}" placeholder="name@domain.com" required="" autocomplete="off">
                            @if($errors->has('email'))
                                <div class="error">{{ $errors->first('email') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="button-container">
                            <button class="primary-button" type="submit" name="submit" value="invite" id="invite">
                                @lang('labels.save')
                            </button>
                            <a href="{{route('manager.staff.list')}}" class="secondary-button">
                                @lang('labels.cancel')
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection
