export function showGroupPopup () {
    const modal = document.getElementById('group-order-modal');

    if(modal === null) {
        return;
    }

    modal.style.display = "flex";
    modal.classList.add('show');

    document.getElementById('backdrop').style.display = "block";

    // Add listeners for buttons

    document.getElementById('group-order-yes').addEventListener('click', function () {
        saveGroupOrderStatus(true);
    });

    document.getElementById('group-order-no').addEventListener('click', function () {
        saveGroupOrderStatus(false);
    });
}

function saveGroupOrderStatus(status = false) {
    const url = document.getElementById('group-order-modal').getAttribute('data-action-url');

    $.ajax({
        method: 'PUT',
        url,
        data: {
            in_group: status
        },
        success: function(response) {},
        error: function() {},
        complete: function() {}
    });

    hideGroupPopup();
}

function hideGroupPopup() {
    const modal = document.getElementById('group-order-modal');

    modal.style.display = "none";
    modal.classList.remove('show');

    document.getElementById('backdrop').style.display = "none";
}
