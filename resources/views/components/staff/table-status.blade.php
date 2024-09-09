@if ($isBusy)
    <button class="button button--small button--red {{ $activeOrders ? '' : 'restaurant-tables__status-update '}}" type="button" data-id={{ $tableId }} data-new-status="free">@lang('labels.busy')</button>
@else
    <button class="restaurant-tables__status-update button button--small button--green" type="button" data-id={{ $tableId }} data-new-status="busy">@lang('labels.free')</button>
@endif