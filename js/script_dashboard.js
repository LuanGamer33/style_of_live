    const userInfo = document.getElementById('user-info');

firebase.auth().onAuthStateChanged((user) => {
    if (user) {
        // Mostrar usuario en pantalla
        document.getElementById("user-info").textContent = `Hola, ${user.displayName} (${user.email})`;

        // Enviar email y nombre a tu backend en PHP
        fetch("../php/sync_user.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: user.email, nombre: user.displayName })
        })
        .then(res => res.json())
        .then(data => {
            console.log("Usuario sincronizado con MySQL:", data);

            // Guardar el id_us en localStorage para usarlo después
            if (data.id_us) {
                localStorage.setItem("id_us", data.id_us);
            }
        })
        .catch(err => console.error("Error:", err));
    } else {
        window.location.href = "../index.html";
    }
});


function logout() {
    firebase.auth().signOut().then(() => {
        window.location.href = "../index.html";
    });
}

function guardarNota() {
    const id_us = localStorage.getItem("id_us");
    const nombre = document.getElementById("nota-nombre").value;
    const contenido = document.getElementById("nota-contenido").value;

    fetch("../php/guardar_nota.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_us, nombre, contenido })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("resultado").innerText =
            data.status === "ok"
                ? "Nota guardada con ID: " + data.id_nota
                : "Error: " + data.msg;
    })
    .catch(err => console.error("Error:", err));
}

function cargarNotas() {
    const id_us = localStorage.getItem("id_us");

    fetch("../php/obtener_notas.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_us })
    })
    .then(res => res.json())
    .then(notas => {
        const lista = document.getElementById("lista-notas");
        lista.innerHTML = "";

        if (notas.length === 0) {
            lista.innerHTML = "<p>No tienes notas guardadas.</p>";
            return;
        }

        notas.forEach(nota => {
            const card = document.createElement("div");
            card.classList.add("nota-card");
            card.innerHTML = `
                <h3>${nota.nom}</h3>
                <p>${nota.cont}</p>
            `;
            lista.appendChild(card);
        });
    })
    .catch(err => console.error("Error al obtener notas:", err));
}

firebase.auth().onAuthStateChanged((user) => {
    if (user) {
        document.getElementById("user-info").textContent = `Hola, ${user.displayName} (${user.email})`;

        fetch("../php/sync_user.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: user.email, nombre: user.displayName })
        })
        .then(res => res.json())
        .then(data => {
            if (data.id_us) {
                localStorage.setItem("id_us", data.id_us);
                cargarNotas(); // cargar notas al entrar
            }
        });
    } else {
        window.location.href = "../index.html";
    }
});


