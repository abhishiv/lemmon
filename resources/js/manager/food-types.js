document.addEventListener('DOMContentLoaded', function () {
    let foodTypesTable = $('#food-types-table');

    let foodTypesDataTable = foodTypesTable.DataTable({
        processing: true,
        stateSave: true,
        scrollX: true,
        serverSide: false,    
        rowReorder: true,
        "dom": "tip",
        "language": {
            "search": "Search in table:",
            "paginate": {
                "previous": "",
                "next": "",
            },
        },
        oLanguage: {
            sInfo: "( _START_ to _END_ of _MAX_ )",
            sInfoEmpty: "",
            sInfoFiltered: "( _TOTAL_ of _MAX_  )",
            sSearch: "Filter records:",
            infoFiltered: ""
        },
        ajax: foodTypesTable.data('table-url'),
        columns: [
            {data: 'order', name: 'Order'},
            {data: 'name', name: 'Name'},
            {data: 'created_at', name: 'Date Time'},
            {data: 'action', name: 'Action', sortable: false}
        ],
        initComplete: function () {
            $.fn.DataTable.ext.search.push(
                function (settings, data, dataIndex) {
                    let selectedItem = $('.status__change').val();
                    if (selectedItem === "" || selectedItem === data[5]) {
                        return true;
                    }
                    return false;
                }
            );
            foodTypesDataTable.search('').draw(false);
        }
    });
    $('.data-search').keyup(function () {
        foodTypesDataTable.search($(this).val()).draw();
    });

    foodTypesDataTable.on('row-reordered', function (e, diff, edit) {
        foodTypesDataTable.rowReorder.disable();

        let data = [];

        $.each(diff, function (item, value) {
            console.log(item, value);
            data[value.oldPosition] = value.newPosition;
        });

        $.ajax({
            type: 'PUT',
            url: $(this).data('update-url'),
            data: {data},
            success: function (data) {
                foodTypesDataTable.ajax.reload();
                foodTypesDataTable.rowReorder.enable();
            }
        });
        foodTypesDataTable.draw();
    });
});