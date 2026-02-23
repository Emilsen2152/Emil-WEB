const API_BASE = "https://emil.elevweb.no/pingpanik/timerboard";

const resultsList = document.getElementById("results-list");
const form = document.getElementById("timing-form");

// ===========================
//   TOKEN / PERMISSIONS CHECK
// ===========================
(async () => {
    try {
        const res = await fetch('https://emil.elevweb.no/user', {
            credentials: 'include',
            headers: { "Content-Type": "application/json" }
        });

        if (!res.ok) throw new Error('Token validering feila.');

        const data = await res.json();
        const user = data.user;

        if (user.username !== 'admin' &&
            !user.permissions.includes('admin') &&
            !user.permissions.includes('pingpanik')) {
            window.location.href = 'resultat';
        }

        loadEntries();
    } catch (err) {
        console.error(err);
        window.location.href = 'resultat';
    }
})();

// ===========================
//   HENT RESULTATLISTE
// ===========================
async function loadEntries() {
    try {
        const res = await fetch(API_BASE, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok) return alert(data.message || "Feil ved henting av resultat.");

        renderEntries(data.entries);
    } catch (err) {
        console.error("Feil ved lasting:", err);
        alert("Klarte ikkje å lasta resultat.");
    }
}

// ===========================
//   VIS RESULTATLISTE
// ===========================
function renderEntries(entries) {
    resultsList.innerHTML = "";

    if (!entries || entries.length === 0) {
        resultsList.innerHTML = `<p>Ingen resultat enno.</p>`;
        return;
    }

    const header = document.createElement("div");
    header.classList.add("result-header");
    header.innerHTML = `
        <span>#</span>
        <span>Namn</span>
        <span>E-post</span>
        <span>Telefon</span>
        <span>Alder</span>
        <span>Tid</span>
        <span>Handling</span>
    `;
    resultsList.appendChild(header);

    entries.forEach((entry, index) => {
        const div = document.createElement("div");
        div.classList.add("result-entry");
        div.dataset.id = entry._id;

        div.innerHTML = `
            <span class="result-rank">${index + 1}.</span>
            <span class="result-name">${entry.name}</span>
            <span class="result-email">${entry.email}</span>
            <span class="result-phone">${entry.phone}</span>
            <span class="result-age">${entry.age}</span>
            <span class="result-time">${entry.formattedTime}</span>
            <button class="form-button form-button--reset">Fjern</button>
        `;

        div.querySelector("button").addEventListener("click", () => deleteEntry(entry._id));
        resultsList.appendChild(div);
    });
}

// ===========================
//   SLETT DELTAKAR
// ===========================
async function deleteEntry(id) {
    //if (!confirm("Er du sikker på at du vil fjerna denne deltakaren?")) return;

    try {
        const res = await fetch(`${API_BASE}/${id}`, {
            method: "DELETE",
            credentials: 'include'
        });

        const data = await res.json();
        if (!res.ok) return alert(data.message || "Klarte ikkje å sletta deltakar.");

        //alert("Deltakar sletta.");
        loadEntries();
    } catch (err) {
        console.error("Feil ved sletting:", err);
        alert("Klarte ikkje å sletta deltakar.");
    }
}

// ===========================
//   HANDTER SKJEMA
// ===========================
if (form) {
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const phone = form.phone.value.trim();
        const age = form.age.value.trim();
        const time = form.time.value.trim();

        if (!name || !email || !phone || !age || !time) {
            return alert("Alle felt er påkrevd.");
        }

        const timeParts = time.split(":");
        if (timeParts.length !== 2) return alert("Ugyldig tidformat. Bruk MM:SS.");

        const minutes = parseInt(timeParts[0], 10);
        const seconds = parseInt(timeParts[1], 10);
        if (isNaN(minutes) || isNaN(seconds)) return alert("Ugyldig tidformat. Bruk MM:SS.");

        try {
            const res = await fetch(API_BASE, {
                method: "POST",
                credentials: 'include',
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ name, email, phone, age, time })
            });

            const data = await res.json();
            if (!res.ok) return alert(data.message || "Feil ved registrering av deltakar.");

            alert("Deltakar registrert!");
            form.reset();
            loadEntries();
        } catch (err) {
            console.error("Feil ved registrering:", err);
            alert("Klarte ikkje å registrere deltakar.");
        }
    });
}
