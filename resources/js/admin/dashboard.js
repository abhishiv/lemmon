import numberFormat from "../helpers/number-format.js";
import priceFormat from "../helpers/price-format.js";

(function() {
    // Initialize select2
    jQuery('#restaurant').select2();

    // Date picker for dashboard
    const startDate = new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        endDate = new Date();

    let selectedStartDate = getFormattedDate(startDate),
    selectedEndDate = getFormattedDate(endDate),
    selectedRestaurant = 'restaurant';

    function getFormattedDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hour = String(date.getHours()).padStart(2, '0');
        const minute = String(date.getMinutes()).padStart(2, '0');
        return `${year}/${month}/${day} ${hour}:${minute}`;
    }

    // Get change event from select2
    jQuery('#restaurant').on('select2:select', (e) => selectedRestaurant = $(e.target).val());

    // From field
    jQuery('#date_from').datetimepicker({
         onShow: function (ct) {
             this.setOptions({
                maxDate:jQuery('#date_to').val()?jQuery('#date_to').val():false
             });
        },
        step: 15,
    }).on("change", function() {
        selectedStartDate = this.value;
    });

    // To field
    jQuery('#date_to').datetimepicker({
        maxDate: endDate,
        onShow: function (ct) {
            this.setOptions({
                minDate:jQuery('#date_from').val()? jQuery('#date_from').val() :false,
                maxDate: endDate,
            });
        },
        step: 15,
    }).on("change", function() {
       selectedEndDate = this.value;
    });

    // Add event listener to buttons
    $('#btn-run-stats').on('click', () => refreshStatistics());
    $('#btn-reset-stats').on('click', () => resetStatistics());

    // Send ajax to refresh
    function refreshStatistics() {
        if(selectedStartDate === undefined || selectedStartDate === null) return;
        if(selectedEndDate === undefined || selectedEndDate === null) return;

        jQuery.ajax({
            type: 'POST',
            url: $('#statistics-container').data('statistics-url'),
            data: {
                startDate: selectedStartDate,
                endDate: selectedEndDate,
                restaurant: selectedRestaurant,
            },
            dataType: 'json',
            success: function(data) {
                refreshStatisticsUI(data);
            },
            error: function(data) {
                console.error(data);
            }
        });

    }

    // Reset params for dashboard
    function resetStatistics() {
        selectedStartDate = getFormattedDate(startDate),
        selectedEndDate = getFormattedDate(endDate),
        selectedRestaurant = 'restaurant';

        // reset date pickers
        jQuery('#date_from').datetimepicker('setOptions', {
            value: startDate,
        });
        jQuery('#date_to').datetimepicker('setOptions', {
            value: endDate,
        });

        // reset select2
        jQuery('#restaurant').val(selectedRestaurant).trigger('change');

        // send query for new stats
        refreshStatistics();
    }

    // Refresh UI after new statistics
    function refreshStatisticsUI(data) {

        jQuery('.stats-restaurant-name').html(data['restaurant_name']);

        jQuery('.stats-dates').html(' ' + data['date_from'] + ' - ' + data['date_to']);

        // Money
        jQuery('#order_total_money').html(priceFormat(data['order_total_money']));

        jQuery('#order_cash_money').html(priceFormat(data['order_cash_money']));

        jQuery('#order_card_money').html(priceFormat(data['order_card_money']));

        jQuery('#order_terminal_money').html(priceFormat(data['order_terminal_money']));

        jQuery('#order_tip_money').html(priceFormat(data['order_tip_money']));

        // Order count
        jQuery('#order_total_count').html(numberFormat(data['order_total_count']));

        jQuery('#order_cash_count').html(numberFormat(data['order_cash_count']));

        jQuery('#order_terminal_count').html(numberFormat(data['order_terminal_count']));

        jQuery('#order_card_count').html(numberFormat(data['order_card_count']));
    }

    // Export statistics

    let currentExportJobId = null;
    let exportInterval = null;

    $('#btn-export-stats').on('click', () => exportStatistics());
    $('#btn-download-stats').on('click', () => downloadStatistics());

    function exportStatistics() {
        const disableButt = () => {
            $('#btn-export-stats').css('pointer-events', 'none').html('Loading, please wait...');
        }

        const enableButt = () => {
            $('#btn-export-stats').css('pointer-events', 'auto').html('Initiate Export');
        }

        jQuery.ajax({
            type: 'POST',
            url: $('#statistics-container').data('export-url'),
            data: {
                startDate: selectedStartDate,
                endDate: selectedEndDate,
                restaurant: selectedRestaurant,
            },
            dataType: 'json',
            beforeSend: function() {
                disableButt();
            },
            success: function(data) {
                enableButt();
                if(data.success !== true) {
                    return;
                }

                currentExportJobId = data.job_id;

                startExportInterval();
                $('#btn-download-stats').removeClass('d-none').css('pointer-events', 'none').html('Preparing...').data('download-url', '');
            },
            error: function(data) {
                console.error(data);
            }
        });
    }

    function verifyExportJobDone() {
        if(currentExportJobId === null) {
            clearExportInterval();
        }

        jQuery.ajax({
            type: 'POST',
            url: $('#statistics-container').data('export-get-url'),
            data: {
                job_id: currentExportJobId,
            },
            dataType: 'json',
            success: function(data) {
                if(data.status === undefined || data.status === null) {
                    return;
                }
                // if the job has received an invalid response or an error
                if(data.status === 'invalid') {
                    clearExportInterval();
                    $('#btn-download-stats').css('pointer-events', 'none').html('Error!');
                }
                // if job is in processing
                if(data.status === 'processing') {
                    return;
                }

                // if the job has been processed then get the url and clear the interval
                if(data.result === null || data.result  === undefined || data.result.length === 0) {
                    clearExportInterval();
                    $('#btn-download-stats').css('pointer-events', 'none').html('Error!');
                }

                // First stop the interval
                clearExportInterval();
                // Then set the button to allow download
                $('#btn-download-stats').css('pointer-events', 'auto').html('Download').data('download-url', data.result);

            },
            error: function(data) {
                clearExportInterval();
                $('#btn-download-stats').css('pointer-events', 'none').html('Error!');
            }
        })
    }

    function startExportInterval() {
        if(exportInterval !== null) return false;

        // set interval at every 5 seconds
        exportInterval = setInterval(() => verifyExportJobDone(), 5000);
    }

    function clearExportInterval() {
        if(exportInterval === null) return false;

        clearInterval(exportInterval);
        exportInterval = null;
    }

    function downloadStatistics() {
        const url = $('#btn-download-stats').data('download-url');
        if(url === undefined || url === null || url === '') {
            return false;
        }

        location.href = url;
        $('#btn-download-stats').addClass('d-none');
    }
})();
