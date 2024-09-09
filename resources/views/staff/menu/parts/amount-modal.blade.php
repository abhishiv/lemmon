<div id="add-amount-modal" class="add-amount-modal lemmon-modal">
    <div class="lemmon-modal__inner">
        <div class="lemmon-modal__section lemmon-modal__header">
            <h2 class="lemmon-modal__header-text">@lang('labels.enter-amount')</h2>
            <button class="lemmon-modal__close" type="button">
                <span class="lemmon-modal__close-icon icon-close"></span>
            </button>
        </div>
        <div class="lemmon-modal__section lemmon-modal__body">
            <input id="amount-field" class="input" type="text" placeholder="@lang('labels.currency') {{ priceFormat(0) }}">
            <div class="lemmon-modal__messages lemmon-modal__messages--align-left ">
                <p class="add-amount-modal__invalid lemmon-modal__message lemmon-modal__message--error">@lang('labels.checkout-amount-invalid')</p>
                <p class="add-amount-modal__valid lemmon-modal__message">@lang('labels.tips'): @lang('labels.currency') <span class="add-amount-modal__tips"></span></p>
            </div>
        </div>
        <div class="lemmon-modal__section lemmon-modal__footer">
            <button class="lemmon-modal__confirm button button--secondary" type="button" disabled>@lang('labels.confirm')</button>
            <button class="add-amount-modal__no-tips button" type="button">@lang('labels.no-tips')</button>
        </div>
    </div>
</div>