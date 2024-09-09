@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager.js', '../') }}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')

    <div class="row">
        <h2 class="title">@lang('labels.edit-staff')</h2>
    </div>
    <div class="row">
        <form id="staffForm" action="{{ route('manager.staff.update', $user->id) }}" method="POST" class="add-item">
            @csrf @method('PUT')
            <button name="submit" class="submit" value="" type="submit" style="display: none;"></button>
            <div class="col-6">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="custom">
                            <label>@lang('labels.name')</label>
                            <input type="text" name="name" id="name" value="{{  old('name',$user->name) }}" required=""
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
                                    <option
                                        @selected($user->staff_type == $type) value="{{ $type }}">@lang('labels.' . $type)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="Phone">
                                @lang('labels.phone-number')</label>
                            <input type="text" name="phone" id="phone" value="{{  old('phone',$user->phone) }}"
                                   placeholder="+41787641099">
                            @if($errors->has('phone'))
                                <div class="error">{{ $errors->first('phone') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="email">
                                @lang('labels.email')</label>
                            <input type="text" name="email" id="email" value="{{  old('email',$user->email) }}"
                                   placeholder="name@domain.com" required="">
                            @if($errors->has('email'))
                                <div class="error">{{ $errors->first('email') }}</div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-sm-6 flex">
            <div class="button-container">
                <a class="primary-button" id="save-staff-submit">
                    @lang('labels.save-changes')
                </a>
                @if($user->status == \App\Models\User::PENDING)
                    <a class="primary-button" id="resend-invite">
                        @lang('labels.resend-invite')
                    </a>
                @else
            </div>
            <form id="staffStatus" action="{{ route('manager.staff.change.status', $user->id) }}" method="POST"
                  class="flex">
                @method('PUT') @csrf
                <div class="button-container">
                    <a href="{{route('manager.staff.list')}}" class="secondary-button">
                        @lang('labels.cancel')
                    </a>
                    @if($user->status != App\Models\User::OUTOFOFFICE)
                        <button name="status" value="{{App\Models\User::OUTOFOFFICE}}"
                                type="submit" class="secondary-button"> @lang('labels.out-of-office')
                        </button>
                    @else
                        <button name="status" value="{{App\Models\User::ACTIVE}}" type="submit"
                                class="secondary-button">
                            @lang('labels.mark-active')
                        </button>
                    @endif
                </div>
            </form>
            @endif
        </div>
    </div>

@endsection
