<div class="menu shadow" id="menu" style="position: sticky;">
    <ul class="menu__list" style=" display: flex;">
        @foreach($menu->items as $item)
            @if(!empty($products[$item->product_category_id]))
                <li class="menu__list__item">
                    <a data-item-id="{{$item->id}}">{{$item->title}}</a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
