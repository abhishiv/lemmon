import { showGroupPopup } from "./menu/group-popup.js";

class AddToCartButton {
    constructor() {
        this.$singleItemPrice = $('.product-price__amount');
        this.$quantity = $('.add-to-cart-button__quantity');
        this.$price = $('.add-to-cart-button__price');
    }

    init() {
        this.updateQuantity($('.single-product__quantity-input').val());
        this.updatePrice(this.$singleItemPrice.text());
        this.updateOnInput();
    }

    updateQuantity(quantity) {

        if (!isNaN(quantity)) {
            this.$quantity.html(quantity);
            this.updatePrice(this.$singleItemPrice.text());
        }

    }

    updatePrice(price) {
        const quantity = parseInt(this.$quantity.text());

        const total = Number(price * quantity).toFixed(2);

        this.$price.html(total);
    }

    updateOnInput() {
        const widget = this;

        $('.single-product__quantity-input').on('input', function(e) {
            const $this = $(this);

            let newQuantity = parseInt($this.val());

            if (isNaN(newQuantity)) {
                newQuantity = 1;  
            }

            $this.val(newQuantity);
            widget.updateQuantity(newQuantity);
        });
    }
}

const addToCartButton = new AddToCartButton();

$(document).ready(() => {

    let serviceScroll,
        categoryScroll,
        subServiceScroll;

    const restaurantId = $('#header').data('restaurant-id');

    let selectedService = 0;

    const scrollToLocalStoragePosition = () => {
        showGroupPopup();

        // If the session is new, then do not scroll to saved position
        if (document.referrer === '') {
            return false;
        }

        // check if selectedService is not null
        if (restaurantId !== undefined
            && restaurantId !== null
            && localStorage.getItem("menu-selected-service-" + restaurantId.toString()) !== null
            && localStorage.getItem("menu-selected-service-" + restaurantId.toString()) !== '0'
            && $('#service .menu__list__item a[data-menu-id="' + localStorage.getItem("menu-selected-service-" + restaurantId.toString()).toString() + '"]').length !== 0) {
            // Setting a timeout because if this is called instantly after load
            // Then the click listener is not yet set up
            setTimeout(() => {
                // $('#service .menu__list__item a[data-menu-id="' + localStorage.getItem("menu-selected-service-" + restaurantId.toString()).toString() + '"]').trigger('click');
                clickService($('#service .menu__list__item a[data-menu-id="' + localStorage.getItem("menu-selected-service-" + restaurantId.toString()).toString() + '"]').get(0), true);

                if (localStorage.getItem("menu-scroll-position-" + restaurantId.toString()) !== null && localStorage.getItem("menu-scroll-position-" + restaurantId.toString()) !== undefined) {
                    window.scrollTo({
                        top: localStorage.getItem("menu-scroll-position-" + restaurantId.toString()),
                        behavior: "instant",
                    });
                }
            }, 0);
            return;
        }

        if (restaurantId !== undefined && localStorage.getItem("menu-scroll-position-" + restaurantId.toString()) !== null) {
            window.scrollTo({
                top: localStorage.getItem("menu-scroll-position-" + restaurantId.toString()),
                behavior: "instant",
            });
        }

        setTimeout(() => {
            $(window).trigger('scroll');
        }, 100)
    }

    const saveToLocalStoragePosition = () => {
        if (restaurantId === undefined || restaurantId === null) {
            return false;
        }

        // Save scroll position
        localStorage.setItem("menu-scroll-position-" + restaurantId.toString(), $(window).scrollTop())

        // Save selected service
        localStorage.setItem("menu-selected-service-" + restaurantId.toString(), selectedService);
    }

    var isComplete;
    //time welcome page
    if ($('.success-payment').length) {
        $('body').css({'overflow': 'hidden'});

        setTimeout(function () {
            $('.success-payment').remove();
            $('body').css({'overflow': 'scroll'});

            scrollToLocalStoragePosition();
        }, 3000);
    } else {
        scrollToLocalStoragePosition();
    }

    //menu category scroll
    const scrollToElement = (el) => {
        const headerOffset = document.getElementById('menu-list').offsetTop;
        const elementPosition = el.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
            top: offsetPosition,
            behavior: "smooth"
        });
    }

    /**
     * Scroll to bottom of menu
     */
    const scrollToBottom = () => {
        $("html, body").animate({scrollTop: $(document).height() - $(window).height()});
    }

    /**
     * Scroll to top of menu
     */
    const scrollToTop = () => {
        $("html, body").animate({scrollTop: 0});
    }

    const scrollToElementOverlayScrollbar = (el, type = '') => {
        if (!(el instanceof jQuery)) {
            el = $(el);
        }

        if (el.length === 0 || el === undefined) {
            return;
        }

        let osInstance;

        // get the os instance based on the element's type
        if (type === 'service') {
            osInstance = serviceScroll.getElements();
        }

        if (type === 'subservice') {
            osInstance = subServiceScroll.getElements();
        }

        if (type === 'category') {
            osInstance = categoryScroll.getElements();
        }

        // Get the viewport
        const {viewport} = osInstance;

        // Get the position of the element
        const left = el.position().left - viewport.getBoundingClientRect().left - window.innerWidth / 2 + el.width();

        // Scroll the viewport to the position
        viewport.scrollTo({top: 0, left: left > 0 ? left : 0})
    }

    const hideAllItems = (except) => {
        $('#menu-list > div').not('.empty').hide();
        $(except).show();
    }

    const unhideItems = () => {
        $('#menu-list > div').not('.empty').show();
        selectedService = 0;
    }

    // Services + 'All' category click to scroll
    $('#service .menu__list__item a').on('click', (e) => clickService(e.currentTarget));

    // Subservice click to scroll
    $('#subservice .menu__list__item a').on('click', (e) => {
        // Unhide elements
        const firstSubservice = getFirstSubserviceId();
        if (firstSubservice !== false && firstSubservice === $(e.target).data('category-id')) {
            scrollToTop();
            return;
        }

        scrollToElement($('#subservice-' + $(e.currentTarget).data('category-name').toString()).get(0));
    });

    // Category click to scroll
    $('#category .menu__list__item a').on('click', (e) => {
        // Unhide elements
        unhideItems();

        const lastCateg = getLastCategoryId();

        if (lastCateg !== false && lastCateg === $(e.target).data('item-id')) {
            scrollToBottom();
            return;
        }

        scrollToElement($('#category-' + $(e.currentTarget).data('item-id').toString()).get(0));
    })

    /**
     * Handle the click event on a service
     * 1. Scroll to the top of the viewport
     * 2. If the service has been loaded from local storage, then reset to previous scroll position
     * @param {DOM Element} el - the anchor tag in the header associated to the service
     * @param {boolean} onLoad - true if the function was called on the loading of the page (so the scroll pos can be restored), false if otherwise
     */
    const clickService = (el, onLoad = false) => {
        if ($(el).data('menu-id') === 0) {
            // All

            // Unhide elements
            unhideItems();

            scrollToElement($('div[data-service-id="' + $(el).data('menu-id').toString() + '"].products').get(0));
        } else {
            hideAllItems($('div[data-service-id="' + $(el).data('menu-id').toString() + '"].products').get(0));
            selectedService = $(el).data('menu-id');

            if(localStorage.getItem("menu-selected-service-" + restaurantId.toString()) !== selectedService.toString() || onLoad === false) {
                scrollToTop();
            }
        }

        $(window).trigger('scroll');
    }

    const elementViewportPosition = (el) => {
        return el.getBoundingClientRect().top - (document.getElementById('menu-list').offsetTop + 1);
    }

    const showServiceHeader = (service) => {

        $('#service').find('.menu__list__item--active').removeClass('menu__list__item--active');
        $('#subservice').find('.menu__list__item--active').removeClass('menu__list__item--active');

        $('#category').hide();
        $('#subservice').show();
        $('#subservice .menu__list__item').hide();;
        $('#subservice .menu__list__item[data-subservice-id="' + service.toString() + '"]').show();

        scrollToElementOverlayScrollbar($('#service a[data-menu-id="' + service.toString() + '"]').parent(), 'service')

        // Get the subcategory in the viewport
        const subCategories = $('#service-' + service.toString() + " .category-container");
        let currentSubCategory = subCategories.get(0);
        subCategories.each((i, el) => {
            if (elementViewportPosition(el) > elementViewportPosition(currentSubCategory) && elementViewportPosition(el) < 0) {
                currentSubCategory = el;
            }
        })
        currentSubCategory = $(currentSubCategory);

        if ($(window).scrollTop() === 0
            && currentSubCategory.data('subservice-id') !== getFirstSubserviceId()
            && getFirstSubserviceId() !== false) {

            currentSubCategory = $(subCategories.get(0));
        }

        if (Math.abs(Math.floor($(window).scrollTop()) + $(window).height() - $(document).height()) < 5 && selectedService !== 0) {
            // highlight the last one
            currentSubCategory = $(subCategories.get(subCategories.length - 1));
        }

        $('a[data-category-name="' + service.toString() + '-' + currentSubCategory.data('subservice-id') + '"]').parent().addClass('menu__list__item--active');

        // Scroll to subservice
        scrollToElementOverlayScrollbar($('a[data-category-name="' + service.toString() + '-' + currentSubCategory.data('subservice-id') + '"]').parent(), 'subservice');
    }

    $(window).scroll(() => window.requestAnimationFrame(() => {
        if (selectedService !== 0) {
            showServiceHeader(selectedService);

            $('[data-menu-id="' + selectedService.toString() + '"]').parent().addClass('menu__list__item--active');
            return;
        }

        $(".products").filter((i, el) => (elementViewportPosition(el) < 0)).each((i, el) => {
            const element = $(el);

            let category = element.data('categ-id'),
                service = element.data('service-id');

            if (service !== 0) {
                // hide categories
                showServiceHeader(service);
            } else {
                // If last category is too short, then it will never be highlighted
                // So verify if scroll is at bottom and if the current category is not the last one to be shown
                if (Math.abs(Math.floor($(window).scrollTop()) + $(window).height() - $(document).height()) < 5) {
                    const lastCateg = getLastCategoryId();
                    if (lastCateg !== false && lastCateg !== category) {
                        category = lastCateg;
                    }
                }

                // hide services
                $('#category').show()
                $('#subservice').hide();
                $('#category .menu__list__item').css("color", "#B7E9FD");
                $('#category .menu__list__item[data-category-id="' + category.toString() + '"]').css("color", "#fff");

                $('#category li.menu__list__item a').css('color', '#B7E9FD');
                $('#category li.menu__list__item[data-category-id="' + category.toString() + '"] a').css("color", "#fff");

                // Scroll to 'All' service
                scrollToElementOverlayScrollbar($('#service a[data-menu-id="0"]').parent(), 'service')

                // Scroll to the category
                scrollToElementOverlayScrollbar($('#category li.menu__list__item[data-category-id="' + category.toString() + '"]'), 'category');
            }

            $('[data-menu-id="' + $(el).attr('data-service-id') + '"]').parent().addClass('menu__list__item--active');
        })
    }));

    // Trigger a scroll in order to highlight correct service/subservice/category
    $(window).trigger('scroll');

    /**
     * Get the id of the first subservice to be shown
     * @returns {Boolean | Number} - (false if there are no subservices)
     */
    function getFirstSubserviceId() {
        let id = -1;

        // const subservices = $('#subservice .menu__list__item a');
        const services = $('#menu-list .products');

        if (services.length === 0) return false;

        const first = services[0],
            service = $(first).data('service-id');

        if (first === undefined || first === null || service === 0 || service === undefined || service === null) {
            return false;
        }

        const subServices = $(first).children('.category-container');

        if (subServices.length === 0) return false;

        const firstSubservice = subServices[0];

        if (firstSubservice === undefined || firstSubservice === null) {
            return false;
        }

        id = $(firstSubservice).data('subservice-id');

        if (id === undefined || id === null || typeof id !== 'number') {
            return false;
        }

        return id;
    }


    /**
     * Get the id of the last category to be shown
     * @returns {Boolean | Number} - (false if there are no categories)
     */
    function getLastCategoryId() {
        let id = -1;

        const categories = $('[data-service-id="0"]');

        if (categories.length === 0) return false;

        const last = categories[categories.length - 1];

        if (last === undefined || last === null) {
            return false;
        }

        id = $(last).data('categ-id');

        if (id === undefined || id === null || typeof id !== 'number') {
            return false;
        }

        return id;
    }

    if ($('#category').length) {
        // orizontal category scrollbar
        categoryScroll = OverlayScrollbars(document.querySelector('#category'), {
            className: "menu",
            resize: "horizontal",
            sizeAutoCapable: true,
            paddingAbsolute: true,
            overflowBehavior: {
                y: 'visible-hidden'
            },
            scrollbars: {
                visibility: 'hidden'
            }
        });
    }

    if ($('#service').length) {
        // orizontal menu scrollbar
        serviceScroll = OverlayScrollbars(document.querySelector('#service'), {
            className: "menu",
            resize: "horizontal",
            sizeAutoCapable: true,
            paddingAbsolute: true,
            overflowBehavior: {
                y: 'visible-hidden'
            },
            scrollbars: {
                visibility: 'hidden'
            }
        });
    }

    if ($('#subservice').length) {
        // orizontal menu scrollbar
        subServiceScroll = OverlayScrollbars(document.querySelector('#subservice'), {
            className: "menu",
            resize: "horizontal",
            sizeAutoCapable: true,
            paddingAbsolute: true,
            overflowBehavior: {
                y: 'visible-hidden'
            },
            scrollbars: {
                visibility: 'hidden'
            }
        });
    }
    //Toggle service subcategories
    // $('#service a').click(function () {
    //     if ($(this).attr('id') === 'reset') {
    //         $('#subservice').hide()
    //         $('#category').show()
    //         return;
    //     }
    //     $('#subservice li, #category').hide()
    //     $('#subservice').show()
    //     $(`#subservice a[data-item-id='${$(this).data('item-id')}']`).closest('li').show()
    // })

    $('.header-top__icons__search').click(function () {
        $('.header__search').toggle().css({'display': 'block'});
        $('.header-top__icons__search').hide();
        $('.header-top__cart').hide();
        $('.header-top__close').show();
        $('.menu-title').hide();
        $('.header-top__review').hide();
    });

    $(function () {
        $('.header-top__icons__search').click(function () {
            $('.search-title').show();
            $(".header").addClass("shadow");
        });
    });

    $('.header-top__close').click(function () {
        $(".header").removeClass("shadow");
        $('.header__search').toggle();
        $('.header-top__close').hide();
        $('.search-title').hide();
        $('.header-top__icons__search').show();
        $('.header-top__cart').show();
        $('.header-top__review').show();
        $('#filter').val('');
        filter($('#filter').val());
        $('.menu-title').show();
    });

    // searchbar functionality
    $("#filter").on("keyup", function () {
        filter($(this).val());
        countItems();
    });

    function filter(string) {
        let value = string.toLowerCase();
        let all = 0;
        if (string === '') {
            $('.empty').css({'display': 'none'});
            $(".products, .category-container, .category-container a").show();
            return;
        }
        $(".products").each(function () {
            let el = 0;
            all += $(this).data('count');
            $(this).find('.products__list__item__title').filter(function () {
                if ($(this).text().toLowerCase().indexOf(value) === -1) {
                    $(this).closest('a').hide();
                    el++;
                    all--;
                } else {
                    $(this).closest('a').show();
                    el--;
                }
            });

            if ($(this).data('count') == el) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });

        if (!all) {
            $('.empty').css({'display': 'flex'});
        } else {
            $('.empty').css({'display': 'none'});
        }
    }

    function countItems() {
        $(".category-container").each(function () {
            $(this).show();
            if ($(this).find('a:visible').length === 0) {
                $(this).hide();
            }
        });
    }

    //Quantity ++
    $('.add').click(function (e) {
        e.preventDefault();

        const $this = $(this);

        $this.addClass('active-btn');

        const $quantity = $this.siblings('.quantity');

        let newQuantity = parseInt($quantity.val()) + 1;

        if (isNaN(newQuantity)) {
            newQuantity = 1;
        }

        $quantity.val(newQuantity);
        addToCartButton.updateQuantity(newQuantity);

        removeActiveClass();
    });

    function removeActiveClass() {
        setTimeout(
            function () {
                $('.add, .remove').removeClass('active-btn');
            }, 150);
    }

    // Quantity--
    $('.remove').click(function (e) {
        e.preventDefault();

        const $this = $(this);

        $this.addClass('active-btn');

        const $quantity = $this.siblings('.quantity');

        const currentQuantity = parseInt($quantity.val());

        if (currentQuantity > 1) {
            $quantity.val(currentQuantity - 1);
            addToCartButton.updateQuantity(currentQuantity - 1);
        }

        removeActiveClass();
    });

    //Update cart and cart count
    $(".product-cart--bottom").click(function () {
        if (isComplete == false) {
            return
        }
        isComplete = true;
        let bundle = document.querySelectorAll('.inp-cbx[type="checkbox"]:checked');

        let isValid = true;
        $('.extra-items-box').each(function () {
            let parent = $(this);
            let min_limit = parent.data('min-limit');

            let checkedCount = parent.find('.extra-item input[type="checkbox"]:checked').length;

            if (checkedCount < min_limit) {
                //scroll into view center
                parent[0].scrollIntoView({behavior: "smooth", block: "center"});

                //animate by growing and srinking the span badge element without ading classes, just js
                let badge = parent.closest('.extras-box').find('.badge');
                badge.addClass('smooth-scale'); // Adaugă clasa pentru a face tranziția

                setTimeout(function() {
                    badge.removeClass('smooth-scale'); // Elimină clasa pentru a revini la scala normală
                }, 500);

                isValid = false;
            }
        });

        if(!isValid){
            return
        }
        let resultArray = { products: {}, extras: {}, removables: {} };

        bundle.forEach(function(element) {
            const name = element.getAttribute('name');
            const [type, bundleId, productId, price, decimal] = name.split('.'); // Split the name attribute by period
            // Convert the price to a float (you can use parseFloat or parseInt as needed)
            const priceFloat = parseFloat(price + '.' + decimal)

            // Check if the type (e.g., products, extras, removables) exists in resultArray
            if (!resultArray[type]) {
                resultArray[type] = {};
            }

            // Check if the bundleId exists in resultArray[type]
            if (!resultArray[type][bundleId]) {
                resultArray[type][bundleId] = {};
            }

            // Add the product ID and price to the resultArray
            resultArray[type][bundleId][productId] = priceFloat;
        });

        // Now resultArray should be populated based on the extracted values

        let product = $(this).closest('.single').data('product-id');
        let quantity = $('#qty').val();
        let notes = $('textarea').val();
        $.ajax({
            type: 'POST',
            url: $(this).data('href'),
            data: {
                product_id: product,
                quantity: quantity,
                itemNotes: notes,
                bundle: resultArray,
            },
            success: function (data, status) {
                if (data.count > 0) {
                    $('.empty_cart').hide();
                    $('.full_cart').show();
                }
                $('.btn-animation').css({'transform': 'translate(0, 0)'});
                setTimeout(
                    function () {
                        // $('#lemmon svg').css({'scale': '1.3'});
                        window.location = $('.product-header__close').attr('href');
                    }, 500);

            }
        });
    });

    // Attach on scroll save position event
    $(window).on("scroll", () => window.requestAnimationFrame(() => saveToLocalStoragePosition()));

    $('.extra-item__checkbox').prop('checked', false);

    $('.extra-item__checkbox').on('click', function(e) {
        const $this = $(this);
        const $bundleGroup = $this.closest('.extra-items-box');
        const maxLimit = parseInt($bundleGroup.data('max-limit'));

        if ($bundleGroup.find('.extra-item__checkbox:checked').length > maxLimit && $this.prop('checked') === true) {
            if (maxLimit === 1) {
                $bundleGroup.find('.extra-item__checkbox:checked').not($this).prop('checked', false);
            } else {
                e.preventDefault();
            }
        }
    });

    $('.extra-item__checkbox').on('change', function(e) {
        const mainProductPrice = parseFloat($('.product-price').data('price'));
        const selectedBundleItemsPrice = getBundleItemsTotal();

        const total = new Intl.NumberFormat('de-CH', { 
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(
            mainProductPrice + selectedBundleItemsPrice,
        );

        $('.product-price__amount').html(total);
        addToCartButton.updatePrice(total);
    });
    
    function getBundleItemsTotal() {
        let total = 0;

        const prices = $('.extra-item__checkbox:checked').map(function() {
            return parseFloat($(this).data('price'));
        }).get();

        return prices.reduce((accumulator, currentValue) => accumulator + currentValue, total);
    }

    addToCartButton.init();
});