document.getElementById("medlemskap-skjema").addEventListener("submit", async (e) => {
    e.preventDefault();

    const navn = document.getElementById("navn").value;
    const epost = document.getElementById("epost").value;
    const telefon = document.getElementById("telefon").value;

    const res = await fetch("send.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ navn, epost, telefon })
    });

    const data = await res.json();
    const resultatBox = document.getElementById("påmeldingsresultat");
    const tekst = document.getElementById("påmeldingsresultat-tekst");

    if (data.status === "success") {
        resultatBox.classList.remove("d-none", "alert-danger");
        resultatBox.classList.add("alert-success");
        tekst.textContent = data.message;
        document.getElementById("medlemskap-skjema").reset();
    } else {
        resultatBox.classList.remove("d-none", "alert-success");
        resultatBox.classList.add("alert-danger");
        tekst.textContent = data.message;
    }
});
