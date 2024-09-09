Dropzone.autoDiscover = false;

$(document).ready(function () {

    if ($('.dashboard__empty').length) {
        $('.dashboard-header__view').hide();
    }

    $(".status__change").select2({
        width: '160px',
        height: '30px',
        minimumResultsForSearch: -1,
        selectionCssClass: ':all:'
    });
    $('.status__change').on('select2:close', function (e) {
        let $parent = $(this).closest('.status');

        $parent.find('div').attr('class', '');

        if ($parent.find('.select2-selection__rendered[title="Active"]').length) {
            $parent.find('div').toggleClass('status-active');
        }
        if ($parent.find('.select2-selection__rendered[title="Pending"]').length) {
            $parent.find('div').toggleClass('status-pending');
        }

        if ($parent.find('.select2-selection__rendered[title="Blocked"]').length) {
            $parent.find('div').toggleClass('status-blocked');
        }

        $('div.alert .close').on('click', function () {
            $(this).parent().alert('close');
        });
    });

    if ($('#dropzone-logo').length) {
        let dropzone = new Dropzone("#dropzone-logo", {
            url: jQuery("#dropzone-logo").data('url'),
            addRemoveLinks: true,
            maxFilesize: 5,
            resizeWidth: 350,
            resizeHeight: 350,
            uploadMultiple: false,
            dictDefaultMessage: 'Select image file.<br> Format supported: PNG.<br> Max filesize: 5Mb.',
            maxFiles: 1,
            acceptedFiles: ".png",
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            init: function () {
                $.ajax({
                    url: jQuery('#dropzone-logo').data("logo"),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        if (!data) {
                            console.log('failed to retrieve logo');
                            return;
                        }

                        dropzone.options.addedfile.call(dropzone, data);
                        dropzone.options.thumbnail.call(dropzone, data, data.path);
                        dropzone.emit("complete", data);
                    }
                });

                this.on("success", function (file, response) {
                    $('#restaurantForm').append(
                        $(document.createElement('input')).prop({
                            name: 'logo',
                            type: 'hidden',
                            value: file.dataURL
                        }).attr('data-uuid', file.upload.uuid)
                    )
                });

                this.on('error', function(file, message) {
                    $('#logo-error').text(message);
                });

                this.on("removedfile", function (file) {
                    $('input[value="' + file.name + '"]').remove()
                    $('#logo-error').text('');

                    if (typeof file.upload == 'undefined') {
                        return
                    }
                    if ($("[data-uuid=" + file.upload.uuid + "]")) {
                        $("[data-uuid=" + file.upload.uuid + "]").remove()
                    }
                });

            },
        });
    }

})


