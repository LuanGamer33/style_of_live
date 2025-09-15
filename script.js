const container = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');

registerBtn.addEventListener('click', () => {
    container.classList.add('active');
});

loginBtn.addEventListener('click', () => {
    container.classList.remove('active');
});

function loginWith(providerName) {
let provider;

if (providerName === 'google') {
    provider = new firebase.auth.GoogleAuthProvider();
} else if (providerName === 'github') {
    provider = new firebase.auth.GithubAuthProvider();
} else {
    return;
}

firebase.auth().signInWithPopup(provider)
    .then((result) => {
        const user = result.user;
        window.location.href = "dashboard.html";
    })
    .catch((error) => {
        console.error("❌ Error al iniciar sesión:", error);
        alert("Error al iniciar sesión con " + providerName);
    });
}

firebase.auth().onAuthStateChanged((user) => {
if (user) {
    console.log("🔓 Usuario autenticado:", user.email);
} else {
    console.log("🔒 No hay usuario autenticado");
}
});