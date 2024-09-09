<div id="open-amount-modal" class="staff-menu__custom-item open-amount-modal lemmon-modal" data-add-url="{{ route('staff.cart.add') }}"
     data-update-url="{{ route('staff.cart.update') }}" data-product-id="">
    <div class="lemmon-modal__inner">
        <div class="lemmon-modal__section lemmon-modal__header">
            <h2 class="lemmon-modal__header-text">@lang('labels.insert_open_amount')</h2>
            <button class="lemmon-modal__close" type="button">
                <span class="lemmon-modal__close-icon icon-close"></span>
            </button>
        </div>
        <div class="lemmon-modal__section lemmon-modal__body">
            <div class="lemmon-modal__section lemmon-modal__body">
                <input id="open-amount-field" class="input" type="number" min="0.1" step="any"
                       placeholder="@lang('labels.currency') {{ priceFormat(0) }}">
            </div>
        </div>
        <div class="lemmon-modal__section lemmon-modal__footer">
            <button class="lemmon-modal__cancel button button--secondary"
                    type="button">@lang('labels.cancel')</button>
            <button class="staff-menu__add-price button"
                    type="button">@lang('labels.add-button')</button>
        </div>
    </div>
</div>
