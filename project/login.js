document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'check_login.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // localstore
                    localStorage.setItem('userEmail', response.email);
                    
                    // buying.htm
                    window.location.href = 'buying.htm';
                } else {
                    errorMessage.textContent = 'Login failed, please check your email';
                }
            } else {
                errorMessage.textContent = 'Errors。';
            }
        };
        xhr.send(`email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`);
    });
});