Dropzone.autoDiscover = false;

$(document).ready(function () {

    if ($('#dropzone').length) {
        let dropzone = new Dropzone("#dropzone", {
            url: jQuery("#dropzone").data('url'),
            addRemoveLinks: true,
            maxFilesize: 5,
            dictDefaultMessage: 'Select image file.<br> Format supported: JPEG,JPG,PNG.<br> Max filesize: 5Mb.',
            maxFiles: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            init: function () {
                $.ajax({
                    url: jQuery('#dropzone').data("gallery"),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $.each(data, function (key, value) {
                            let file = {name: value.name, size: value.size, id: value.id};
                            dropzone.options.addedfile.call(dropzone, file);
                            dropzone.options.thumbnail.call(dropzone, file, value.path);
                            dropzone.emit("complete", file);
                        });
                    }
                });

                this.on("success", function (file, response) {
                    $('#extraForm').append(
                        $(document.createElement('input')).prop({
                            name: 'images[]',
                            type: 'hidden',
                            value: file.dataURL
                        }).attr('data-uuid', file.upload.uuid)
                    )
                });

                this.on('error', function(file, message) {
                    $('#upload-error').text(message)
                    console.log(message);
                });

                this.on("removedfile", function (file) {
                    $('input[value="' + file.name + '"]').remove()
                    $('#upload-error').text('');

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
    if ($('#dropzone-single').length) {
        let dropzone = new Dropzone("#dropzone-single", {
            url: jQuery("#dropzone-single").data('url'),
            addRemoveLinks: true,
            maxFilesize: 5,
            resizeWidth: 350,
            resizeHeight: 350,
            uploadMultiple: false,
            dictDefaultMessage: 'Select image file.<br> Format supported: JPEG,JPG,PNG.<br> Max filesize: 5Mb.',
            maxFiles: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            init: function () {
                $.ajax({
                    url: jQuery('#dropzone-single').data("gallery"),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $.each(data, function (key, value) {
                            if (value.type === 'single') {
                                let file = {name: value.name, size: value.size, id: value.id};
                                dropzone.options.addedfile.call(dropzone, file);
                                dropzone.options.thumbnail.call(dropzone, file, value.path);
                                dropzone.emit("complete", file);
                            }
                        });
                    }
                });

                this.on("success", function (file, response) {
                    $('#extraForm').append(
                        $(document.createElement('input')).prop({
                            name: 'images[single][]',
                            type: 'hidden',
                            value: file.dataURL
                        }).attr('data-uuid', file.upload.uuid)
                    )
                });

                this.on('error', function(file, message) {
                    $('#single-error').text(message);
                });

                this.on("removedfile", function (file) {
                    $('input[value="' + file.name + '"]').remove()
                    $('#single-error').text('');

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
    if ($('#dropzone-list').length) {
        let dropzone = new Dropzone("#dropzone-list", {
            url: jQuery("#dropzone-list").data('url'),
            addRemoveLinks: true,
            thumbnailWidth: 250,
            thumbnailHeight: 250,
            maxFilesize: 5,
            resizeWidth: 350,
            resizeHeight: 350,
            resizeMethod: 'crop',
            uploadMultiple: false,
            dictDefaultMessage: 'Select image file.<br> Format supported: JPEG,JPG,PNG.<br> Max filesize: 5Mb.',
            maxFiles: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            init: function () {
                $.ajax({
                    url: jQuery('#dropzone-list').data("gallery"),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $.each(data, function (key, value) {
                            if (value.type === 'list') {
                                let file = {name: value.name, size: value.size, id: value.id};
                                dropzone.options.addedfile.call(dropzone, file);
                                dropzone.options.thumbnail.call(dropzone, file, value.path);
                                dropzone.emit("complete", file);
                            }
                        });
                    }
                });

                this.on('error', function(file, message) {
                    $('#list-error').text(message)
                    console.log(message);
                });

                this.on("success", function (file, response) {
                    $('#extraForm').append(
                        $(document.createElement('input')).prop({
                            name: 'images[list][]',
                            type: 'hidden',
                            value: file.dataURL
                        }).attr('data-uuid', file.upload.uuid)
                    )
                });

                this.on("removedfile", function (file) {
                    $('input[value="' + file.name + '"]').remove()
                    $('#list-error').text('');

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

    const showError = (field_name, errorMessage) => {
        const errorText = document.getElementById('error-' + field_name);
        errorText.style.display = 'block';
        errorText.innerHTML = errorMessage;
    }

    const hideError = (field_name) => {
        const errorText = document.getElementById('error-' + field_name);
        errorText.style.display = 'none';
        errorText.innerHTML = '';
    }

    // Form validation
    document.getElementById('save-extra-submit').addEventListener("click", (e) => {
        const form = document.getElementById('extraForm'),
            submitButton = e.currentTarget;
        let errors = 0;

        // Error messages
        const errorRequired = document.getElementById('error-message-required').innerHTML,
            errorNumericRequired = document.getElementById('error-message-numeric-required').innerHTML,
            errorMaxLength = document.getElementById('error-message-max-length').innerHTML,
            errorCharacters = document.getElementById('error-characters').innerHTML;

        // title
        const title = document.getElementById('title').value;
        if(typeof title !== "string" || title.length === 0) {
            errors++;
            showError('title', errorRequired);
        } else {
            hideError('title');
        }

        // Description
       /* const description = document.getElementById('description').value;
        if(typeof description !== "string" || description.length > 300) {
            errors++;
            showError('description', errorMaxLength + ' 300 ' + errorCharacters + '.');
        } else {
            hideError('description');
        }*/

        if(errors > 0) {
            if(submitButton.classList.contains('primary-button')) {
                submitButton.classList.remove('primary-button');
                submitButton.classList.add('danger-button');
            }
            return;
        }

        if(submitButton.classList.contains('danger-button')) {
            submitButton.classList.remove('danger-button');
            submitButton.classList.add('primary-button');
        }
        form.submit();
    });

    setTimeout(function () {
        showFoodType();

        $('#type').on('change', function () {
            showFoodType();
        });
    });

    // Check current extra type
    function showFoodType() {
        if($('#type').val() !== 'restaurant') {
            $("#food-type option:selected").removeAttr("selected");

            $('#food-type-container').hide();
            return;
        }

        $('#food-type-container').show();
    }
});

