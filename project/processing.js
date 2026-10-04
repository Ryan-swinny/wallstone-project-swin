// processing.js

$(document).ready(function() {
    loadProcessingItems();

    $('#processOrdersBtn').click(function() {
        processOrders();
    });

    $('#logoutLink').click(function(e) {
        e.preventDefault();
        logout();
    });
});

function loadProcessingItems() {
    $.ajax({
        url: 'processing.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayProcessingItems(response.items);
            } else {
                alert('Failed to load processing items: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading processing items:', error);
        }
    });
}

function displayProcessingItems(items) {
    const tableBody = $('#processingTable tbody');
    tableBody.empty();
    items.forEach(item => {
        tableBody.append(`
            <tr>
                <td>${item.id}</td>
                <td>${item.name}</td>
                <td>$${parseFloat(item.price).toFixed(2)}</td>
                <td>${item.quantity_available}</td>
                <td>${item.quantity_on_hold}</td>
                <td>${item.quantity_sold}</td>
            </tr>
        `);
    });
}

function processOrders() {
    $.ajax({
        url: 'processing.php',
        method: 'POST',
        data: { action: 'process_orders' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadProcessingItems(); // reload to see the update 
            } else {
                alert('Failed to process orders: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error processing orders:', error);
        }
    });
}

function logout() {
    $.ajax({
        url: 'get_catalog.php?action=process_purchase',
        method: 'POST',
        data: { action: 'logout' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                window.location.href = 'logout.htm?id=' + encodeURIComponent(response.managerId);
            } else {
                alert('Failed to logout: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error during logout:', error);
        }
    });
}