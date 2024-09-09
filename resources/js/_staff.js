$(document).ready(function () {
    initDashboardTableWidgets();
})

let statusUpdateRequestComplete = true;
let scrollPosition = null;

//Callback to reapply all js after ajax
function initDashboardTableWidgets() {
    reloadMasonry();
    initSelect2();
}

function initSelect2() {
    // Clean select2 leftovers
    $('body > .select2-container').remove();

    $(".status__change").select2({
        width: '100%',
        minimumResultsForSearch: -1,
        selectionCssClass: ':all:'
    }); 
}

const $gridView = $('.grid-view-screen');

$gridView.on('layoutComplete', function() {
    $(this).addClass('masonry-initialized');
})

function reloadMasonry() {
    if ($gridView.length) {
        if ($gridView.hasClass('masonry-initialized')) {
            $gridView.masonry('destroy').removeClass('masonry-initialized');
            $gridView.removeData('masonry');
        }

        $gridView.masonry({
            itemSelector: '.table-card',
            percentPosition: true,
            gutter: 15,
        });
    }
}

//Toggle an order's food/bar statuses
function toggleItemsStatus(orderID, type = null, foodTypeId = null) {
    statusUpdateRequestComplete = false;
    $.ajax({
        type: "PUT",
        url: $('.dashboard__table').data('put-url'),
        data: {
            orderID,
            type,
            foodTypeId
        },
        complete: function (data) {
            statusUpdateRequestComplete = true;
            refreshOrders();
        }
    })
}

function refreshOrders() {
    scrollPosition = window.scrollY;

    $.ajax({
        type: 'GET',
        url: $('.dashboard__table').data('get-url'),
        data: {
            type: 'active'
        },
        success: function (data) {
            $('.dashboard__table').html(data);

            refreshOverview();
            initDashboardTableWidgets();

            if(scrollPosition !== null) {
                window.scroll({
                    left: 0,
                    top: scrollPosition,
                    behavior: "instant",
                });
                scrollPosition = null;
            }
        },
        complete: function (data) {
            if (data.status != 200) {
                attemptPageReload();
            }
        }
    })
}

function refreshUI() {
    if (statusUpdateRequestComplete) {
        if ($('body').hasClass('page-staff--active')) {
            refreshOrders();
        } else {
            refreshOverview();
        }
    }
}

// Periodically refresh orders from current view
const refreshInterval = setInterval(refreshUI, 15 * 1000);

function refreshOverview() {
    const $overview = $('.dashboard__overview');
    const url = $overview.find('.dashboard__overview--top').data('overview-url');
    const status = $('.list-view-screen').length ? 'active' : 'closed';

    $.ajax({
        type: 'GET',
        url: url,
        data: {
            status: status,
        },
        success: function (data) {
            $overview.html(data.html);

            $('#dashboard-table').data('print-receipt-url', data.print_receipt_url);

            data.orders_to_print.forEach(item => {
                item.orders.forEach(order => receiptPrinter.getAndPrintOrders(order, item.type));
            });
        },
        complete: function (data) {
            if (data.status != 200) {
                attemptPageReload();
            }
        },
    });
}

function attemptPageReload() {
    if (receiptPrinter.printingJobs.hasJobs()) {
        setTimeout(attemptPageReload, 5000);
    } else {
        setTimeout(function() {
            location.reload();
        }, 1000);
    }
}

$('#dashboard-table').on('select2:select', '.status__change', function (e) {

    const $this = $(this);
    const $orderCard = $this.closest('.table-card');
    const $parent = $this.closest('.status');
    const $statusInner =  $parent.find('.status__inner');
    const newStatus = $this.val();
    const orderId = $orderCard.data('id');

    $statusInner.attr('class', '');
    $statusInner.addClass(['status__inner', `status-${newStatus}`]);

    if (newStatus === 'closed') {
        $orderCard.remove();
    }

    statusUpdateRequestComplete = false;

    $.ajax({
        type: "PUT",
        url: $('#dashboard-table').data('put-url'),
        data: {
            orderID: orderId,
            orderStatus: newStatus,
        },
        complete: function (data) {
            statusUpdateRequestComplete = true;
            refreshUI();

            const printUrl = $('#dashboard-table').data('print-receipt-url');

            if (newStatus === 'closed' && printUrl.length) {
                receiptPrinter.getAndPrintReceipt({
                    url: printUrl,
                    orderId: orderId,
                    onlyCash: true
                });
            }
        }
    });

});

$('#dashboard-table').on('click', '.toggle-items-status', function(e) {
    const $this = $(this);
    const $orderCard = $this.closest('.table-card');

    $orderCard.addClass('move-to');
    const orderId = $orderCard.data('id');

    toggleItemsStatus(orderId, $this.data('items-type'), $this.data('food-type-id'));
});

$('#dashboard-table').on('click', '.item-action', function (e) {
    $('.modal-actions .overlay').css({'display': 'flex'});
    $('#cancel-order-btn').attr('data-url', e.target.getAttribute('data-url'));
});

$(".cancel-order-btn").on("click", function (e) {
    const url = e.target.getAttribute("data-url");

    jQuery.ajax({
        type: 'DELETE',
        url,
        success: function (data) {
            if(data.success === false && data.error !== undefined) {
                alert(data.error);
            }

            $('#overlay-modal').css('display', 'none');
            refreshOrders();
        },
        error: function (data) {
        }
    })
});

$('.close-modal, #close-popup-btn').on('click', function () {
    $('.overlay').hide();
});


$('.product-type').on('click', function(e) {
    const filters = ['bar', 'restaurant'];
    const $body = $('body');
    const type = $(this).data('product-type');

    $body.removeClass(['page-staff--showing-bar', 'page-staff--showing-restaurant']);

    if (filters.includes(type)) {
        $('body').addClass(`page-staff--showing-${type}`);
    }
});

// const staffDashboard = {
//     elements: {
//         $dashboardTable: $('.dashboard__table')
//     },
//     checkForNewOrders: function () {
//         const widget = this;

//         $.ajax({
//             type: 'GET',
//             url: widget.elements.$dashboardTable.data('check-url'),
//             success: function (data) {
//                 console.log(data);
//             }
//         });
//     }
// }