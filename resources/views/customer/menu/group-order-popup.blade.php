<div id="group-order-modal" class="modal" tabindex="-1" role="dialog" data-action-url="{{ route('customer.group-order') }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h4 class="title">@lang('customer.are-you-in-a-group')</h4>

                <div class="button-container">
                    <button type="button" class="secondary-button round" id="group-order-no">@lang('labels.no')</button>
                    <button type="button" class="primary-button round" id="group-order-yes">@lang('labels.yes')</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show" id="backdrop" style="display: none;"></div>
