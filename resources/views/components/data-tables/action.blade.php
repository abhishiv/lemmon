<div class="actions">
    <a title="Edit" href="{{$editRoute}}">
        <img src="{{url('dist/img/edit.svg')}}" alt=""/>
    </a>

    <form class="delete-action" method='POST' action="{{$deleteRoute}}">
        @csrf @method('DELETE')
        <button class="delete-item" title="Delete">
            <img src="{{url('dist/img/delete-table.svg')}}" alt=""/>
        </button>
    </form>


    @if(isset($copyRoute))
        <a href="{{ $copyRoute }}" title="Copy">
            <img src="{{ url('dist/img/copy.svg') }}" alt="">
        </a>
    @endif
</div>
