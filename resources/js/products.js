$(document).ready(function () {
    
    // Event delegation to handle click events for "Remove" buttons
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('extra-remove')) {
            const removeButtonDiv = e.target.closest('.col-sm-12');        
            const endDiv = removeButtonDiv;
            endDiv.remove();
            removeButtonDiv.remove();
        }
    });
    
    document.querySelector('.add-more-extra').addEventListener('click', function () {
        addExtraElement();
    });

    document.querySelector('.add-more-product').addEventListener('click', function () {
        addProductElement();
    });

    document.querySelector('.add-more-removable').addEventListener('click', function () {
        addRemovableElement();
    });

    document.querySelector('.open-extras').addEventListener('click', function () {
        openTab(event, 'extras');
    });

    document.querySelector('.open-removables').addEventListener('click', function () {
        openTab(event, 'removables');
    });

    document.querySelector('.open-products').addEventListener('click', function () {
        openTab(event, 'products');
    });

    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.querySelectorAll(".tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
          tabcontent[i].style.display = "none";
        }
        tablinks = document.querySelectorAll(".tablinks");
        for (i = 0; i < tablinks.length; i++) {
          tablinks[i].className = tablinks[i].className.replace(" activeTab", "");
        }
        document.querySelector("#"+tabName).style.display = "block";
        evt.currentTarget.className += " activeTab";
    }

    function addRemovableElement() {
        const removableDiv = document.querySelector('#removable');
        const rowDiv = document.querySelector('#removable-part');

        var childCount = document.querySelectorAll('div.removable-div').length;
        var duplicate = false;

        var Ids = document.querySelectorAll('.removableId');
        var input;
        Ids.forEach(element => {
            if(element.value == removableDiv.value) {
                input = element.parentElement.children[0];
                duplicate = true;
            }
        });

        if(duplicate) {
            input.className = "input-error gray input-custom";
            input.scrollIntoView();
            setTimeout(() => {
                input.className = "gray input-custom";
            }, 3000);
            return;
        } else {
            const mainDiv = createExtraElement(removableDiv.selectedOptions[0].innerText, removableDiv.value, 'removable['+(childCount+1)+']', (childCount+1)*5, 'removable-div', 'removableId', false);
            rowDiv.insertBefore(mainDiv, document.querySelector('#child-removable-part'));
        }
    }

    function addExtraElement() {
        const extraDiv = document.querySelector('#extra');
        const rowDiv = document.querySelector('#extra-part');

        var childCount = document.querySelectorAll('div.extra-div').length;
        var duplicate = false;

        var Ids = document.querySelectorAll('.extraId');
        var input;
        Ids.forEach(element => {
            if(element.value == extraDiv.value) {
                input = element.parentElement.children[0];
                duplicate = true;
            }
        });

        if(duplicate) {
            input.className = "input-error gray input-custom";
            input.scrollIntoView();
            setTimeout(() => {
                input.className = "gray input-custom";
            }, 3000);
            return;
        } else {
            const mainDiv = createExtraElement(extraDiv.selectedOptions[0].innerText, extraDiv.value, 'extra['+(childCount+1)+']', (childCount+1)*5, 'extra-div', 'extraId', true);
            rowDiv.insertBefore(mainDiv, document.querySelector('#child-extra-part'));
        }
    }

    function addProductElement() {
        const productDiv = document.querySelector('#product');

        const rowDiv = document.querySelector('#products-part');

        var childCount = document.querySelectorAll('div.product-div').length;
        var duplicate = false;

        var Ids = document.querySelectorAll('.productId');
        var input;
        Ids.forEach(element => {
            if(element.value == productDiv.value) {
                input = element.parentElement.children[0];
                duplicate = true;
            }
        });

        if(duplicate) {
            input.className = "input-error gray input-custom";
            input.scrollIntoView();
            setTimeout(() => {
                input.className = "gray input-custom";
            }, 3000);
            return;
        } else {
            const mainDiv = createExtraElement(productDiv.selectedOptions[0].innerText, productDiv.value, 'product['+(childCount+1)+']', (childCount+1)*5, 'product-div', 'productId', true);
            rowDiv.insertBefore(mainDiv, document.querySelector('#child-products-part'));
        }
    }

    function createExtraElement(title, value, name, order, id, inputId, price = false) {
        const div = document.createElement('div');
        div.className = 'col-sm-12 custom child-div '+id;
        const inputText = document.createElement('input');
        inputText.innerHTML = title;
        inputText.className = 'gray input-custom';
        inputText.style = 'width: 100%';
        inputText.value = title;
        inputText.disabled = true;

        const input = document.createElement('input');
        input.className = 'gray input-custom';
        input.style = 'width: 100%';
        input.value = order;
        input.placeholder = 'Order';
        input.name = name + '[order]';

        if(price) {
            const inputPrice = document.createElement('input');
            inputPrice.className = 'gray input-custom';
            inputPrice.style = 'width: 100%';
            inputPrice.value = '';
            inputPrice.placeholder = 'Price';
            inputPrice.name = name + '[price]';
            div.appendChild(inputPrice);
        }

        const inputHidden = document.createElement('input');
        inputHidden.type = 'hidden';
        inputHidden.name = name + '[id]';
        inputHidden.value = value;
        inputHidden.className = inputId;

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn danger-button extra-remove';
        removeButton.textContent = 'Remove';

        div.appendChild(inputText);
        div.appendChild(input);
        div.appendChild(inputHidden);
        div.appendChild(removeButton);

        return div;
    }

    
});