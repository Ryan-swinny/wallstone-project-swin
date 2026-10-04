let cart = {};
let itemDetails = {};

function loadItems() {
    $.ajax({
        url: 'get_catalog.php?action=get_items',
        dataType: 'xml',
        success: function (xml) {
            const items = [];
            $(xml).find('good').each(function () {
                const item = {
                    itemNumber: $(this).find('id').text(),
                    name: $(this).find('name').text(),
                    description: $(this).find('description').text(),
                    price: parseFloat($(this).find('price').text()),
                    quantityAvailable: parseInt($(this).find('quantity_available').text()),
                    quantityOnHold: parseInt($(this).find('quantity_on_hold').text()),
                    quantitySold: parseInt($(this).find('quantity_sold').text())
                };
                items.push(item);
            });
            displayCatalog(items);
            updateItemDetails(items);
        },
        error: function (xhr, status, error) {
            console.error('Failed to load items:', error);
            console.log('Response:', xhr.responseText);
            alert('Failed to load items. Please check the console for more details.');
        }
    });
}

function displayCatalog(items) {
    const catalogBody = $('#catalog tbody');
    catalogBody.empty();
    items.forEach(item => {
        if (item.quantityAvailable > 0) {
            catalogBody.append(`
                <tr>
                    <td>${item.itemNumber}</td>
                    <td>${item.name}</td>
                    <td>${item.description}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>${item.quantityAvailable}</td>
                    <td><button class="addToCart" data-item="${item.itemNumber}">Add to cart</button></td>
                </tr>
            `);
        }
    });
}

function updateItemDetails(items) {
    itemDetails = {};
    items.forEach(item => {
        itemDetails[item.itemNumber] = item;
    });
}

function addToCart(itemNumber) {
    $.ajax({
        url: 'get_catalog.php?action=update_cart',
        method: 'POST',
        data: { action: 'add', itemNumber: itemNumber },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                cart = response.cart;
                loadItems(); // Reload items to update quantities
                displayCart();
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to add item to cart:', error);
        }
    });
}

function removeFromCart(itemNumber) {
    $.ajax({
        url: 'get_catalog.php?action=update_cart',
        method: 'POST',
        data: { action: 'remove', itemNumber: itemNumber },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                cart = response.cart;
                loadItems(); // Reload items to update quantities
                displayCart();
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to remove item from cart:', error);
        }
    });
}

function displayCart() {
    const cartBody = $('#cart tbody');
    cartBody.empty();
    Object.entries(cart).forEach(([itemNumber, item]) => {
        cartBody.append(`
            <tr data-item="${itemNumber}">
                <td>${itemNumber}</td>
                <td class="quantity">${item.quantity}</td>
                <td>$${(item.price * item.quantity).toFixed(2)}</td>
                <td><button class="removeFromCart" data-item="${itemNumber}">Remove from cart</button></td>
            </tr>
        `);
    });
}

function confirmPurchase() {
    $.ajax({
        url: 'get_catalog.php?action=process_purchase',
        method: 'POST',
        data: { action: 'confirm' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(`Your purchase has been confirmed. Total amount: $${response.totalAmount.toFixed(2)}`);
                processOrders();
                cart = {};
                loadItems();
                displayCart();
            } else {
                alert('Failed to process purchase');
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to confirm purchase:', error);
        }
    });
}

function processOrders() {
    $.ajax({
        url: 'processing_orders.php',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log(response.message);
            } else {
                console.error('Failed to process orders:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error processing orders:', error);
        }
    });
}

function cancelPurchase() {
    $.ajax({
        url: 'get_catalog.php?action=process_purchase',
        method: 'POST',
        data: { action: 'cancel' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Your purchase request has been cancelled');
                cart = {};
                loadItems();
                displayCart();
            } else {
                alert('Failed to cancel purchase');
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to cancel purchase:', error);
        }
    });
}

$(document).ready(function () {
    // Initial load
    loadItems();

    // Load items every 10 seconds
    setInterval(loadItems, 10000);

    $(document).on('click', '.addToCart', function () {
        addToCart($(this).data('item'));
    });

    $(document).on('click', '.removeFromCart', function () {
        removeFromCart($(this).data('item'));
    });

    $('#confirmPurchase').click(confirmPurchase);
    $('#cancelPurchase').click(cancelPurchase);

    $('#logout').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: 'user_logout.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // clean out localstorage
                    localStorage.removeItem('userEmail');
                    // redirect to logout
                    window.location.href = 'user_logout.htm';
                } else {
                    console.error('logout failed:', response.message);
                    alert('logout failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('logout failed:', error);
                alert('logout failed，please try later');
            }
        });
    });
        });

