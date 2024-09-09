@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager.js', '..') }}" defer></script>
@endpush
@section('body_class', 'management-table ')
@section('content')
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div style="color: red">{{$error}}</div>
        @endforeach
    @endif

    <div class="row">
        <h2 class="title"> @lang('labels.edit-service') </h2>
    </div>
    <form id="serviceForm" action="{{ route('manager.service.update', $service->id) }}" method="POST"
          class="add-item">
        @method('PUT') @csrf
        <div class="row">
            <div class="col-6">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="custom">
                            <label>@lang('labels.name')</label>
                            <input type="text" id="name" name="name"
                                   placeholder="@lang('labels.name')"
                                   value="{{ $service->name }}">
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
                                    <option
                                        @selected($status == $service->status) value="{{ $status }}">@lang('labels.'.  $status)</option>
                                @endforeach
                            </select>
                            @if($errors->has('status'))
                                <div class="error">{{ $errors->first('status') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="service-type">
                                @lang('labels.type')
                            </label>
                            <select id="service-type" name="type[]" class="multi-select" required multiple size="2">
                                @php
                                    $serviceTypes = $service->serviceTypes()->pluck('alias')->toArray();
                                @endphp
                                @foreach(\App\Models\Service::TYPES as $type)
                                    <option @selected(in_array($type, $serviceTypes)) value="{{ $type }}">@lang('labels.'.  $type)</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="error">{{ $errors->first('type') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="custom-field">
                            <label for="hide-unavailable"> @lang('labels.hide-unavailable')</label>
                            <input @checked($service->hide_unavailable) type="checkbox" id="hide-unavailable"
                                   name="hide_unavailable">
                            @if($errors->has('hide_unavailable'))
                                <div class="error">{{ $errors->first('hide_unavailable') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="custom-field">
                            <label for="visible-only-to-staff"> @lang('labels.visible-only-to-staff')</label>
                            <input 
                                @checked($service->visible_only_to_staff)
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
                    <div class="col-sm-12">
                        <div class="custom">
                            <label>  @lang('labels.description')</label>
                            <textarea id="w3review" name="description" name="description"
                                      placeholder="@lang('labels.service_description')">{{ old('description',$service->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="error">{{ $errors->first('description') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <livewire:choose-days :servicedays="$service->days" />
                </div>
                <div class="row">
                    {{-- <div class="col-sm-6">
                        <div class="custom-field">
                            <label>@lang('labels.days')</label>
                            <div class="days-list">
                                @foreach(\App\Models\Service::WEEKDAYS as $key => $day)
                                    <div class="@checked(in_array($key, $service->days))">
                                        <input type="checkbox" class="checkbox" id="input-{{ $key }}" name="days[]"
                                               @checked(in_array($key, $service->days ?? [])) value="{{ $key }}"><label
                                            for="input-{{ $key }}">{{Str::limit($day, 3, '')}}</label>
                                    </div>
                                @endforeach
                            </div>
                            <span class="info-text">@lang('labels.service-day-info')</span>
                        </div>
                    </div> --}}
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="button-container">
                            <button class="primary-button" type="submit">
                                @lang('labels.save-changes')
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
                                @foreach($categories as $categ_id => $category)
                                    <li class="parent first" {{ empty($category['products']) ? 'style=display:none;' : null}} data-id="{{$categ_id}}">
                                        <div >{{ $category['categ']['name'] }}</div>
                                        <ul>
                                            @if(!empty($category['products']))
                                                @foreach($category['products'] as $product)
                                                    <li class="child ui-sortable-handle"
                                                        data-category-id="{{$categ_id}}">
                                                        <div><input name="products[]" hidden
                                                                    value="{{$product->id}}"> {{$product->name}}</div>
                                                    </li>
                                                @endforeach
                                            @endif
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
                                @foreach($serviceCategories as $categ_id2)
                                    <li class="parent second ui-sortable-handle"
                                        data-id="{{$categ_id2}}">
                                        <div class="categ-{{$categ_id2}}" {{ !isset($serviceProducts[$categ_id2]) ? 'style=display:none' : null }}>{{ $categories[$categ_id2]['categ']['name'] }}
                                        </div>
                                        <ul>
                                            @if(isset($serviceProducts[$categ_id2]))
                                                @foreach($serviceProducts[$categ_id2] as $product)
                                                    <li class="child ui-sortable-handle second"
                                                        data-category-id="{{$categ_id2}}">
                                                        <div><input name="products[]" hidden
                                                                    value="{{$product['id']}}"> {{$product['name']}}
                                                        </div>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </li>
                                @endforeach

                                @foreach($categories as $categ_id => $category)
                                    @if(!in_array($categ_id, $serviceCategories))
                                    <li class="parent second ui-sortable-handle"
                                        data-id="{{$categ_id}}">
                                        <div class="categ-{{$categ_id}}" {{ !isset($serviceProducts[$categ_id]) ? 'style=display:none' : null }}>{{ $category['categ']['name'] }}
                                        </div>
                                        <ul>
                                        </ul>
                                    </li>
                                        @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection
