@extends('layouts.manager')
@push('scripts')
    <script src="{{ mix('/dist/js/data-table.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{ mix('/dist/js/manager.js', '..') }}" defer></script>
    <script type="text/javascript" src="{{mix('/dist/js/general.js', '..')}}" defer></script>
@endpush
@section('body_class', 'management-table full')
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
    <div class="table-header">
        <div class="row">
            <div class="col-4"><h2 class="title"> Table Management<span class="summary"></span></h2></div>
            <div class="status offset-3 col-5">
                <div class="status-all">
                    <select autocomplete="off" name="status" class="status__change" id="list_tables_status">
                        <option value="">{{__('labels.all_statuses')}}</option>
                        @foreach(\App\Models\RestaurantTable::STATUSES as $status)
                            <option value="{{trans('labels.' . $status)}}">@lang('labels.' . $status)</option>
                        @endforeach
                    </select>
                    <input type="text" class="data-search" placeholder="Search">
                    @include('layouts.parts.icons.data-search')
                </div>
            </div>
        </div>
    </div>
    <table class="table" id="restaurant-tables-table" data-table-url="{{route('manager.table.data.table')}}">
        <thead>
        <tr>
            <th>
                @lang('labels.name')
            </th>
            <th>
                @lang('labels.room')
            </th>
            <th>
                @lang('labels.type')
            </th>
            <th>
                @lang('labels.status')
            </th>
            <th>
                @lang('labels.actions')
            </th>
        </tr>
        </thead>
    </table>

    <div class="table-footer">
        <div class="button-container">
            <a href="{{ route('manager.table.create') }}" class="primary-button"
               type="submit">
                @lang('labels.add-table')
            </a>
            @if(\App\Models\RestaurantTable::all()->isNotEmpty())
                <a download href="{{route('manager.table.export.zip')}}"
                   data-href="{{route('manager.table.export.zip')}}"
                   id="export-zip" class="secondary-button"
                   type="submit">
                    @lang('labels.export-all')
                    <img src="{{url('dist/img/export-icon.svg')}}" alt=""/>
                </a>
            @endif
            {{--                                <a href="" download class="secondary-button"> Print--}}
            {{--                                    <svg style="margin-left:10px;" width="21" height="20" viewBox="0 0 21 20" fill="none"--}}
            {{--                                         xmlns="http://www.w3.org/2000/svg">--}}
            {{--                                        <path--}}
            {{--                                            d="M14.8749 1H6.12515C5.50375 1 5 1.51105 5 2.14146V3.85854C5 4.48895 5.50375 5 6.12515 5H14.8749C15.4963 5 16 4.48895 16 3.85854V2.14146C16 1.51105 15.4963 1 14.8749 1Z"--}}
            {{--                                            fill="white" stroke="#006EF5" stroke-width="1.5" stroke-miterlimit="10"/>--}}
            {{--                                        <path--}}
            {{--                                            d="M18.817 5H2.183C1.52965 5 1 5.46723 1 6.04358V13.9564C1 14.5328 1.52965 15 2.183 15H18.817C19.4704 15 20 14.5328 20 13.9564V6.04358C20 5.46723 19.4704 5 18.817 5Z"--}}
            {{--                                            fill="white" stroke="#006EF5" stroke-width="1.5" stroke-miterlimit="10"/>--}}
            {{--                                        <path--}}
            {{--                                            d="M14.8749 13H6.12515C5.50375 13 5 13.4861 5 14.0858V17.9142C5 18.5139 5.50375 19 6.12515 19H14.8749C15.4963 19 16 18.5139 16 17.9142V14.0858C16 13.4861 15.4963 13 14.8749 13Z"--}}
            {{--                                            fill="white" stroke="#006EF5" stroke-width="1.5" stroke-miterlimit="10"/>--}}
            {{--                                    </svg>--}}
            {{--                                </a>--}}
        </div>
    </div>
@endsection
