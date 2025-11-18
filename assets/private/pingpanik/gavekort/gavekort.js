// =======================================
//          KONFIG
// =======================================
const API_BASE = "https://emil-web-api-production.up.railway.app/pingpanik/giftcards";
const token = localStorage.getItem("emil-web-token");

const userCheckUrl = "https://emil-web-api-production.up.railway.app/user";


// =======================================
//          TOKEN CHECK
// =======================================
(async () => {
    if (!token) return (window.location.href = "../../konto/login?redirect=pingpanik/gavekort");

    try {
        const res = await fetch(userCheckUrl, {
            headers: {
                "Authorization": token,
                "Content-Type": "application/json"
            }
        });

        if (!res.ok) throw new Error("Token validering feila.");

        const data = await res.json();
        const user = data.user;

        if (
            user.username !== "admin" &&
            !user.permissions.includes("admin") &&
            !user.permissions.includes("pingpanik")
        ) {
            window.location.href = "../../";
            alert('Du har ikkje tilgang til denne sida.');
        }

    } catch (err) {
        console.error(err);
        localStorage.removeItem("emil-web-token");
        window.location.href = "../../konto/login?redirect=pingpanik/gavekort";
    }
})();


// =======================================
//          HJELPEFUNKSJONER
// =======================================

function showMessage(container, message, success = true) {
    container.innerHTML = `
        <div class="${success ? "success-box" : "error-box"}">
            <p>${message}</p>
        </div>
    `;
}

function showCard(container, card) {
    container.innerHTML = `
        <div class="success-box">
            <div class="info-title">Gavekort</div>
            <div class="info-row"><strong>Eigar:</strong> ${card.owner}</div>
            <div class="info-row"><strong>Saldo:</strong> ${card.balance} kr</div>
            <div class="info-row"><strong>Startverdi:</strong> ${card.originalValue} kr</div>
            <div class="info-row"><strong>Strekkode:</strong> ${card.barcode}</div>
        </div>
    `;
}


// =======================================
//          REGISTRER GAVEKORT
// =======================================

document.getElementById("new-giftcard-form").addEventListener("submit", async (e) => {
    e.preventDefault();

    const owner = document.getElementById("recipient-name").value.trim();
    const amount = Number(document.getElementById("amount").value);
    const barcode = document.getElementById("barcode").value.trim();

    const box = document.getElementById("giftcard-info");

    try {
        const res = await fetch(API_BASE, {
            method: "POST",
            headers: {
                "Authorization": token,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                owner,
                balance: amount,
                originalValue: amount,
                barcode
            })
        });

        const data = await res.json();
        if (!res.ok) return showMessage(box, data.message, false);

        showMessage(box, "Gavekort oppretta!");
        showCard(box, data.card);

        e.target.reset();

    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved oppretting.", false);
    }
});


// =======================================
//             HENT GAVEKORT
// =======================================

document.getElementById("fetch-giftcard-form").addEventListener("submit", async (e) => {
    e.preventDefault();

    const barcode = document.getElementById("fetch-barcode").value.trim();
    const box = document.getElementById("giftcard-info");

    try {
        const res = await fetch(`${API_BASE}/${barcode}`, {
            headers: {
                "Authorization": token,
                "Content-Type": "application/json"
            }
        });

        const data = await res.json();
        if (!res.ok) return showMessage(box, data.message, false);

        showCard(box, data.card);

    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved henting.", false);
    }
});


// =======================================
//             BRUK GAVEKORT
// =======================================

document.getElementById("redeem-giftcard-form").addEventListener("submit", async (e) => {
    e.preventDefault();

    const barcode = document.getElementById("redeem-barcode").value.trim();
    const amount = Number(document.getElementById("redeem-amount").value);
    const box = document.getElementById("redeem-info");

    try {
        const res = await fetch(`${API_BASE}/${barcode}/salg`, {
            method: "PUT",
            headers: {
                "Authorization": token,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ amount })
        });

        const data = await res.json();
        if (!res.ok) return showMessage(box, data.message, false);

        showMessage(box, "Gavekort oppdatert!");
        showCard(box, data.card);

        e.target.reset();

    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved oppdatering.", false);
    }
});
