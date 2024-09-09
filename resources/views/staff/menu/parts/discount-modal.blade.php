<div id="add-discount-modal" class="lemmon-modal">
    <div class="lemmon-modal__inner">
        <div class="lemmon-modal__section lemmon-modal__header">
            <h2 class="lemmon-modal__header-text">@lang('labels.discount')</h2>
            <button class="lemmon-modal__close" type="button">
                <span class="lemmon-modal__close-icon icon-close"></span>
            </button>
        </div>
        <div class="lemmon-modal__section lemmon-modal__body">
            <label for="discount-percentage-field" class="label">@lang('labels.percentage')</label>
            <input id="discount-percentage-field" class="input" type="number" min="0" step="any" placeholder="10%" data-link="discount-amount-field" data-discount-type="percentage">
            <p class="lemmon-modal__text">@lang('labels.enter-specific-amount')</p>
            <label for="discount-amount-field" class="label">@lang('labels.custom-amount')</label>
            <input id="discount-amount-field" class="input" type="number" min="0" step="any" placeholder="@lang('labels.currency') {{ priceFormat(0) }}" data-link="discount-percentage-field" data-discount-type="fixed-amount">
        </div>
        <div class="lemmon-modal__section lemmon-modal__footer">
            <button class="lemmon-modal__cancel button button--secondary" type="button">@lang('labels.cancel')</button>
            <button class="lemmon-modal__confirm button" type="button" disabled>@lang('labels.add-discount')</button>
        </div>
    </div>
</div>