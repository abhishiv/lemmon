<div class="staff-bundle-item">
    <div class="checkbox">
        <input 
            id="bundle-{{ $bundleId }}-{{ $entityType }}-{{ $item->id }}"
            type="checkbox" 
            class="staff-bundle-item__input checkbox__input"
            data-bundle-id="{{ $bundleId }}"
            data-entity-type="{{ $entityType }}"
            data-entity-id="{{ $item->id }}"
        >
        <label 
            for="bundle-{{ $bundleId }}-{{ $entityType }}-{{ $item->id }}"
            class="checkbox__label"
        >{{ $entityType === 'products' ? $item->name : $item->title }}</label>
    </div>
    <div class="staff-bundle-item__price">+{{ priceFormat($item->pivot->price) }} @lang('labels.currency')</div>
</div>