<div class="col-sm-12">
    <div class="custom-field" style="margin-top: 0 !important;">
        <label> @lang('labels.schedule')</label>
    </div>
    <div class="tab" style="margin-top: 0;">
        <div class="custom-field" style="margin-top: 0 !important;">
        <div class="days-list">
            @foreach (\App\Models\Service::WEEKDAYS as $key => $day)
            {{-- @dd($key, $day, $days) --}}
            <div class="{{ isset($days[Str::limit($day, 3, '')]) && is_array($days[Str::limit($day, 3, '')]) && !empty($days[Str::limit($day, 3, '')]) ? 'checked' : '' }}" style="flex-grow:1;">
                <button type="button" wire:click="switchTab('{{ Str::limit($day, 3, '') }}')"
                    class="checkbox {{ $activeTab == Str::limit($day, 3, '') ? 'activeTab' : '' }}" style="width:100%;height:100%; line-height:normal; font-size: 14px;">{{Str::limit($day, 3, '')}}</button>
            </div>
            @endforeach
        </div>
        </div>
    </div>
    @foreach (\App\Models\Service::WEEKDAYS as $key => $day)
        <div id="{{ Str::limit($day, 3, '') }}" class="tabcontent"
            style="display: {{ $activeTab == Str::limit($day, 3, '') ? 'block' : 'none' }};">
            @foreach ($days[$activeTab] as $index => $time)
                <div class="row">
                    <div class="col-sm-5">
                        <div class="custom">
                            <label> @lang('labels.start')</label>
                            <input class="gray" type="time" id="days"
                                wire:model="days.{{ $activeTab }}.{{ $index }}.start"
                                value="{{ $time['start'] }}">
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="custom">
                            <label> @lang('labels.end')</label>
                            <input class="gray" type="time" id="days"
                                wire:model="days.{{ $activeTab }}.{{ $index }}.end"
                                value="{{ $time['end'] }}">
                        </div>
                    </div>
                    <div class="col-sm-2" style="align-items: end; display:flex">
                        <button style="width: 100%;" type="button" class="btn delete-button extra-remove"
                            wire:click="removeTimeFromDay('{{ $activeTab }}', {{ $index }})">
                            <svg style="display: inline;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
            @foreach ($days as $weekday => $timestamp)
                @foreach ($timestamp as $indexNumber => $weektime)
                    @foreach ($weektime as $type => $timevalue)
                        @if ($timevalue)
                            <input type="text"
                                name="days[{{ $weekday }}][{{ $indexNumber }}][{{ $type }}]"
                                value="{{ $timevalue }}" hidden id="">
                        @endif
                    @endforeach
                @endforeach
            @endforeach
            <div class="add-removable-group-button" style="display: flex;gap: 6px;margin-top:10px;">
                <div class="button-container" style="margin-top:0!important;width:100%">
                    <button type="button" wire:click="addTimeToDay('{{ Str::limit($day, 3, '') }}')"
                        style="margin-right: 0;" class="primary-button">
                        @lang('labels.add_schedule')
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>
