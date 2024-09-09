Dropzone.autoDiscover = false;

$(document).ready(function () {
    var adjustment;


    $(function () {
        $(".category-multiselect").sortable();
    });


    $('.multiple-select').on('click', '.child', function () {
        $(this).toggleClass('selected');
    });

    $(".multiple-select").sortable({
        items: 'li',
        toleranceElement: '> div',
        group: 'multiple_select_group',
        connectWith: '.multiple_select_group',
        pullPlaceholder: true,
        // animation on drop
        onDrop: function ($item, container, _super) {
            var $clonedItem = $('<li/>').css({height: 0});
            $item.before($clonedItem);
            $clonedItem.animate({'height': $item.height()});

            $item.animate($clonedItem.position(), function () {
                $clonedItem.detach();
                _super($item, container);
            });
        },
        // set $item relative to cursor position
        onDragStart: function ($item, container, _super) {
            var offset = $item.offset(),
                pointer = container.rootGroup.pointer;

            adjustment = {
                left: pointer.left - offset.left,
                top: pointer.top - offset.top
            };

            _super($item, container);
        },
        onDrag: function ($item, position) {
            $item.css({
                left: position.left - adjustment.left,
                top: position.top - adjustment.top
            });
        },
        update: function ($item, ui) {
            const element = jQuery(ui.item);

            if (ui.sender !== null && !element.hasClass('parent')) {
                return;
            }

            if (ui.sender === null && !element.hasClass('parent')) {
                if (jQuery($item.target).hasClass('selected-products') && !element.parent().is(jQuery('.selected-products li[data-id="' + (element.data('category-id') + '"] ul')))) {
                    jQuery('.selected-products li[data-id="' + (element.data('category-id') + '"] ul')).append(element);
                    jQuery('.categ-' + element.data('category-id')).show();

                    if (jQuery('li.first[data-id="' + element.data('category-id') + '"] ul').children().length === 0) {
                        jQuery('li.first[data-id="' + element.data('category-id') + '"] div').hide()
                    }
                    return;
                }
                if (jQuery($item.target).hasClass('all-products') && !element.parent().is(jQuery('.all-products li[data-id="' + (element.data('category-id') + '"] ul')))) {
                    jQuery('.all-products li[data-id="' + (element.data('category-id') + '"] ul')).append(element);
                    jQuery('.all-products li[data-id="' + element.data('category-id').toString() + '"] > div').show();

                    if (jQuery('li.second[data-id="' + element.data('category-id') + '"] ul').children().length === 0) {
                        jQuery('.categ-' + element.data('category-id')).hide();
                    }
                    return;
                }
            }

            // If the element is a parent from the 'all products' category, it is moved to the 'selected products' category
            // and the sortable that this update is called upon is from the first sortable
            if (element.hasClass('parent') && element.hasClass('first') && element.parents('.selected-products').length !== 0 && jQuery($item.target).hasClass('all-products')) {
                return;
            }

            // Same as previous but for right to left operation
            if (element.hasClass('parent') && element.hasClass('second') && element.parents('.all-products').length !== 0 && jQuery($item.target).hasClass('selected-products')) {
                return;
            }

            // If the selected products sortable is receiving a parent item
            if (element.hasClass('parent') && element.hasClass('first') && element.parents('.selected-products').length !== 0 && jQuery($item.target).hasClass('selected-products')) {

                // First, get the position of the moved element
                let parent = 0,
                    position = 0,
                    after = false;

                if (element.parents('li.parent.second').length !== 0) {
                    // the moved category has been placed in another category
                    parent = element.parents('li.parent.second').data('id');
                    position = element.index();
                    if (position > element.parent().find('li.second').length / 2) {
                        after = true;
                    }
                } else if (element.parent().hasClass('selected-products')) {
                    position = 0;
                    jQuery('.selected-products').children().each((i, el) => {
                        if ($(el).children('div').css('display') === 'none') {
                            return true; // continue
                        }
                        if ($(el).hasClass('first')) {
                            return false; // break
                        }
                        position++;
                    });
                }

                // First, move the parent container back to the all products sortable
                jQuery('.all-products').prepend(element);

                // Then, move all the children items to the selected products sortable
                element.find('li.child').each((i, el) => moveElementToRight(el));

                // And move the new parent in the desired position
                if (parent !== 0) {
                    // has a category to move after
                    const benchmarkElement = $('.selected-products li.parent[data-id="' + parent.toString() + '"]');
                    if (after) {
                        $('.selected-products li.parent[data-id="' + element.data('id').toString() + '"]').insertAfter(benchmarkElement);
                    } else {
                        $('.selected-products li.parent[data-id="' + element.data('id').toString() + '"]').insertBefore(benchmarkElement);
                    }
                    return;
                }
                if (parent === 0 && position === 0) {
                    $('.selected-products').prepend($('.selected-products li.parent[data-id="' + element.data('id').toString() + '"]'));
                    return;
                }
                if (parent === 0) {
                    let counter = 0;
                    jQuery('.selected-products').children().each((i, el) => {
                        if ($(el).children('div').css('display') === 'none') {
                            return true; // continue
                        }
                        if (counter === position) {
                            $('.selected-products li.parent[data-id="' + element.data('id').toString() + '"]').insertBefore($(el));
                            return false;
                        }
                        counter++;
                    });
                    return;
                }

                return;
            }

            // If the all products sortable is receiving a child item
            if (element.hasClass('parent') && element.hasClass('second') && element.parents('.all-products').length !== 0 && jQuery($item.target).hasClass('all-products')) {
                // First, get the position of the moved element
                let parent = 0,
                    position = 0,
                    after = false;

                if (element.parents('li.parent.first').length !== 0) {
                    // the moved category has been placed in another category
                    parent = element.parents('li.parent.first').data('id');
                    position = element.index();
                    if (position > element.parent().find('li.first').length / 2) {
                        after = true;
                    }
                } else if (element.parent().hasClass('all-products')) {
                    position = 0;
                    jQuery('.all-products').children().each((i, el) => {
                        if ($(el).css('display') === 'none' || $(el).children('div').css('display') === 'none') {
                            return true; // continue
                        }
                        if ($(el).hasClass('second')) {
                            return false; // break
                        }
                        position++;
                    });
                }

                // Move parent container back
                jQuery('.selected-products').prepend(element);

                // Then move all the children items
                element.find('li.child').each((i, el) => moveElementToLeft(el));

                // And move the new parent in the desired position
                if (parent !== 0) {
                    // has a category to move after
                    const benchmarkElement = $('.all-products li.parent[data-id="' + parent.toString() + '"]');
                    if (after) {
                        $('.all-products li.parent[data-id="' + element.data('id').toString() + '"]').css('display', 'block').insertAfter(benchmarkElement);
                    } else {
                        $('.all-products li.parent[data-id="' + element.data('id').toString() + '"]').css('display', 'block').insertBefore(benchmarkElement);
                    }
                    return;
                }
                if (parent === 0 && position === 0) {
                    $('.all-products').prepend($('.all-products li.parent[data-id="' + element.data('id').toString() + '"]'));
                    $('.all-products li.parent[data-id="' + element.data('id').toString() + '"]').css('display', 'block');
                    return;
                }
                if (parent === 0) {
                    let counter = 0;
                    jQuery('.selected-products').children().each((i, el) => {
                        if ($(el).children('div').css('display') === 'none') {
                            return true; // continue
                        }
                        if (counter === position) {
                            $('.all-products li.parent[data-id="' + element.data('id').toString() + '"]').css('display', 'block').insertBefore($(el));
                            return false;
                        }
                        counter++;
                    });
                    return;
                }
                return;
            }

            // If the sortable parent element is moved to a different sortable container
            if (element.hasClass('parent') && ui.sender != null) {
                if (element.hasClass('second') && !element.parent().hasClass('selected-products')) {
                    jQuery('.selected-products').prepend(element);
                }
                if (element.hasClass('first') && !element.parent().hasClass('all-products')) {
                    jQuery('.all-products').prepend(element);
                }
                return;
            }
            // If the sortable parent element is moved throughout the same sortable container
            if (element.hasClass('parent') && !element.parent().hasClass('multiple-select')) {
                if (jQuery($item.target).hasClass('selected-products')) {
                    jQuery('.selected-products').append(element);
                } else {
                    jQuery('.all-products').append(element);
                }
                return;
            }
        },
        receive: function ($item, ui) {
            const element = jQuery(ui.item);
            // If the sortable child element is moved throughout the same sortable container
            if (element.hasClass('parent')) {
                return;
            }
            if (jQuery($item.target).hasClass('selected-products') && !element.parent().is(jQuery('.selected-products li[data-id="' + (element.data('category-id') + '"] ul')))) {
                jQuery('.selected-products li[data-id="' + (element.data('category-id') + '"] ul')).append(element);
                jQuery('.categ-' + element.data('category-id')).show();

                if (jQuery('li.first[data-id="' + element.data('category-id') + '"] ul').children().length === 0) {
                    jQuery('li.first[data-id="' + element.data('category-id') + '"] div').hide()
                }
                return;
            }
            if (jQuery($item.target).hasClass('all-products') && !element.parent().is(jQuery('.all-products li[data-id="' + (element.data('category-id') + '"] ul')))) {
                jQuery('.all-products li[data-id="' + (element.data('category-id') + '"] ul')).append(element);
                jQuery('.all-products li[data-id="' + element.data('category-id').toString() + '"] > div').show();

                if (jQuery('li.second[data-id="' + element.data('category-id') + '"] ul').children().length === 0) {
                    jQuery('.categ-' + element.data('category-id')).hide();
                }
                return;
            }
        }
    });

    /**
     * Move element in sortable to parent in the right table (the selected elements)
     * @param el - element to be moved, must have category-id data attribute
     */
    function moveElementToRight(el) {
        const element = jQuery(el), parent = element.parents('li.parent');
        if (element.parent().children().length === 1) {
            parent.children('div').hide()
        }
        // Move the element
        jQuery('.selected-products li[data-id="' + (element.data('category-id') + '"] ul')).append(element);
        // Show the category label
        $('.categ-' + element.data('category-id')).show();
        // Unselect this element
        element.removeClass('selected');
        element.removeClass('first');
        element.addClass('second');
    }

    /**
     * Move element in sortable to parent back in the left table (the unselected elements)
     * @param el - element to be moved, must have category-id data attribute
     */
    function moveElementToLeft(el) {
        const element = jQuery(el), parent = element.parents('li.parent');
        if (element.parent().children().length === 1) {
            parent.children('div').hide()
        }
        // Move the element
        jQuery('.all-products li[data-id="' + (element.data('category-id') + '"] ul')).append(element);
        // Show the text
        jQuery('.all-products li[data-id="' + (element.data('category-id') + '"] div')).show();
        // Unselect this element
        element.removeClass('selected');
        element.removeClass('second');
        element.addClass('first');
    }

    $('#multiselect_leftAll').click(function () {
        $('.selected-products li.child').each((i, el) => moveElementToLeft(el))
    })

    $('#multiselect_rightAll').click(function () {
        $('.all-products li.child').each((i, el) => moveElementToRight(el));
    })

    $('#multiselect_leftSelected').click(function () {
        $('.selected-products li.selected').each((i, el) => moveElementToLeft(el));
    })

    $('#multiselect_rightSelected').click(function () {
        $('.all-products li.selected').each((i, el) => moveElementToRight(el))

    })

    $("#serviceForm button[type='submit']").click(function (e) {
        e.preventDefault();
        $('.all-products input').prop('disabled', true);
        $("#serviceForm").submit();
    });


    $('.dashboard-header__user__out').click(function () {
        $('.label_out').toggle();
        ($(this)).toggleClass('rotate');
    });

    $(".status__change").select2({
        width: '160px',
        height: '30px',
        minimumResultsForSearch: -1,
        selectionCssClass: ':all:'
    });


    $('.status__change').on('select2:close', function (e) {
        $('#status-form').submit();

    });

    OverlayScrollbars(document.querySelector('#multiselect'), {
        className: "multiple-select",
        resize: "vertical",
        sizeAutoCapable: true,
        paddingAbsolute: true,
        overflowBehavior: {
            y: 'hidden',
            x: 'hidden'
        },
        scrollbars: {
            visibility: 'hidden'
        }
    });

    $('.days-list label').click(function () {
        $(this).closest('div').toggleClass("checked");
    });

    // $('#save-product-submit').click(function () {
    //     $('#productForm').submit();
    // });

    $('#save-table-submit').click(function () {
        $('#tableForm').submit();
    });

    $('#save-staff-submit').click(function () {
        $('.submit').click();
    })

    $('#resend-invite').click(function () {
        $('.submit').val('reinvite');
        $('.submit').click();
    });

// $('#export-zip').click(function (){
//     $.ajax({
//         url: $(this).data('href'),
//         type: 'GET',
//         data: {},
//         success: function (data){
//             $.fileDownload(data)
//                 .done(function () { alert('File download a success!'); })
//         }
//     })
// })
    // Function to add new time intervals
    function addTimeInterval(buttonSelector, startTimeName, endTimeName, startLabel, endLabel) {
        const buttonElement = document.querySelector(buttonSelector);
        const rowDiv = buttonElement.parentElement.parentElement;

        const startDiv = createInputDiv(startLabel, startTimeName, 'time');
        const endDiv = createInputDiv(endLabel, endTimeName, 'time');
        const removeButtonDiv = document.createElement('div');
        removeButtonDiv.className = 'col-sm-6 remove-container';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn danger-button remove-interval extra-remove';
        removeButton.textContent = 'Remove';

        removeButtonDiv.appendChild(removeButton);

        rowDiv.insertBefore(startDiv, buttonElement.parentElement);
        rowDiv.insertBefore(endDiv, buttonElement.parentElement);
        rowDiv.insertBefore(removeButtonDiv, buttonElement.parentElement);
    }

    // Helper function to create input divs
    function createInputDiv(labelText, inputName, type = 'time') {
        const div = document.createElement('div');
        div.className = 'col-sm-3 custom';

        const label = document.createElement('label');
        label.innerHTML = labelText;

        const input = document.createElement('input');
        input.className = 'gray';
        input.type = type;
        input.name = inputName;

        div.appendChild(label);
        div.appendChild(input);

        return div;
    }

    // Event delegation to handle click events for "Remove" buttons
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-interval')) {
            const removeButtonDiv = e.target.closest('.col-sm-6');
            const startDiv = removeButtonDiv.previousElementSibling.previousElementSibling;
            const endDiv = removeButtonDiv.previousElementSibling;
            const allRemoveButtons = removeButtonDiv.parentElement.querySelectorAll('.remove-interval');

            if (allRemoveButtons.length === 1) {
                startDiv.querySelector('input').value = '';
                endDiv.querySelector('input').value = '';
            } else {
                startDiv.remove();
                endDiv.remove();
                removeButtonDiv.remove();
            }
        }
    });
    
    try {
        document.querySelector('.add-more-kitchen').addEventListener('click', function () {
            addTimeInterval('.add-more-kitchen', 'kitchen_start_time[]', 'kitchen_end_time[]', 'Start', 'End');
        });
    
        document.querySelector('.add-more-bar').addEventListener('click', function () {
            addTimeInterval('.add-more-bar', 'bar_start_time[]', 'bar_end_time[]', 'Start', 'End');
        });
    } catch {}


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

    if ($('#dropzone-welcome-screen').length) {
        let dropzone = new Dropzone("#dropzone-welcome-screen", {
            url: jQuery("#dropzone-welcome-screen").data('url'),
            addRemoveLinks: true,
            maxFilesize: 5,
            resizeWidth: 350,
            resizeHeight: 350,
            uploadMultiple: false,
            dictDefaultMessage: 'Select image file.<br> Format supported: PNG, JPG, JPEG.<br> Max filesize: 5Mb.',
            maxFiles: 1,
            acceptedFiles: ".jpeg,.jpg,.png",
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            init: function () {
                $.ajax({
                    url: jQuery('#dropzone-welcome-screen').data("welcome-screen"),
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
                            name: 'welcome_screen_image',
                            type: 'hidden',
                            value: file.dataURL
                        }).attr('data-uuid', file.upload.uuid)
                    )
                });

                this.on('error', function(file, message) {
                    $('#welcome-screen-error').text(message);
                });

                this.on("removedfile", function (file) {
                    $('input[value="' + file.name + '"]').remove()
                    $('#welcome-screen-error').text('');

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

    $('#service-type').on('change', function(e) {
        e.preventDefault();
    });

    $('#service-type option').on('click', function(e) {
        e.preventDefault();

        const $this = $(this);
        const $select = $this.parent();
        const isSelected = $this.attr('selected');

        if (isSelected && $select.find('option[selected]').length <= 1) {
            return;
        }

        if (isSelected) {
            $this.removeAttr('selected');
        } else {
            $this.attr('selected', true);
        }

        const types = $select.find('option[selected]').map(function() {
            return $(this).attr('value');
        }).get();

        $select.val(types);
    });

});
