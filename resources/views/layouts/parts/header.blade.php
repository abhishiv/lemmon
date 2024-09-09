<div class="sticky-header header-{{ auth()->user()->getRoleNames()[0] }} container">
    <header class="dashboard-header">
        <div class="header-top">
            <a href="{{ route(auth()->user()->getRoleNames()[0] . '.dashboard') }}">
                <div class="dashboard-header__logo">
                    @include('layouts.parts.icons.logo')
                </div>
            </a>
            <div class="dashboard-header__user">
                @if (Route::is('staff.dashboard'))
                    <div class="dashboard-header__products__filter">
                        <label class="header-label">@lang('labels.filter'):</label>
                        <div class="types">
                            <label class="product-type product-type--bar" data-product-type="bar">
                                @include('layouts.parts.icons.drink')
                            </label>
                        </div>
                        <div class="types">
                            <label class="product-type product-type--restaurant" data-product-type="restaurant">
                                @include('layouts.parts.icons.food')
                            </label>
                        </div>
                        <div class="types">
                            <label class="product-type product-type--all" data-product-type="all">
                                @include('layouts.parts.icons.all-types')
                            </label>
                        </div>
                    </div>
                @endif
                @if (!Route::is('staff.tip.list'))
                    <p class="dashboard-header__user__name">{{ucfirst(auth()->user()->name)}}</p>
                    <div class="dashboard-header__user__out">
                        <div>
                            @include('layouts.parts.icons.logout')
                        </div>
                        <form class="label_out" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                @endif
                {{--                <p class="dashboard-header__user--short">{{strtoupper(auth()->user()->initials)}}</p>--}}
            </div>
        </div>
        <div>@include('layouts.parts.sidebar')</div>
    </header>
</div>
