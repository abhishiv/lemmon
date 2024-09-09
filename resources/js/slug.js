//Restaurant SLUG
function str_slug(title, separator) {
    if (typeof separator == 'undefined') separator = '-';

    // Convert all dashes/underscores into separator
    var flip = separator == '-' ? '_' : '-';

    var title = title

    title = title.replace(flip, separator);

    // Remove all characters that are not the separator, letters, numbers, or whitespace.
    title = title.toLowerCase()
        .replace(new RegExp('[^a-z0-9' + separator + '\\s]', 'g'), '');

    // Replace all separator characters and whitespace by a single separator
    title = title.replace(new RegExp('[' + separator + '\\s]+', 'g'), separator);

    return title.replace(new RegExp('^[' + separator + '\\s]+|[' + separator + '\\s]+$', 'g'),'');
}
slug = $('#slug');
formName = $('#name');

formName.on('focusout', () => {
    slug.val(str_slug(formName.val(), '-'))
})

slug.on('focusout', () => {
    slug.val(str_slug(slug.val(), '-'))
})

