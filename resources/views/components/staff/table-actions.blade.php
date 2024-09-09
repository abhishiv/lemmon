<div class="lemmon-table__actions-wrapper">
    @if ($isBusy)
        <button class="restaurant-tables__pay button button--small" type="button"
                data-table="{{ $tableId }}">@lang('labels.pay')</button>
    @endif
    <form class="restaurant-tables__add-order" action="{{ route('staff.menu') }}" method="post">
        @csrf
        <input type="hidden" name="table_id" value="{{ $tableId }}">
        <button class="button button--small button--light" type="submit">@lang('labels.add-button')</button>
    </form>
    @if ($isBusy)
        <form class="restaurant-tables__orders" action="{{ route('staff.dashboard') }}" method="post">
            @csrf
            <input type="hidden" name="table_id" value="{{ $tableId }}">
            <button class="button button--small button--secondary"
                    type="submit">{{ trans_choice('labels.orders', 2) }}</button>
        </form>

    @endif
    <div class="lemmon-table__actions-buttons">
        <button class="lemmon-table__actions-toggle">
            <span class="lemmon-table__actions-icon icon-arrow-down"></span>
        </button>
        <div class="lemmon-table__actions-dropdown">
            <ul class="lemmon-table__actions-list">
                <li class="lemmon-table__actions-item">
                    <button class="restaurant-tables__change_table lemmon-table__actions-button" type="button"
                            data-table-id="{{ $tableId }}"
                            data-table-name="{{$tableName}}">@lang('labels.change_table')</button>
                </li>
            </ul>
        </div>
    </div>
</div>
