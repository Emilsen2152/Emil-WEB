document.addEventListener("DOMContentLoaded", () => {
    const skjema = document.getElementById("påmeldingsskjema");
    const feedback = document.getElementById("feedback");
    const feedbackText = document.getElementById("feedback-text");
    const feedbackImg = document.getElementById("feedback-img");
    const feedbackClose = document.getElementById("feedback-close");

    skjema.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Finn submit-knappen
        const submitBtn = skjema.querySelector("button[type='submit']");
        const originalText = submitBtn.innerHTML; // lagre original tekst

        // Deaktiver knappen og vis spinner
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sender...`;

        fetch("send.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                // Vis feedback-boksen
                feedback.classList.remove("d-none");
                feedback.classList.remove("text-success", "text-danger");
                feedbackText.textContent = data.message;

                if (data.success) {
                    feedback.classList.add("text-success");
                    feedbackImg.src = "assets/green_check.png"; // grønt check
                } else {
                    feedback.classList.add("text-danger");
                    feedbackImg.src = "assets/red_x.png"; // rød X
                }

                // Skjul skjemaet
                skjema.reset();
                skjema.style.display = "none";

                // Gjenopprett knappen til original tilstand (aktiv)
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            })
            .catch(error => {
                feedback.classList.remove("d-none");
                feedbackText.textContent = "Det oppstod ein feil. Vennligst prøv igjen.";
                feedback.classList.add("text-danger");
                feedbackImg.src = "https://placehold.co/150x150/FF0000/FFFFFF?text=X"; // rød X

                // Skjul skjemaet
                skjema.reset();
                skjema.style.display = "none";

                // Gjenopprett knappen til original tilstand (aktiv)
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                console.error(error);
            });
    });




    // Lukke-knapp
    feedbackClose.addEventListener("click", () => {
        feedback.classList.add("d-none");
        skjema.style.display = "block"; // vis skjemaet igjen
        feedbackImg.src = "https://placehold.co/150x150"; // default placeholder
    });
});
