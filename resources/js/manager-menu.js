// #### Left column functionality ####
$(document).ready(function () {
    disableBox()
})

//Uncheck ALL categories in left collumn
function uncheck() {
    $('#categories-list :checkbox').each(function () {
        if (!$(this).is(':disabled')) {
            this.checked = false;
        }
    });
}

//Check ALL categories in left collumn
function check() {
    $('#categories-list :checkbox').each(function () {
        if (!$(this).is(':disabled')) {
            this.checked = true;
        }
    });
}

//Select categories to be inserted from left column
$('#select-all-categories').click(function (event) {
    if (this.checked) {
        check()
    } else {
        uncheck()
    }
});

//Disable boxes if exist in right
function disableBox() {
    let currentCategories = []

    let catList = document.querySelectorAll('.item-list-body input')

    document.querySelectorAll('.category-id').forEach((el, index) => {
        currentCategories[index] = el.value;
    })
    catList.forEach(el => {
        if (currentCategories.includes(el.value)) {
            el.disabled = true
        }
    })
}

$('#add-categories').click(function (event) {
    catList = $('.item-list-body input:checked')

    Array.from(catList).forEach((el, index) => {

        listCount = $('#sortable li').length
        title = el.parentNode.textContent

        li = $('<li>', {
            class: "menu-item-bar draggable",
        }).attr('data-id', el.value).append(title).appendTo('#sortable')

        mark = $('<span>', {
            class: 'toggleItem',
            style: 'float: right',
        }).attr('data-toggle-id', index).append('V').appendTo(li)

        firstDiv = $('<div>', {
            class: 'panel-collapse edit-item-menu collapse',
        }).attr('data-toogle', index).appendTo(li)


        secondDiv = $('<div>', {
            style: 'display: flex; flex-direction: column',
        }).appendTo(firstDiv)

        $('body').on('click', "[data-toggle-id='" + index + "']", function () {
            $(this).next().collapse('toggle');
        });

        label = $('<label>', {}).append('Name').appendTo(secondDiv)

        inp = $('<input>', {
            type: 'text',
            name: 'menu_items["' + listCount + '"][title]',
            value: title
        }).appendTo(secondDiv)

        inp = $('<input>', {
            type: 'hidden',
            class: 'category-id',
            name: 'menu_items["' + listCount + '"][product_category_id]',
            value: el.value
        }).appendTo(secondDiv)

        inp = $('<input>', {
            type: 'hidden',
            name: 'menu_items["' + listCount + '"][menu_order]',
            value: listCount
        }).appendTo(secondDiv)

        btn = $('<button>', {
            type: 'button',
            style: 'background-color: #a92222; color: white',
            class: 'pull-right btn-menu-select delete-sortable'
        }).append('Remove').appendTo(firstDiv)

        btn.on('click', function () {
            $(this).closest('li').remove()
        })

    })
    setOrder()
    uncheck()
    disableBox()
})

// #### Right column functionality ####

//Make inserted items sortable
$("#sortable").sortable({
    appendTo: document.body
});

//Toggle collapse
$(".toggleItem").on('click', function () {
    $(this).next().collapse('toggle');
});

//Delete categories from menu list
$(".delete-sortable").click(function () {
    $(this).closest('li').remove()
});

//Delete ALL items from list
function removeAll() {
    $('#sortable').empty();
}

// Set values for menu items order
$('#save-menu').click(function () {
    setOrder();
})

function setOrder() {
    var array = $('#sortable li input[name*="menu_order"]')
    var n = array.length;
    for (i = 0; i < n; i++) {
        array[i].value = i;
    }
}

$('body').on('mouseup', "#sortable li", function () {
    setOrder()
})

$(document).ready(function () {
    setOrder()
});

