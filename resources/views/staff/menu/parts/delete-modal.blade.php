<div id="delete-item-modal" class="delete-item-modal lemmon-modal" data-remove-item-url="{{ route('staff.tables.remove-order-item') }}">
    <div class="lemmon-modal__inner">
        <div class="lemmon-modal__section lemmon-modal__header">
            <h2 class="lemmon-modal__header-text">@lang('labels.remove_items_text')</h2>
            <button class="lemmon-modal__close" type="button">
                <span class="lemmon-modal__close-icon icon-close"></span>
            </button>
        </div>
        <div class="lemmon-modal__section lemmon-modal__body">
            <div class="lemmon-modal__messages lemmon-modal__messages--align-left delete-items-container">

            </div>
        </div>
        <div class="lemmon-modal__section lemmon-modal__footer">
            <p class="footer-ajax-message"></p>
            <button class="lemmon-modal__close button button--secondary" type="button">@lang('labels.no')</button>
            <button class="lemmon-modal__confirm button yes-btn" type="button">@lang('labels.yes')</button>
        </div>
    </div>
</div>
