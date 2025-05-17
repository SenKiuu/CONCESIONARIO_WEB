// Función para mostrar u ocultar los formularios con un efecto de transición
document.getElementById('goToRegister').addEventListener('click', function() {
    document.getElementById('loginForm').classList.add('fade');
    document.getElementById('registerForm').classList.remove('fade');
    document.getElementById('registerForm').classList.add('active');
});

document.getElementById('goToLogin').addEventListener('click', function() {
    document.getElementById('registerForm').classList.add('fade');
    document.getElementById('loginForm').classList.remove('fade');
    document.getElementById('loginForm').classList.add('active');
});
