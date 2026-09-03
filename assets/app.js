import './bootstrap.js';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

console.log('Kafoden - app chargée');

// Bascule l'affichage d'un champ mot de passe (visible / masqué)
window.togglePasswordVisibility = function (inputId, bouton) {
    const input = document.getElementById(inputId);
    const icone = bouton.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icone.classList.remove('bi-eye');
        icone.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icone.classList.remove('bi-eye-slash');
        icone.classList.add('bi-eye');
    }
};