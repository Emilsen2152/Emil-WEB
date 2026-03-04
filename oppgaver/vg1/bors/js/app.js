// Minimal dummy JS
(function () {

    // Opprettelse av spillvariabler.
    const player = {
        navn: prompt("Hva er navnet ditt?") || "Spiller 1",
        saldo: 20000
    };

    const NPCBuyers = [

    ];

    const fornavnListe = [
        "Ola", "Kari", "Per", "Lise", "Nils", "Eva", "Jens", "Ingrid", "Morten", "Sofie",
        "Erik", "Anna", "Jonas", "Ida", "Magnus", "Emma", "Sander", "Julie", "Andreas", "Maria",
        "Lars", "Hanne", "Kristian", "Silje", "Tobias", "Martine", "Henrik", "Camilla", "Ole", "Kristine",
        "Eirik", "Helene", "Fredrik", "Vilde", "Martin", "Amalie", "Sindre", "Thea", "Joakim", "Sara",
        "Stian", "Celine", "Vegard", "Malin", "Sebastian", "Emilie", "Daniel", "Nora", "Thomas", "Live",
        "William", "Astrid", "Alexander", "Frida", "Filip", "Linnea", "Lukas", "Maja", "Benjamin", "Tuva",
        "Markus", "Oda", "Mathias", "Selma", "Adrian", "Hedda", "Jonathan", "Sanna", "Isak", "Tiril",
        "Noah", "Jenny", "Oliver", "Kaja", "Jakob", "Solveig", "Håkon", "Ragnhild", "Knut", "Birgit",
        "Arne", "Odd", "Tor", "Rune", "Bjørn", "Dag", "Leif", "Stein", "Gunnar", "Terje"
    ];
    const etternavnListe = [
        "Nordmann", "Hansen", "Johansen", "Olsen", "Larsen", "Andersen", "Nilsen", "Pedersen", "Berg", "Haugen",
        "Kristiansen", "Johnsen", "Solberg", "Moen", "Knutsen", "Myhre", "Holm", "Dahl", "Lien", "Lunde",
        "Halvorsen", "Bakke", "Andreassen", "Jacobsen", "Svendsen", "Strand", "Sæther", "Haug", "Mathisen", "Aas",
        "Fredriksen", "Bjørnstad", "Isaksen", "Karlsen", "Sørensen", "Eide", "Lie", "Tangen", "Thorsen", "Aasen",
        "Foss", "Vik", "Sund", "Skogen", "Løvås", "Hovland", "Moe", "Rønning", "Gundersen", "Hegg",
        "Grande", "Birkeland", "Kleiven", "Heggen", "Huse", "Tveit", "Skår", "Løken", "Rud", "Torp",
        "Åsheim", "Torsvik", "Røed", "Skjelstad", "Børresen", "Alstad", "Grønning", "Voll", "Holen", "Sørli",
        "Kjeldsen", "Tveten", "Sætre", "Kleven", "Tollaksen", "Rød", "Gjerstad", "Nergaard", "Finstad", "Bøe"
    ];

    // Generer 99 NPC-kjøpere

    for (let i = 1; i <= 99; i++) {
        const navn = `${fornavnListe[Math.floor(Math.random() * fornavnListe.length)]} ${etternavnListe[Math.floor(Math.random() * etternavnListe.length)]}`;

        if (navn === player.navn || NPCBuyers.some(npc => npc.navn === navn)) {
            i--; // Navnet er allerede tatt, prøv igjen
            continue;
        }

        NPCBuyers.push({
            navn: navn,
            saldo: Math.floor(Math.random() * 50000) + 5000 // Random saldo mellom 5k og 55k
        });
    }

    const daysOfWeek = ["Søndag", "Mandag", "Tirsdag", "Onsdag", "Torsdag", "Fredag", "Lørdag"];

    // spelvariabel
    const game = {
        date: new Date(2026, 0, 1),
        open: true
    }

    // Kategori
    const category = [
        { navn: "Transport", id: 1 },
        { navn: "Olje", id: 2 },
        { navn: "Tech", id: 3 },
        { navn: "Diverse", id: 0 }
    ]

    // Aksjer
    const stock = [
        { navn: "Statoil", price: 1000, available: 500, owned: 0, npcOwned: {}, category: 2 },
        { navn: "Telenor", price: 100, available: 400, owned: 0, npcOwned: {}, category: 3 },
        { navn: "Tine", price: 45.5, available: 250, owned: 0, npcOwned: {}, category: 0 },
        { navn: "PingPanik", price: 15, available: 100000, owned: 0, npcOwned: {}, category: 3 },
        { navn: "Apple", price: 3299, available: 2300, owned: 0, npcOwned: {}, category: 3 },
        { navn: "Klofta Taxi", price: 60, available: 200, owned: 0, npcOwned: {}, category: 1 },
        { navn: "Oslo Buss", price: 120, available: 300, owned: 0, npcOwned: {}, category: 1 },
        { navn: "Oslo Lufthavn", price: 250, available: 150, owned: 0, npcOwned: {}, category: 1 },
        { navn: "Norsk Sjømat", price: 80, available: 400, owned: 0, npcOwned: {}, category: 0 },
        { navn: "Boombox", price: 220, available: 350, owned: 0, npcOwned: {}, category: 0 },
        { navn: "Voss Kommunikasjon", price: 90, available: 300, owned: 0, npcOwned: {}, category: 3 },
    ]


    // Her er trykk på ny day knappen
    document.getElementById("newDay").addEventListener("click", () => {
        runNewDay();
    });

    // Her er knappane frå aksjelista
    document.addEventListener("click", function (event) {
        if (!event.target) return;

        if (event.target.tagName !== "BUTTON") return;

        if (event.target.id === "kjop") {
            const stockID = event.target.getAttribute("data-stock");
            buyStock(stockID);
        } else if (event.target.id === "selg") {
            const stockID = event.target.getAttribute("data-stock");
            sellStock(stockID);
        }
    });

    function sellStock(stockID) {
        if (!game.open) {
            alert("Børsen er stengt. Du kan ikkje selga aksjar nå.");
            return;
        }
        const currentStock = stock[stockID];
        const antall = parseInt(prompt("Kor mange aksjar vil du selga?"));

        const totalPris = currentStock.price * antall;

        if (isNaN(antall) || antall <= 0) {
            alert("Ugyldig antall.");
            return;
        }

        if (antall > currentStock.owned) {
            alert("Du kan ikkje selga aksjar du ikkje eig.");
            return;
        }

        player.saldo += totalPris;
        stock[stockID].owned -= antall;
        stock[stockID].available += antall;

        updateGUI();
    }

    function buyStock(stockID) {
        if (!game.open) {
            alert("Børsen er stengt. Du kan ikkje kjøpa aksjar nå.");
            return;
        }
        const currentStock = stock[stockID];
        const antall = parseInt(prompt("Kor mange aksjar vil du kjøpa?"));

        const totalPris = currentStock.price * antall;

        if (isNaN(antall) || antall <= 0) {
            alert("Ugyldig antall.");
            return;
        }

        if (antall > currentStock.available) {
            alert("Det er ikke nok aksjer tilgjengelig.");
            return;
        }

        if (totalPris > player.saldo) {
            alert("Du har ikkje nok peng for å kjøpe aksjane.")
            return;
        }

        // Kjøp er godkjent

        player.saldo -= totalPris;
        stock[stockID].owned += antall;
        stock[stockID].available -= antall;

        updateGUI();
    }

    // Kode som blir kjørt ved ny day
    function runNewDay() {
        runNPCTransactions();

        // Kalkuler nye priser på aksjer

        // update days and year (for simplicity, we just add 7 days)

        game.date = new Date(game.date.getTime() + 60 * 60 * 24 * 1000);

        if (game.date.getDay() === 0 || game.date.getDay() === 6) {
            game.open = false;
            // Weekend - no price changes
        } else {
            game.open = true;
            calculateNewPrices();
        }

        // update the GUI
        updateGUI();
    }

    function runNPCTransactions() {
        if (!game.open) return; // No transactions if market is closed
        for (const npc of NPCBuyers) {
            const action = Math.random() < 0.5 ? "buy" : "sell";
            const stockID = Math.floor(Math.random() * stock.length);
            const currentStock = stock[stockID];
            const antall = Math.floor(Math.random() * 10) + 1; // Random between 1 and 10

            if (action === "buy") {
                const totalPris = currentStock.price * antall;
                if (antall > currentStock.available || totalPris > npc.saldo) {
                    continue; // Skip if not enough stock or saldo
                }
                npc.saldo -= totalPris;
                currentStock.npcOwned[npc.navn] = (currentStock.npcOwned[npc.navn] || 0) + antall;
                currentStock.available -= antall;
            } else {
                const owned = currentStock.npcOwned[npc.navn] || 0;
                if (antall > owned) {
                    continue; // Skip if NPC doesn't own enough to sell
                }
                npc.saldo += currentStock.price * antall;
                currentStock.npcOwned[npc.navn] -= antall;
                currentStock.available += antall;
            }
        }
    }

    function formatMoney(amount) {
        return amount.toLocaleString('no-NO', { style: 'currency', currency: 'NOK' });
    }

    function calculateNewPrices() {
        for (let i = 0; i < stock.length; i++) {
            if (stock[i].price === 0) continue;

            // -30% til +30%
            const percentChange = (Math.random() * 0.6) - 0.3;

            stock[i].price = stock[i].price * (1 + percentChange);

            // Rund til 2 desimalar
            stock[i].price = Math.round(stock[i].price * 100) / 100;

            // Konkurs
            if (stock[i].price < 1) {
                stock[i].price = 0;
                stock[i].available = 0;
                stock[i].owned = 0;
            }
        }
    }

    function updateGUI() {
        document.getElementById("day").textContent = daysOfWeek[game.date.getDay()];
        document.getElementById("date").textContent =
            `${game.date.getDate()}/${game.date.getMonth() + 1}/${game.date.getFullYear()}`;

        const openEl = document.getElementById("open");
        openEl.textContent = game.open ? "Open" : "Stengt";
        openEl.classList.toggle("text-bg-success", game.open);
        openEl.classList.toggle("text-bg-danger", !game.open);

        document.getElementById("balance").textContent = formatMoney(player.saldo);

        redrawStockList();
        updateLeaderboard();
    }

    function redrawStockList() {
        const tbody = document.getElementById("stockBody");
        tbody.innerHTML = "";

        for (let i = 0; i < stock.length; i++) {
            const s = stock[i];

            if (s.price === 0) {
                tbody.innerHTML += `
        <tr class="table-danger">
          <td><span class="fw-semibold">${s.navn}</span></td>
          <td class="text-end">Konkurs</td>
          <td class="text-end">0</td>
          <td class="text-end">0</td>
          <td class="text-end">
            <span class="badge text-bg-secondary">Ingen handel</span>
          </td>
        </tr>
      `;
                continue;
            }

            const actions = game.open
                ? `
        <button class="btn btn-sm btn-outline-primary" id="kjop" data-stock="${i}">Kjøp</button>
        <button class="btn btn-sm btn-outline-danger ms-2" id="selg" data-stock="${i}">Selg</button>
      `
                : `<span class="badge text-bg-secondary">Stengt</span>`;

            tbody.innerHTML += `
      <tr>
        <td class="fw-semibold">${s.navn}</td>
        <td class="text-end">${formatMoney(s.price)}</td>
        <td class="text-end">${s.owned}</td>
        <td class="text-end">${s.available}</td>
        <td class="text-end">${actions}</td>
      </tr>
    `;
        }
    }

    function updateLeaderboard() {
        const tbody = document.getElementById("leaderboardBody");
        tbody.innerHTML = "";

        const allPlayers = [];

        // Player total
        let playerStockValue = 0;
        for (const s of stock) playerStockValue += s.owned * s.price;

        allPlayers.push({
            navn: player.navn,
            saldo: player.saldo,
            stockValue: playerStockValue,
            total: player.saldo + playerStockValue
        });

        // NPC totals
        for (const npc of NPCBuyers) {
            let npcStockValue = 0;
            for (const s of stock) {
                const owned = s.npcOwned[npc.navn] || 0;
                npcStockValue += owned * s.price;
            }

            allPlayers.push({
                navn: npc.navn,
                saldo: npc.saldo,
                stockValue: npcStockValue,
                total: npc.saldo + npcStockValue
            });
        }

        allPlayers.sort((a, b) => b.total - a.total);

        for (let i = 0; i < allPlayers.length; i++) {
            const p = allPlayers[i];
            const rank = i + 1;

            const medal =
                rank === 1 ? "🥇" :
                    rank === 2 ? "🥈" :
                        rank === 3 ? "🥉" : "";

            tbody.innerHTML += `
      <tr>
        <td class="text-muted">${rank}${medal ? " " + medal : ""}</td>
        <td class="fw-semibold">${p.navn}</td>
        <td class="text-end">${formatMoney(p.saldo)}</td>
        <td class="text-end">${formatMoney(p.stockValue)}</td>
        <td class="text-end fw-bold">${formatMoney(p.total)}</td>
      </tr>
    `;
        }
    }
    updateGUI();
})();
