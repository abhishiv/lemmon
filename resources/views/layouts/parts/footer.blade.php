<div class="overlay">
    <div class="overlay-box">
        <div class="close-modal">
            @include('layouts.parts.icons.close-modal')
        </div>

        <p>@lang('labels.delete-item')</p>
        <div class="button-container">
            <form id="delete-form" method='POST' action="" style="display: flex; justify-content: end;">
                @csrf @method('DELETE')
                <a class="secondary-button" id="close-popup">@lang('labels.no')</a>
                <button id="delete-btn" type='submit' name='send'
                        class="primary-button">@lang('labels.yes')
                </button>
            </form>
        </div>
        <div class="action">@lang('labels.undone-action')</div>
    </div>
</div>
<div class="container">
    <div class="dashboard-footer">
        <p class="dashboard-footer__copyright">©{{ date('Y') }} @lang('labels.lemmon')</p>
    </div>
</div>
