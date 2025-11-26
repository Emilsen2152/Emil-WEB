// =======================================
//          KONFIG
// =======================================
const API_BASE = "https://emil-web-api-production.up.railway.app/pingpanik/giftcards";
const userCheckUrl = "https://emil-web-api-production.up.railway.app/user";

// =======================================
//          TOKEN CHECK
// =======================================
(async () => {
    try {
        const res = await fetch(userCheckUrl, {
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' }
        });

        if (!res.ok) throw new Error("Token validering feila.");

        const data = await res.json();
        const user = data.user;

        if (
            user.username !== "admin" &&
            !user.permissions.includes("admin") &&
            !user.permissions.includes("pingpanik")
        ) {
            alert('Du har ikkje tilgang til denne sida.');
            window.location.href = "../../";
        }

    } catch (err) {
        console.error(err);
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
            ${card.localMessage ? `<div class="info-row"><em>${card.localMessage}</em></div>` : ""}
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
//          FETCH HELPERS
// =======================================
async function sendRequest(url, options = {}) {
    options.credentials = 'include'; // send cookie
    options.headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
    const res = await fetch(url, options);
    const data = await res.json();
    return { res, data };
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
        const { res, data } = await sendRequest(API_BASE, {
            method: "POST",
            body: JSON.stringify({ owner, balance: amount, originalValue: amount, barcode })
        });

        if (!res.ok) return showMessage(box, data.message, false);
        data.card.localMessage = "Gavekort oppretta";
        showCard(box, data.card);
        e.target.reset();

    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved oppretting.", false);
    }
});

// =======================================
//          HENT GAVEKORT
// =======================================
document.getElementById("fetch-giftcard-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const barcode = document.getElementById("fetch-barcode").value.trim();
    const box = document.getElementById("giftcard-info");

    try {
        const { res, data } = await sendRequest(`${API_BASE}/${barcode}`);
        if (!res.ok) return showMessage(box, data.message, false);
        data.card.localMessage = "Gavekort hentet";
        showCard(box, data.card);

    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved henting.", false);
    }
});

// =======================================
//          PÅFyll GAVEKORT
// =======================================
document.getElementById("topup-giftcard-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const barcode = document.getElementById("topup-barcode").value.trim();
    const amount = Number(document.getElementById("topup-amount").value);
    const box = document.getElementById("topup-info");

    try {
        const { res, data } = await sendRequest(`${API_BASE}/${barcode}/topup`, {
            method: "PUT",
            body: JSON.stringify({ amount })
        });

        if (!res.ok) return showMessage(box, data.message, false);
        data.card.localMessage = `Gavekort påfylt med ${amount} kr`;
        showCard(box, data.card);
        e.target.reset();

    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved påfylling.", false);
    }
});

// =======================================
//          PRODUKT LISTE
// =======================================
const products = [
    { name: "VEPSEBOL JORDBÆR", price: 10 },
    { name: "KVIKK LUNSJ 47G FREIA", price: 20 },
    { name: "SUNNIVA ISTE FERSKEN 1/2L", price: 27 },
    { name: "MR.LEE NUDLER KOPP KYLLINGSMAK 65G", price: 25 },
    { name: "MR.LEE NUDLER KOPP KJØTTSMAK 65G", price: 25 },
    { name: "BILLYS PAN PIZZA ORIGINAL 170G", price: 25 },
    { name: "FANTA ORANGE 500ML FLASKE", price: 27 },
    { name: "SPRITE REGULAR 500ML FLASKE", price: 27 },
    { name: "COCA-COLA ZERO SUGAR 500ML FLASKE", price: 27 },
    { name: "COCA-COLA 500ML FLASKE", price: 27 },
    { name: "AHLGRENS BILER ORIGINAL 160G", price: 35 },
    { name: "SNICKERS 2PK 75G", price: 25 },
    { name: "STRATOS MELLOMPLATE 65G", price: 25 },
    { name: "TWIX XTRA 75G", price: 30 },
    { name: "KIMS SALT CRUNCH 200G", price: 25 },
    { name: "KIMS PAPRIKA KICK 200G", price: 25 },
    { name: "LABAN ORIGINAL 260G", price: 35 }
];

const productContainer = document.getElementById("product-container");
const addProductBtn = document.getElementById("add-product-btn");

function addProductLine() {
    const div = document.createElement("div");
    div.classList.add("product-line");
    div.innerHTML = `
        <select class="form-input product-select">
            <option value="">Velg produkt</option>
            ${products.map((p, i) => `<option value="${i}">${p.name} (${p.price} kr)</option>`).join("")}
        </select>
        <input type="number" min="1" value="1" class="form-input product-qty">
        <button type="button" class="remove-btn">✖</button>
    `;
    productContainer.appendChild(div);

    div.querySelector(".remove-btn").addEventListener("click", () => div.remove());
}

addProductLine();
addProductBtn.addEventListener("click", addProductLine);

// =======================================
//          BRUK GAVEKORT
// =======================================
document.getElementById("redeem-giftcard-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const barcode = document.getElementById("redeem-barcode").value.trim();
    const box = document.getElementById("redeem-info");

    let totalAmount = 0;
    const itemsBought = [];

    document.querySelectorAll(".product-line").forEach(line => {
        const index = line.querySelector(".product-select").value;
        const qty = Number(line.querySelector(".product-qty").value);
        if (index !== "" && qty > 0) {
            const product = products[index];
            totalAmount += product.price * qty;
            itemsBought.push({ name: product.name, quantity: qty, price: product.price });
        }
    });

    if (totalAmount === 0) return showMessage(box, "Velg minst ett produkt.", false);

    try {
        const { res, data } = await sendRequest(`${API_BASE}/${barcode}/sale`, {
            method: "PUT",
            body: JSON.stringify({ amount: totalAmount })
        });

        if (!res.ok) return showMessage(box, data.message, false);

        data.card.localMessage = `Gavekort oppdatert! Totalt brukt: ${totalAmount} kr`;
        if (itemsBought.length > 0) {
            data.card.items = data.card.items || [];
            data.card.items.push(...itemsBought);
        }

        showCard(box, data.card);

        e.target.reset();
        productContainer.innerHTML = "";
        addProductLine();
    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved oppdatering.", false);
    }
});
