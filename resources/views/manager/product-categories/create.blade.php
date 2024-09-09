@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('dist/js/manager.js', '../') }}" defer></script>
@endpush
@section('body_class', 'management-table')
@section('content')

    <div class="row">
        <h2 class="title"> @lang('labels.add-category')</h2>
    </div>
    <form id="category-form" action="{{ route('manager.product.category.store') }}" method="POST">
        @csrf
        <div class="row">
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
                    <div class="col-sm-6">
                        <div class="custom">
                            <label for="status">
                                @lang('labels.status')
                            </label>
                            <select
                                id="status" name="status">
                                @foreach(\App\Models\ProductCategory::STATUSES as $status)
                                    <option value="{{$status}}">@lang('labels.' . $status)</option>
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
                                @lang('labels.save')
                            </button>
                            <a href="{{route('manager.product.category.list')}}" class="secondary-button">
                                @lang('labels.cancel')
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
