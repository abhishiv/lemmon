<div class="dashboard-top">
    <h2 class="title">{{__('labels.order')}} {{__('labels.overview')}}</h2>
    <p class="text">
        <span class="dashboard__new-count dashboard__new-count--all">{{$count}}</span>
        @if ($status !== 'closed')
            <span class="dashboard__new-count dashboard__new-count--bar">{{$barNewOrderCount}}</span>
            <span class="dashboard__new-count dashboard__new-count--restaurant">{{$restaurantNewOrderCount}}</span>
        @endif
        <span>{{ __('labels.new') }} {{ trans_choice('labels.order_plurals', $count)}}</span>
    </p>
</div>
<div class="dashboard__overview--top" data-overview-url="{{route('staff.order.overview')}}">
    {{--    <h2 class="title">{{__('labels.order')}} {{__('labels.overview')}}</h2>--}}
    {{--    <p class="text">{{$count}} {{ __('labels.new') }} {{ trans_choice('labels.order_plurals', $count)}}</p>--}}
    @foreach($overview as $categoryName => $category)
        <div class="dashboard__category dashboard__category--{{ $category['type'] }}">
            <p class="menu-category">{{$categoryName}}</p>
            @foreach($category['products'] as $name => $qty)
                <div class="orders"><p>{{$qty}}</p>{{$name}}</div>
            @endforeach
        </div>
    @endforeach
    <div @class(['button-container', 'section-divider' => $count != 0 && count($overview) > 0])>
        <a class="dashboard__link dashboard__link--closed button" href="{{ route('staff.dashboard.completed') }}">{{ __('labels.view-completed') }}</a>
        <a class="dashboard__link dashboard__link--active button" href="{{ route('staff.dashboard') }}">{{ __('labels.view-new') }}</a>
    </div>
</div>

