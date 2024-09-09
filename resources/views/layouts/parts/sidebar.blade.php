<ul class="dashboard-menu__list">
    @foreach(sideBar() as $k => $link)
        @can($link['permission'])
            @if($link['link'] )
                <li class="dashboard-menu__list__item {{ isset($link['class']) ? $link['class'] : '' }} {{ in_array(Request::segment(2), $link['segment']) ? 'active' : '' }} {{ isset($link['add-btn'])? 'add-btn-li': '' }}">
                    <a class="link {{ Request::url() == $link['link'] || Request::url('create') == $link['link'] ? 'active' : ''}} {{ in_array(Request::segment(2), $link['segment']) ? 'active' : '' }}"
                       href="{{ $link['link'] }}"><i class="fa fa-{{ $link['icon'] }}"></i>
                        <span class="title">{{ $link['title'] }}</span>
                    </a>
                </li>
            @endif
        @endcan
    @endforeach
</ul>
