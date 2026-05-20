document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("activationForm");
    const alertBox = document.getElementById("customAlert");

    if (form) {
        form.addEventListener("submit", function(e) {
            e.preventDefault(); // Bloque le rechargement classique de la page

            const email = document.getElementById("email").value;
            const code = document.getElementById("code").value;

            // Préparation des données à envoyer
            const formData = new FormData();
            formData.append("action", "verify");
            formData.append("email", email);
            formData.append("code", code);

            // Appel AJAX vers verify_code.php
            fetch("verify_code.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json()) // On attend du JSON en retour
            .then(data => {
                // On affiche la boîte d'alerte avec le style adapté
                alertBox.style.display = "block";
                alertBox.textContent = data.message;

                if (data.status === "success") {
                    alertBox.className = "alert success";
                    form.reset(); // Vide le formulaire en cas de succès
                    
                    // Optionnel : Rediriger vers l'étape suivante après 2 secondes
                    // setTimeout(() => { window.location.href = 'create_password.php'; }, 2000);
                } else {
                    alertBox.className = "alert error";
                }
            })
            .catch(error => {
                alertBox.style.display = "block";
                alertBox.className = "alert error";
                alertBox.textContent = "Une erreur réseau est survenue.";
            });
        });
    }
});