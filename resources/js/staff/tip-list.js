import priceFormat from "../helpers/price-format.js";

(function () {
        // Get tip list URL and current currency
        const actionUrl = document.querySelector('[name="tips-list-get-url"]').getAttribute('content');

        // Date picker for dashboard
        const startDate = new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        endDate = new Date();

        let selectedStartDate = getFormattedDate(startDate),
        selectedEndDate = getFormattedDate(endDate);

        function getFormattedDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hour = String(date.getHours()).padStart(2, '0');
            const minute = String(date.getMinutes()).padStart(2, '0');
            return `${year}/${month}/${day} ${hour}:${minute}`;
        }

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
        document.getElementById('btn-run-tips').addEventListener('click', () => refreshStatistics());
        document.getElementById('btn-reset-tips').addEventListener('click', () => resetStatistics());

        // Send ajax to refresh
        function refreshStatistics() {
            if(selectedStartDate === undefined || selectedStartDate === null) return;
            if(selectedEndDate === undefined || selectedEndDate === null) return;

            jQuery.ajax({
                type: 'POST',
                url: actionUrl,
                data: {
                    startDate: selectedStartDate,
                    endDate: selectedEndDate
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
            selectedStartDate = getFormattedDate(startDate);
            selectedEndDate = getFormattedDate(endDate);

            // reset date pickers
            jQuery('#date_from').datetimepicker('setOptions', {
                value: startDate,
            });
            jQuery('#date_to').datetimepicker('setOptions', {
                value: endDate,
            });

            // Send request to get new statistics
            refreshStatistics();
        }

        // Refresh UI after new statistics
        function refreshStatisticsUI(data) {
            if(data.tablesData === undefined || data.totalTips === undefined) {
                return;
            }

            // Reset tips container
            const tipsTableBody = document.getElementById('tips-table-body');
            tipsTableBody.innerHTML = '';

            Object.keys(data.tablesData).forEach(function (key) {
                const table = data.tablesData[key];

                if (table.tips) {
                    // Create table row
                    const tableRow = document.createElement('tr');

                    // Create table columns
                    const nameColumn = document.createElement('td'),
                        moneyColumn = document.createElement('td');

                    // Set name column
                    nameColumn.innerHTML = table.name;

                    // Add name column to table row
                    tableRow.prepend(nameColumn);

                    // Set money column
                    moneyColumn.innerHTML = priceFormat(table.tips);

                    // Add money column to table row
                    tableRow.append(moneyColumn);

                    // Append table row to table body
                    tipsTableBody.append(tableRow);
                }
            })

            // Show total
            document.getElementById('tips-table-total').innerHTML = priceFormat(data.totalTips);
        }
})();
