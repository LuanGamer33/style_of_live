// Verificar autenticación antes de cargar la página
firebase.auth().onAuthStateChanged((user) => {
  if (!user) {
    window.location.href = "../index.html";
  }
});
