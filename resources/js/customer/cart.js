function showTipsPopup(event, submitter) {
    if (submitter === null) {
        return;
    }

    // Intercept only if the chosen payment method is card
    if (submitter.attr('value') !== 'online') {
        setTipsValue();
        return;
    }

    // If the user has given his tip then continue with the form
    if (document.querySelector('input[name="tips"]').getAttribute('value') !== '') {
        return;
    }

    event.preventDefault();

    setTipsValue();

    // Show modal
    const modal = document.getElementById('tips-modal');
    modal.style.display = "block";
    modal.classList.add('show');

    document.getElementById('backdrop').style.display = "block";

    // Toggle custom tips input
    if (document.querySelectorAll('.tip-amount').length >= 1) {
        addListenerToTipAmountRadios();
    }

    // Add listener to custom value input
    addListenerToCustomTipInput();

    // Add listeners to form buttons
    addPayWithTipsListener();
    addPayNoTipsListener();
}

function addListenerToCustomTipInput() {
    document.getElementById('tip-custom-amount').addEventListener('change', function (event) {
        setTipsValue(validateDecimalValue(event.target.value));

    });

    document.getElementById('tip-custom-amount').addEventListener('click', function (event) {
        const option = document.querySelectorAll('.option');
        option.forEach(function (el) {
            el.setAttribute('class', 'option');
        });
    });
}

function addListenerToTipAmountRadios() {
    document.querySelectorAll('.tip-amount').forEach(function (element) {
        element.addEventListener('click', function () {
            // set the value of tip to current element val
            setTipsValue(element.getAttribute('value'));

            const option = document.querySelectorAll('.option');
            option.forEach(function (el) {
                el.setAttribute('class', 'option');
            });

            element.closest('.option').setAttribute('class', 'option checked');
            document.getElementById('tip-custom-amount').value = document.getElementById('tip-custom-amount').defaultValue;

        })
    })
}

/**
 * Validate if the input string is a floating point number
 * @param {string} value
 * @returns string|null
 */
function validateDecimalValue(value) {
    if (/^\s*$/.test(value) || isNaN(value)) {
        return 0;
    }

    // Also if the value is not positive
    if (Number(value) <= 0) {
        return 0;
    }

    return value;
}

function setTipsValue(value = 0) {
    document.querySelector('input[name="tips"]').setAttribute('value', value);
}

function showOverlay() {
    document.getElementById('payment-overlay').style.display = 'flex';
}

function addPayWithTipsListener() {
    const button = document.getElementById('pay-with-tips');

    button.addEventListener('click', function () {
        if (document.querySelector('input[name="tips"]').getAttribute('value') === '0') {
            return false;
        }

        // Proceed with the form
        document.querySelector('button[name="payment_method"][value="online"]').click();
        showOverlay();
    });
}

function addPayNoTipsListener() {
    const button = document.getElementById('pay-no-tips');

    button.addEventListener('click', function () {
        const total = parseFloat($('#total').text());

        if (isNaN(total) || total <= 0) {
            return;
        }

        setTipsValue();

        // Proceed with the form
        document.querySelector('button[name="payment_method"][value="online"]').click();
        showOverlay();
    });
}

class CartTotalsController {
    hiddenClass = 'customer-cart-totals__row--hidden';

    constructor() {
        this.$totalsTakeawayRow = $('#takeaway-discount');
        this.$takeawayDiscountValue = $('#takeaway-discount-value');

        this.$totalsCompanyRow = $('#company-discount');
        this.$companyDiscountValue = $('#company-discount-value');

        this.routes = {
            refresh: $('#cart-form').data('refresh-url'),
        }
    }

    update(customerType) {
        const widget = this;

        if (!widget.routes.refresh) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: widget.routes.refresh,
            data: {
                customer_type: customerType,
            },
            success: function (data) {
                if (data.success === true) {
                    widget.updateUI(data.totals);
                }
            }
        });
    }

    updateUI(totals) {
        $('#total').text(totals.total);

        if(totals.discount === true) {
            this.updateDiscount(totals.discount_type, totals.discount_value);
        } else {
            this.hideDiscount();
        }
    }

    updateDiscount(type, value) {
        if (type === 'takeaway') {
            this.$takeawayDiscountValue.text(value);
            this.$totalsTakeawayRow.css({'display': 'flex'});
            this.$totalsCompanyRow.addClass(this.hiddenClass);
        } else if (type === 'company') {
            this.$companyDiscountValue.text(value);
            this.$totalsCompanyRow.removeClass(this.hiddenClass);
            this.$totalsTakeawayRow.hide();
        }
        
    }

    hideDiscount() {
        this.$totalsTakeawayRow.hide();
        this.$totalsCompanyRow.addClass(this.hiddenClass);
    }
}

class CustomerTypeController {
    individualFields = ['first-name', 'last-name'];
    companyFields = ['company-name'];
    hiddenClass = 'form-control-wrapper--hidden';

    types = {
        individual: 'individual',
        company: 'company',
    }

    constructor(totalsControler) {
        this.totalsControler = totalsControler;
        this.$typeSelector = $('#company-order');

        this.attachEventListeners();
    }

    attachEventListeners() {
        const widget = this;

        widget.$typeSelector.on('change', function(e) {
            const $this = $(this);

            let customerType;

            // This was initially a select, but it was changed to a checkbox by client request
            // if it needs to be a select again, it should work fine (the select can be found in the template's git history)
            if ($this.is('input[type="checkbox"]')) {
                customerType = $this.is(':checked') ? widget.types.company : widget.types.individual;
                $this.val(customerType);
            } else {
                customerType = $this.val();
            }

            widget.show(customerType);

            widget.totalsControler.update(customerType);
        });
    }

    enableSelector() {
        this.$typeSelector.closest('.form-control-wrapper').removeClass(this.hiddenClass);
        this.show(this.$typeSelector.val());
    }

    show(type) {
        if (type === this.types.individual) {
            this.disableFields(this.companyFields);
            this.enableFields(this.individualFields);
        } else if (type === this.types.company) {
            this.disableFields(this.individualFields);
            this.enableFields(this.companyFields);
        }
    } 

    enableFields(fields) {
        fields.forEach(id => {
            const $field = $('#' + id);
            const $container = $field.closest('.form-control-wrapper');

            $field.attr('required', true);
            $container.removeClass('form-control-wrapper--hidden');
        });
    }

    disableFields(fields) {
        fields.forEach(id => {
            const $field = $('#' + id);
            const $container = $field.closest('.form-control-wrapper');

            $field.removeAttr('required');
            $container.addClass(this.hiddenClass);
        });
    }
}

$(document).ready(() => {

    const cartTotalsController = new CartTotalsController();
    const customerTypeController = new CustomerTypeController(cartTotalsController);

    // //Empty cart
    $('.cart-remove').on('click', function (e) {
        e.preventDefault();

        $('.overlay-btn').css({'display': 'flex', 'justify-content': 'center'});
    })

    $('.overlay-btn, .overlay-content .close-modal').on('click', function () {
        $('.overlay-btn, .overlay-content').css({'display': 'none'});
    })

    $('.remove-btn').on('click', function () {
        $.ajax({
            type: 'DELETE',
            url: $(this).data('href'),
            data: {},
            success: function (data) {
                if ($('.list').empty()) {
                    document.location.reload();
                }
            }
        });
    });

    $('.products__remove').on('click', function (e) {
        e.preventDefault();

        let product = $(this).closest('.products')
        let productId = product.data('product-id')
        let quantity = product.find('.quantity').val()

        $.ajax({
            type: 'POST',
            url: $(this).data('href'),
            data: {
                product_id: productId,
                quantity: quantity,
            },
            success: function (data) {
                product.remove()

                $('#total').text(data.total)

                if ($('.item-list').empty()) {
                    document.location.reload()
                }
            }
        });
    })

    if ($('.empty-cart').is(":visible")) {
        $('.cart-list').css({'overflow': 'hidden'});
    }
    $('.swipe-remove').on('touchstart', function (e) {
        $(e.target).swipeDetector({}, $(this));
    });

    let submitter = null;

    $("#right-away").click(function(){
        $(".form-control-group--pickup-later input, .form-control-group--pickup-later select").each(function(){
           if($(this).attr('required') !== undefined){
               $(this).removeAttr('required');
           }
        });
    });

    $("#pick-a-day").click(function(){
        $(".form-control-group--pickup-later input, .form-control-group--pickup-later select").each(function(){
            $(this).attr('required', 'required');
        });
    });

    $('button.payment[value="online"]').on('click', function (e) {
        submitter = $('button.payment[value="online"]');
    });

    $('button.payment[value="cash"]').on('click', function () {
        submitter = $('button.payment[value="cash"]');
    });

    $('#cart-form').on('submit', function (event) {
        showTipsPopup(event, submitter);
        submitter = null;
    });

    $('.cart-form__service-method').on('click', function(e) {
        e.preventDefault();
        const $this = $(this);
        const serviceMethod = $this.data('service-method');
        const $totalsTakeawayRow = $('#takeaway-discount');

        $('#service-method').val(serviceMethod);

        $('body').addClass('page--show-' + serviceMethod);

        const takeaway = $this.data('takeaway');

        if (takeaway !== 'yes') {
            $totalsTakeawayRow.hide();
        }

        $('.main-cart').addClass('main-cart--payment');

        if ($this.data('show-details-form') === true) {
            $('body').addClass('page--show-pickup-details');
        }

        if (serviceMethod === 'takeaway') {
            $('.cart-form__group--delivery').find('input, select').removeAttr('required');
            $('.cart-form__group--offsite').find('input, select').removeAttr('required');
            $('.cart-form__group--takeaway').find('input:not(.cart-form__pickup), select').removeAttr('required');
            
        } else if (serviceMethod === 'delivery') {
            $('.cart-form__group--takeaway').find('input, select').removeAttr('required');
            customerTypeController.enableSelector();
        }

        $.ajax({
            type: 'POST',
            url: $this.data('href'),
            data: {
                takeaway: takeaway,
            },
            success: function (data) {
                $('#total').text(data.total);
                if(data.takeaway_discount != 0) {
                    $('#takeaway-discount-value').text(data.takeaway_discount);
                    $totalsTakeawayRow.css({'display': 'flex'});
                } else {
                    $totalsTakeawayRow.hide();
                }
            }
        });
    });

    $('.cart-form__group--takeaway .cart-form__pickup').on('change', function(e) {
        const pickupTime = $(this).val();
        const $pickupInfo = $('.form-control-group--pickup-later');
        const $body = $('body');
        const visibleClass = 'form-control-group--visible';
        const showOffsiteClass = 'page--show-offsite';

        if (pickupTime === 'later') {
            $pickupInfo.addClass(visibleClass);
            $body.addClass(showOffsiteClass);
            customerTypeController.show(customerTypeController.types.individual);
            $('.cart-form__group--takeaway').find('input, select').attr('required', true);
        } else {
            $pickupInfo.removeClass(visibleClass);
            $body.removeClass(showOffsiteClass);
            $('.cart-form__group--offsite').find('input, select').removeAttr('required');
            $('.cart-form__group--takeaway').find('input:not(.cart-form__pickup), select').removeAttr('required');
        }
    });

    $('#pickup-day').on('change', function(e) {
        const $timeGrid = $('#pickup-time-grid');
        const filteredClass = 'form-control-grid--later'

        if ($(this).children('option:selected').data('day') === 'today') {
            $timeGrid.addClass(filteredClass);
        } else {
            $timeGrid.removeClass(filteredClass);
        }

        if ($timeGrid.hasClass('form-control-grid--expanded')) {
            $timeGrid.find('.form-control-toggle').trigger('click');
        }

        $timeGrid.find('.customer-radio-button:checked').prop('checked', false);
    });

    $('.form-control-toggle').on('click', function(e) {
        e.preventDefault();

        $(this).closest('.form-control-grid').toggleClass('form-control-grid--expanded');
    });

    $('#delivery-city').on('change', function(e) {
        const fee = $(this).children('option:selected').data('amount');

        if (fee) {
            $('#delivery-fee-value').html(fee);
            $('#delivery-fee').removeClass('customer-cart-totals__row--hidden');
        } else {
            $('#delivery-fee-value').html('fee');
            $('#delivery-fee').addClass('customer-cart-totals__row--hidden');
        }
    });
});

//Quantity ++
$('.add').click(function (e) {
    e.preventDefault();

    const $this = $(this);
    const $product = $this.closest('a');
    const $quantityInput = $this.siblings('.quantity');

    const quantity = parseInt($quantityInput.val()) + 1;

    $quantityInput.val(quantity);
    $this.siblings('.remove').removeClass('customer-cart-item__quantity-button--disabled');

    updateCart($product, quantity)
});

// Quantity--
$('.remove').click(function (e) {
    e.preventDefault();

    const $this = $(this);
    const disabledClass = 'customer-cart-item__quantity-button--disabled';

    if ($this.hasClass(disabledClass)) {
        return;
    }

    const $product = $this.closest('a');
    const $quantityInput = $this.siblings('.quantity');

    let quantity = parseInt($quantityInput.val());

    if (quantity < 2) {
        return;
    }

    quantity -= 1;
    $quantityInput.val(quantity);

    if (quantity === 1) {
        $this.addClass(disabledClass);
    }

    updateCart($product, quantity);
});

$('.quantity').on('change', function(e) {
    const $this = $(this);
    const $product = $this.closest('a');
    const disabledClass = 'customer-cart-item__quantity-button--disabled';

    let quantity = parseInt($this.val());

    if (isNaN(quantity) || quantity < 1) {
        quantity = 1;
        $this.val(quantity);
    }

    if (quantity > 1) {
        $this.siblings('.remove').removeClass(disabledClass);
    } else {
        $this.siblings('.remove').addClass(disabledClass);
    }

    updateCart($product, quantity);
});

function updateCart($product, quantity) {
    $("button[name='payment_method']").prop('disabled', true)
    $.ajax({
        type: 'POST',
        url: $product.data('href'),
        data: {
            product_id: $product.data('id'),
            quantity: quantity,
        },
        success: function (data) {
            $("button[name='payment_method']").prop('disabled', false)
            $('#total').text(data.total)
            $product.find('.price').text(`CHF ${data.productTotal}`);

        }
    });
}

(function ($) {
    $.fn.swipeDetector = function (options, slider) {

        // States: 0 - no swipe, 1 - swipe started, 2 - swipe released
        var swipeState = 0;
        // Coordinates when swipe started
        var startX = 0;
        var startY = 0;
        // Distance of swipe
        var pixelOffsetX = 0;
        var pixelOffsetY = 0;
        // Target element which should detect swipes.
        var swipeTarget = this;
        var defaultSettings = {
            // Amount of pixels, when swipe don't count.
            swipeThreshold: 100,
            // Flag that indicates that plugin should react only on touch events.
            // Not on mouse events too.
            useOnlyTouch: true
        };

        // Initializer
        (function init() {
            options = $.extend(defaultSettings, options);
            // Support touch and mouse as well.
            swipeTarget.on('mousedown touchstart', function (event) {
                swipeStart(event);
            });
            $('html').on('mouseup touchend', function (event) {
                swipeEnd(event, slider);
            });
            $('html').on('mousemove touchmove', swiping);
        })();

        function swipeStart(event) {
            if (options.useOnlyTouch && !event.originalEvent.touches)
                return;

            if (event.originalEvent.touches)
                event = event.originalEvent.touches[0];

            if (swipeState === 0) {
                swipeState = 1;
                startX = event.clientX;
                startY = event.clientY;
            }
        }

        function swipeEnd(event, slider) {

            if (swipeState === 2) {
                swipeState = 0;
                if (Math.abs(pixelOffsetX) > Math.abs(pixelOffsetY) &&
                    Math.abs(pixelOffsetX) > options.swipeThreshold) { // Horizontal Swipe
                    if (pixelOffsetX < 0) {
                        swipeTarget.trigger($.Event('swipeLeft.sd'));
                        slider.css({'transform': 'translate(-104px)', 'transition': '0.5s'});
                    } else {
                        swipeTarget.trigger($.Event('swipeRight.sd'));
                        slider.css({'transform': 'translate(0)'});
                    }
                }
            }
        }

        function swiping(event) {
            // If swipe don't occuring, do nothing.
            if (swipeState !== 1)
                return;


            if (event.originalEvent.touches) {
                event = event.originalEvent.touches[0];
            }

            var swipeOffsetX = event.clientX - startX;
            var swipeOffsetY = event.clientY - startY;

            if ((Math.abs(swipeOffsetX) > options.swipeThreshold) ||
                (Math.abs(swipeOffsetY) > options.swipeThreshold)) {
                swipeState = 2;
                pixelOffsetX = swipeOffsetX;
                pixelOffsetY = swipeOffsetY;

            }
        }

        return swipeTarget; // Return element available for chaining.
    }
}(jQuery));
