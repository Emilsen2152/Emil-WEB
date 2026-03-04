(function () {
    'use strict';

    /* ============================================================
       Utils
       ============================================================ */

    const randInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
    const randPick = (arr) => arr[Math.floor(Math.random() * arr.length)];
    const formatMoney = (amount) =>
        amount.toLocaleString('no-NO', { style: 'currency', currency: 'NOK' });

    const daysOfWeek = ["Søndag", "Mandag", "Tirsdag", "Onsdag", "Torsdag", "Fredag", "Lørdag"];

    const $ = (id) => document.getElementById(id);

    /* ============================================================
       Game state
       ============================================================ */

    const player = {
        navn: prompt("Hva er navnet ditt?") || "Deg",
        saldo: 25_000,
    };

    const plannedTransactions = []; // for player (exec on Monday)
    const plannedNPCTransactionsByNPC = new Map(); // npcName -> Array<{action, stockID, antall}>

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

    // Aksjer
    const stock = [
        { navn: "Statoil", price: 1000, available: 500, owned: 0, npcOwned: new Map(), category: 2 },
        { navn: "Telenor", price: 100, available: 400, owned: 0, npcOwned: new Map(), category: 3 },
        { navn: "Tine", price: 45.5, available: 250, owned: 0, npcOwned: new Map(), category: 0 },
        { navn: "PingPanik", price: 15, available: 100000, owned: 0, npcOwned: new Map(), category: 3 },
        { navn: "Apple", price: 3299, available: 2300, owned: 0, npcOwned: new Map(), category: 3 },
        { navn: "Klofta Taxi", price: 60, available: 200, owned: 0, npcOwned: new Map(), category: 1 },
        { navn: "Oslo Buss", price: 120, available: 300, owned: 0, npcOwned: new Map(), category: 1 },
        { navn: "Oslo Lufthavn", price: 250, available: 150, owned: 0, npcOwned: new Map(), category: 1 },
        { navn: "Norsk Sjømat", price: 80, available: 400, owned: 0, npcOwned: new Map(), category: 0 },
        { navn: "Boombox", price: 220, available: 350, owned: 0, npcOwned: new Map(), category: 0 },
        { navn: "Voss Kommunikasjon", price: 90, available: 300, owned: 0, npcOwned: new Map(), category: 3 },
    ];

    const game = {
        date: new Date(2026, 0, 1),
        open: true
    };

    /* ============================================================
       NPC generation
       ============================================================ */

    const NPCBuyers = [];
    const usedNames = new Set([player.navn]);

    while (NPCBuyers.length < 99) {
        const navn = `${randPick(fornavnListe)} ${randPick(etternavnListe)}`;
        if (usedNames.has(navn)) continue;
        usedNames.add(navn);

        NPCBuyers.push({
            navn,
            saldo: randInt(5_000, 55_000),
        });
    }

    /* ============================================================
       Trading core
       ============================================================ */

    function getNpcOwned(s, npcName) {
        return s.npcOwned.get(npcName) || 0;
    }

    function setNpcOwned(s, npcName, qty) {
        if (qty <= 0) s.npcOwned.delete(npcName);
        else s.npcOwned.set(npcName, qty);
    }

    /**
     * Executes a trade if possible.
     * actorType: "player" | "npc"
     * action: "buy" | "sell"
     */
    function trade({ actorType, actor, stockID, action, antall }) {
        const s = stock[stockID];
        if (!s || s.price === 0) return { ok: false, reason: 'invalid_stock' };

        if (!Number.isFinite(antall) || antall <= 0) return { ok: false, reason: 'invalid_amount' };

        const total = s.price * antall;

        if (action === 'buy') {
            if (antall > s.available) return { ok: false, reason: 'not_available' };
            if (total > actor.saldo) return { ok: false, reason: 'no_funds' };

            actor.saldo -= total;
            s.available -= antall;

            if (actorType === 'player') {
                s.owned += antall;
            } else {
                setNpcOwned(s, actor.navn, getNpcOwned(s, actor.navn) + antall);
            }
            return { ok: true };
        }

        // sell
        const ownedQty = actorType === 'player' ? s.owned : getNpcOwned(s, actor.navn);
        if (antall > ownedQty) return { ok: false, reason: 'not_owned' };

        actor.saldo += total;
        s.available += antall;

        if (actorType === 'player') {
            s.owned -= antall;
        } else {
            setNpcOwned(s, actor.navn, ownedQty - antall);
        }
        return { ok: true };
    }

    function promptAmount(message) {
        const antall = parseInt(prompt(message) || '', 10);
        if (!Number.isFinite(antall) || antall <= 0) return null;
        return antall;
    }

    function planPlayerTrade(action, stockID) {
        const antall = promptAmount(`Kor mange aksjar vil du ${action === 'buy' ? 'kjøpa' : 'selga'}?`);
        if (!antall) return alert("Ugyldig antall.");

        plannedTransactions.push({ type: action, stockID, antall });
        alert(`Planlagt ${action === 'buy' ? 'kjøp' : 'salg'} lagt til for neste virkedag.`);
    }

    function buyStock(stockID) {
        if (!game.open) {
            alert("Børsen er stengt. Du kan ikkje kjøpa aksjar nå.");
            if (confirm("Vil du planlegga kjøp/salg for neste virkedag?")) planPlayerTrade('buy', stockID);
            return;
        }

        const antall = promptAmount("Kor mange aksjar vil du kjøpa?");
        if (!antall) return alert("Ugyldig antall.");

        const res = trade({ actorType: 'player', actor: player, stockID, action: 'buy', antall });
        if (!res.ok) {
            if (res.reason === 'not_available') alert("Det er ikke nok aksjer tilgjengelig.");
            else if (res.reason === 'no_funds') alert("Du har ikkje nok peng for å kjøpe aksjane.");
            else alert("Kunne ikkje gjennomføra kjøp.");
            return;
        }
        updateGUI();
    }

    function sellStock(stockID) {
        if (!game.open) {
            alert("Børsen er stengt. Du kan ikkje selga aksjar nå.");
            if (confirm("Vil du planlegga kjøp/salg for neste virkedag?")) planPlayerTrade('sell', stockID);
            return;
        }

        const antall = promptAmount("Kor mange aksjar vil du selga?");
        if (!antall) return alert("Ugyldig antall.");

        const res = trade({ actorType: 'player', actor: player, stockID, action: 'sell', antall });
        if (!res.ok) {
            if (res.reason === 'not_owned') alert("Du kan ikkje selga aksjar du ikkje eig.");
            else alert("Kunne ikkje gjennomføra salg.");
            return;
        }
        updateGUI();
    }

    /* ============================================================
       Day progression
       ============================================================ */

    function isWeekend(d) {
        const day = d.getDay();
        return day === 0 || day === 6;
    }

    function calculateNewPrices() {
        for (const s of stock) {
            if (s.price === 0) continue;

            const percentChange = (Math.random() * 0.6) - 0.3; // -30% .. +30%
            let newPrice = s.price * (1 + percentChange);

            newPrice = Math.round(newPrice * 100) / 100;

            if (newPrice < 1) {
                s.price = 0;
                s.available = 0;
                s.owned = 0;
                s.npcOwned.clear();
            } else {
                s.price = newPrice;
            }
        }
    }

    function executePlannedPlayerTradesIfMonday() {
        if (game.date.getDay() !== 1) return;

        alert("Ny veka! Børsen er nå åpen, planlagte transaksjoner for denne veka blir nå gjennomført.");

        for (const t of plannedTransactions) {
            const res = trade({
                actorType: 'player',
                actor: player,
                stockID: t.stockID,
                action: t.type,
                antall: t.antall
            });

            if (!res.ok) {
                const s = stock[t.stockID];
                alert(
                    `Planlagt ${t.type === 'buy' ? 'kjøp' : 'salg'} av ${t.antall} aksjer i ${s?.navn ?? '(ukjent)'} kunne ikke gjennomføres.`
                );
            }
        }
        plannedTransactions.length = 0;
    }

    function planNpcTransactionsForClosedMarket() {
        // Plan one transaction per NPC (only on closed days)
        for (const npc of NPCBuyers) {
            const action = Math.random() < 0.5 ? "buy" : "sell";
            const stockID = randInt(0, stock.length - 1);
            const antall = randInt(1, 10);

            const arr = plannedNPCTransactionsByNPC.get(npc.navn) || [];
            arr.push({ action, stockID, antall });
            plannedNPCTransactionsByNPC.set(npc.navn, arr);
        }
    }

    function runNPCTransactions() {
        if (!game.open) {
            planNpcTransactionsForClosedMarket();
        }

        for (const npc of NPCBuyers) {
            // 1) execute planned (if any)
            const planned = plannedNPCTransactionsByNPC.get(npc.navn);
            if (planned && planned.length) {
                for (const t of planned) {
                    trade({ actorType: 'npc', actor: npc, stockID: t.stockID, action: t.action, antall: t.antall });
                }
                plannedNPCTransactionsByNPC.delete(npc.navn);
            }

            // 2) do one live transaction
            const action = Math.random() < 0.5 ? "buy" : "sell";
            const stockID = randInt(0, stock.length - 1);
            const antall = randInt(1, 10);

            trade({ actorType: 'npc', actor: npc, stockID, action, antall });
        }
    }

    function runNewDay() {
        runNPCTransactions();

        // next day
        game.date = new Date(game.date.getTime() + 24 * 60 * 60 * 1000);

        if (isWeekend(game.date)) {
            game.open = false;
        } else {
            game.open = true;
            calculateNewPrices();
        }

        if (game.open) executePlannedPlayerTradesIfMonday();

        updateGUI();
    }

    /* ============================================================
       UI rendering
       ============================================================ */

    function redrawStockList() {
        const tbody = $("stockBody");
        tbody.textContent = "";

        const frag = document.createDocumentFragment();

        for (let i = 0; i < stock.length; i++) {
            const s = stock[i];
            const tr = document.createElement("tr");

            if (s.price === 0) {
                tr.className = "table-danger";
                tr.innerHTML = `
          <td><span class="fw-semibold">${s.navn}</span></td>
          <td class="text-end">Konkurs</td>
          <td class="text-end">0</td>
          <td class="text-end">0</td>
          <td class="text-end"><span class="badge text-bg-secondary">Ingen handel</span></td>
        `;
                frag.appendChild(tr);
                continue;
            }

            const actionLabelBuy = game.open ? "Kjøp" : "Planlegg kjøp";
            const actionLabelSell = game.open ? "Selg" : "Planlegg selg";

            const buttonStyle = game.open ? '' : '-outline'

            tr.innerHTML = `
        <td class="fw-semibold">${s.navn}</td>
        <td class="text-end">${formatMoney(s.price)}</td>
        <td class="text-end">${s.owned}</td>
        <td class="text-end">${s.available}</td>
        <td class="text-end">
          <button class="btn btn-sm btn${buttonStyle}-primary" data-action="buy" data-stock="${i}">${actionLabelBuy}</button>
          <button class="btn btn-sm btn${buttonStyle}-danger ms-2" data-action="sell" data-stock="${i}">${actionLabelSell}</button>
        </td>
      `;
            frag.appendChild(tr);
        }

        tbody.appendChild(frag);
    }

    function updateLeaderboard() {
        const tbody = $("leaderboardBody");
        tbody.textContent = "";

        // Player value
        let playerStockValue = 0;
        for (const s of stock) playerStockValue += s.owned * s.price;

        const allPlayers = [{
            navn: player.navn,
            saldo: player.saldo,
            stockValue: playerStockValue,
            total: player.saldo + playerStockValue
        }];

        // NPC values
        for (const npc of NPCBuyers) {
            let npcStockValue = 0;
            for (const s of stock) {
                const owned = getNpcOwned(s, npc.navn);
                if (owned) npcStockValue += owned * s.price;
            }

            allPlayers.push({
                navn: npc.navn,
                saldo: npc.saldo,
                stockValue: npcStockValue,
                total: npc.saldo + npcStockValue
            });
        }

        allPlayers.sort((a, b) => b.total - a.total);

        const frag = document.createDocumentFragment();

        for (let i = 0; i < allPlayers.length; i++) {
            const p = allPlayers[i];
            const rank = i + 1;

            const medal =
                rank === 1 ? "🥇" :
                    rank === 2 ? "🥈" :
                        rank === 3 ? "🥉" : "";

            const tr = document.createElement("tr");
            tr.innerHTML = `
        <td class="text-muted">${rank}${medal ? " " + medal : ""}</td>
        <td class="fw-semibold">${p.navn}</td>
        <td class="text-end">${formatMoney(p.saldo)}</td>
        <td class="text-end">${formatMoney(p.stockValue)}</td>
        <td class="text-end fw-bold">${formatMoney(p.total)}</td>
      `;
            frag.appendChild(tr);
        }

        tbody.appendChild(frag);
    }

    function updateGUI() {
        $("day").textContent = daysOfWeek[game.date.getDay()];
        $("date").textContent = `${game.date.getDate()}/${game.date.getMonth() + 1}/${game.date.getFullYear()}`;

        const openEl = $("open");
        openEl.textContent = game.open ? "Open" : "Stengt";
        openEl.classList.toggle("text-bg-success", game.open);
        openEl.classList.toggle("text-bg-danger", !game.open);

        $("balance").textContent = formatMoney(player.saldo);

        redrawStockList();
        updateLeaderboard();
    }

    /* ============================================================
       Events
       ============================================================ */

    $("newDay")?.addEventListener("click", runNewDay);

    // Delegation: catches button clicks in stock list
    document.addEventListener("click", (event) => {
        const btn = event.target?.closest?.("button[data-action][data-stock]");
        if (!btn) return;

        const action = btn.getAttribute("data-action");
        const stockID = Number(btn.getAttribute("data-stock"));

        if (!Number.isInteger(stockID) || stockID < 0 || stockID >= stock.length) return;

        if (action === "buy") buyStock(stockID);
        else if (action === "sell") sellStock(stockID);
    });

    updateGUI();
})();