$('#add-printer').on('click', function(e) {
    const $lastRow = $('.printers .printers__item').last();
    const newRowIndex = $lastRow.length ? parseInt($lastRow.data('printer')) + 1 : 0;

    const $newRow = $('.empty-printer-row .printers__item').clone();

    $newRow.attr('data-printer', newRowIndex);
    $newRow.find('input, select').each(function(index, formControl) {
        const $formControl = $(formControl);

        const newId = $formControl.attr('id') + newRowIndex;

        $formControl.attr('id', newId);
        $formControl.siblings('label').attr('for', newId);
    });;

    $lastRow.length ? $newRow.insertAfter($lastRow) : $newRow.prependTo($('.printers'));
});

$('.printers').on('click', '.printers__delete', function(e) {
    $(this).closest('.printers__item').remove();
});

$('.printers').on('change', '.printers__type-select', function(e) {
    e.preventDefault();
    console.log('change event');
    return;
    const $this = $(this);

    $this.siblings('.printers__type').val($this.val());

    console.log($(this).val());
});

$('.printers').on('click', '.printers__type-select option', function(e) {
    e.preventDefault();
    const $this = $(this);
    const $select = $this.parent();
    const isSelected = $this.attr('selected');
    const value = $this.attr('value');

    if (isSelected && $select.find('option[selected]').length <= 1) {
        return;
    }

    if (isSelected) {
        $this.removeAttr('selected');
    } else if ($('.printers .printers__type-select').not($select).find(`option[value="${value}"][selected]`).length === 0) {
        $this.attr('selected', true);
    }

    const printTypes = $select.find('option[selected]').map(function() {
        return $(this).attr('value');
    }).get();

    $select.val(printTypes);
    $select.siblings('.printers__type').val(printTypes.join(','));
});

$('#add-city').on('click', function(e) {
    const $lastRow = $('.admin-delivery-cities .admin-delivery-cities__item').last();
    const newRowIndex = $lastRow.length ? parseInt($lastRow.data('city')) + 1 : 0;

    const $newRow = $('.empty-cities-row .admin-delivery-cities__item').clone();

    $newRow.attr('data-city', newRowIndex);
    $newRow.find('input').each(function(index, formControl) {
        const $formControl = $(formControl);

        const newId = $formControl.attr('id') + newRowIndex;

        $formControl.attr('id', newId);
        $formControl.siblings('label').attr('for', newId);
    });;

    $lastRow.length ? $newRow.insertAfter($lastRow) : $newRow.prependTo($('.admin-delivery-cities'));
});


$('.admin-delivery-cities').on('click', '.admin-delivery-cities__delete', function(e) {
    $(this).closest('.admin-delivery-cities__item').remove();
})