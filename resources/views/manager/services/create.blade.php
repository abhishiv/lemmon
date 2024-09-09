@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager.js', '..') }}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div style="color: red">{{$error}}</div>
        @endforeach
    @endif
    <div class="row">
        <h2 class="title">  @lang('labels.add-services') </h2>
    </div>


    <form id="serviceForm" action="{{ route('manager.service.store') }}" method="POST" class="add-item">
        <div class="row">
            <div class="col-6">
                @csrf
                <div class="row">
                    <div class="col-sm-6">
                        <div class="custom">
                            <label> @lang('labels.name')</label>
                            <input type="text" id="name" name="name"
                                   placeholder="@lang('labels.name')" required=""
                                   value="{{ old('name') }}">
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
                                @foreach(\App\Models\Service::statuses as $status)
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
                    <div class="col-sm-12">
                        <div class="custom">
                            <label for="service-type">
                                @lang('labels.type')
                            </label>
                            <select
                                id="service-type" name="type[]" class="multi-select" required multiple size="2">
                                @foreach(\App\Models\Service::TYPES as $type)
                                    <option
                                        value="{{ $type }}">@lang('labels.'.  $type)</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="error">{{ $errors->first('type') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="custom">
                            <label for="description">
                                @lang('labels.description')
                            </label>
                            <textarea name="description" id="w3review"
                                      placeholder="@lang('labels.service_description')">{{old('description')}}</textarea>
                            @if($errors->has('description'))
                                <div class="error">{{ $errors->first('description') }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <livewire:choose-days />

                    <div class="col-sm-6">
                        <div class="custom-field">
                            <label for="hide-unavailable"> @lang('labels.hide-unavailable')</label>
                            <input @checked(old('hide_unavailable')) type="checkbox" id="hide-unavailable" value="true"
                                   name="hide_unavailable">
                            @if($errors->has('hide_unavailable'))
                                <div class="error">{{ $errors->first('hide_unavailable') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom-field">
                            <label for="visible-only-to-staff"> @lang('labels.visible-only-to-staff')</label>
                            <input 
                                @checked(old('visible_only_to_staff')) 
                                type="checkbox" 
                                id="visible-only-to-staff"
                                name="visible_only_to_staff"
                                class="large-checkbox"
                            >
                            @if($errors->has('visible_only_to_staff'))
                                <div class="error">{{ $errors->first('visible_only_to_staff') }}</div>
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
                            <a href="{{route('manager.service.list')}}" class="secondary-button">
                                @lang('labels.cancel')
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="custom">
                            <label> @lang('labels.all-items')</label>
                            <ul class="multiple-select multiple_select_group all-products">
                                @foreach($categories as $category)
                                    <li class="parent first" data-id="{{$category['id']}}">
                                        <div>{{ $category['name'] }}</div>
                                        <ul>
                                            @foreach($category['products'] as $product)
                                                <li class="child ui-sortable-handle"
                                                    data-category-id="{{$category['id']}}">
                                                    <div><input name="products[]" hidden
                                                                value="{{$product->id}}"> {{$product->name}}</div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>

                                @endforeach
                            </ul>
                        </div>

                    </div>

                    <div class="col-sm-2 center">
                        <div class="button-container box">
                            <button type="button" id="multiselect_rightAll" class="primary-button add-item">
                                <span>@lang('labels.add-all')</span></button>
                            <button type="button" id="multiselect_leftAll" class="primary-button delete-item">
                                <span>@lang('labels.remove-all')</span>
                            </button>
                            <button type="button" id="multiselect_rightSelected" class="primary-button add-item">
                                <span>@lang('labels.add-button')</span></button>
                            <button type="button" id="multiselect_leftSelected" class="primary-button delete-item">
                                <span>@lang('labels.remove-button')</span>
                            </button>

                        </div>
                    </div>

                    <div class="col-sm-5">
                        <div class="custom">
                            <label>@lang('labels.service-items')</label>
                            <ul class="multiple-select selected-products multiple_select_group">
                                @foreach($categories as $category)
                                    <li class="parent second" data-id="{{$category['id']}}">
                                        <div class="categ-{{$category['id']}}" style="display:none;">{{ $category['name'] }}</div>
                                        <ul>
                                        </ul>
                                    </li>

                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
