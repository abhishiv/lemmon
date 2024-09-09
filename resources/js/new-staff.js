class LemmonUtility {

    breakpoint = 1024;
    breakpointCrossed = false;

    constructor() {
        this.referenceWidth = window.innerWidth;

        this.initRefresh();
    }

    static isSmallScreen() {
        return window.matchMedia('(max-width: 1024px)').matches;
    }

    hasCrossedBreakpoint() {
        return this.breakpointCrossed;
    }

    checkIfBreakpointWasCrossed() {

        if (window.innerWidth <= this.breakpoint && this.referenceWidth > this.breakpoint) {
            // if the width was larger before, and now it's smaller, then it did
            return true;
        } else if (window.innerWidth > this.breakpoint && this.referenceWidth <= this.breakpoint) {
            // if the width was smaller before, and now it's larger, then it did
            return true;
        }

        return false;
    }

    initRefresh() {
        const widget = this;

        $(window).on('resize', function (e) {
            widget.breakpointCrossed = widget.checkIfBreakpointWasCrossed();
            widget.referenceWidth = window.innerWidth;
        })
    }

    // this method expects a jQuery object
    hasVerticalScroll($element) {
        const element = $element[0];

        if (element) {
            return element.clientHeight < element.scrollHeight;
        }
    }
}

const lemmonUtility = new LemmonUtility();

$('.dropdown').each(function () {
    const $this = $(this);

    $this.select2({
        minimumResultsForSearch: Infinity,
        width: 'element',
        selectionCssClass: ':all:',
        dropdownCssClass: ':all:',
        dropdownParent: $this.parent(),
    });
});

$('.dashboard').attr('data-views-filter', $('#staff-orders-views-filter').val());

$('#staff-orders-views-filter').on('select2:select', function (e) {
    const filter = $(this).val();
    const hideFilter = 'header__filter-wrapper--hidden';
    const $filterWrapper = $('#staff-orders-status-filter').closest('.header__filter-wrapper');

    $('.dashboard').attr('data-views-filter', filter);

    if (filter === 'bar' || filter === 'restaurant') {
        $filterWrapper.addClass(hideFilter);
    } else {
        $filterWrapper.removeClass(hideFilter);
    }
}).trigger('select2:select');

$('.dashboard').attr('data-day-filter', $('#staff-orders-day-filter').val());

$('#staff-orders-day-filter').on('select2:select', function (e) {
    const filter = $(this).val();

    $('.dashboard').attr('data-day-filter', filter);
});

$('.dashboard').attr('data-status-filter', $('#staff-orders-status-filter').val());

$('#staff-orders-status-filter').on('select2:select', function (e) {
    const filter = $(this).val();

    $('.dashboard').attr('data-status-filter', filter);
});

$('#staff-orders-table-filter').on('select2:select', function (e) {
    const table = $(this).val();
    const $dashboard = $('.dashboard');
    const filteredFlag = 'dashboard--filtered-by-table';
    const tableVisible = 'order-card--table-visible';


    $(`.${tableVisible}`).removeClass(tableVisible);

    if (table === 'all') {
        $dashboard.removeClass(filteredFlag);
    } else {
        $dashboard.addClass(filteredFlag);
        $(`.order-card[data-table="${table}"]`).addClass(tableVisible);
    }
}).trigger('select2:select');

function toggleItemsStatus(orderID, type = null, foodTypeId = null) {
    const $main = $('.main');

    let scrollPosition = {
        vertical: window.scrollY,
        horizontal: $main.scrollLeft(),
    };

    $.ajax({
        type: "PUT",
        url: $('.dashboard').data('put-url'),
        data: {
            orderID,
            type,
            foodTypeId
        },
        success: function (data) {
            $('.dashboard .section__inner').html(data);

            $('#staff-orders-table-filter').trigger('select2:select');

            if (scrollPosition.vertical !== 0) {
                window.scroll({
                    left: 0,
                    top: scrollPosition.vertical,
                    behavior: "instant",
                });
            }

            if (scrollPosition.horizontal !== 0) {
                $main.scrollLeft(scrollPosition.horizontal);
            }
        }
    })
}

$('.dashboard').on('click', '.order-card__toggle', function (e) {
    const $this = $(this);
    const $orderCard = $this.closest('.order-card');
    const orderId = $orderCard.data('id');

    toggleItemsStatus(orderId, $this.data('items-type'), $this.data('food-type-id'));
});

$('.dashboard').on('click', '.order-card__close', function (e) {
    const $this = $(this);
    const $orderCard = $this.closest('.order-card');
    const newStatus = $this.data('new-status');
    const orderId = $orderCard.data('id');
    const $main = $('.main');

    if ($this.data('checkout') === 'yes') {
        checkout.show({
            table: $orderCard.find('.order-card__table').text(),
            orderId: orderId,
        });

        return;
    }

    let scrollPosition = {
        vertical: window.scrollY,
        horizontal: $main.scrollLeft(),
    };

    $.ajax({
        type: "PUT",
        url: $('.dashboard').data('put-url'),
        data: {
            orderID: orderId,
            orderStatus: newStatus,
        },
        success: function (data) {
            $('.dashboard .section__inner').html(data);

            $('#staff-orders-table-filter').trigger('select2:select');

            if (scrollPosition.vertical !== 0) {
                window.scroll({
                    left: 0,
                    top: scrollPosition.vertical,
                    behavior: "instant",
                });
            }

            if (scrollPosition.horizontal !== 0) {
                $main.scrollLeft(scrollPosition.horizontal);
            }
        }
    });

});

$('.dashboard').on('click', '.order-card__menu-toggle', function (e) {
    $(this).closest('.order-card__menu').toggleClass('order-card__menu--open');
});

$('#restaurant-tables').on('click', '.lemmon-table__actions-toggle', function (e) {
    $('.lemmon-table__actions-buttons').removeClass('lemmon-table__actions--open');
    $(this).closest('.lemmon-table__actions-buttons').toggleClass('lemmon-table__actions--open');
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('.lemmon-table__actions-buttons, .lemmon-table__actions-toggle').length) {
        // Clicked outside actions buttons or toggle button
        $('.lemmon-table__actions-buttons').removeClass('lemmon-table__actions--open');
    }
});

$('.dashboard').on('click', '.order-card__cancel', function (e) {
    const cancelUrl = $(this).attr('data-url');
    const $confirmationDialog = $('#order-cancel-confirmation');

    $confirmationDialog.find('.lemmon-modal__confirm').attr('data-url', cancelUrl);
    $confirmationDialog.addClass('lemmon-modal--open');
});

$('#order-cancel-confirmation .lemmon-modal__confirm').on("click", function (e) {
    const url = $(this).attr("data-url");
    const $main = $('.main');

    let scrollPosition = {
        vertical: window.scrollY,
        horizontal: $main.scrollLeft(),
    };

    jQuery.ajax({
        type: 'DELETE',
        url,
        success: function (data) {
            $('.dashboard .section__inner').html(data);

            $('#staff-orders-table-filter').trigger('select2:select');

            if (scrollPosition.vertical !== 0) {
                window.scroll({
                    left: 0,
                    top: scrollPosition.vertical,
                    behavior: "instant",
                });
            }

            if (scrollPosition.horizontal !== 0) {
                $main.scrollLeft(scrollPosition.horizontal);
            }

            $('#order-cancel-confirmation').removeClass('lemmon-modal--open');
        },
        error: function (data) {
        }
    })
});

$('.lemmon-modal:not(.lemmon-modal--improved) .lemmon-modal__close, .lemmon-modal:not(.lemmon-modal--improved) .lemmon-modal__cancel').on('click', function (e) {
    e.preventDefault();

    $(this).closest('.lemmon-modal').removeClass('lemmon-modal--open');
});

function attemptPageReload() {
    if (printerManager.activeJobsExist()) {
        setTimeout(attemptPageReload, 5000);
    } else {
        setTimeout(function () {
            location.reload();
        }, 1000);
    }
}

function refreshOrders() {
    const $dashboard = $('.dashboard');
    const $main = $('.main');

    let scrollPosition = {
        vertical: window.scrollY,
        horizontal: $main.scrollLeft(),
    };

    $.ajax({
        type: 'GET',
        url: $dashboard.data('get-url'),
        success: function (data) {
            $dashboard.find('.section__inner').html(data);

            $('#staff-orders-table-filter').trigger('select2:select');

            if (scrollPosition.vertical !== 0) {
                window.scroll({
                    left: 0,
                    top: scrollPosition.vertical,
                    behavior: "instant",
                });
            }

            if (scrollPosition.horizontal !== 0) {
                $main.scrollLeft(scrollPosition.horizontal);
            }
        },
        complete: function (data) {
            if (data.status != 200) {
                attemptPageReload();
            }
        }
    })
}

$('a[href]:not([target="_blank"]), form button[type="submit"]').on('click', function (e) {
    if (printerManager.activeJobsExist()) {
        e.preventDefault();
        printerManager.infoBubble.showDefault();
    }
});

if ($('body').hasClass('page-orders')) {
    setInterval(refreshOrders, 15 * 1000);
}

class LemmonNotification {
    visibleClass = 'lemmon-notification--visible';

    constructor(id) {
        this.$bubble = $('#' + id);
    }

    flash(delay = 2000) {
        const widget = this;

        widget.show();

        setTimeout(function () {
            widget.hide();
        }, delay);
    }

    show() {
        this.$bubble.addClass(this.visibleClass);
    }

    hide() {
        this.$bubble.removeClass(this.visibleClass);
    }
}

class LemmonModal {
    openClass = 'lemmon-modal--open';
    messageVisibleClass = 'lemmon-modal__message--visible';

    constructor(id) {
        this.$interface = $('#' + id);
        this.$header = this.$interface.find('.lemmon-modal__header-text');
        this.$body = this.$interface.find('.lemmon-modal__body');
        this.$confirmButton = this.$interface.find('.lemmon-modal__confirm');

        this.addCloseEventListener();
    }

    open() {
        this.$interface.addClass(this.openClass);
    }

    close() {
        this.$interface.removeClass(this.openClass);
        this.hideMessages();
    }

    addCloseEventListener() {
        const widget = this;

        widget.$interface.find('.lemmon-modal__close, .lemmon-modal__cancel').on('click', function () {
            if ($('.add-amount-modal__no-tips').length) {
                $('.add-amount-modal__no-tips').removeAttr('disabled');
            }
            widget.close();
        });
    }

    updateHeaderText(text) {
        this.$header.html(text);
    }

    updateContent(content) {
        this.$body.html(content)
    }

    hideMessages() {
        this.$interface.find('.lemmon-modal__message').removeClass(this.messageVisibleClass);
    }
}

class StaffCart {
    $cart = $('.staff-order__cart');
    $emptyCart = $('.staff-order__empty');
    $table = $('.staff-order__table');


    constructor() {
        this.addUrl = $('.staff-menu').data('add-url');
        this.updateUrl = this.$cart.data('update-url');
        this.storeUrl = this.$cart.data('store-url');
        this.attachEventListeners();

        this.addShadow(this.$cart.find('.staff-cart'));
    }

    attachEventListeners() {
        const widget = this;

        widget.$cart.on('click', '.staff-cart__button', function (e) {
            const $this = $(this);
            const $item = $this.parent();
            const action = $this.data('action');
            const productId = $item.data('product-id');
            const key = $item.data('key');
            let quantity = parseInt($item.find('.staff-cart__quantity').text());

            switch (action) {
                case 'increase': {
                    quantity++;
                    break;
                }
                case 'decrease': {
                    quantity--;
                    break;
                }
                default:
                    return;
            }

            widget.updateItemQuantity({
                key: key,
                id: productId,
                quantity: quantity,
            });
        });

        widget.$emptyCart.on('click', function (e) {
            const $this = $(this);

            const emptyUrl = $this.data('url');

            if (!emptyUrl) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: emptyUrl,
                success: function (data) {
                    widget.updateCart(data);
                    productFilter.reset();
                    widget.resetTable();
                }
            });
        });

        $(window).on('resize', function () {
            widget.addShadow(widget.$cart.find('.staff-cart'));
        });
    }

    add(item) {
        const widget = this;

        if (!widget.addUrl) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: widget.addUrl,
            data: {
                item: item,
            },
            success: function (data) {
                widget.updateCart(data);
                widget.updateMenuItemQuantity(item);
            }
        });
    }

    updateItemQuantity(item) {
        const widget = this;

        if (!widget.updateUrl) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: widget.updateUrl,
            data: {
                item: item,
            },
            success: function (data) {
                widget.updateCart(data);
                widget.updateMenuItemQuantity(item);
            }
        });
    }

    updateMenuItemQuantity(product) {
        const $item = $(`.staff-menu__item[data-product-id="${product.id}"]`);

        const quantities = $(`.staff-cart__item[data-product-id="${product.id}"]`).map(function () {
            return parseInt($(this).find('.staff-cart__quantity').text());
        }).get();

        const total = quantities.reduce((accumulator, currentValue) => accumulator + currentValue, 0);

        if (total < 1) {
            $item.find('.staff-menu__button').removeClass(productFilter.buttonSelectedClass);
            $item.find('.staff-menu__button-quantity').html('');
            return;
        }

        $item.find('.staff-menu__button-quantity').html(total);
        $item.find('.staff-menu__button').addClass(productFilter.buttonSelectedClass);
    }

    getTable() {
        return parseInt(this.$table.attr('data-table'));
    }

    getTableText() {
        return isNaN(this.getTable()) ? '' : this.$table.text();
    }

    resetTable() {
        this.$table.attr('data-table', '');
        this.$table.html('');
    }

    isEmpty() {
        return $('.staff-cart__item').length === 0;
    }

    updateCart(content) {
        this.$cart.html(content);

        const $cartInner = this.$cart.find('.staff-cart');

        this.addShadow($cartInner);
    }

    addShadow($cartInner) {
        const boxShadowClass = 'staff-cart--shadow';

        if (lemmonUtility.hasVerticalScroll($cartInner)) {
            $cartInner.addClass(boxShadowClass);
        } else {
            $cartInner.removeClass(boxShadowClass);
        }
    }
}

const staffCart = new StaffCart();

class MobilePosController {
    menuOpenClass = 'staff-menu--open';
    searchOpenClass = 'staff-menu__search--open';
    searchInteractedWithClass = 'input--interacted-with';

    constructor(productFilter) {
        this.productFilter = productFilter;
        this.$openMenuButton = $('.staff-order__menu');
        this.$menu = $('.staff-menu');
        this.$backButton = $('.staff-menu__back');
        this.$toggleSearch = $('.staff-menu__toggle-search');
        this.$searchInterface = $('.staff-menu__search');
        this.$searchInput = $('#menu-search');
        this.$closeSearch = $('.staff-menu__close-search');
        this.$footerBackButton = $('#staff-menu-footer-back');
        this.$footerCartButton = $('#staff-menu-footer-cart');

        this.attachEventListeners();
    }

    attachEventListeners() {
        const widget = this;

        widget.$openMenuButton.on('click', function (e) {
            e.preventDefault();

            widget.$menu.addClass(widget.menuOpenClass);
            widget.productFilter.resetProducts();
            widget.productFilter.resetCategories();
            widget.productFilter.resetServices();
            widget.productFilter.displayServices();
        });

        widget.$backButton.on('click', function (e) {
            e.preventDefault();

            if (widget.productFilter.$products.hasClass(productFilter.productsVisibleClass)) {
                const selectedCategoryId = widget.productFilter.getSelectedServiceByVisibleProduct();
                widget.productFilter.resetProducts();
                widget.productFilter.displayCategories(selectedCategoryId);
            } else if (widget.productFilter.$categories.hasClass(productFilter.categoriesVisibleClass)) {
                widget.productFilter.resetCategories();
                widget.productFilter.displayServices();
            } else {
                widget.closeMenu();
            }
        });

        widget.$toggleSearch.on('click', function (e) {
            widget.$searchInterface.addClass(widget.searchOpenClass);
            widget.$searchInput.focus();
        });

        widget.$closeSearch.on('click', function (e) {
            const hasBeenInteractedWith = widget.$searchInput.hasClass(widget.searchInteractedWithClass);

            widget.closeSearch();
            widget.$searchInput.trigger('input');

            if (hasBeenInteractedWith) {
                widget.productFilter.resetProducts();
                widget.productFilter.resetCategories();
                widget.productFilter.displayServices();
            }
        });

        widget.$footerBackButton.on('click', function (e) {
            const selectedCategoryId = widget.productFilter.getSelectedServiceByVisibleProduct();
            widget.productFilter.resetProducts();
            widget.productFilter.displayCategories(selectedCategoryId);
        });

        widget.$footerCartButton.on('click', function (e) {
            widget.closeMenu();
        });

        $(window).on('resize', function () {
            if (!LemmonUtility.isSmallScreen()) {
                widget.closeMenu();
            }
        });
    }

    closeMenu() {
        this.resetMenu();
        this.$menu.removeClass(this.menuOpenClass);
    }

    resetMenu() {
        this.productFilter.clearSearch();
        this.closeSearch();
        this.productFilter.resetProducts();
        this.productFilter.resetCategories();
        this.productFilter.displayServices();
    }

    closeSearch() {
        this.$searchInput.val('');
        this.$searchInterface.removeClass(this.searchOpenClass);
        this.$searchInput.removeClass(this.searchInteractedWithClass);
    }
}

class ProductFilter {
    cart = staffCart;

    $menu = $('.staff-menu');
    $searchInput = $('#menu-search');
    $services = $('.staff-menu__services');
    $categories = $('.staff-menu__categories');
    $products = $('.staff-menu__products');

    searchClass = 'staff-menu--search';

    servicesVisibleClass = 'staff-menu__services--visible';
    categoriesVisibleClass = 'staff-menu__categories--visible';
    productsVisibleClass = 'staff-menu__products--visible';

    buttonSelectedClass = 'staff-menu__button--selected';
    buttonVisibleClass = 'staff-menu__button--visible';
    itemVisibleClass = 'staff-menu__item--visible';

    constructor() {
        this.mobileController = new MobilePosController(this);

        this.clearSearch();

        this.attachEventListeners();

        if (!LemmonUtility.isSmallScreen()) {
            this.openDefault();
        }
    }

    attachEventListeners() {
        const widget = this;

        widget.$searchInput.on('input', function (e) {
            const text = $(this).val().toLowerCase();

            if (text.length) {

                if (!LemmonUtility.isSmallScreen()) {
                    widget.$services.find(`.${widget.buttonSelectedClass}`).trigger('click');
                } else {
                    widget.$searchInput.addClass(widget.mobileController.searchInteractedWithClass);
                }

                widget.$menu.addClass(widget.searchClass);

                $('.staff-menu__item').each(function (i, item) {
                    const $item = $(item);

                    const productName = $item.data('product-name').toLowerCase();

                    if (productName.indexOf(text) > -1) {
                        $item.addClass(widget.itemVisibleClass);
                    } else {
                        $item.removeClass(widget.itemVisibleClass);
                    }
                });
            } else {
                if (!LemmonUtility.isSmallScreen()) {
                    widget.openDefault();
                } else {
                    widget.clearSearch();
                }
            }
        });

        $('.staff-menu__services .staff-menu__button').on('click', function (e) {
            const $this = $(this);
            const serviceId = $this.data('id');

            if (!LemmonUtility.isSmallScreen()) {
                if ($this.hasClass(widget.buttonSelectedClass)) {
                    $this.removeClass(widget.buttonSelectedClass);
                    widget.resetCategories();
                } else {
                    $this.parent().find(`.${widget.buttonSelectedClass}`).removeClass(widget.buttonSelectedClass);
                    $this.addClass(widget.buttonSelectedClass);
                    widget.displayCategories(serviceId);
                }
            } else {
                widget.mobileController.closeSearch();
                widget.hideServices();
                widget.displayCategories(serviceId);
            }
        });

        $('.staff-menu__categories .staff-menu__button').on('click', function (e) {
            const $this = $(this);
            const categoryId = $this.data('category-id');
            const serviceId = $this.data('service-id');


            if (!LemmonUtility.isSmallScreen()) {
                if ($this.hasClass(widget.buttonSelectedClass)) {
                    $this.removeClass(widget.buttonSelectedClass);
                    widget.resetProducts();
                } else {
                    $this.parent().find(`.${widget.buttonSelectedClass}`).removeClass(widget.buttonSelectedClass);
                    $this.addClass(widget.buttonSelectedClass);
                    widget.displayProducts(categoryId, serviceId);
                }
            } else {
                widget.mobileController.closeSearch();
                widget.hideCategories();
                widget.displayProducts(categoryId, serviceId);
            }

        });

        $('.staff-menu__products .staff-menu__button').on('click', function (e) {
            const $this = $(this);

            if ($this.hasClass('staff-menu__button--bundle')) {
                const $modal = $this.parent().find('.lemmon-modal');

                $modal.find('.staff-bundle-item__input:checked').prop('checked', false);
                $modal.find('.staff-menu-bundle--error').removeClass('staff-menu-bundle--error');
                $modal.addClass('lemmon-modal--open');
                return;
            }

            const $parent = $this.parent();
            const productId = $parent.data('product-id');

            if ($this.hasClass('staff-menu__button--is-custom')) {
                const $modal = new OpenAmountModal('open-amount-modal');
                $modal.setup(productId);
                $modal.open();

                return;
            }

            if ($this.hasClass(widget.buttonSelectedClass)) {
                const $cartItem = widget.cart.$cart.find(`.staff-cart__item[data-key=${productId}]`);

                $cartItem.find('.staff-cart__button[data-action="increase"]').trigger('click');
                return;
            }

            widget.addProductToCart({
                id: productId,
                quantity: 1,
            });
        });

        $('.staff-bundle-item__input').on('click', function (e) {
            const $this = $(this);
            const $bundle = $this.closest('.staff-menu-bundle');
            const max = parseInt($bundle.data('max'));

            if ($bundle.find('.staff-bundle-item__input:checked').length > max && $this.prop('checked') === true) {
                if (max === 1) {
                    $bundle.find('.staff-bundle-item__input:checked').not($this).prop('checked', false);
                } else {
                    e.preventDefault();
                }
            }
        });

        $('.staff-menu__add').on('click', function (e) {
            const $modal = $(this).closest('.lemmon-modal');
            const productId = $modal.closest('.staff-menu__item').data('product-id');

            if (!widget.validateCompositeItem($modal)) {
                return;
            }

            let bundle = {};

            $modal.find('.staff-bundle-item__input:checked').each(function () {
                const $this = $(this);
                const entityType = $this.data('entity-type');
                const bundleId = $this.data('bundle-id');
                const entityId = $this.data('entity-id');

                const key = `bundle_${bundleId}_${entityType}`;

                if (!Object.hasOwn(bundle, key)) {
                    bundle[key] = [];
                }
                bundle[key].push({
                    bundle_id: bundleId,
                    entity_type: entityType,
                    entity_id: entityId,
                });
            });

            widget.addProductToCart({
                id: productId,
                quantity: 1,
                bundle: bundle,
            });

            $modal.removeClass('lemmon-modal--open');
        });

        $(window).on('resize', function () {

            if (!LemmonUtility.isSmallScreen()) {
                widget.openDefault();
            }

            if (lemmonUtility.hasCrossedBreakpoint()) {
                $('.staff-menu__composite-item.lemmon-modal--open').removeClass('lemmon-modal--open');
            }
        });
    }

    openDefault() {
        this.clearSearch();

        const $firstService = this.$services.find('.staff-menu__button').first();

        if ($firstService.length && !$firstService.hasClass(this.buttonSelectedClass)) {
            $firstService.trigger('click');
        }

        const $firstCategory = this.$categories.find('.staff-menu__button').first();

        if ($firstCategory.length && !$firstCategory.hasClass(this.buttonSelectedClass)) {
            $firstCategory.trigger('click');
        }
    }

    clearSearch() {
        this.$searchInput.val('');
        this.$menu.removeClass(this.searchClass);
    }

    displayServices() {
        this.$services.addClass(this.servicesVisibleClass);
        this.$services.scrollTop(0);
    }

    hideServices() {
        this.$services.removeClass(this.servicesVisibleClass);
    }

    resetServices() {
        this.$services.find(`.${this.buttonSelectedClass}`).removeClass(this.buttonSelectedClass);
    }

    displayCategories(serviceId) {
        this.resetCategories();
        this.$categories.addClass(this.categoriesVisibleClass);
        this.$categories.find(`.staff-menu__button[data-service-id="${serviceId}"]`).addClass(this.buttonVisibleClass);
        this.$categories.scrollTop(0);
    }

    hideCategories() {
        this.$categories.removeClass(this.categoriesVisibleClass);
    }

    resetCategories() {
        this.$categories.removeClass(this.categoriesVisibleClass);
        this.$categories.find(`.${this.buttonSelectedClass}`).removeClass(this.buttonSelectedClass);
        this.$categories.find(`.${this.buttonVisibleClass}`).removeClass(this.buttonVisibleClass);
        this.resetProducts();
    }

    getSelectedServiceByVisibleProduct() {
        const $selectedProduct = this.$products.find(`.${this.itemVisibleClass}`).first();

        if ($selectedProduct.length) {
            return $selectedProduct.data('service-id');
        }

        return false;
    }

    displayProducts(categoryId, serviceId) {
        this.resetProducts();
        this.$products.addClass(this.productsVisibleClass);
        this.$products.find(`.staff-menu__item[data-service-id="${serviceId}"][data-category-id="${categoryId}"]`).addClass(this.itemVisibleClass);
        this.$products.scrollTop(0);
    }

    resetProducts() {
        this.$products.removeClass(this.productsVisibleClass);
        this.$products.find(`.${this.itemVisibleClass}`).removeClass(this.itemVisibleClass);
    }

    addProductToCart(product) {
        this.cart.add(product);
    }

    validateCompositeItem($modal) {
        let valid = true;

        $modal.find('.staff-menu-bundle').each(function (i, bundle) {
            const $bundle = $(bundle);

            const min = parseInt($bundle.data('min'));
            const max = parseInt($bundle.data('max'));

            const selectedItems = $bundle.find('.staff-bundle-item__input:checked').length;

            if (selectedItems < min || selectedItems > max) {
                $bundle.addClass('staff-menu-bundle--error');
                valid = false;

                setTimeout(() => {
                    $bundle.removeClass('staff-menu-bundle--error');
                }, 500);
            }
        });

        return valid;
    }

    reset() {
        const widget = this;

        widget.$products.find(`.${widget.buttonSelectedClass}`).each(function (i, button) {
            const $button = $(button);

            $button.find('.staff-menu__button-quantity').html('');
            $button.removeClass(widget.buttonSelectedClass);
        });

        if (!LemmonUtility.isSmallScreen()) {
            widget.openDefault();
        }
    }
}

const productFilter = new ProductFilter();

class TipsModal extends LemmonModal {
    constructor(id) {
        super(id);
        this.$input = this.$interface.find('#tips-field');

        this.attachEventListeners();
    }

    attachEventListeners() {
        const widget = this;

        widget.$input.on('input', function (e) {
            if ($(this).val()) {
                widget.$confirmButton.removeAttr('disabled');
                return;
            }

            widget.$confirmButton.attr('disabled', true);
        });
    }

    open() {
        this.$input.val('');
        this.$confirmButton.attr('disabled', true);
        super.open();
    }

    save() {
        throw new Error('You have to implement this method. Each Checkout Modal works differently');
    }
}

class DiscountModal extends LemmonModal {
    constructor(id) {
        super(id);

        this.attachEventListeners();
    }

    attachEventListeners() {
        const widget = this;

        widget.$interface.find('.input').on('input', function (e) {
            const $this = $(this);
            const $linkedInput = $(`#${$this.data('link')}`);

            if ($this.val()) {
                $linkedInput.val('');
                $linkedInput.attr('disabled', true);
                widget.$confirmButton.removeAttr('disabled');
            } else {
                $linkedInput.removeAttr('disabled');
                widget.$confirmButton.attr('disabled', true);
            }
        });
    }

    open() {
        this.$interface.find('.input').val('').removeAttr('disabled');
        this.$confirmButton.attr('disabled', true);
        super.open();
    }

}

class OpenAmountModal extends LemmonModal {
    constructor(id) {
        super(id);
        this.$input = $('#open-amount-field');
        this.addUrl = this.$interface.attr('data-add-url');
        this.updateUrl = this.$interface.attr('data-update-url');
        this.$confirmButton = $('.staff-menu__add-price');
        this.attachEventListeners();
        this.cart = new StaffCart();
    }

    attachEventListeners() {
        const widget = this;

        widget.$confirmButton.click(function () {
            widget.performAjax(widget);
        });

        // Trigger AJAX on Enter key press within the input field
        widget.$input.on('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                widget.performAjax(widget);
            }
        });
    }

    performAjax(widget){
        const amount = widget.$input.val();

        if (amount) {
            let item = {};
            item.id = widget.$interface.attr('data-product-id');
            item.price = amount;
            item.quantity = 1;

            widget.cart.add(item);
            widget.close();
        }
    }

    setup(product_id) {
        this.$interface.attr('data-product-id', product_id);
    }

    open() {
        super.open();
    }

    close() {
        this.$interface.attr('data-product-id', '');
        this.$input.val('');
        super.close();
    }

}

$('body').on('click', '.restaurant-tables__change_table', function (e) {
    $('#change-table-modal .footer-ajax-message').text('');
    $('#change-table-modal .yes-btn').attr('data-force', "");
    $('#change-table-modal .yes-btn').removeAttr('disabled');

    //replace the string :tableName with the table name from the attribute data-table-name
    //$('#change-table-modal .lemmon-modal__header-text').text($('#change-table-modal .lemmon-modal__header-text').text().replace(':tableName', $(this).attr('data-table-name')));

    $('#select-table option').each(function () {
        $(this).removeAttr("disabled");
    });

    let tableId = $(this).attr('data-table-id');

    $('#select-table option').each(function (i) {
        if ($(this).val() == tableId) {
            $(this).attr("disabled", "disabled");
        }
    });

    var $select = $('#select-table');

    // Select the first option programmatically
    $select.val($select.find('option:first').val()).trigger('change');

    let $modal = new ChangeTableModal('change-table-modal', tableId);
    $modal.open();
});


class ChangeTableModal extends LemmonModal {
    constructor(id, oldTableId) {
        super(id);
        this.attachEventListeners();
        this.$confirmButton = $('#change-table-modal .yes-btn');
        this.$closeButton = $('#change-table-modal .no-btn');
        this.$selectTableDropdown = $('#select-table');
        this.oldTableId = oldTableId;
        this.routes = {
            change: this.$interface.data('change-table-url'),
        };
    }

    attachEventListeners() {
        const widget = this;

        widget.$confirmButton.off('click');

        widget.$confirmButton.on('click', function () {
            $.ajax({
                type: 'POST',
                url: widget.routes.change,
                data: {
                    force: $(this).attr("data-force"),
                    old_table_id: widget.oldTableId,
                    new_table_id: widget.$selectTableDropdown.val()
                },
                success: function (data) {

                    $('#change-table-modal .footer-ajax-message').text(data.message);

                    if (!data.force) {
                        if (data.success) {
                            widget.$confirmButton.attr('disabled', 'disabled');
                            $('#refresh-button').click();
                            setTimeout(function () {
                                $('.lemmon-modal__close').click();
                            }, 2000);
                        }
                    } else {
                        widget.$confirmButton.attr("data-force", "true");
                    }
                }
            });
        });

    }

    open() {
        super.open();
    }

    close() {
        super.close();
    }

}


class DeleteItemModal extends LemmonModal {
    constructor(id) {
        super(id);
        this.attachEventListeners();
        this.$confirmButton = $('#delete-item-modal .yes-btn');
        this.$closeButton = $('#delete-item-modal .no-btn');
        this.routes = {
            remove: this.$interface.data('remove-item-url'),
        };
    }

    attachEventListeners() {
        const widget = this;

        widget.$confirmButton.off('click');

        widget.$confirmButton.on('click', function () {
            $.ajax({
                type: 'POST',
                url: widget.routes.remove,
                data: {
                    items: getPartialChecked()
                },
                success: function (data) {
                    $('#delete-item-modal .footer-ajax-message').text(data.message);
                    if (data.success) {

                        widget.$confirmButton.attr('disabled', 'disabled');
                        setTimeout(function () {
                            let tableId = $('.table-summary').attr('data-table-id');
                            $('#refresh-button').click();
                            $('.lemmon-modal__close').click();
                            if (!data.close_table) {
                                $('.restaurant-tables__cell button[data-table="' + tableId + '"]').click();
                            }
                        }, 2000);
                    }
                }
            });
        });

    }

    open() {
        super.open();
    }

    close() {
        super.close();
    }

}

class AmountModal extends LemmonModal {
    constructor(id, checkout) {
        super(id);
        this.checkout = checkout;
        this.$input = this.$interface.find('#amount-field');
        this.$noTipsButton = this.$interface.find('.add-amount-modal__no-tips');
        this.$invalidMessage = this.$interface.find('.add-amount-modal__invalid');
        this.$validMessage = this.$interface.find('.add-amount-modal__valid');
        this.$tipsAmount = this.$validMessage.find('.add-amount-modal__tips');
        this.paymentMethod = false;
        this.total = false;

        this.attachEventListeners();
    }

    attachEventListeners() {
        const widget = this;

        widget.$input.on('input', function (e) {
            const value = $(this).val();

            if (value === '') {
                widget.$noTipsButton.removeAttr('disabled');
            } else {
                widget.$noTipsButton.attr('disabled', true);
            }

            const amount = parseFloat(value);

            if (isNaN(amount)) {
                widget.$confirmButton.attr('disabled', true);
                widget.hideMessages();
            } else {
                if (amount >= widget.total) {
                    widget.$confirmButton.removeAttr('disabled');
                    widget.showTips();
                } else {
                    widget.$confirmButton.attr('disabled', true);
                    widget.showInvalid();
                }
            }
        });

        widget.$confirmButton.on('click', function (e) {
            const tips = widget.getTips();

            //if (tips) {
            widget.checkout.pay(widget.paymentMethod, {
                tips: tips,
                total: widget.$input.val(),
            });
            widget.close();
            // }
        });

        widget.$noTipsButton.on('click', function (e) {
            widget.checkout.pay(widget.paymentMethod);
            widget.close();
        });
    }

    open(paymentMethod) {
        this.paymentMethod = paymentMethod;
        this.$interface.find('.input').val('').removeAttr('disabled');
        this.$confirmButton.attr('disabled', true);
        this.$tipsAmount.html('');
        super.open();
    }

    close() {
        this.checkout.$actions.find('.button').removeAttr('disabled');
        super.close();
    }

    getTips() {
        const amount = parseFloat(this.$input.val());

        if (amount && this.total) {
            const tips = amount - this.total;

            if (tips > 0) {
                return tips.toFixed(2);
            }
        }

        return false;
    }

    showTips() {
        this.$invalidMessage.removeClass(this.messageVisibleClass);

        const tips = this.getTips() ? this.getTips() : 0;

        this.$tipsAmount.html(tips);
        this.$validMessage.addClass(this.messageVisibleClass);
    }

    showInvalid() {
        this.$validMessage.removeClass(this.messageVisibleClass);
        this.$invalidMessage.addClass(this.messageVisibleClass);
    }

    deactivateActions() {
        this.$confirmButton.attr('disabled', true);
        this.$noTipsButton.attr('disabled', true);
    }
}

// This class handles table checkout
// It had to be created because the initial checkout was becoming too complex
class LemmonCheckout {
    orders = null;
    ordersBreakdown = null;
    options = null;
    discountDisabled = false;

    openClass = 'lemmon-checkout--open';
    completeClass = 'lemmon-checkout--complete';
    closingOrdersClass = 'lemmon-checkout--closing-orders'
    ordersClosedClass = 'lemmon-checkout--orders-closed'

    constructor(id) {
        this.$interface = $('#' + id);

        if (!this.$interface.length) {
            return;
        }

        this.$table = this.$interface.find('.lemmon-checkout__table');
        this.$totalsBreakdown = this.$interface.find('.lemmon-checkout__totals-breakdown');

        this.$tipsButton = this.$interface.find('.lemmon-checkout__add-tips');
        this.tipsDialog = new TipsModal('add-tip-modal');

        this.amountDialog = new AmountModal('add-amount-modal', this);

        this.$discountButton = this.$interface.find('.lemmon-checkout__add-discount');
        this.discountDialog = new DiscountModal('add-discount-modal');
        this.$includedDiscountLabel = this.$interface.find('.lemmon-checkout__discount-included');
        this.$includedDiscountValue = this.$interface.find('.lemmon-checkout__discount-value');

        this.$actions = this.$interface.find('.lemmon-checkout__actions');
        this.$printButton = this.$interface.find('.lemmon-checkout__print');
        this.$closeButton = this.$interface.find('.lemmon-checkout__close');

        this.routes = {
            totals: this.$interface.data('totals-url'),
            options: this.$interface.data('options-url'),
            pay: this.$interface.data('pay-url'),
            close: this.$interface.data('close-url'),
            print: $('.main').data('print-receipt-url'),
        };

        this.attachEventListeners();
    }

    attachEventListeners() {
        const widget = this;

        widget.$tipsButton.on('click', function (e) {
            e.preventDefault();

            widget.tipsDialog.open();
        });

        widget.tipsDialog.$confirmButton.on('click', function (e) {
            e.preventDefault();

            let amount = parseFloat(widget.tipsDialog.$input.val());

            if (isNaN(amount)) {
                return;
            }

            amount = Math.abs(amount);

            if (!widget.routes.options) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: widget.routes.options,
                data: {
                    table_id: widget.options.tableId,
                    orders: widget.orders,
                    tips: amount,
                },
                success: function (data) {
                    widget.tipsDialog.$input.val('');

                    if (data.success) {
                        widget.update(data.result);
                        widget.tipsDialog.close();
                    }
                }
            });
        });

        widget.$discountButton.on('click', function (e) {
            e.preventDefault();

            // The user shouldn't be able to add a manual discount if a takeaway discount is included
            if (widget.discountDisabled) {
                widget.showIncludedDiscount();
                return;
            }

            widget.discountDialog.open();
        });

        widget.discountDialog.$confirmButton.on('click', function (e) {
            e.preventDefault();

            const $filledInput = widget.discountDialog.$interface.find('.input:not([disabled])');

            if ($filledInput.length !== 1) {
                return;
            }

            let amount = parseFloat($filledInput.val());

            if (isNaN(amount)) {
                return;
            }

            amount = Math.abs(amount);

            if (!widget.routes.options) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: widget.routes.options,
                data: {
                    table_id: widget.options.tableId,
                    orders: widget.orders,
                    partial: getPartialChecked(),
                    discount: {
                        type: $filledInput.data('discount-type'),
                        amount: amount,
                    },
                },
                success: function (data) {
                    widget.discountDialog.$interface.find('.input').val('');

                    if (data.success) {
                        widget.update(data.result);
                        widget.discountDialog.close();
                    }
                }
            });
        });

        widget.$actions.find('.lemmon-checkout__payment').on('click', function (e) {

            $('.lemmon-modal__back').addClass('d-none');
            $('.lemmon-modal__edit').removeClass('d-none');

            const $this = $(this);

            widget.$actions.find('.button').attr('disabled', true);

            // the action is triggered when closing an order, so we have to update it by
            // adding / updating the payment method
            // any tips and any discounts set in the session are also going to be stored / updated
            // But, first, if card is selected, we have to show the 'Add amount' modal to give the
            // staff the possibility to add a tip
            const paymentMethod = $this.data('payment-method');

            if (paymentMethod === 'card') {
                widget.amountDialog.open(paymentMethod);
            } else if (paymentMethod === 'cash') {
                // it's cash, so we just set the payment method
                widget.pay(paymentMethod);
            }
        });

        widget.$closeButton.on('click', function (e) {
            $('.final-print-button').attr('data-partial-pay-id', '');
            widget.close();
        });

        widget.$printButton.on('click', function (e) {
            e.preventDefault();

            if (!widget.ordersBreakdown?.length && !widget.routes.print) {
                return;
            }

            console.log('print-receipt-id' + $('.final-print-button').attr('data-partial-pay-id'));

            printerManager.getAndPrintReceipts({
                url: widget.routes.print,
                jobs: widget.ordersBreakdown,
                partial: getPartialChecked(),
                partial_pay_id: $('.final-print-button').attr('data-partial-pay-id')
            });
        });
    }

    show(options) {

        const widget = this;

        widget.options = options;

        widget.$table.html(options.table);

        widget.getTotals({
            tableId: options.tableId,
            partial: options.partial
        });

        widget.$interface.addClass(widget.openClass);

    }

    close() {
        this.clearPaymentOptions();
        this.$table.html('');
        this.$totalsBreakdown.html('');
        this.$interface.removeClass([this.openClass, this.completeClass, this.closingOrdersClass, this.ordersClosedClass]);
        this.resetDiscount();
        this.orders = null;
        this.ordersBreakdown = null;
    }

    update(data) {
        this.orders = data.orders;
        this.ordersBreakdown = data.orders_breakdown;

        const totals = data.totals;

        let totalsTemplate = `<div class="lemmon-checkout__amount">${totals.currency ?? ''} <span class="lemmon-checkout__amount-value">${totals.amount_due}</span></div>`;

        if (Object.hasOwn(totals, 'manual_discount')) {
            const discountText = totals.manual_discount.type === 'percentage' ? `${totals.manual_discount.amount}%` : `${totals.currency} ${totals.manual_discount.net_amount}`;
            totalsTemplate += `<div class="lemmon-checkout__discount">${totals.currency} ${totals.subtotal} - ${discountText} ${totals.manual_discount.suffix}</div>`;
        }

        if (Object.hasOwn(totals, 'tips')) {
            totalsTemplate += `<div class="lemmon-checkout__tips">${totals.currency} ${totals.gross} + ${totals.currency} ${totals.tips.value} ${totals.tips.suffix}</div>`;
        }

        if (totals.discount_takeaway) {
            this.disableDiscount(totals.discount_takeaway);
        } else {
            this.resetDiscount();
        }

        this.$totalsBreakdown.html(totalsTemplate);

        if (this.amountDialog) {
            this.amountDialog.total = parseFloat(totals.amount_due);
        }
    }

    getTotals(profile = {}) {
        const widget = this;

        if (!widget.routes.totals) {
            return;
        }

        $.ajax({
            type: 'GET',
            url: widget.routes.totals,
            data: profile,
            success: function (data) {
                if (data.success) {
                    widget.update(data.result);
                } else {
                    widget.orders = null;
                }
            }
        });
    }

    updateTotalAmount(amount) {
        this.$interface.find('.lemmon-checkout__amount-value').html(Number(amount).toFixed(2));
    }

    clearPaymentOptions() {
        const widget = this;

        if (!widget.routes.options) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: widget.routes.options,
            data: {
                clear_all: true,
            },
            success: function (data) {

            }
        });
    }

    disableDiscount(amount) {
        this.$includedDiscountValue.html(amount);
        this.discountDisabled = true;
    }

    showIncludedDiscount() {
        this.$includedDiscountLabel.addClass('js--visible');
        this.$discountButton.attr('disabled', true);
    }

    resetDiscount() {
        this.$includedDiscountLabel.removeClass('js--visible');
        this.$includedDiscountValue.html('');
        this.$discountButton.removeAttr('disabled');
        this.discountDisabled = false;
    }

    pay(paymentMethod, options = {}) {
        const widget = this;

        const payUrl = widget.routes.pay;

        let partial = getPartialChecked();

        if (!payUrl) {
            return;
        }

        const payload = {
            table_id: widget.options.tableId,
            close: widget.orders,
            payment: widget.getOrdersForPayment(),
            payment_method: paymentMethod,
            partial: partial
        };

        $('.final-print-button').attr('data-partial-pay', JSON.stringify(partial));

        if (options.tips !== false) {
            payload.tips = options.tips;
        }

        $.ajax({
            type: 'POST',
            url: payUrl,
            data: payload,
            success: function (data) {
                if (data.success === false) {
                    return;
                }

                widget.$actions.find('.button').removeAttr('disabled');
                if (options.total) {
                    widget.updateTotalAmount(options.total);
                }

                if (data.payment_id) {
                    $('.final-print-button').attr('data-partial-pay-id', data.payment_id);
                }

                widget.$interface.addClass(widget.completeClass);
                staffTables.clear();
            }
        });
    }

    getOrdersForPayment() {
        if (!this.ordersBreakdown) {
            return false;
        }

        const needPayment = this.ordersBreakdown.filter(item => item.payment === 'merged');

        if (!needPayment.length) {
            return false;
        }

        return needPayment[0].orders;
    }

    closeOrders(options) {
        const widget = this;

        if (!widget.routes.close) {
            return;
        }

        widget.$table.html(options.table);
        widget.$interface.addClass([widget.openClass, widget.closingOrdersClass]);

        widget.ordersBreakdown = options.orders.map(orderId => ({
            payment: 'online',
            orders: [orderId],
        }));

        $.ajax({
            type: 'POST',
            url: widget.routes.close,
            data: {
                table_id: options.tableId,
                orders: options.orders
            },
            success: function (data) {
                if (data.success === false) {
                    return;
                }
                widget.$interface.removeClass(widget.closingOrdersClass);
                widget.$interface.addClass(widget.ordersClosedClass);
                staffTables.clear();
            }
        });
    }
}

class Checkout {
    $interface = $('#lemmon-order-checkout');

    $table = $('#lemmon-order-checkout .lemmon-checkout__table');
    $totalsBreakdown = $('#lemmon-order-checkout .lemmon-checkout__totals-breakdown');
    $actions = $('#lemmon-order-checkout .lemmon-checkout__actions');

    $tipsButton = $('#lemmon-order-checkout .lemmon-checkout__add-tips');

    $discountButton = $('#lemmon-order-checkout .lemmon-checkout__add-discount');

    tableButtonSelectedClass = 'select-table-modal__button--selected';

    $includedDiscountLabel = $('#lemmon-order-checkout .lemmon-checkout__discount-included');
    $includedDiscountValue = $('#lemmon-order-checkout .lemmon-checkout__discount-value');

    openClass = 'lemmon-checkout--open';
    completeClass = 'lemmon-checkout--complete';

    constructor() {
        if (!this.$interface.length) {
            return;
        }

        this.totalsUrl = this.$interface.data('totals-url');
        this.optionsUrl = this.$interface.data('options-url');
        this.tipsDialog = new TipsModal('add-tip-modal');
        this.amountDialog = new AmountModal('add-amount-modal', this);
        this.discountDialog = new DiscountModal('add-discount-modal');
        this.openAmountDialog = new OpenAmountModal('open-amount-modal');
        this.orderId = false;
        this.serviceMethod = false;
        this.discountDisabled = false;
        this.orderSentNotification = new LemmonNotification('order-sent-notification');

        this.$selectTableModal = $('#send-to-table');
        this.$selectTableDropdown = $('#select-table');
        this.$closeSelectTableModal = $('.select-table-modal__back');

        this.clearBrowserCacheForTableDropdown();

        this.attachEventListeners();
    }

    clearBrowserCacheForTableDropdown() {
        // the table may be pre-selected from the Tables page, so it should not be updated then
        if (this.$selectTableDropdown.find('option[selected]').length === 0) {
            this.$selectTableDropdown.val('none').trigger('change');
        }
    }

    attachEventListeners() {
        const widget = this;

        $('.staff-order__takeaway').on('click', function (e) {
            const $this = $(this);

            if (staffCart.isEmpty()) {
                e.preventDefault();
                return;
            }

            widget.serviceMethod = $this.data('service-method');

            widget.show();
        });

        $('#lemmon-order-checkout .lemmon-checkout__close').on('click', function (e) {
            widget.close();
        });

        $('.staff-order__send').on('click', function (e) {
            const $this = $(this);
            let selectedTable;

            if (staffCart.isEmpty()) {
                e.preventDefault();
                return;
            }

            widget.serviceMethod = $this.data('service-method');

            if (LemmonUtility.isSmallScreen()) {
                widget.$selectTableModal.addClass('lemmon-modal--open');

                selectedTable = widget.$selectTableDropdown.val();

                if (selectedTable === "none") {
                    widget.$selectTableModal.find('.lemmon-modal__confirm').attr('disabled', true);
                } else {
                    widget.$selectTableModal.find('.lemmon-modal__confirm').removeAttr('disabled');
                }

                return;
            }

            selectedTable = staffCart.getTable();

            if (selectedTable) {
                if (!widget.serviceMethod || !staffCart.storeUrl) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: staffCart.storeUrl,
                    data: {
                        table: selectedTable,
                        service_method: widget.serviceMethod,
                    },
                    success: function () {
                        staffCart.$emptyCart.trigger('click');
                        widget.serviceMethod = false;
                        widget.orderSentNotification.flash();
                        printerManager.getOrdersToPrint();
                    }
                });

                return;
            }

            widget.$selectTableModal.addClass('lemmon-modal--open');

            selectedTable = widget.$selectTableDropdown.val();

            if (selectedTable === "none") {
                widget.$selectTableModal.find('.lemmon-modal__confirm').attr('disabled', true);
            } else {
                widget.$selectTableModal.find('.lemmon-modal__confirm').removeAttr('disabled');
            }
        });

        widget.$selectTableDropdown.on('change', function (e) {
            const selectedTable = $(this).val();

            if (selectedTable === "none") {
                widget.$selectTableModal.find('.lemmon-modal__confirm').attr('disabled', true);
                widget.$selectTableModal.find(`.${widget.tableButtonSelectedClass}`).removeClass(widget.tableButtonSelectedClass);
            } else {
                widget.$selectTableModal.find('.lemmon-modal__confirm').removeAttr('disabled');
                widget.$selectTableModal.find(`.${widget.tableButtonSelectedClass}`).removeClass(widget.tableButtonSelectedClass);
                widget.$selectTableModal.find(`.select-table-modal__button[data-table="${selectedTable}"`).addClass(widget.tableButtonSelectedClass);
            }
        });

        widget.$selectTableModal.find('.lemmon-modal__confirm').on('click', function (e) {
            e.preventDefault();
            const $this = $(this);

            const selectedTable = widget.$selectTableDropdown.val();

            if (selectedTable === "none" || !widget.serviceMethod || !staffCart.storeUrl) {
                return;
            }

            $this.attr('disabled', true);

            $.ajax({
                type: 'POST',
                url: staffCart.storeUrl,
                data: {
                    table: selectedTable,
                    service_method: widget.serviceMethod,
                },
                success: function () {

                    staffCart.$emptyCart.trigger('click');
                    $this.removeAttr('disabled');
                    widget.$selectTableModal.removeClass('lemmon-modal--open');
                    widget.orderSentNotification.flash();
                    if ($(window).width() > 768) {
                        printerManager.getOrdersToPrint();
                    }
                }
            });
        });

        widget.$selectTableModal.find('.select-table-modal__button').on('click', function (e) {
            const $this = $(this);

            if ($this.hasClass(widget.tableButtonSelectedClass)) {
                $this.removeClass(widget.tableButtonSelectedClass);
                widget.$selectTableDropdown.val('none').trigger('change');
            } else {
                widget.$selectTableModal.find(`.${widget.tableButtonSelectedClass}`).removeClass(widget.tableButtonSelectedClass);
                $this.addClass(widget.tableButtonSelectedClass);
                widget.$selectTableDropdown.val($this.data('table')).trigger('change');
            }
        });

        widget.$closeSelectTableModal.on('click', function (e) {
            widget.$selectTableModal.removeClass('lemmon-modal--open');
        });

        widget.$tipsButton.on('click', function (e) {
            e.preventDefault();

            widget.tipsDialog.open();
        });

        widget.tipsDialog.$confirmButton.on('click', function (e) {
            e.preventDefault();

            let amount = parseFloat(widget.tipsDialog.$input.val());

            if (isNaN(amount)) {
                return;
            }

            amount = Math.abs(amount);

            const requestData = {
                tips: amount,
            };

            if (widget.orderId) {
                requestData.order_id = widget.orderId;
            }

            if (!widget.optionsUrl) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: widget.optionsUrl,
                data: requestData,
                success: function (data) {
                    widget.tipsDialog.$input.val('');

                    if (data.success) {
                        widget.update(data.totals);
                        widget.tipsDialog.close();
                    }
                }
            });
        });

        widget.$discountButton.on('click', function (e) {
            e.preventDefault();

            // The user shouldn't be able to add a manual discount if a takeaway discount is included
            if (widget.discountDisabled) {
                widget.showIncludedDiscount();
                return;
            }

            widget.discountDialog.open();
        });

        widget.discountDialog.$confirmButton.on('click', function (e) {
            e.preventDefault();

            const $filledInput = widget.discountDialog.$interface.find('.input:not([disabled])');

            if ($filledInput.length !== 1) {
                return;
            }

            let amount = parseFloat($filledInput.val());

            if (isNaN(amount)) {
                return;
            }

            amount = Math.abs(amount);

            const requestData = {
                discount: {
                    type: $filledInput.data('discount-type'),
                    amount: amount,
                },
            };

            if (widget.orderId) {
                requestData.order_id = widget.orderId;
            }

            if (!widget.optionsUrl) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: widget.optionsUrl,
                data: requestData,
                success: function (data) {
                    widget.discountDialog.$interface.find('.input').val('');

                    if (data.success) {
                        widget.update(data.totals);
                        widget.discountDialog.close();
                    }
                }
            });
        });

        widget.$actions.find('.lemmon-checkout__payment').on('click', function (e) {
            const $this = $(this);

            widget.$actions.find('.button').attr('disabled', true);

            // if there's no order id, it means that the checkout is triggered from the POS (Menu) interface
            // so the order doesn't exist yet - it needs to be created
            if (!widget.orderId) {

                // we need to know the service method when creating the order
                if (!widget.serviceMethod) {
                    return;
                }

                widget.placeOrder($this.data('payment-method'));
                return;
            }

            // the action is triggered when closing an order, so we have to update it by
            // adding / updating the payment method
            // any tips and any discounts set in the session are also going to be stored / updated
            // But, first, if card is selected, we have to show the 'Add amount' modal to give the
            // staff the possibility to add a tip
            const paymentMethod = $this.data('payment-method');

            if (paymentMethod === 'card') {
                widget.amountDialog.open(paymentMethod);
            } else if (paymentMethod === 'cash') {
                // it's cash, so we just set the payment method
                widget.pay(paymentMethod);
            }
        });

        widget.$interface.find('.lemmon-checkout__print').on('click', function (e) {
            e.preventDefault();

            const $this = $(this);
            const order = $this.attr('data-order');

            if (order) {
                const printUrl = $('.main').data('print-receipt-url');

                if (!printUrl) {
                    return;
                }

                printerManager.getAndPrintReceipt({
                    url: printUrl,
                    orderId: order,
                });
            }
        });
    }

    show(options = {}) {
        const widget = this;

        widget.$table.html(options.table ?? '');

        widget.orderId = options.orderId ?? false;

        widget.getTotals(widget.orderId);
        widget.setOrderForPrinting(widget.orderId);

        widget.$interface.addClass(widget.openClass);
    }

    close() {
        this.clearPaymentOptions();
        this.$table.html('');
        this.$totalsBreakdown.html('');
        this.$interface.removeClass([this.openClass, this.completeClass]);
        this.resetDiscount();
    }

    update(totals) {

        let totalsTemplate = `<div class="lemmon-checkout__amount">${totals.currency ?? ''} <span class="lemmon-checkout__amount-value">${totals.amount_due}</span></div>`;

        if (Object.hasOwn(totals, 'manual_discount')) {
            const discountText = totals.manual_discount.type === 'percentage' ? `${totals.manual_discount.amount}%` : `${totals.currency} ${totals.manual_discount.net_amount}`;
            totalsTemplate += `<div class="lemmon-checkout__discount">${totals.currency} ${totals.subtotal} - ${discountText} ${totals.manual_discount.suffix}</div>`;
        }

        if (Object.hasOwn(totals, 'tips')) {
            totalsTemplate += `<div class="lemmon-checkout__tips">${totals.currency} ${totals.gross} + ${totals.currency} ${totals.tips.value} ${totals.tips.suffix}</div>`;
        }

        if (totals.discount_takeaway) {
            this.disableDiscount(totals.discount_takeaway);
        } else {
            this.resetDiscount();
        }

        this.$totalsBreakdown.html(totalsTemplate);

        if (this.amountDialog) {
            this.amountDialog.total = parseFloat(totals.amount_due);
        }
    }

    updateTotalAmount(amount) {
        this.$interface.find('.lemmon-checkout__amount-value').html(Number(amount).toFixed(2));
    }

    getTotals(orderId = false) {
        const widget = this;

        const requestData = {};

        if (orderId) {
            requestData.order_id = orderId;
        }

        if (!widget.totalsUrl) {
            return;
        }

        $.ajax({
            type: 'GET',
            url: widget.totalsUrl,
            data: requestData,
            success: function (data) {
                if (data.success) {
                    widget.update(data.totals);
                }
            }
        });
    }

    clearPaymentOptions() {
        const widget = this;

        widget.orderId = false;
        widget.serviceMethod = false;

        if (!widget.optionsUrl) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: widget.optionsUrl,
            data: {
                clear_all: true,
            },
            success: function (data) {

            }
        });
    }

    placeOrder(paymentMethod) {
        const widget = this;

        if (!staffCart.storeUrl) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: staffCart.storeUrl,
            data: {
                table: widget.serviceMethod === 'takeaway' ? null : staffCart.getTable(),
                service_method: widget.serviceMethod,
                payment_method: paymentMethod,
            },
            success: function (data) {
                widget.$actions.find('.button').removeAttr('disabled');
                widget.setOrderForPrinting(data.order);
                printerManager.getOrdersToPrint();
                widget.$interface.addClass(widget.completeClass);
                staffCart.$emptyCart.trigger('click');
            }
        });
    }

    pay(paymentMethod, options = {}) {
        const widget = this;

        const payUrl = widget.$interface.data('pay-url');

        if (!payUrl) {
            return;
        }

        const payload = {
            order_id: widget.orderId,
            payment_method: paymentMethod,
        };

        if (options.tips) {
            payload.tips = options.tips;
        }

        $.ajax({
            type: 'POST',
            url: payUrl,
            data: payload,
            success: function () {
                widget.$actions.find('.button').removeAttr('disabled');

                if (options.total) {
                    widget.updateTotalAmount(options.total);
                }

                widget.$interface.addClass(widget.completeClass);
            }
        });
    }

    disableDiscount(amount) {
        this.$includedDiscountValue.html(amount);
        this.discountDisabled = true;
    }

    showIncludedDiscount() {
        this.$includedDiscountLabel.addClass('js--visible');
        this.$discountButton.attr('disabled', true);
    }

    resetDiscount() {
        this.$includedDiscountLabel.removeClass('js--visible');
        this.$includedDiscountValue.html('');
        this.$discountButton.removeAttr('disabled');
        this.discountDisabled = false;
    }

    setOrderForPrinting(order) {
        const $printButton = this.$interface.find('.lemmon-checkout__print');

        if ($printButton.length && order) {
            $printButton.attr('data-order', order);
        }
    }
}

const checkout = new Checkout();

class TableSearchController {
    openClass = 'select-table-modal__search--open';
    buttonHiddenClass = 'select-table-modal__button--hidden';

    constructor() {
        this.$container = $('.select-table-modal__search');
        this.$openSearch = $('.select-table-modal__toggle-search');
        this.$closeButton = $('.select-table-modal__close-search');
        this.$input = $('#table-search');

        this.attachEventListeners();
    }

    attachEventListeners() {
        const widget = this;

        widget.$openSearch.on('click', function (e) {
            widget.clear();
            widget.$container.addClass(widget.openClass);
        });

        widget.$closeButton.on('click', function (e) {
            widget.$container.removeClass(widget.openClass);
            widget.clear();
        });

        widget.$input.on('input', function (e) {
            const text = $(this).val().toLowerCase();

            if (text.length) {
                $('.select-table-modal__button').each(function (i, button) {
                    const $button = $(button);
                    const tableName = $button.data('table-name').toLowerCase();

                    if (tableName.indexOf(text) > -1) {
                        $button.removeClass(widget.buttonHiddenClass);
                    } else {
                        $button.addClass(widget.buttonHiddenClass);
                    }
                });
            } else {
                $(`.${widget.buttonHiddenClass}`).removeClass(widget.buttonHiddenClass);
            }
        });
    }

    clear() {
        this.$input.val('');
        $(`.${this.buttonHiddenClass}`).removeClass(this.buttonHiddenClass);
    }
}

const tableSearchController = new TableSearchController();

class AddNotes {
    constructor() {
        this.$modal = $('#add-notes');
        this.$submit = this.$modal.find('.lemmon-modal__confirm');
        this.notesUrl = this.$modal.attr('data-notes-url');
        this.attachEventListeners();
    }

    attachEventListeners() {
        const widget = this;

        $('.staff-order__add-note').on('click', function (e) {
            e.preventDefault();

            widget.openModal();
        });

        widget.$submit.on('click', function (e) {
            e.preventDefault();

            const payload = {};

            widget.$modal.find('.input').each(function () {
                const $this = $(this);

                payload[$this.attr('data-key')] = $this.val();
            });

            $.ajax({
                type: 'POST',
                url: widget.notesUrl,
                data: {
                    notes: payload
                },
                success: function (data) {
                    if (data.success === false) {
                        return;
                    }

                    staffCart.updateCart(data);
                    widget.closeModal();
                }
            });
        });

        widget.$modal.on('input', '.input', function (e) {
            widget.$submit.removeAttr('disabled');
        });
    }

    openModal() {
        this.$modal.find('.lemmon-modal__body').html(this.buildTemplate());
        this.$submit.attr('disabled', true);
        this.$modal.addClass('lemmon-modal--open');
    }

    buildTemplate() {
        const cartItems = this.getItems();

        let template = '';

        if (cartItems.length) {
            cartItems.forEach(item => {
                template += `
                    <div class="lemmon-modal__row">
                        <label for="add-note-input-${item.key}" class="label label--small">${item.name + item.extras}</label>
                        <input id="add-note-input-${item.key}" data-key="${item.key}" class="input input--small" type="text" placeholder="Type to add a note here..." value="${item.note}">
                    </div>
                `
            });
        } else {
            template = '<span>You need to add items to cart first.</span>'
        }


        return template;
    }

    getItems() {
        const widget = this;

        return $('.staff-cart__item').map(function () {
            const $this = $(this);

            return {
                key: $this.attr('data-key'),
                name: $this.find('.staff-cart__main-item').text(),
                extras: widget.getExtras($this),
                note: widget.getNote($this),
            };
        }).get();
    }

    getExtras($item) {
        const extras = $item.find('.staff-cart__extra').map(function () {
            return $(this).text();
        }).get();

        if (extras.length) {
            return ` (${extras.join(' / ')})`;
        }

        return '';
    }

    getNote($item) {
        const $note = $item.find('.staff-cart__notes-text');

        if ($note.length) {
            return $note.text();
        }

        return '';
    }

    hasValues() {
        let hasValues = false;

        this.$modal.find('.input').each(function () {
            if ($(this).val().length) {

                hasValues = true;
                return false;
            }
        });

        return hasValues;
    }

    closeModal() {
        this.$modal.find('.lemmon-modal__body').html('');
        this.$modal.removeClass('lemmon-modal--open');
    }
}

const addNotes = new AddNotes();

function getPartialChecked() {
    let selectedItems = {};
    $('.table-summary__partial-checkbox:checked').each(function () {
        let fullId = $(this).attr('name');
        let [orderItemId, productId] = fullId.split('-', 2);
        let qty = $(this).val();

        // Ensuring the structure in selectedItems
        if (!selectedItems[productId]) {
            selectedItems[productId] = {};
        }

        selectedItems[productId]['order_item_id'] = orderItemId;
        selectedItems[productId]['product_id'] = productId;
        selectedItems[productId]['qty'] = qty;
    });

    if (jQuery.isEmptyObject(selectedItems) && $('.final-print-button').data('partial-pay')) {
        selectedItems = JSON.parse($('.final-print-button').attr('data-partial-pay'));
    }


    return selectedItems;
}

class TableOrdersSummaryModal extends LemmonModal {
    actionVisibleClass = 'lemmon-modal__action--active';
    summary = null;
    initialQuantities = {};

    constructor(id) {
        super(id);
        this.summaryUrl = this.$interface.attr('data-url');
        this.$addOrderInput = this.$interface.find('input[name="table_id"]');
        this.$closeOrders = this.$interface.find('.lemmon-modal__close-orders');
        this.$actions = this.$interface.find('.lemmon-modal__action');
        this.$paymentDisabled = this.$interface.find('.table-orders-summary__payment-disabled');
        this.checkout = new LemmonCheckout('lemmon-table-checkout');
        this.initCheckout();
        this.initCloseOrders();
        this.editOrders();
        this.attachEventListeners(this);
    }

    attachEventListeners(e) {

        $('body').on('click', '.lemmon-modal__delete', function (e) {
            const $container = $('.delete-items-container');
            $container.empty();
            let items = getPartialChecked();

            $('#delete-item-modal .footer-ajax-message').text('');
            $('#delete-item-modal .yes-btn').removeAttr('disabled');

            $.each(items, function (id, item) {
                let productName = 'Unknown Product';

                $('.table-summary__item--unpaid').each(function () {
                    const $item = $(this);

                    const itemOrderId = $item.find('.table-summary__partial-checkbox').attr('name').split('-')[0];

                    if (itemOrderId === item.order_item_id) {
                        productName = $item.find('.table-summary__main').text().trim();
                        return false; // Break out of the loop once we find the match
                    }
                });

                const $listItem = $('<div></div>').text(`${productName} - Quantity: ${item.qty}`);
                $container.append($listItem);
            });

            let $modal = new DeleteItemModal('delete-item-modal');
            $modal.open();
        });
    }

    initCheckout() {
        const widget = this;

        this.$confirmButton.on('click', function (e) {
            e.preventDefault();

            const $this = $(this);

            let partials = getPartialChecked();

            if (Object.keys(partials).length) {
                $('.lemmon-checkout__print:not(.final-print-button)').hide();
                $('.lemmon-checkout__add-discount').hide();
            } else {
                $('.lemmon-checkout__print:not(.final-print-button)').show();
                $('.lemmon-checkout__add-discount').show();
            }

            widget.checkout.show({
                tableId: $this.attr('data-table'),
                table: widget.$header.text(),
                partial: partials
            });
        });
    }

    initCloseOrders() {
        const widget = this;

        this.$closeOrders.on('click', function (e) {
            e.preventDefault();

            if (!widget.summary) {
                return;
            }

            const orders = widget.summary.orders_breakdown
                .filter(item => item.payment === 'online' && item.orders.length)
                .map(item => item.orders)
                .reduce((result, currentArray) => result.concat(currentArray), []);

            if (!orders.length) {
                return;
            }

            widget.checkout.closeOrders({
                tableId: widget.summary.table,
                table: widget.$header.text(),
                orders: orders,
            });
        });
    }

    open(config) {
        this.updateHeaderText(config.tableName);
        this.storeInitialQuantities();
        this.$actions.removeClass(this.actionVisibleClass);

        this.getSummary(config.tableId);

        this.updateAddOrderId(config.tableId);
        this.updatePayButton(config.tableId);
        super.open();
    }

    close() {
        this.updateHeaderText('');
        this.updateContent('');
        this.updateAddOrderId('');
        this.updatePayButton('');
        this.disableActions();
        super.close();
    }

    getSummary(tableId) {
        const widget = this;

        if (!widget.summaryUrl) {
            return;
        }

        widget.summary = null;

        $.ajax({
            type: 'POST',
            url: widget.summaryUrl,
            data: {
                id: tableId
            },
            success: function (data) {
                if (data.success === false) {
                    widget.updateContent('<p>' + data.message + '</p>');
                    return;
                }

                widget.summary = data.summary;
                widget.updateContent(data.html);
                widget.updateActions(data.summary);
            }
        });
    }

    updateAddOrderId(tableId) {
        this.$addOrderInput.val(tableId);
    }

    updatePayButton(tableId) {
        this.$confirmButton.attr('data-table', tableId);
    }

    updateActions(summary) {
        const payable = summary.orders_breakdown.some(item => item.payment === 'merged' && item.orders.length);


        if (payable) {

            this.$confirmButton.addClass(this.actionVisibleClass);

            if (summary.closable || summary.totals.net === '0.00') {
                this.$confirmButton.removeAttr('disabled');
            } else {
                this.$confirmButton.attr('disabled', true);
                this.$paymentDisabled.addClass(this.messageVisibleClass);
            }

        } else if (summary.closable) {
            this.$closeOrders.addClass(this.actionVisibleClass);
            this.$closeOrders.removeAttr('disabled');
        } else {
            this.disableActions();
        }

    }

    disableActions() {
        this.$actions.removeClass(this.actionVisibleClass);
        this.$actions.attr('disabled', true);
    }

    storeInitialQuantities() {
        $('.staff-cart__quantity').each(() => {
            let initialVal = $(this).data('initial-value');
            let itemId = $(this).closest('.table-summary__item').find('input[type="checkbox"]').attr('name');
            this.initialQuantities[itemId] = initialVal;
        });
    }

    editOrders() {

        $('.lemmon-modal__edit').click(function () {
            $('.table-summary__checkbox').removeClass('d-none');
            $('.lemmon-modal__delete').removeClass('d-none');
            $(this).addClass('d-none');
            $('.lemmon-modal__back').removeClass('d-none');
        });

        $('.lemmon-modal__back').click(function () {
            $('.table-summary__checkbox').addClass('d-none');
            $('.lemmon-modal__delete').addClass('d-none');
            $('.lemmon-modal__edit').removeClass('d-none');
            $('.lemmon-modal__confirm').attr('disabled', 'disabled');
            $(this).addClass('d-none');

            // Reset quantities to initial values
            $('.staff-cart__quantity').each(function () {
                $(this).text($(this).data('initial-value'));
            });

            // Uncheck all checkboxes and hide increase/decrease buttons
            $('.table-summary__partial-checkbox').prop('checked', false);
            $('.staff-cart__button').addClass('d-none');
        });

        // Checkbox change event
        $(document).on('change', 'input[type="checkbox"]', function () {

            let $item = $(this).closest('.table-summary__item');
            let $quantityDiv = $item.find('.staff-cart__quantity');

            if ($(this).is(':checked')) {
                $(this).val($(this).data('initial-value'));
                if ($(this).data('initial-value') > 1) {
                    $item.find('.staff-cart__button').removeClass('d-none');
                }
            } else {
                $quantityDiv.text($quantityDiv.data('initial-value'));
                $item.find('.staff-cart__button').addClass('d-none');
            }

            // Enable/disable the confirm button based on checkbox states
            if ($('.table-summary__partial-checkbox:checked').length > 0) {
                $('.lemmon-modal__confirm').removeAttr('disabled');
            } else {
                $('.lemmon-modal__confirm').attr('disabled', 'disabled');
            }
        });

        // Decrease button click event
        $(document).on('click', '.staff-cart__button[data-action="decrease"]', function () {
            let $quantityDiv = $(this).closest('.table-summary__qty').find('.staff-cart__quantity');
            let quantity = parseInt($quantityDiv.text());
            let $checkbox = $(this).closest('.table-summary__item').find('.table-summary__partial-checkbox');

            if (quantity > 1) {
                quantity--;
                $quantityDiv.text(quantity);
                $checkbox.val(quantity);
            }

            if (quantity === 1) {
                $(this).prop('disabled', true);
            }

        });

        // Increase button click event
        $(document).on('click', '.staff-cart__button[data-action="increase"]', function () {
            let $quantityDiv = $(this).closest('.table-summary__qty').find('.staff-cart__quantity');
            let $checkbox = $(this).closest('.table-summary__item').find('.table-summary__partial-checkbox');
            let quantity = parseInt($quantityDiv.text());
            let initialQuantity = $quantityDiv.data('initial-value');

            if (quantity < initialQuantity) {
                quantity++;
                $quantityDiv.text(quantity);
                $checkbox.val(quantity);
                $(this).siblings('.staff-cart__button[data-action="decrease"]').prop('disabled', false);
            }
        });
    }
}

class StaffTables {
    constructor(id) {
        this.$table = $(`#${id}`);
        this.$statusFilter = $('#table-filters');
        this.$roomFilter = $('#tables-room-filter');
        this.orderSummary = new TableOrdersSummaryModal('table-orders-summary');

        this.routes = {
            list: this.$table.data('list-url'),
            updateStatus: this.$table.data('update-status-url'),
        }

        this.initDataTables() && this.attachEventListeners();
    }

    initDataTables() {
        const widget = this;

        if (!widget.routes.list) {
            return false;
        }

        widget.$dataTables = widget.$table.DataTable({
            serverSide: true,
            ajax: {
                url: widget.routes.list,
                type: 'POST',
                data: function (d) {
                    d.room = widget.$roomFilter.val();
                    d.status = widget.$statusFilter.find('.filters-nav__item--active .filters-nav__button').data('status');
                }
            },
            paging: false,
            searching: true,
            dom: 'lrtip',
            info: false,
            autoWidth: false,
            columnDefs: [
                {
                    orderable: true,
                    targets: 0,
                    width: 'calc(50% - 231px)',
                    data: 'name',
                    className: 'restaurant-tables__cell restaurant-tables__name lemmon-table__cell lemmon-table__cell--highlight'
                },
                {
                    orderable: false,
                    targets: 1,
                    width: '25%',
                    data: 'room',
                    className: 'restaurant-tables__cell restaurant-tables__room lemmon-table__cell'
                },
                {
                    orderable: false,
                    targets: 2,
                    width: '25%',
                    data: 'is_busy',
                    className: 'restaurant-tables__cell restaurant-tables__status lemmon-table__cell'
                },
                {
                    orderable: false,
                    targets: 3,
                    width: '231px',
                    data: 'actions',
                    className: 'restaurant-tables__cell restaurant-tables__actions lemmon-table__cell lemmon-table__actions'
                },
            ],
            createdRow: function (row, data, dataIndex) {
                $(row).addClass(['restaurant-tables__row', 'lemmon-table__row']);
            },
            language: {
                zeroRecords: () => widget.$statusFilter.find('.filters-nav__item--active .filters-nav__button').data('no-records'),
            },
        });

        widget.initRefresh();

        return true;
    }

    initRefresh() {
        const widget = this;

        widget.refreshInterval = setInterval(function () {
            widget.$dataTables.draw();
        }, 15000);
    }

    attachEventListeners() {
        const widget = this;

        widget.$statusFilter.find('.filters-nav__button').on('click', function (e) {
            const $this = $(this);
            const $parent = $this.parent();

            const activeItemClass = 'filters-nav__item--active';

            if (!$parent.hasClass(activeItemClass)) {
                $parent.siblings().removeClass(activeItemClass);
                $parent.addClass(activeItemClass);
                widget.$dataTables.draw();
            }
        });

        widget.$roomFilter.on('select2:select', function (e) {
            widget.$dataTables.draw();
        });

        widget.$table.on('click', '.restaurant-tables__pay', function (e) {
            e.preventDefault();

            $('.lemmon-modal__back').addClass('d-none');
            $('.lemmon-modal__edit').removeClass('d-none');
            $('.final-print-button').attr('data-partial-pay', '{}');

            const $this = $(this);

            const $row = $this.closest('.restaurant-tables__row');
            const tableName = $row.find('.restaurant-tables__name').text();

            widget.orderSummary.open({
                tableName: tableName,
                tableId: $this.attr('data-table'),
            });
        });

        widget.$table.on('click', '.restaurant-tables__status-update', function (e) {
            const $this = $(this);

            $.ajax({
                type: 'POST',
                url: widget.routes.updateStatus,
                data: {
                    table_id: $this.data('id'),
                    new_status: $this.data('new-status'),
                },
                success: function (data) {
                    widget.$dataTables.draw();
                }
            });
        })


    }

    clear() {
        this.$dataTables.draw()
        this.orderSummary.close();
    }
}

const staffTables = new StaffTables('restaurant-tables');

class ProductStatusConfirmationDialog extends LemmonModal {
    enableClass = 'product-status-confirmation--enable';
    disableClass = 'product-status-confirmation--disable'

    constructor(id) {
        super(id);
        this.$productName = this.$interface.find('.product-status-confirmation__placeholder');
    }

    open(config) {
        this.updateUI(config);
        super.open();
    }

    close() {
        this.updateUI({
            productName: '',
            productType: '',
            productId: '',
        });

        super.close();
    }

    updateUI(config) {
        this.$productName.html(config.productName);
        this.$confirmButton.attr('data-id', config.productId);
        this.$confirmButton.attr('data-type', config.productType);

        if (Object.hasOwn(config, 'newStatus')) {
            this.$confirmButton.attr('data-new-status', config.newStatus);
            this.$interface.addClass(config.newStatus == 'available' ? this.enableClass : this.disableClass);
        } else {
            this.$confirmButton.removeAttr('data-new-status');
            this.$interface.removeClass([this.enableClass, this.disableClass]);
        }
    }
}

class StaffProducts {
    statuses = {
        enable: "enable",
        disable: "disable",
    };

    constructor(id) {
        this.$table = $(`#${id}`);
        this.updateUrl = this.$table.data('update-url');
        this.confirmationDialog = new ProductStatusConfirmationDialog('product-status-confirmation');
        this.notification = new LemmonNotification('product-status-updated-notification');
        this.initDataTables();
        this.clearBrowserCachedInputStatuses();
        this.attachEventListeners();
    }

    initDataTables() {
        this.$dataTables = this.$table.DataTable({
            paging: false,
            searching: true,
            info: false,
            columnDefs: [
                {
                    orderable: false,
                    targets: 0
                },
                {
                    orderable: true,
                    targets: 1
                },
            ],
        });

        const $dataTablesWrapper = this.$table.closest('.dataTables_wrapper');
        $dataTablesWrapper.addClass('product-table-datatable');

        const $searchInput = $dataTablesWrapper.find('.dataTables_filter input');
        $searchInput.attr('placeholder', 'Search a product on the list');
        $searchInput.addClass('input');
    }

    clearBrowserCachedInputStatuses() {
        this.$table.find('.checkbox__input[checked]').prop('checked', true);
        this.$table.find('.checkbox__input:not([checked])').prop('checked', false);
    }

    attachEventListeners() {
        const widget = this;

        widget.$table.on('click', '.checkbox__input', function (e) {
            e.preventDefault();

            const $this = $(this);
            const $row = $this.closest('.lemmon-table__row');

            const item = {
                productId: $row.attr('data-id'),
                productType: $row.attr('data-type'),
                productName: $row.find('.product-table__name').text(),
                newStatus: widget.getStatus($this.prop('checked')),
            }

            widget.confirmationDialog.open(item);
        });

        widget.confirmationDialog.$confirmButton.on('click', function (e) {
            const $this = $(this);

            const payload = {
                id: $this.attr('data-id'),
                type: $this.attr('data-type'),
                status: $this.attr('data-new-status'),
            };

            if (!widget.updateUrl) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: widget.updateUrl,
                dataType: 'json',
                data: payload,
                success: function (data) {
                    if (data.success === false) {
                        return;
                    }

                    widget.updateCheckbox(payload);
                    widget.confirmationDialog.close();
                    widget.notification.flash();
                }
            });
        });
    }

    updateCheckbox(config) {
        this.$table.find(`.lemmon-table__row[data-type="${config.type}"][data-id="${config.id}"] .checkbox__input`).prop('checked', this.translateStatus(config.status));
    }

    getStatus(checked) {
        if (checked) {
            return this.statuses.enable;
        }

        return this.statuses.disable;
    }

    translateStatus(status) {
        if (status == this.statuses.enable) {
            return true;
        }

        return false;
    }
}

const staffProducts = new StaffProducts('product-table');

class HeaderController {
    constructor() {
        this.$links = $('.header__link');

        this.disableLinksOnSmallScreens();
    }

    disableLinksOnSmallScreens() {
        this.$links.on('click', function (e) {
            if (LemmonUtility.isSmallScreen()) {
                e.preventDefault();
            }
        });
    }


}

const headerController = new HeaderController();
