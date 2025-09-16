    const userInfo = document.getElementById('user-info');

    firebase.auth().onAuthStateChanged((user) => {
    if (user) {
        userInfo.textContent = `Hola, ${user.displayName} (${user.email})`;
    } else {
        window.location.href = "../index.html";
    }
});

function logout() {
    firebase.auth().signOut().then(() => {
        window.location.href = "../index.html";
    });
}