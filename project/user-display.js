document.addEventListener('DOMContentLoaded', function() {
    // User display functionality
    function updateMenuWithUserEmail() {
        const email = localStorage.getItem('userEmail');
        const userNameElement = document.getElementById('userNameDisplay');
        if (userNameElement && email) {
            userNameElement.textContent = `Welcome, ${email}`;
        }
    }

    updateMenuWithUserEmail();

    // Logout handling functionality
    const logoutLink = document.getElementById('logout');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'user_logout.php', true);
            xhr.setRequestHeader('Content-Type', 'application/vnd.ant.code-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            localStorage.removeItem('userEmail');
                            window.location.href = 'user_logout.htm';
                        } else {
                            console.error('Logout failed:', response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                } else {
                    console.error('Logout request failed');
                }
            };
            xhr.send();
        });
    }
});