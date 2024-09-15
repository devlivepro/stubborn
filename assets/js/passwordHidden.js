// assets/js/passwordHidden.js

document.addEventListener('DOMContentLoaded', function() {
    var togglePasswordVisibility = document.getElementById('togglePasswordVisibility');
    var passwordField = document.getElementById('registration_form_plainPassword_first');
    var confirmPasswordField = document.getElementById('registration_form_plainPassword_second');

    console.log("toggleCheckbox: ", togglePasswordVisibility);
    console.log("passwordField: ", passwordField);
    console.log("confirmPasswordField: ", confirmPasswordField);

    // Réinitialise la case à cocher lorsque la page est rechargée
    if (togglePasswordVisibility) {
        togglePasswordVisibility.checked = false;
    }

    if (togglePasswordVisibility && passwordField && confirmPasswordField) {
        togglePasswordVisibility.addEventListener('change', function() {
            if (this.checked) {
                passwordField.type = 'text';
                confirmPasswordField.type = 'text';
            } else {
                passwordField.type = 'password';
                confirmPasswordField.type = 'password';
            }
        });
    } else {
        console.log("Les éléments nécessaires pour afficher les mots de passe n'ont pas été trouvés.");
    }
});
