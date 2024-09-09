@extends('layouts.manager')
@push('scripts')
    @include('manager.products.form-validation.error-messages')
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '../') }}" defer></script>
    <script type="text/javascript" src="{{ mix('/dist/js/manager-product.js', '../') }}" defer></script>
    <script type="text/javascript" src="{{ mix('/dist/js/products.js', '../') }}" defer></script>
@endpush

@section('body_class', 'management-table full')

@section('content')

    <div class="row">
        <h2 class="title"> @lang('labels.edit-menu-item')</h2>
    </div>

    <form id="productForm" action="{{ route('manager.product.update', $product->id) }}" method="POST"
          class="add-item row">
        @method('PUT') @csrf
        @foreach($product->images as $image)
            <input class="hidden_images" type="hidden" name="images[]" value="{{ $image->filename }}"/>
        @endforeach
        <div class="col-sm-6">
            <div class="row">
                @csrf
                <div class="col-sm-6">
                    <div class="custom">
                        <label>{{__('labels.name')}}</label>
                        <input type="text" id="name" name="name" required=""
                               placeholder="Product {{__('labels.name')}}"
                               value="{{  old('name',$product->name) }}">
                        <div id="error-name" class="error" style="display:none;"></div>
                        @if($errors->has('name'))
                            <div class="error">{{ $errors->first('name') }}</div>
                        @endif
                        @if($errors->has('slug'))
                            <div class="error">{{ $errors->first('slug') }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="custom">
                        <label for="category_id">
                            @lang('labels.category')
                        </label>
                        <select id="category_id" name="category_id">
                            @foreach($categories as $category)
                                <option
                                    @selected(in_array($category->id, $product->categories->pluck('id')->toArray())) value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div id="error-category_id" class="error" style="display:none;"></div>
                        @if($errors->has('category_id'))
                            <div class="error">{{ $errors->first('category_id') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="custom">
                        <label for="price">  @lang('labels.price')</label>
                        <input type="number" name="price" id="price" value="{{ old('price',$product->price) }}"
                               placeholder="@lang('labels.currency') 5" required="" min="0">
                        <div id="error-price" class="error" style="display:none;"></div>
                        @if($errors->has('price'))
                            <div class="error">{{ $errors->first('price') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="custom">
                        <label for="special_price"> @lang('labels.promo-price')</label>
                        <input type="number" name="special_price" id="special_price" required=""
                               value="{{ old('special_price',$product->special_price) }}"
                               placeholder="@lang('labels.currency') 3" min="0">
                        <div id="error-special_price" class="error" style="display:none;"></div>
                        @if($errors->has('special_price'))
                            <div class="error">{{ $errors->first('special_price') }}</div>
                        @endif
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="custom">
                        <label for="additional_info">
                            @lang('labels.additional_info')
                        </label>
                        <input type="text" name="additional_info" id="additional_info"
                               value="{{ old('additional_info',$product->additional_info) }}"
                               placeholder="Gluten Free">
                        <div id="error-additional_info" class="error" style="display:none;"></div>
                    </div>
                    @if($errors->has('additional_info'))
                        <div class="error">{{ $errors->first('additional_info') }}</div>
                    @endif
                </div>
                <div class="col-sm-6">
                    <div class="custom">
                        <label for="serving">
                            @lang('labels.type')
                        </label>
                        <select id="type" name="type">
                            @foreach(\App\Models\Product::TYPES as $type)
                                <option
                                    @selected($product->type == $type)  value="{{ $type }}">@lang('labels.'.  $type)</option>
                            @endforeach
                        </select>
                        <div id="error-type" class="error" style="display:none;"></div>
                        @if($errors->has('type'))
                            <div class="error">{{ $errors->first('type') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="custom">
                        <label for="category_id">
                            @lang('labels.status')
                        </label>
                        <select
                            id="status" name="status">
                            @foreach($statuses as $status)
                                <option
                                    @selected($status == $product->status) value="{{ $status }}">@lang('labels.'.  $status)</option>
                            @endforeach
                        </select>
                        <div id="error-status" class="error" style="display:none;"></div>
                        @if($errors->has('status'))
                            <div class="error">{{ $errors->first('status') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6" id="food-type-container">
                    <div class="custom">
                        <label for="food-type">
                            {{ trans_choice('labels.course', 1) }}
                        </label>
                        <select
                            id="food-type" name="food_type_id">
                            @foreach($foodTypes as $foodType)
                                <option
                                    @selected($foodType->id == $product->food_type_id)
                                    value="{{ $foodType->id }}">{{ $foodType->name }}</option>
                            @endforeach

                        </select>
                        <div id="error-food-type" class="error" style="display:none;"></div>
                        @if($errors->has('food_type_id'))
                            <div class="error">{{ $errors->first('food_type_id') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="custom">
                        <label>  @lang('labels.description')</label>
                        <textarea id="description" name="description" required="" name="description"
                                  placeholder="@lang('labels.product_description')">{{ old('description',$product->description) }}</textarea>
                        <div id="error-description" class="error" style="display:none;"></div>
                        @if($errors->has('description'))
                            <div class="error">{{ $errors->first('description') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="custom">
                        <label for="is_custom">
                            @lang('labels.is_custom')
                        </label>
                        <input id="is_custom" {{ $product->is_custom ? 'checked' : '' }} type="checkbox" name="is_custom" value="1"/>
                        <div id="error-food-type" class="error" style="display:none;"></div>
                        @if($errors->has('is_custom'))
                            <div class="error">{{ $errors->first('is_custom') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <livewire:extra-component :active="(!$product->bundles?->isEmpty())" :errorsHandling="$errors->toArray()"
                                      :oldExtraValues="old('extra')" :oldProductValues="old('product')"
                                      :oldRemovableValues="old('removable')" :product="$product"/>
        </div>
    </form>

    <div class="row">
        <div class="col-sm-3">
            <div class="custom">
                <label for="weight">@lang('labels.list-image') </label>
                <form action="{{ route('manager.product.verify.image')}}"
                      data-url="{{ route('manager.product.verify.image') }}"
                      data-gallery="{{ route('manager.product.images', $product->id )}}"
                      class="dropzone drop-grid drop" id="dropzone-list">
                    @csrf
                </form>
                <p class="image-error" id="list-error"></p>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="custom">
                <label for="weight"> @lang('labels.single-image')</label>
                <form action="{{ route('manager.product.verify.image')}}"
                      data-url="{{ route('manager.product.verify.image') }}"
                      data-gallery="{{ route('manager.product.images', $product->id )}}"
                      class="dropzone drop-grid drop" id="dropzone-single">
                    @csrf
                </form>
                <p class="image-error" id="single-error"></p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <div class="button-container">
                @if($categories->isEmpty())
                    <p>@lang('labels.product-category-condition')</p>
                    <a href="{{route('manager.product.category.create')}}">
                        @lang('labels.click-to-add-category')
                    </a>
                @else
                    <button class="primary-button" id="save-product-submit">
                        @lang('labels.save-changes')
                    </button>
                @endif
                <a href="{{route('manager.product.list')}}" class="secondary-button">
                    @lang('labels.cancel')
                </a>
                <div style="margin-left: 2rem; margin-right: 1rem;">
                    <livewire:switch-bundle
                        :switchBundleToggle="(!empty(old('extra'))) || (!empty(old('product'))) || (!empty(old('removable'))) || (!$product->bundles?->isEmpty())"/>
                </div>
            </div>
        </div>
    </div>
@endsection
