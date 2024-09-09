$(document).ready(function () {
    if ($('#product-table').length) {
        let productTable = $('#product-table');

        let productDataTable = productTable.on("draw.dt", function (e, dt, type, indexes) {
            if ($('.list-tables').length) {
                $('#product-table').closest('.list-tables').find('.summary').html($('#product-table_info').text());
            } else {
                $('.summary').html($('#product-table_info').text());
            }

        }).DataTable({
            processing: true,
            stateSave: true,
            serverSide: false,
            pagingType: 'simple_numbers',
            scrollX: true,
            dom: "tip",
            columnDefs: [
                { width: '80px', targets: 4 }
            ],
            language: {
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
            ajax: productTable.data('table-url'),
            columns: [
                {data: 'service', name: 'Service'},
                {data: 'category', name: 'Category'},
                {data: 'food_type_id', name: 'Food type'},
                {data: 'name', name: 'Name'},
                {data: 'price', name: 'Price'},
                {data: 'special_price', name: 'Promotional Price'},
              /*  {data: 'description', name: 'Description'},*/
                {data: 'status', name: 'Status'},
                {data: 'action', name: 'Action', sortable: false}
            ],
            drawCallback: function (settings) {
                var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                pagination.toggle(this.api().page.info().pages > 1);
            },
            initComplete: function () {
                $.fn.dataTable.ext.search.push(
                    function (settings, data) {
                        if (settings.nTable.id === 'product-table') {

                            let selectedItem;

                            if ($('#dashboard_product_status').length) {
                                selectedItem = $('#dashboard_product_status').val();
                            } else {
                                selectedItem = $('.status__change').val();
                            }

                            return selectedItem === "" || selectedItem === data[6];
                        } else {
                            return true;
                        }
                    }
                );
                productDataTable.search('').draw(false);
            }
        });

        $('.data-search').keyup(function () {
            productDataTable.search($(this).val()).draw();
        });

        $('#dashboard_product_status, #list_product_status').on('change', function () {
            productDataTable.draw();
            $(this).closest('div').attr('class', '').addClass('status-' + $(this).children("option:selected").val())
        });
    }

    if ($('#extra-table').length) {
        let extraTable = $('#extra-table')

        let extraDataTable = extraTable.on("draw.dt", function (e, dt, type, indexes) {
            $('.summary').html($('#extra-table_info').text());
        }).DataTable({
            processing: true,
            stateSave: true,
            serverSide: false,
            order: [[1, 'asc']],
            "ordering": false,
            scrollX: true,
            agingType: 'simple_numbers',
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
            ajax: extraTable.data('table-url'),
            columns: [
                {data: 'title', name: 'Extra Title'},
                {data: 'description', name: 'Extra Description'},
                {data: 'status', name: 'Status'},
                {data: 'action', name: 'Action', sortable: false}
            ],
            initComplete: function () {
                $.fn.DataTable.ext.search.push(
                    function (settings, data, dataIndex) {
                        var selectedItem = $('.status__change').val();
                        if (selectedItem === "" || selectedItem === data[4]) {
                            return true;
                        }
                        return false;
                    }
                );
                extraDataTable.search('').draw(false);
            }
        });

        $('.data-search').keyup(function () {
            extraDataTable.search($(this).val()).draw();
        });
    };

    if ($('#services-table').length) {
        let serviceTable = $('#services-table')

        let serviceDataTable = serviceTable.on("draw.dt", function (e, dt, type, indexes) {
            $('.summary').html($('#services-table_info').text());
        }).DataTable({
            processing: true,
            stateSave: true,
            serverSide: false,
            order: [[4, 'asc']],
            "ordering": false,
            scrollX: true,
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
            ajax: serviceTable.data('table-url'),
            columns: [
                {data: 'order', name: 'Order'},
                {data: 'name', name: 'Name'},
                {data: 'productsNumber', name: 'Number of products'},
                {data: 'status', name: 'Status'},
                {data: 'action', name: 'Action', sortable: false}
            ],
            initComplete: function () {
                $.fn.DataTable.ext.search.push(
                    function (settings, data, dataIndex) {
                        let selectedItem = $('.status__change').val();
                        if (selectedItem === "" || selectedItem === data[4]) {
                            return true;
                        }
                        return false;
                    }
                );
                serviceDataTable.search('').draw(false);
            },
            rowReorder: {
                dataSrc: 'id'
            }
        });


        $('.data-search').keyup(function () {
            serviceDataTable.search($(this).val()).draw();
        });

        serviceDataTable.on('row-reorder', function (e, diff, edit) {
            serviceDataTable.rowReorder.disable();

            let data = [];

            $.each(diff, function (item, value) {
                data[value.oldData] = value.newPosition;
            })

            $.ajax({
                type: 'PUT',
                url: $(this).data('order-update-url'),
                data: {data},
                success: function (data) {
                    serviceDataTable.ajax.reload();
                    serviceDataTable.rowReorder.enable();
                }
            });
            serviceDataTable.draw();
        });

        $('.status__change').on('change', function () {
            serviceDataTable.draw();
            $(this).closest('div').attr('class', '').addClass('status-' + $(this).children("option:selected").val())
        });

    }

    if ($('#restaurant-table').length) {

        let restaurantTable = $('#restaurant-table');

        let restaurantDataTable = restaurantTable.on("draw.dt", function (e, dt, type, indexes) {
            $('.summary').html($('#restaurant-table_info').text());
        }).DataTable({
            processing: true,
            stateSave: true,
            serverSide: false,
            scrollX: true,
            pagingType: 'simple_numbers',
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
            ajax: restaurantTable.data('table-url'),
            columns: [
                {data: 'name', name: 'Restaurant Name'},
                {data: 'onboarded_at', name: 'Onboarded at'},
                {data: 'onboarded_by', name: 'Onboarded by'},
                {data: 'status', name: 'Status'},
                {data: 'action', name: 'Action', sortable: false}
            ],
            initComplete: function () {
                $.fn.DataTable.ext.search.push(
                    function (settings, data, dataIndex) {
                        var selectedItem = $('.status__change').val();
                        if (selectedItem === "" || selectedItem === data[3]) {
                            return true;
                        }
                        return false;
                    }
                );
                restaurantDataTable.search('').draw(false);
            }
        });

        $('.data-search').keyup(function () {
            restaurantDataTable.search($(this).val()).draw();
        });

        $('.status__change').on('change', function () {
            restaurantDataTable.draw();
            $(this).closest('div').attr('class', '').addClass('status-' + $(this).children("option:selected").val())
        });
    }

    if ($('#category-table').length) {
        let categoryTable = $('#category-table')

        let categoryDataTable = categoryTable.on("draw.dt", function (e, dt, type, indexes) {
            $('.summary').html($('#category-table_info').text());
        }).DataTable({
            processing: true,
            stateSave: true,
            serverSide: false,
            order: [[3, 'asc']],
            "ordering": false,
            scrollX: true,
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
            ajax: categoryTable.data('table-url'),
            columns: [
                {data: 'order', name: 'ID'},
                {data: 'name', name: 'Category Name'},
                {data: 'productNumber', name: 'Number of Products'},
                {data: 'status', name: 'Status'},
                {data: 'action', name: 'Action', sortable: false}
            ],
            initComplete: function () {
                $.fn.DataTable.ext.search.push(
                    function (settings, data, dataIndex) {
                        let selectedItem = $('.status__change').val();
                        if (selectedItem === "" || selectedItem === data[3]) {
                            return true;
                        }
                        return false;
                    }
                );
                categoryDataTable.search('').draw(false);
            },
            rowReorder: {
                dataSrc: 'id'
            },
            order: [[3, 'asc']],
        });

        categoryDataTable.on('row-reorder', function (e, diff, edit) {
            categoryDataTable.rowReorder.disable();

            let data = {};

            $.each(diff, function (item, value) {
                data[value.oldData] = value.newPosition;
            })

            $.ajax({
                type: 'PUT',
                url: $(this).data('order-update-url'),
                data: {data},
                success: function (data) {
                    categoryDataTable.ajax.reload();
                    categoryDataTable.rowReorder.enable();
                }
            });
            categoryDataTable.draw(false);
        });

        $('.data-search').keyup(function () {
            categoryDataTable.search($(this).val()).draw();
        });

        $('.status__change').on('change', function () {
            categoryDataTable.draw();
            $(this).closest('div').attr('class', '').addClass('status-' + $(this).children("option:selected").val())
        });

    }

    if ($('#staff-table').length) {
        let staffTable = $('#staff-table');

        let staffDataTable = staffTable.on("draw.dt", function (e, dt, type, indexes) {
            if ($('.list-tables').length) {
                $('#staff-table').closest('.list-tables').find('.summary').html($('#staff-table_info').text());
            } else {
                $('.summary').html($('#staff-table_info').text());
            }
        }).DataTable({
            processing: true,
            stateSave: true,
            serverSide: false,
            scrollX: true,
            dom: "tip",
            language: {
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
            ajax: staffTable.data('table-url'),
            columns: [
                {data: 'name', name: 'Employee Name'},
                {data: 'staff_type', name: 'Staff Type'},
                {data: 'status', name: 'Status'},
                {data: 'action', name: 'Action', sortable: false}
            ],
            initComplete: function () {
                $.fn.dataTable.ext.search.push(
                    function (settings, data) {
                        if (settings.nTable.id === 'staff-table') {
                            let selectedItem = $('.status__change').val();

                            if ($('#dashboard_staff_status').length) {
                                selectedItem = $('#dashboard_staff_status').val();
                            }
                            return selectedItem === "" || selectedItem === data[2];
                        }
                        return true;
                    }
                );
                staffDataTable.search('').draw(false);
            }
        });

        $('#refresh-button').on('click', function() {
            staffDataTable.draw();
        });

        $('.data-search').keyup(function () {
            staffDataTable.search($(this).val()).draw();
        });
        $('#dashboard_staff_status, #list_staff_status').on('change', function () {
            staffDataTable.draw();
            $(this).closest('div').attr('class', '').addClass('status-' + $(this).children("option:selected").val())
        });
    }

    if ($('#restaurant-tables-table').length) {
        let tablesTable = $('#restaurant-tables-table')

        let tablesDataTable = tablesTable.on("draw.dt", function (e, dt, type, indexes) {
            if ($('.list-tables').length) {
                $('#restaurant-tables-table').closest('.list-tables').find('.summary').html($('#restaurant-tables-table_info').text());
            } else {
                $('.summary').html($('#restaurant-tables-table_info').text());
            }
        }).DataTable({
            processing: true,
            stateSave: true,
            serverSide: false,
            scrollX: true,
            dom: "tip",
            language: {
                search: "Search in table:",
                paginate: {
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
            ajax: tablesTable.data('table-url'),
            columns: [
                {data: 'name', name: 'Table Name'},
                {data: 'room', name: 'Room'},
                {data: 'type', name: 'Type'},
                {data: 'status', name: 'Status'},
                {data: 'action', name: 'Action', sortable: false, width: '50%' }
            ],
            initComplete: function () {
                $.fn.dataTable.ext.search.push(
                    function (settings, data) {
                        if (settings.nTable.id === 'restaurant-tables-table') {
                            let selectedItem = $('.status__change').val();

                            if ($('#dashboard_table_status').length) {
                                selectedItem = $('#dashboard_table_status').val();
                            }

                            return selectedItem === "" || selectedItem === data[2];
                        }
                        return true;
                    }
                );
                tablesDataTable.search('').draw(false);
            }
        });
        $('.data-search').keyup(function () {
            tablesDataTable.search($(this).val()).draw();
        });

        $('#dashboard_table_status, #list_tables_status').on('change', function () {
            tablesDataTable.draw();
            $(this).closest('div').attr('class', '').addClass('status-' + $(this).children("option:selected").val())
        });
    }

    if ($('#orders-table').length) {
        let ordersTable = $('#orders-table')

        let ordersDataTable = ordersTable.on("draw.dt", function (e, dt, type, indexes) {
            $('.summary').html($('#orders-table_info').text());
        }).DataTable({
            processing: true,
            stateSave: true,
            scrollX: true,
            serverSide: false,
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
            ajax: ordersTable.data('table-url'),
            columns: [
                {data: 'id', name: 'ID'},
                {data: 'display_id', name: 'Internal ID'},
                {data: 'table_id', name: 'Table'},
                {data: 'amount', name: 'Amount'},
                {data: 'tips', name: 'Tips'},
                {data: 'created_at', name: 'Date Time'},
                {data: 'status', name: 'Status'},
                {data: 'payment', name: 'Payrexx ID'},
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
                ordersDataTable.search('').draw(false);
            }
        });
        $('.data-search').keyup(function () {
            ordersDataTable.search($(this).val()).draw();
        });

        $('.status__change').on('change', function () {
            ordersDataTable.draw();
            $(this).closest('div').attr('class', '').addClass('status-' + $(this).children("option:selected").val())
        });
    }
});

