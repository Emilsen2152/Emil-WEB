const API_BASE = "https://emil-web-api-production.up.railway.app/pingpanik/timerboard-open";
const resultsList = document.getElementById("results-list");

// Funksjon for å hente og vise resultatlista
async function loadEntries() {
    try {
        const res = await fetch(API_BASE);
        const data = await res.json();

        if (!res.ok) {
            console.error("Feil ved henting:", data.message || data);
            return;
        }

        renderEntries(data.entries);
    } catch (err) {
        console.error("Feil ved henting:", err);
    }
}

// Funksjon for å vise lista i HTML
function renderEntries(entries) {
    // Tøm gamle rader, behold header
    const header = resultsList.querySelector(".result-header");
    resultsList.innerHTML = "";
    if (header) resultsList.appendChild(header);

    if (!entries || entries.length === 0) {
        const p = document.createElement("p");
        p.textContent = "Ingen resultat enno.";
        resultsList.appendChild(p);
        return;
    }

    entries.forEach((entry, index) => {
        const div = document.createElement("div");
        div.classList.add("result-entry");
        div.innerHTML = `
            <span class="result-rank">${index + 1}.</span>
            <span class="result-name">${entry.name}</span>
            <span class="result-time">${entry.time}</span>
        `;
        resultsList.appendChild(div);
    });
}

// Last inn lista med ein gong
loadEntries();

// Oppdater automatisk kvart minutt (60000 ms)
setInterval(loadEntries, 60000);
