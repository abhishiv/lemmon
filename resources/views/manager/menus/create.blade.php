@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager-menu.js', '..') }}" defer></script>
@endpush
@section('body_class', 'management-table full')
@section('content')
    <div class="container-fluid manager-create-menu">
        <h2><span>Menu</span></h2>
        <div class="row" id="main-row">
            <div class="col-sm-3 cat-form">
                <h3><span>@lang('labels.add-menu')</span></h3>

                <div class="panel-group" id="menu-items">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <a href="" data-toggle=data-parent="#menu-items"> @lang('labels.categories')<span
                                    class="caret pull-right"></span></a>
                        </div>
                        <div class="panel-collapse" id="categories-list">
                            <div class="panel-body">
                                <div class="item-list-body">
                                    @foreach($categories as $key => $cat)
                                        <p><input type="checkbox" name="categories[]"
                                                  value="{{$cat->id}}"> {{$cat->name}}</p>+
                                    @endforeach
                                </div>
                                <div class="item-list-footer">
                                    <label class="btn btn-sm btn-default"><input type="checkbox"
                                                                                 id="select-all-categories"> Select All</label>
                                    <button type="button" class="pull-right btn btn-default btn-sm" id="add-categories">
                                        @lang('labels.add-to-menu')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-9 cat-view">
                    <h3><span>Menu Structure</span></h3>
                    <h4>Create New Menu</h4>
                    <form method="post" action="{{ route('manager.menu.store') }}">
                        {{csrf_field()}}
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Name</label>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <input type="text" name="title" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-6 text-right">
                                <button class="btn btn-sm btn-primary">Create Menu</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection
