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
    container.classList.remove("hidden");
}

function showCard(container, card) {
    let itemsHtml = "";
    if (card.items && card.items.length > 0) {
        itemsHtml = "<div class='info-row'><strong>Kjøpte produkter:</strong><ul>";
        card.items.forEach(item => {
            itemsHtml += `<li>${item.name} x${item.quantity} (${item.price} kr/stk)</li>`;
        });
        itemsHtml += "</ul></div>";
    }

    container.innerHTML = `
        <div class="success-box">
            <div class="info-title">Gavekort</div>
            <div class="info-row"><strong>Eigar:</strong> ${card.owner}</div>
            <div class="info-row"><strong>Saldo:</strong> ${card.balance} kr</div>
            <div class="info-row"><strong>Startverdi:</strong> ${card.originalValue} kr</div>
            <div class="info-row"><strong>Strekkode:</strong> ${card.barcode}</div>
            ${itemsHtml}
        </div>
    `;
    container.classList.remove("hidden");
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
//          PRODUKT LISTE
// =======================================
const products = [
    { name: "Søtsaker", price: 10 },
    { name: "Drikke", price: 15 },
    { name: "Smørbrød", price: 25 }
];

const productListContainer = document.getElementById("product-list");
products.forEach((prod, index) => {
    const div = document.createElement("div");
    div.classList.add("product-row");
    div.innerHTML = `
        <label class="form-label">${prod.name} (${prod.price} kr per stk):</label>
        <input type="number" min="0" value="0" id="product-${index}" class="form-input">
    `;
    productListContainer.appendChild(div);
});

// =======================================
//             BRUK GAVEKORT
// =======================================
document.getElementById("redeem-giftcard-form").addEventListener("submit", async (e) => {
    e.preventDefault();

    const barcode = document.getElementById("redeem-barcode").value.trim();
    const box = document.getElementById("redeem-info");

    let totalAmount = 0;
    const itemsBought = [];

    products.forEach((prod, index) => {
        const qty = Number(document.getElementById(`product-${index}`).value);
        if (qty > 0) {
            totalAmount += prod.price * qty;
            itemsBought.push({ name: prod.name, quantity: qty, price: prod.price });
        }
    });

    if (totalAmount === 0) return showMessage(box, "Velg minst ett produkt.", false);

    try {
        const res = await fetch(`${API_BASE}/${barcode}/salg`, {
            method: "PUT",
            headers: {
                "Authorization": token,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ amount: totalAmount, items: itemsBought })
        });

        const data = await res.json();
        if (!res.ok) return showMessage(box, data.message, false);

        showMessage(box, `Gavekort oppdatert! Totalt brukt: ${totalAmount} kr`);
        showCard(box, data.card);

        e.target.reset();

    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved oppdatering.", false);
    }
});
