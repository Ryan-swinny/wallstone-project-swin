var xhr = new XMLHttpRequest();

window.onload = function() {
    console.log('DOM fully loaded and parsed');
    var registerButton = document.getElementById('registerButton');
    if (registerButton) {
        registerButton.onclick = registerCustomer;
    } else {
        console.error('Register button not found');
    }
};

function registerCustomer() {
    console.log('registerCustomer function called');
    var firstName = document.getElementById('firstName').value;
    var lastName = document.getElementById('lastName').value;
    var email = document.getElementById('email').value;
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirmPassword').value;
    var phone = document.getElementById('phone').value;

    console.log('Form data:', { firstName: firstName, lastName: lastName, email: email, phone: phone });

    // Client-side validation
    if (!firstName || !lastName || !email || !password || !confirmPassword || !phone) {
        showMessage("All fields are required.");
        return;
    }

    if (password !== confirmPassword) {
        showMessage("Passwords do not match.");
        return;
    }

    if (!validatePhoneNumber(phone)) {
        showMessage("Invalid phone number format. Use 0d dddddddd.");
        return;
    }

    // Send data to server
    xhr.open("POST", "register.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = handleResponse;
    
    var data = 'firstName=' + encodeURIComponent(firstName) + '&lastName=' + encodeURIComponent(lastName) + 
               '&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password) + 
               '&phone=' + encodeURIComponent(phone);
    console.log('Sending data:', data);
    xhr.send(data);
}

function handleResponse() {
    console.log('XHR state:', xhr.readyState, 'status:', xhr.status);
    if (xhr.readyState === 4) {
        console.log('Raw response:', xhr.responseText);
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    handleRegistrationSuccess(response.customerId, document.getElementById('email').value);
                } else {
                    showMessage(response.message || "An error occurred during registration.");
                }
            } catch (e) {
                console.error('Error parsing JSON:', e);
                showMessage("An error occurred while processing the response. Please check the console for details.");
            }
        } else {
            showMessage("Server error: " + xhr.status + ". Please try again later.");
        }
    }
}

function handleRegistrationSuccess(customerId, email) {
    var message = 
        '<div class="success-message">' +
            '<h3>Registration Successful!</h3>' +
            '<p>Your customer ID is: <strong>' + customerId + '</strong></p>' +
            '<p>You have registered with the email: <strong>' + email + '</strong></p>' +
            '<p>Please save this information for your records.</p>' +
            '<p>You can now <a href="login.htm" class="action-link">log in</a> with your email and password.</p>' +
        '</div>' +
        '<div class="navigation-links">' +
            '<a href="buyOnline.htm" class="action-link">Back to Home</a>' +
        '</div>';
    showMessage(message);
    
    var form = document.getElementById('registerForm');
    if (form) {
        form.reset();
    }
}

function showMessage(message) {
    console.log('Showing message:', message);
    var messageElement = document.getElementById('message');
    if (!messageElement) {
        messageElement = document.createElement('div');
        messageElement.id = 'message';
        var form = document.getElementById('registerForm');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(messageElement, form);
        } else {
            document.body.insertBefore(messageElement, document.body.firstChild);
        }
    }
    messageElement.innerHTML = message;
    messageElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validatePhoneNumber(phone) {
    var phoneRegex = /^0\d\s\d{8}$/;
    return phoneRegex.test(phone);
}