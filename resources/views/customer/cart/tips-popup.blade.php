<div id="tips-modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog bottom" role="document">
        <div class="modal-content tips">
            <div class="modal-body">
                <h4 class="title">@lang('customer.tip-popup-title')</h4>
                <div class="options-row">
                    @if(!empty($recommendedAmounts))
                        <div class="options">
                            @foreach($recommendedAmounts as $key => $amount)
                                <div class="option">
                                    <input class="tip-amount" type="radio" id="tip-amount-{{ $key + 1 }}"
                                           name="tip-amount"
                                           value="{{ $amount }}">
                                    <label
                                        for="tip-amount-{{ $key + 1 }}">@lang('labels.currency') {{ priceFormat($amount) }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="custom-tip">
                        <input class="customer-input custom-input" placeholder="{{ trans('customer.other-amount') }}" autocomplete="off" id="tip-custom-amount" type="number" inputmode="decimal" step="0.01">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="button-container">
                    <button type="button" class="customer-button" id="pay-with-tips">@lang('customer.tip-button')</button>
                    <button type="button" class="customer-button customer-button--secondary" id="pay-no-tips">@lang('customer.no-tip-button')</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show" id="backdrop" style="display: none;"></div>
