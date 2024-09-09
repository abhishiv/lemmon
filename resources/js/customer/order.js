$(document).ready(() => {
    $('body').on('click', '.send-email button[value="email"]', function (e) {
        e.preventDefault();
        const type = $(this).val();
        if ($(this).val() == 'email' && $('#email').val().length == 0) {
            $('#email-error').text('Email field is required!').show();
            return;
        }
        if ($(this).val() == 'email' && !isEmail($('#email').val())) {
            $('#email-error').text('Invalid Email!').show();
            return;
        }
        $('.send-email').append('<input type="hidden" name="type" value="email" />').submit();
    })

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    setInterval(function () {
            $.ajax({
                type: 'POST',
                cache: false,
                async: true,
                data: {},
                headers: {"cache-control": "no-cache"},
                url: $('.success').data('get-url'),
                statusCode: {
                    404: function() {
                        location.reload();
                    },
                },
                success: function (data) {
                    const status = data.status,
                        canceled = data.canceled,
                        url = data.url;

                    if (status === 'new') {
                        $('#order-preparing').fadeOut(2000);
                        $('#order-enjoy').fadeOut(2000);
                        $("#card-payment").fadeIn(2000);
                    } else if (status === 'preparing') {
                        $("#card-payment").fadeOut(2000);
                        $('#order-enjoy').fadeOut(2000);
                        $('#order-preparing').fadeIn(2000);
                    } else if (status === 'ready') {
                        $("#card-payment").fadeOut(2000);
                        $('#order-preparing').fadeOut(2000);
                        $('#order-enjoy').fadeIn(2000);
                    }

                    if (canceled !== null && canceled !== undefined) {
                        document.getElementById('canceled-order-number').innerHTML = canceled;
                        $('.cancel-action').show();
                    }

                    if (url !== null && url !== undefined) {
                        window.location.replace(url);
                    }
                }
                ,
                complete: function (data) {

                }
            })
        },
        5000
    )
});

$('.review').click(function (e) {
    const url = $(e.target).data('url');

    jQuery.ajax(url, {
        method: "POST",
        url,
        success: (data) => {
            $('.scrollable-list').html(data);
            $('#order-review-modal').css({'display': 'flex'});
        },
        error: (error) => {
            console.error(error);
        }
    })
})

$('.customer-modal__close').on('click', function (e) {
    e.preventDefault()
    $(this).closest('.customer-modal').hide();
})

$('.close-modal').on('click', function () {
    $('.overlay-content').hide();
})
