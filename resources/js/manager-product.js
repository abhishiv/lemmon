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
                    $('#productForm').append(
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
                    $('#productForm').append(
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
                    $('#productForm').append(
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
    document.getElementById('save-product-submit').addEventListener("click", (e) => {
        const form = document.getElementById('productForm'),
            submitButton = e.currentTarget;
        let errors = 0;

        // Error messages
        const errorRequired = document.getElementById('error-message-required').innerHTML,
            errorNumericRequired = document.getElementById('error-message-numeric-required').innerHTML,
            errorMaxLength = document.getElementById('error-message-max-length').innerHTML,
            errorCharacters = document.getElementById('error-characters').innerHTML;

        // Name
        const name = document.getElementById('name').value;
        if(typeof name !== "string" || name.length === 0) {
            errors++;
            showError('name', errorRequired);
        } else {
            hideError('name');
        }

        // Price
        const price = document.getElementById('price').value;
        if(typeof price !== "string" || price.length === 0 || parseFloat(price) === NaN) {
            errors++;
            showError('price', errorNumericRequired);
        } else {
            hideError('price');
        }

        // Type
        const type = document.getElementById('type').value;
        if(typeof type !== "string" || type.length === 0) {
            errors++;
            showError('type', errorRequired);
        } else {
            hideError('type');
        }

        // Description
        const description = document.getElementById('description').value;
        if(typeof description !== "string" || description.length > 300) {
            errors++;
            showError('description', errorMaxLength + ' 300 ' + errorCharacters + '.');
        } else {
            hideError('description');
        }

        // Status
        const status = document.getElementById('status').value;
        if(typeof status !== "string" || status.length === 0) {
            errors++;
            showError('status', errorRequired);
        } else {
            hideError('status');
        }

        // Category ID
        const categoryId = document.getElementById('category_id').value;
        if(typeof categoryId !== "string" || categoryId.length === 0) {
            errors++;
            showError('category_id', errorRequired);
        } else {
            hideError('category_id');
        }

        // Nullable fields

        // Special Price
        const specialPrice = document.getElementById('special_price').value;
        if(specialPrice.length !== 0 && parseFloat(specialPrice) === NaN) {
            errors++;
            showError('special_price', errorNumericRequired);
        } else {
            hideError('special_price');
        }

        // Additional info
        const additionalInfo = document.getElementById('additional_info').value;
        if(additionalInfo.length > 50) {
            errors++;
            showError('additional_info', errorMaxLength + ' 50 ' + errorCharacters + '.');
        } else {
            hideError('additional_info');
        }

        // Food type
        const foodType = document.getElementById('food-type').value;
        if(type === 'restaurant' && (foodType.length === 0 || parseInt(foodType) === NaN)) {
            errors++;
            showError('food-type', errorRequired);
        } else {
            hideError('food-type');
        }

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

    // Check current product type
    function showFoodType() {
        if($('#type').val() !== 'restaurant') {
            $("#food-type option:selected").removeAttr("selected");

            $('#food-type-container').hide();
            return;
        }

        $('#food-type-container').show();
    }
});

