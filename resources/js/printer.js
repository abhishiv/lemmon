class PrinterQueue {
    queue = {};

    add(job) {
        if (!this.exists(job)) {
            this.queue[job.id] = job;
        }
    }

    get(jobId) {
        return this.queue[jobId] ?? null;
    }

    getNextJob() {
        const jobs = Object.keys(this.queue);

        if (jobs.length) {
            return this.queue[jobs[0]];
        }

        return false;
    }

    getAllJobs() {
        return Object.values(this.queue);
    }

    hasJobs() {
        return Object.keys(this.queue).length > 0;
    }

    remove(job) {
        if (this.exists(job)) {
            delete this.queue[job.id];
        }
    }

    exists(job) {
        if (this.queue[job.id] !== undefined) {
            return true;
        }

        return false;
    }

    reset() {
        this.queue = {};
    }
}

class PersistentPrinterQueue {
    printerQueue = 'printer-queue';
    printerJobs = 'printer-jobs';

    add(job) {
        const currentQueue = this.getQueue();

        if (currentQueue.includes(job.id)) {
            return false;
        }

        currentQueue.push(job.id);
        localStorage.setItem(this.printerQueue, JSON.stringify(currentQueue));

        const currentJobs = this.getJobs();
        currentJobs[job.id] = job;
        localStorage.setItem(this.printerJobs, JSON.stringify(currentJobs));
    }

    remove(job) {
        const currentQueue = this.getQueue().filter(jobId => jobId !== job.id);
        localStorage.setItem(this.printerQueue, JSON.stringify(currentQueue));

        const currentJobs = this.getJobs();
        delete currentJobs[job.id];
        localStorage.setItem(this.printerJobs, JSON.stringify(currentJobs));
    }

    getQueue() {
        const queue = JSON.parse(localStorage.getItem(this.printerQueue));

        return queue ? queue : [];
    }

    getJobs() {
        const jobs = JSON.parse(localStorage.getItem(this.printerJobs));

        return jobs ? jobs : {};
    }

    getAllandReset() {
        const jobs = this.getJobs();

        this.reset();

        return Object.values(jobs);
    }

    reset() {
        localStorage.removeItem(this.printerQueue);
        localStorage.removeItem(this.printerJobs);
    }
}

class InfoBubble {
    constructor(printer = null) {
        this.printer = printer;
        this.$popUp = $('.printer-bubble');
        this.$popUpText = this.$popUp.find('.printer-bubble__text');
        this.$defaultMessage = this.$popUp.find('.printer-bubble__default-message');
    }

    showDefault() {
        this.$popUp.attr('job-status', 'default_message');
        this.$defaultMessage.addClass('printer-bubble__default-message--active');
        this.show();
    }

    hideDefault() {
        this.$defaultMessage.removeClass('printer-bubble__default-message--active');
    }

    show() {
        this.$popUp.addClass('printer-bubble--active');
    }

    setMessage(message) {
        if (!this.printer.messages || !this.printer.messages[message]) {
            return;
        }

        this.hideDefault();
        this.$popUp.attr('job-status', message);
        this.$popUpText.html(this.printer.messages[message]);
    }

    hide() {
        this.$popUp.removeClass('printer-bubble--active');
        this.$popUp.removeAttr('job-status');
        this.hideDefault();
    }
}

const persistentQueue = new PersistentPrinterQueue();

class PrinterUtility {
    async generateHostName(serialNo) {
        const epsonDomain = "omnilinkcert.epson.biz";

        const buff = new Uint8Array([].map.call(serialNo, (c) => c.charCodeAt(0))).buffer;
        const hashHex = await crypto.subtle.digest('SHA-256', buff);

        const base32Text = this.encodeBase32(new Uint8Array(hashHex));

        return base32Text + '.' + epsonDomain;
    }

    encodeBase32(byteArray) {
        const bit5toBase32Dic = {
            '00000': 'A', '00001': 'B', '00010': 'C', '00011': 'D',
            '00100': 'E', '00101': 'F', '00110': 'G', '00111': 'H',
            '01000': 'I', '01001': 'J', '01010': 'K', '01011': 'L',
            '01100': 'M', '01101': 'N', '01110': 'O', '01111': 'P',
            '10000': 'Q', '10001': 'R', '10010': 'S', '10011': 'T',
            '10100': 'U', '10101': 'V', '10110': 'W', '10111': 'X',
            '11000': 'Y', '11001': 'Z', '11010': '2', '11011': '3',
            '11100': '4', '11101': '5', '11110': '6', '11111': '7'
        };

        var byteText = '';
        for (var i = 0; i < byteArray.length; i++) {
            byteText += String(Number(byteArray[i]).toString(2)).padStart(8, '0');
        }

        const bit5Array = byteText.match(/.{1,5}/g);
        var base32Text = '';
        for (var i = 0; i < bit5Array.length; i++) {
            var bit5Text = bit5Array[i];
            if (bit5Text.length < 5) {
                bit5Text = bit5Text + '0'.repeat(5 - bit5Text.length);
            }
            if (bit5Text in bit5toBase32Dic) {
                base32Text += bit5toBase32Dic[bit5Text];
            }
        }

        return base32Text;
    }
}

const printerUtility = new PrinterUtility();

class Printer {
    printerModel = 'TM-m30III';
    config = {
        printer: null,
        options: {
            crypto: false,
            buffer: false
        },
        lineLength: 48,
    };
    device = null;
    printer = null;
    restaurant = null;
    orders = null;
    labels = null;
    messages = null;
    status = null;
    columns = [
        {
            headerKey: 'description',
            productKey: 'name',
            width: 32,
            align: 'left',
        },
        {
            headerKey: 'quantity',
            productKey: 'quantity',
            width: 4,
            align: 'right',
        },
        {
            headerKey: 'total',
            productKey: 'price',
            width: 12,
            align: 'right',
        }
    ];
    currency = '';
    currentJob = null;
    attemptingConnection = false;
    connectAttempts = 0;
    logo = {
        src: null,
        data: null,
    };

    constructor(printer) {
        this.config.printer = printer;
        this.queue = new PrinterQueue();
        this.printingJobs = new PrinterQueue();
        this.persistentQueue = persistentQueue;
        this.infoBubble = new InfoBubble(this);
    }

    handle(batch) {
        batch.orders.forEach(job => {
            if (!this.isJobRegistered(job)) {
                this.queue.add(job);
                this.persistentQueue.add({
                    id: job.id,
                    printer: batch.printer,
                    orders: [job],
                });
            }
        });

        if (this.attemptingConnection) {
            console.log('still attempting connection');
            return;
        }

        if (this.device?.isConnected() && this.printer && this.isSameConfig(batch.printer)) {
            this.processQueue();
            return;
        }

        this.config.printer = batch.printer;
        this.connect();
    }

    updateSettings(settings) {
        this.messages = settings.messages;
        this.restaurant = settings.restaurant;
        this.labels = settings.labels;
        this.status = settings.status;
    }

    isJobRegistered(job) {
        return this.queue.exists(job) || this.printingJobs.exists(job);
    }

    isSameConfig(printer) {
        return Object.values(printer).toString() === Object.values(this.config.printer).toString();
    }

    async connect() {
        const printer = this;
        console.log('attempting connect');

        if (printer.attemptingConnection) {
            console.log('trying to connect while already connecting');
            return;
        }

        setTimeout(function () {
            printer.infoBubble.setMessage('connecting');
        }, 1000);

        printer.attemptingConnection = true;
        printer.connectAttempts += 1;
        printer.printingJobs.reset();
        printer.device = new epson.ePOSDevice();

        // the IP has been replaced with the serial no. of the printer,
        // but the field name has remained the same
        const hostName = await printerUtility.generateHostName(printer.config.printer.ip);

        printer.device.connect(hostName, printer.config.printer.port, printer.callbackEpsonConnect.bind(printer));
    }

    callbackEpsonConnect(resultConnect) {
        const printer = this;
        console.log(resultConnect);
        if ((resultConnect == 'OK') || (resultConnect == 'SSL_CONNECT_OK')) {
            console.log('Connect status ok');
            printer.infoBubble.setMessage('connected');
            printer.device.createDevice(printer.config.printer.device_id, printer.device.DEVICE_TYPE_PRINTER, printer.config.options, printer.callbackCreateDevice.bind(printer));
        } else {
            console.log('Connect status not ok');
            printer.attemptingConnection = false;
            if (printer.connectAttempts < 2) {
                setTimeout(function () {
                    printer.connect();
                }, 5000);
            } else {
                printer.infoBubble.setMessage('failed');
                setTimeout(function () {
                    printer.infoBubble.hide();
                }, 1000);

                const pendingJobs = printer.queue.getAllJobs();

                pendingJobs.forEach(job => {
                    if (job.ticket.print_type === 'ticket') {
                        printerManager.updateTicketStatus(job, printer.status.failed);
                    }
                    ;
                });

                printer.queue.reset();
                printer.persistentQueue.reset();
                printer.connectAttempts = 0;
            }
        }
    }

    callbackCreateDevice(deviceObj, errorCode) {
        const printer = this;
        printer.attemptingConnection = false;
        if (deviceObj === null) {
            console.log('Error code: ' + errorCode);
            return;
        }

        printer.connectAttempts = 0;
        printer.printer = deviceObj;

        console.log(printer.printer);

        printer.processQueue();

        printer.printer.onreceive = function (response) {
            console.log(response);
            const job = printer.printingJobs.get(response.printjobid);

            if (job) {
                if (response.success) {
                    printer.infoBubble.setMessage('printed');
                    if (job.ticket.print_type === "ticket") {
                        printerManager.updateTicketStatus(job, printer.status.success);
                    }
                } else {
                    printer.queue.add(job); // EPTR_COVER_OPEN
                    printer.persistentQueue.add({
                        id: job.id,
                        printer: printer.config.printer,
                        orders: [job]
                    });
                }
            }

            printer.printingJobs.remove(job);

            if (!printerManager.activeJobsExist()) {
                setTimeout(function () {
                    printer.infoBubble.hide();
                }, 1000);
            }
            printer.processQueue();
        };

        printer.printer.onstatuschange = function (status) {
            console.log(status);
        };
    }

    processQueue() {
        const nextJob = this.queue.getNextJob();
        if (nextJob) {
            this.queue.remove(nextJob);
            this.persistentQueue.remove(nextJob);
            this.print(nextJob);
        }
    }

    print(job) {
        if (!this.printer) {
            console.error("Printer not connected!");
            return;
        }

        if (this.printingJobs.exists(job)) {
            this.resetCurrentJob();
            console.log('job already sent to printer');
            this.processQueue();
            return;
        }

        this.printingJobs.add(job);
        this.setCurrentJob(job);
        console.log('job valid');

        switch (job.ticket.print_type) {
            case 'ticket':
                this.printTicket();
                break;
            case 'receipt': {
                if (this.logo.src === this.restaurant.logo) {
                    this.printReceipt();
                } else {
                    this.loadLogo(this.printReceipt.bind(this));
                }
                break;
            }
            default:
                break;
        }
    }

    setCurrentJob(job) {
        this.currentJob = job;
    }

    resetCurrentJob() {
        this.currentJob = null;
    }

    loadLogo(callbackPrintCommand) {
        const printer = this;
        const img = new Image();

        img.onload = function () {
            printer.logo.data = img;
            callbackPrintCommand()
        };

        img.onerror = function () {
            printer.logo.data = false;
            callbackPrintCommand()
        }

        img.src = printer.restaurant?.logo;
        printer.logo.src = printer.restaurant?.logo;
    }

    printTicket() {
        const order = this.currentJob.ticket;

        this.addTicketHeader(order);
        this.addDateAndTime();
        this.printer.addFeed();

        if (order.delivery_info) {
            this.addDeliveryInformation(order.delivery_info);
        }

        if (order.takeaway_info) {
            this.addTakeAwayInformation(order.takeaway_info);
        }

        this.addTicketDetails(order);
        this.printer.addTextStyle(false, false, false);

        const taxonomies = Object.keys(order.items);

        taxonomies.forEach(taxonomy => {
            this.addBoldLine(taxonomy);
            this.printer.addFeed();

            order.items[taxonomy].forEach(item => {
                this.addLine(`${item.quantity} x ${item.name}`);

                if (item.notes) {
                    this.addNotesLine(item.notes);
                }
            });

            this.printer.addFeed();
            this.addDottedLine();
            this.printer.addFeed();
        });

        this.printer.addFeedLine(2);

        this.printer.addCut(this.printer.CUT_FEED);

        this.printer.send(this.currentJob.id);
        this.resetCurrentJob();
        this.printer.close();
        this.infoBubble.setMessage('sent');
    }

    addTicketHeader(order) {
        this.printer.addTextAlign(this.printer.ALIGN_CENTER);
        this.printer.addTextSize(2, 1);
        this.printer.addTextStyle(false, false, true);
        this.printer.addText(this.labels[order.product_type]);
        this.printer.addFeedLine(3);
        this.printer.addTextStyle(false, false, false);
    }

    addTicketDetails(order) {
        this.addBoldLine(`${this.labels.table}: ${order.table.value}`);
        this.addBoldLine(`${this.labels.order}: ${order.order_number.value}`);
        this.printer.addFeed();
        this.addDottedLine();
        this.printer.addFeed();
    }

    addNotesLine(text) {
        this.printer.addTextAlign(this.printer.ALIGN_LEFT);
        this.printer.addTextSize(1, 1);
        this.printer.addTextStyle(false, false, true);
        this.printer.addText(`${this.labels.notes}: `);
        this.printer.addTextStyle(false, false, false);
        this.printer.addText(text);
        this.printer.addFeed();
    }

    printReceipt() {
        const order = this.currentJob.ticket;

        this.currency = order.totals.currency;

        this.addHeader();
        this.addRestaurantInfo();
        this.addDateAndTime();
        this.printer.addFeedLine(2);

        if (order.delivery_info) {
            this.addDeliveryInformation(order.delivery_info);
        }

        if (order.takeaway_info) {
            this.addTakeAwayInformation(order.takeaway_info);
        }

        this.addOrderDetails(order);
        this.addProductSection(order);

        this.addVatLine(order);

        this.addTotals(order);
        this.addFooter();

        this.printer.addFeedLine(2);

        this.printer.addCut(this.printer.CUT_FEED);

        this.printer.send(this.currentJob.id);
        this.resetCurrentJob();
        this.printer.close();
        this.infoBubble.setMessage('sent');
    }

    addHeader() {
        if (this.logo.data) {
            this.placeLogo();
        } else {
            this.printer.addTextAlign(this.printer.ALIGN_CENTER);
            this.printer.addTextSize(2, 3);
            this.printer.addText(this.restaurant.name);
            this.printer.addFeed();
        }

        this.printer.addFeedLine(2);
    }

    placeLogo() {
        const canvas = document.createElement('canvas');

        /*
            Set the canvas width to match the maximum width for the addImage()
            method of the Epson TM-m30III on an 80mm paper
        */
        canvas.width = 576;

        /*
            Set the canvas height to match the aspect ratio of the image
        */
        const factor = this.logo.data.width / canvas.width;
        canvas.height = parseInt(this.logo.data.height / factor);

        const canvasContext = canvas.getContext("2d");
        canvasContext.drawImage(this.logo.data, 0, 0, this.logo.data.width, this.logo.data.height, 0, 0, canvas.width, canvas.height);

        this.printer.addTextAlign(this.printer.ALIGN_CENTER);
        this.printer.addImage(canvasContext, 0, 0, canvas.width, canvas.height);
    }

    addRestaurantInfo() {
        this.addCenteredLine(this.restaurant.address);
        this.addCenteredLine(this.restaurant.phone ?? '');
        this.printer.addFeed();
    }

    addDateAndTime() {
        const datetime = this.currentJob.ticket.datetime;

        this.printer.addTextSize(1, 1);
        this.printer.addTextAlign(this.printer.ALIGN_LEFT);
        this.printer.addText(datetime.date);

        const space = this.config.lineLength - datetime.date.length - datetime.time.length;
        this.printer.addText(' '.repeat(space));
        this.printer.addText(datetime.time);
        this.printer.addFeed();
    }

    addDeliveryInformation(deliveryInformation) {
        this.addBoldLine(`${deliveryInformation.type}`);
        this.printer.addFeed();
        this.addBoldLine(`${this.labels.name}: ${deliveryInformation.name}`, 1, 3);
        this.printer.addFeed();
        this.addLine(`${this.labels.delivery_address}: ${deliveryInformation.street}`);
        this.addLine(`${deliveryInformation.postal_code}, ${deliveryInformation.city}`);
        this.addLine(`${this.labels.phone}: ${deliveryInformation.phone}`);
        this.addLine(`${this.labels.notes}: ${deliveryInformation.notes}`);

        this.printer.addFeed();
    }

    addTakeAwayInformation(takeawayInformation) {
        this.addBoldLine(`${takeawayInformation.type}`);
        this.printer.addFeed();
        this.addBoldLine(`${this.labels.name}: ${takeawayInformation.name}`, 1, 3);
        this.printer.addFeed();
        this.addLine(`${this.labels.phone}: ${takeawayInformation.phone}`);
        this.printer.addFeed();
    }

    addOrderDetails(order) {
        const fields = ['order_number', 'table', 'payment_method'];

        fields.forEach(field => {
            this.addLine(`${order[field].display_name} : ${order[field].value ?? '-'}`);
        });

        this.printer.addFeed();
    }

    addProductSection(order) {
        this.addDottedLine();

        let line = '';
        this.columns.forEach(column => {
            // the content of the column should not exceed the column's width, hence substring()
            let columnText = String(this.labels[column.headerKey]).substring(0, column.width);
            const spaces = ' '.repeat(column.width - columnText.length);

            line += column.align === 'right' ? spaces + columnText : columnText + spaces;
        });

        this.printer.addText(line);
        this.addDottedLine();
        this.printer.addFeed();

        this.addProducts(order.items);
        this.printer.addFeedLine(2);
    }

    addProducts(products) {
        this.printer.addTextAlign(this.printer.ALIGN_LEFT);
        products.forEach(product => this.addProductLine(product));
    }

    addProductLine(product) {
        let line = '';

        this.columns.forEach(column => {
            let columnText = column.productKey === 'price' ? this.currency : '';

            // the content of the column should not exceed the column's width, hence substring()
            columnText += column.productKey === 'price' ? Number(product[column.productKey]).toFixed(2) : String(product[column.productKey]).substring(0, column.width);
            const spaces = ' '.repeat(column.width - columnText.length);

            line += column.align === 'right' ? spaces + columnText : columnText + spaces;
        });
        this.printer.addText(line);
        this.printer.addFeed();
    }

    addVatLine(order) {
        this.addDottedLine();

        const columns = [
            {
                identifier: 'vat.amount',
                header: this.labels.vat_rate,
                align: 'left',
                before: this.labels.vat + ' ',
                after: this.labels.vat_rate
            },
            {
                identifier: 'net',
                header: this.labels.net,
                align: 'left',
                before: this.currency,
                after: '',
                padded: true
            },
            {
                identifier: 'vat.net',
                header: this.labels.vat,
                align: 'left',
                before: this.currency,
                after: '',
                padded: true
            },
            {
                identifier: 'gross',
                header: this.labels.gross,
                align: 'right',
                before: this.currency,
                after: '',
                padded: true
            }
        ];
        const columnWidth = 12;

        let line = '';
        columns.forEach(column => {
            const width = columnWidth - column.header.length;

            const spaces = ' '.repeat(columnWidth - column.header.length);
            line += column.align === 'right' ? spaces + column.header : column.header + spaces;
        });
        this.printer.addText(line);
        this.printer.addFeed();

        line = '';
        columns.forEach(column => {
            const provisionalValue = this.getValueByIdentifier(order.totals, column.identifier);

            const value = column.padded ? Number(provisionalValue).toFixed(2) : String(provisionalValue);
            const columnText = column.before + value + column.after;
            const spaces = ' '.repeat(columnWidth - columnText.length);
            line += column.align === 'right' ? spaces + columnText : columnText + spaces;
        });

        this.printer.addText(line);
        this.printer.addFeed();
        this.addDottedLine();

        this.printer.addFeedLine(2);
    }

    addTotals(order) {
        const lines = [
            {
                identifier: 'discounts.total',
                text: this.labels.discount,

            },
            {
                identifier: 'tips.value',
                text: this.labels.tips,
            },
            {
                identifier: 'delivery_fee.value',
                text: this.labels.delivery,
            },
            {
                identifier: 'amount_due',
                text: this.labels.total,
            }
        ];


        lines.forEach(line => {
            const value = this.getValueByIdentifier(order.totals, line.identifier);

            if (value) {
                let subTotal = this.currency + Number(value).toFixed(2);
                subTotal = ' '.repeat(11 - subTotal.length) + subTotal;
                const lineText = line.text + subTotal;
                const spaces = ' '.repeat(this.config.lineLength - lineText.length);

                this.printer.addTextSize(1, 2);
                this.printer.addText(spaces + lineText);
                this.printer.addFeed();
                this.printer.addTextSize(1, 1);
                this.printer.addFeed();
            }
        });
        this.printer.addFeedLine(2);
    }

    getValueByIdentifier(target, identifier) {
        const keys = identifier.split('.');

        let result = target;

        keys.forEach(key => {
            if (Object.hasOwn(result, key)) {
                result = result[key];
            } else {
                result = '';
            }
        });

        return result;
    }

    addFooter() {
        this.printer.addTextSize(1, 1);
        this.printer.addTextAlign(this.printer.ALIGN_CENTER);
        this.printer.addText(`${this.labels.vat_id}: ${this.restaurant.company_registration}`);
        this.printer.addFeed();

        if (this.restaurant.message) {
            this.printer.addTextAlign(this.printer.ALIGN_CENTER);
            this.printer.addText(this.restaurant.message);
            this.printer.addFeed();
        }
        this.printer.addFeedLine(3);
        this.printer.addText('Powered by Lemmon');
        this.printer.addFeed();
    }

    addDottedLine() {
        this.printer.addText('-'.repeat(this.config.lineLength));
        this.printer.addFeed();
    }

    addLine(text) {
        this.printer.addTextAlign(this.printer.ALIGN_LEFT);
        this.printer.addTextSize(1, 1);
        this.printer.addText(text);
        this.printer.addFeed();
    }

    addBoldLine(text, size1 = 1, size2 = 1) {

        this.printer.addTextAlign(this.printer.ALIGN_LEFT);
        this.printer.addTextSize(size1, size2);
        this.printer.addTextStyle(false, false, true);
        this.printer.addText(text);
        this.printer.addFeed();
        this.printer.addTextStyle(false, false, false);
    }

    addCenteredLine(text) {
        this.printer.addTextAlign(this.printer.ALIGN_CENTER);
        this.printer.addTextSize(1, 1);
        this.printer.addText(text);
        this.printer.addFeed();
    }
};

class PrinterManager {
    constructor() {
        this.printers = {};
        this.persistentQueue = persistentQueue;
        this.infoBubble = new InfoBubble();

        this.preventLeavingPageWhenPrinting();
        if ($(window).width() > 768) {
            this.preConnect();
        }
        this.handleUnprocessedJobs();

        const manager = this;

        if ($(window).width() > 768) {
            setInterval(manager.getOrdersToPrint.bind(manager), 10 * 1000);
        }

    }

    async pingPrinters() {
        $.ajax({
            type: 'GET',
            url: $('.main').data('printers-list'),
            success: async function (data) {
                if (data && data.length > 0) {
                    for (const printer of data) {
                        if (printer.ip) {
                            const hostName = await printerUtility.generateHostName(printer.ip);

                            $.ajax({
                                type: 'get',
                                url: 'https://' + hostName,
                                success: function (pingResponse) {
                                    console.log(pingResponse);
                                },
                                error: function (error) {
                                }
                            });
                        }
                    }
                }
            }
        });
    }

    preConnect() {
        const widget = this;

        $.ajax({
            type: 'GET',
            url: $('.main').data('printers-list'),
            success: function (data) {
                if (data && data.length > 0) {
                    data.forEach(function (printerConfig) {
                        if (!widget.getPrinter(printerConfig.ip)) {
                            const printer = widget.addPrinter(printerConfig);
                            printer.connect();
                        }
                    });
                }
                // setInterval(widget.pingPrinters, 15 * 1000);
            }
        });
    }

    getOrdersToPrint() {
        const manager = this;

        $.ajax({
            type: 'GET',
            url: $('.main').data('orders-to-print-url'),
            success: function (data) {
                $('.dashboard').data('print-receipt-url', data.print_receipt_url);

                localStorage.setItem('printer-settings', JSON.stringify(data.settings));
                data.jobs_by_printer.forEach(item => {
                    manager.processJobs(item, data.settings);
                });
            },
            complete: function (data) {
                if (data.status != 200) {
                    attemptPageReload();
                }
            },
        });
    }

    processJobs(item, settings) {
        let printer = this.getPrinter(item.printer.ip);

        if (!printer) {
            printer = this.addPrinter(item.printer);
        }

        printer.updateSettings(settings);

        if (printer.messages) {
            printer.infoBubble.show();
            printer.infoBubble.setMessage('received');
        }

        printer.handle(item);
    }

    preventLeavingPageWhenPrinting() {
        const manager = this;
        $('a[href]').on('click', function (e) {
            if (manager.activeJobsExist()) {
                e.preventDefault();
            }
        });
    }

    handleUnprocessedJobs() {
        const unprocessedJobs = this.persistentQueue.getAllandReset();

        if (!unprocessedJobs.length) {
            return;
        }

        const settings = JSON.parse(localStorage.getItem('printer-settings'));

        unprocessedJobs.forEach(job => {
            let printer = this.getPrinter(job.printer.ip);

            if (!printer) {
                printer = this.addPrinter(job.printer);
            }

            printer.updateSettings(settings);

            if (printer.messages) {
                printer.infoBubble.show();
                printer.infoBubble.setMessage('unprocessed');
            }

            printer.handle({
                printer: job.printer,
                orders: job.orders,
            });
        });
    }

    activeJobsExist() {
        const printers = Object.values(this.printers);

        let activeJobs = false;

        printers.forEach(printer => {
            if (printer.printingJobs.hasJobs()) {
                activeJobs = true;
            }
        });

        return activeJobs;
    }

    getAndPrintReceipt(options) {
        const manager = this;

        const requestData = {
            order_id: options.orderId,
            type: 'single',
        };

        if (options.onlyCash) {
            requestData.onlyCash = true;
        }

        $.ajax({
            type: 'POST',
            url: options.url,
            data: requestData,
            success: function (data) {
                localStorage.setItem('printer-settings', JSON.stringify(data.settings));

                manager.processJobs({
                    printer: data.printer,
                    orders: data.orders,
                }, data.settings);
            }
        });
    }

    getAndPrintReceipts(options) {
        const manager = this;

        $.ajax({
            type: 'POST',
            url: options.url,
            data: {
                type: 'merged',
                jobs: options.jobs,
                partial: options.partial,
                partial_pay_id: options.partial_pay_id
            },
            success: function (data) {
                if (data.success === false) {
                    return;
                }

                const jobs = data.jobs;

                localStorage.setItem('printer-settings', JSON.stringify(jobs.settings));

                manager.processJobs({
                    printer: jobs.printer,
                    orders: jobs.orders,
                }, jobs.settings);
            }
        });
    }

    addPrinter(printer) {
        this.printers[printer.ip] = new Printer(printer);

        return this.printers[printer.ip];
    }

    getPrinter(ip) {
        return this.printers[ip] ?? null;
    }

    updateTicketStatus(job, status) {
        $.ajax({
            type: 'POST',
            url: job.update_url,
            data: {
                productType: job.ticket.product_type,
                status: status,
            },
            success: function (data) {
                console.log(data);
            }
        });
    }
};

const printerManager = new PrinterManager();

$('.dashboard').on('click', '.order-card__print', function (e) {
    e.preventDefault();
    const $this = $(this);
    const $orderCard = $this.closest('.order-card');
    const orderId = $orderCard.data('id');
    const printUrl = $('.main').data('print-receipt-url');

    if (!printUrl) {
        return;
    }

    printerManager.getAndPrintReceipt({
        url: printUrl,
        orderId: orderId,
    });
});

