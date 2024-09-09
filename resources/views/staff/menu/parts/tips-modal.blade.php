<div id="add-tip-modal" class="lemmon-modal">
    <div class="lemmon-modal__inner">
        <div class="lemmon-modal__section lemmon-modal__header">
            <h2 class="lemmon-modal__header-text">@lang('labels.add-tip')</h2>
            <button class="lemmon-modal__close" type="button">
                <span class="lemmon-modal__close-icon icon-close"></span>
            </button>
        </div>
        <div class="lemmon-modal__section lemmon-modal__body">
            <label for="tips-field" class="label">@lang('labels.amount')</label>
            <input id="tips-field" class="input" type="number" min="0" step="any" placeholder="@lang('labels.currency') {{ priceFormat(0) }}">
        </div>
        <div class="lemmon-modal__section lemmon-modal__footer">
            <button class="lemmon-modal__cancel button button--secondary" type="button">@lang('labels.cancel')</button>
            <button class="lemmon-modal__confirm button" type="button" disabled>@lang('labels.confirm')</button>
        </div>
    </div>
</div>