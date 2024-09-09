@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '../') }}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')

    <div class="row">
        <h2 class="title">@lang('labels.edit-category') </h2>
    </div>
    <form id="category-form" action="{{ route('manager.product.category.update', $category->id) }}" method="POST">
        @method('PUT') @csrf
        <div class="row">
            <div class="col-6">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="custom">
                            <label>@lang('labels.name')</label>
                            <input type="text" name="name" id="name" value="{{  old('name',$category->name) }}"
                                   placeholder="@lang('labels.name')" required="">
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
                                @foreach(\App\Models\ProductCategory::STATUSES as $status)
                                    <option
                                        @selected($status === $category->status) value="{{$status}}">@lang('labels.' . $status)</option>
                                @endforeach
                            </select>
                            @if($errors->has('status'))
                                <div class="error">{{ $errors->first('status') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="button-container">
                            <button type="submit" class="primary-button">
                                @lang('labels.save-changes')
                            </button>
                            <a href="{{route('manager.product.category.list')}}" class="secondary-button">
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
                            <label>@lang('labels.product-orders')</label>
                            <select id="multiselect" hidden></select>
                            <ul class="category-multiselect">
                                @foreach($category->products()->get() as $product)
                                    <li>
                                        <input name="products[]" hidden
                                               value="{{$product->id}}"> {{$product->name}}
                                    </li>
                                @endforeach
                            </ul>
{{--                            <select multiple="multiple" class="multiple-select"--}}
{{--                                    id="multiselect_to" name="products[]">--}}
{{--                                @foreach($category->products()->get() as $product)--}}
{{--                                    <option value="{{$product->id}}">{{$product->name}}</option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            <div class="button-container select-multiple">--}}
{{--                                <button type="button" id="multiselect_move_up"--}}
{{--                                        class="primary-button">@lang('labels.up')</button>--}}
{{--                                <button type="button" id="multiselect_move_down"--}}
{{--                                        class="primary-button">@lang('labels.down')--}}
{{--                                </button>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </form>
@endsection
