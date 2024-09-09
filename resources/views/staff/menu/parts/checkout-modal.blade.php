<div
    id="{{ $checkoutModalId }}"
    class="lemmon-checkout"
    data-totals-url="{{ $totalsUrl }}"
    data-options-url="{{ $optionsUrl }}"
    @if (isset($payUrl))
        data-pay-url="{{ $payUrl }}"
    @endif

    @if (isset($closeOrdersUrl))
        data-close-url="{{ $closeOrdersUrl }}"
    @endif
>
    <div class="lemmon-checkout__header">
        <button class="lemmon-checkout__close lemmon-checkout__back" type="button">
            <span class="lemmon-checkout__back-text">← @lang('labels.back')</span>
        </button>
        <div class="lemmon-checkout__logo">
            @include('layouts.parts.icons.logo')
        </div>
    </div>
    <div class="lemmon-checkout__content">
        <div class="lemmon-checkout__table"></div>
        <div class="lemmon-checkout__totals">
            <div class="lemmon-checkout__title">@lang('labels.total')</div>
            <div class="lemmon-checkout__totals-breakdown"></div>
            <div class="lemmon-checkout__discount-included"><span class="lemmon-checkout__discount-value"></span>% @lang('labels.takeaway-discount-included')</div>
        </div>
        <div class="lemmon-checkout__actions">
            @if ($displayedButton === 'tips')
                <button
                    class="lemmon-checkout__add-tips button button--large button--dark"
                    type="button"
                >@lang('labels.tips')</button>
            @elseif ($displayedButton === 'print')
                <button
                    class="lemmon-checkout__print button button--large button--dark"
                    type="button"
                >@lang('labels.print-receipt')</button>
            @endif
            <button
                class="lemmon-checkout__add-discount button button--large button--dark"
                type="button"
            >@lang('labels.discount')</button>
            <button
                class="lemmon-checkout__payment button button--large button--secondary"
                type="button"
                data-payment-method="{{ \App\Models\Order::CASH }}"
            >@lang('labels.cash')</button>
            <button
                class="lemmon-checkout__payment button button--large"
                type="button"
                data-payment-method="{{ \App\Models\Order::CARD }}"
            >@lang('labels.card')</button>
        </div>
        <div class="lemmon-checkout__complete">
            <img class="lemmon-checkout__complete-icon" src="{{ url('dist/img/checkmark-big.svg') }}" alt="checkmark">
            <div class="lemmon-checkout__complete-text">@lang('labels.transaction-complete')</div>
            <div class="lemmon-checkout__closing-orders">@lang('labels.closing-orders')</div>
            <div class="lemmon-checkout__orders-closed">@lang('labels.orders-closed')</div>
            <div class="lemmon-checkout__complete-actions">
                <button class="lemmon-checkout__close button button--secondary">@lang('labels.close')</button>
                @if ($availablePrinters['receipt'])
                    <button data-partial-pay="" data-partial-pay-id="" class="lemmon-checkout__print button final-print-button">@lang('labels.print-receipt')</button>
                @endif
            </div>
        </div>
    </div>
</div>
