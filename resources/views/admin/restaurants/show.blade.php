@extends('layouts.admin')
@push('scripts')
    <script type="text/javascript" src="{{mix('/dist/js/admin.js', '..')}}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')

    <div class="row">
        <h2 class="title">View Restaurant</h2>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="custom">
                <label>{{__('labels.name')}}</label>
                <input type="text" id="name" name="name" required readonly
                       value="{{  old('name',$restaurant->name) }}">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="custom">
                <label>{{__('labels.slug')}}</label>
                <input type="text" name="slug" id="slug" readonly value="{{ old('slug',$restaurant->slug) }}"
                       required
                >
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <div class="custom">
                <label>{{__('labels.phone-number')}}</label>
                <input class="gray" type="tel" id="phone" name="phone" readonly
                       value="{{ old('phone',$restaurant->phone) }}"
                       required>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="custom">
                <label>{{__('labels.vat')}}</label>
                <input type="text" id="vat" name="vat" required readonly
                       value="{{ old('vat',$restaurant->vat) }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <div class="custom">
                <label>{{__('labels.email')}}</label>
                <input class="gray" type="email" id="email" name="email"
                       readonly value="{{ old('email',$restaurant->email) }}">
            </div>
        </div>

        <div class="col-sm-3">
            <div class="custom">
                <label>{{__('labels.contact')}} {{__('labels.name')}}</label>
                <input type="text" id="contact_person" name="contact_person" required
                       readonly
                       value="{{ old('contact_person',$restaurant->contact_person) }}">
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-sm-3 flex">
            <form id="status-form" action="{{ route('admin.restaurant.update.status', $restaurant->id) }}" method="POST"
                  class="add-item">
                @csrf @method('PUT')
                <div class="button-container">
                    <div class="status">
                        <div class="status-{{ $restaurant->status }}">
                            <select autocomplete="off" name="status" class="status__change">
                                @foreach(\App\Models\Restaurant::CHANGESTATUS as $status)
                                    <option
                                        @selected($restaurant->status == $status) value="{{$status}}">@lang('labels.'. $status)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <a class="primary-button" type="submit" name="submit" value="invite">
                        Send Invite
                    </a>
                </div>
            </form>
            <div class="button-container">
                <form method='POST' action="{{route('admin.restaurant.destroy', $restaurant->id)}}">
                    @csrf @method('DELETE')
                    <button type='submit' class="remove-button">
                        <p>Delete Restaurant</p>
                        <svg width="15" height="18" viewBox="0 0 15 18" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M4.6884 15.5079C4.89613 15.5079 5.06636 15.2514 5.06636 14.9384V7.52702C5.06636 7.21128 4.89902 6.95756 4.6884 6.95756C4.47778 6.95756 4.31333 7.21128 4.31333 7.52702V14.9356C4.31333 15.2514 4.48067 15.5051 4.6884 15.5051M10.2164 15.5051C10.4241 15.5051 10.5943 15.2486 10.5943 14.9356V7.52702C10.5943 7.21128 10.427 6.95756 10.2164 6.95756C10.0058 6.95756 9.83843 7.21128 9.83843 7.52702V14.9356C9.83843 15.2514 10.0058 15.5051 10.2164 15.5051ZM7.50433 15.5051C7.71206 15.5051 7.8794 15.2486 7.8794 14.9356V7.52702C7.8794 7.21128 7.71206 6.95756 7.50433 6.95756C7.2966 6.95756 7.12637 7.21128 7.12637 7.52702V14.9356C7.12637 15.2514 7.29371 15.5051 7.50433 15.5051ZM15 3.62255H0V2.41034C0 2.29757 0.0952106 2.20454 0.210617 2.20454H14.7894C14.9048 2.20454 15 2.29757 15 2.41034V3.62255ZM12.135 18H2.86497L1.52048 4.36116H13.4766L12.135 18.0028V18ZM1.74264 0.205795C1.74264 0.0930305 1.83785 0 1.95326 0H13.0467C13.1621 0 13.2574 0.0930305 13.2574 0.205795V1.46875H1.74264V0.205795Z"
                                fill="#006EF5"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection
