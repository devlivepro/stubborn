// assets/js/passwordHidden.js

document.getElementById('togglePasswordVisibility').addEventListener('change', function() {
    var passwordField = document.getElementById('password');
    var confirmPasswordField = document.getElementById('confirmPassword');
    if (this.checked) {
        passwordField.type = 'text';
        confirmPasswordField.type = 'text';
    } else {
        passwordField.type = 'password';
        confirmPasswordField.type = 'password';
    }
});
