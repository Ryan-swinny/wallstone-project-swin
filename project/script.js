document.addEventListener('DOMContentLoaded', function() {
    const createAccountLink = document.querySelector('.create-account-link');
    
    createAccountLink.addEventListener('click', (event) => {
        event.preventDefault(); 
        window.location.href = 'register.htm'; 
    });

    createAccountLink.addEventListener('click', (event) => {
        event.preventDefault(); 
        window.location.href = 'login.htm'; 
    });
});