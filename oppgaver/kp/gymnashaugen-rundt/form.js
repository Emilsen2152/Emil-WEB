document.addEventListener("DOMContentLoaded", () => {
    const skjema = document.getElementById("påmeldingsskjema");
    const feedback = document.getElementById("feedback");
    const feedbackText = document.getElementById("feedback-text");
    const feedbackImg = document.getElementById("feedback-img");
    const feedbackClose = document.getElementById("feedback-close");

    skjema.addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);

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
                skjema.style.display = "none"; // skjul skjemaet
            } else {
                feedback.classList.add("text-danger");
                feedbackImg.src = "assets/red_x.png"; // rød X
                skjema.style.display = "none"; // skjul skjemaet
            }
        })
        .catch(error => {
            feedback.classList.remove("d-none");
            feedbackText.textContent = "Det oppstod ein feil. Vennligst prøv igjen.";
            feedback.classList.add("text-danger");
            feedbackImg.src = "https://placehold.co/150x150/FF0000/FFFFFF?text=X"; // rød X
            skjema.style.display = "none";
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
