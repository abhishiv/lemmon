<div id="change-table-modal" class="change-table-modal lemmon-modal" data-change-table-url="{{ route('staff.tables.change-table') }}">
    <div class="lemmon-modal__inner">
        <div class="lemmon-modal__section lemmon-modal__header">
            <h2 class="lemmon-modal__header-text">@lang('labels.change_table_text')</h2>
            <button class="lemmon-modal__close" type="button">
                <span class="lemmon-modal__close-icon icon-close"></span>
            </button>
        </div>
        <div class="lemmon-modal__section lemmon-modal__body">
            <div class="lemmon-modal__messages lemmon-modal__messages--align-left">
                <div class="select-table-modal__dropdown">
                    <label for="select-table" class="label">@lang('labels.table-number')</label>
                    <select id="select-table" class="dropdown" autocomplete="off">
                        <option value="">@lang('labels.select-table')</option>
                        @foreach ($tables as $currentTable)
                            <option value="{{ $currentTable->id }}">{{ $currentTable->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="lemmon-modal__section lemmon-modal__footer">
            <p class="footer-ajax-message"></p>
            <button class="lemmon-modal__close button button--secondary" type="button">@lang('labels.no')</button>
            <button class="lemmon-modal__confirm button yes-btn" type="button">@lang('labels.yes')</button>
        </div>
    </div>
</div>

