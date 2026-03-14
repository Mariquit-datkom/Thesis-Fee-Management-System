//Cleans login form to avoid unauthorized login
if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
}

window.addEventListener('pageshow', function (event) {
        const usernameField = document.getElementById('username');
        const passwordField = document.getElementById('password');
        
        if (usernameField) usernameField.value = '';
        if (passwordField) passwordField.value = '';

        const loginForm = document.querySelector('form');
        if (loginForm) {
            loginForm.reset();
        }
});