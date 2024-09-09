$('body').on('click', ".delete-action", function (e) {
    e.preventDefault();
    $('.management-table').addClass('overflow-hidden');
    $('.overlay').css({'display': 'flex'});
    $('#delete-form').attr('action', $(this).attr('action'));
});

$('body').on('click', "#close-popup", function (e) {
    $('.overlay').hide();
    $('.management-table').removeClass('overflow-hidden');
});

$('.overlay').on('click', function () {
    $('.management-table').removeClass('overflow-hidden');
})

$('.close-modal').on('click', function () {
    $('.overlay').hide();
})


$('div.alert .close').on('click', function() {
    $(this).parent().alert('close');
});

$("div.alert").css({'display': 'flex'});
setTimeout(function() { $("div.alert").fadeOut(); }, 6000);
