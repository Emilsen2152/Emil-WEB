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
    { name: "Vepsebol Jordbær", price: 11 },
    { name: "Kvikk Lunsj 47G Freia", price: 22 },
    { name: "Sunniva Iste Fersken 1/2L", price: 28 },
    { name: "Julebrus 50CL", price: 26 },
    { name: "Mr.Lee Nudler Kopp Kyllingsmak 65G", price: 22 },
    { name: "Mr.Lee Nudler Kopp Kjøttsmak 65G", price: 22 },
    { name: "Billys Pan Pizza Original 170G", price: 26 },
    { name: "Fanta Orange 500ML Flaske", price: 22 },
    { name: "Sprite Regular 500ML Flaske", price: 22 },
    { name: "Coca-Cola Zero Sugar 500ML Flaske", price: 22 },
    { name: "Coca-Cola 500ML Flaske", price: 22 },
    { name: "Ahlgrens Biler Original 160G", price: 34 },
    { name: "Snickers 2PK 75G", price: 24 },
    { name: "Stratos Mellomplate 65G", price: 24 },
    { name: "Twix Xtra 75G", price: 29 },
    { name: "Kims Salt Crunch 200G", price: 29 },
    { name: "Kims Paprika Kick 200G", price: 29 },
    { name: "Laban Original 260G", price: 35 }
];

const productContainer = document.getElementById("product-container");
const addProductBtn = document.getElementById("add-product-btn");

// Funksjon for å legge til en ny produktlinje
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

    div.querySelector(".remove-btn").addEventListener("click", () => {
        div.remove();
    });
}

// Legg til første linje automatisk
addProductLine();

addProductBtn.addEventListener("click", addProductLine);

// =======================================
//             BRUK GAVEKORT
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

        // Reset skjema og legg til én linje igjen
        e.target.reset();
        productContainer.innerHTML = "";
        addProductLine();
    } catch (err) {
        console.error(err);
        showMessage(box, "Intern feil ved oppdatering.", false);
    }
});
