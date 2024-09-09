@extends('layouts.manager')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/manager-menu.js', '..') }}" defer></script>
@endpush
@section('body_class', 'management-table full')
@section('content')
    <div class="container-fluid manager-edit-menu">
        <h2><span>Menu</span></h2>
        <div class="row" id="main-row">
            <div class="col-sm-3 cat-form">
                <h3><span>Add Menu Items</span></h3>

                <div class="panel-group" id="menu-items">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span>Categories <span class="caret pull-right"></span></span>
                        </div>
                        <div class="panel-collapse" id="categories-list">
                            <div class="panel-body">
                                <div class="item-list-body">
                                    @foreach($categories as $key => $cat)
                                        <p><input type="checkbox" name="product_category[]"
                                                  value="{{$cat->id}}"> {{$cat->name}}</p>
                                    @endforeach
                                </div>
                                <div class="item-list-footer">
                                    <label class="btn btn-sm btn-default"><input type="checkbox"
                                                                                 id="select-all-categories"> Select
                                        All</label>
                                    <button type="button" class="btn-menu-select"
                                            id="add-categories">Add to Menu
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-9 cat-view">
                <h3><span>Menu Structure</span></h3>
                <form id="save-menu-form" action="{{route('manager.menu.update', $menu->id)}}" method="post">
                    @method('PUT') @csrf
                    <div class="item-list menu ui-sortable">
                        <ul id="sortable">
                            @foreach($menu->items as $key => $item)
                                <li class="menu-item-bar draggable " data-id="{{$item->id}}">{{$item->title}} <span
                                        data-toggle-id="{{$key}}" class="toggleItem" style="float: right">V</span>
                                    <div data-toggle="{{$key}}" class="panel-collapse edit-item-menu collapse">
                                        <div style="display: flex; flex-direction: column">
                                            <label>Item Title</label>
                                            <input type="text" name="menu_items[{{$item->id}}][title]"
                                                   value="{{$item->title}}">
                                            <input type="hidden" class="category-id"
                                                   name="menu_items[{{$item->id}}][product_category_id]"
                                                   value="{{$item->product_category_id}}">
                                            <input type="hidden" name="menu_items[{{$item->id}}][menu_order]" value="">
                                        </div>
                                        <button type="button"
                                                style="background-color: #a92222; color: white"
                                                class="pull-right btn-menu-select delete-sortable"
                                        >Remove
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <button type="submit" class="pull-right btn-menu-select"
                                id="save-menu">Save Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
