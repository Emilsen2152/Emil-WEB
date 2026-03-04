(function () {
    'use strict';

    /* ============================================================
       Utils
       ============================================================ */

    const randInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
    const randPick = (arr) => arr[Math.floor(Math.random() * arr.length)];
    const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
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

    const plannedTransactions = []; // player (exec on Monday)
    const plannedNPCTransactionsByNPC = new Map(); // npcName -> Array<{action, stockID, antall}>

    // Weekend price changes (accumulate while market is closed)
    // Stored as: [{dateISO, changes: Map(stockID -> percentChange)}]
    const plannedPriceChanges = [];

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

    // aksjar
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

    // track baseline + momentum
    for (const s of stock) {
        s.originalPrice = s.price;
        s.prevPrice = s.price;
        s.lastChangePct = 0; // last applied change (decimal, ex: 0.05)
    }

    const game = {
        date: new Date(2026, 0, 1),
        open: true
    };

    /* ============================================================
       NPC generation (smarter profiles)
       ============================================================ */

    const NPCBuyers = [];
    const usedNames = new Set([player.navn]);

    function makeNpcProfile() {
        const risk = Math.random();                 // 0..1 (higher = more aggressive)
        const patience = Math.random();             // 0..1 (higher = holds longer)
        const diversification = clamp(0.3 + Math.random() * 0.7, 0.3, 1); // higher = more spread
        const favCategory = randPick([0, 1, 2, 3]); // 0 diverse, 1 transport, 2 olje, 3 tech
        return { risk, patience, diversification, favCategory };
    }

    while (NPCBuyers.length < 99) {
        const navn = `${randPick(fornavnListe)} ${randPick(etternavnListe)}`;
        if (usedNames.has(navn)) continue;
        usedNames.add(navn);

        NPCBuyers.push({
            navn,
            saldo: randInt(5_000, 55_000),
            profile: makeNpcProfile(),
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

            if (actorType === 'player') s.owned += antall;
            else setNpcOwned(s, actor.navn, getNpcOwned(s, actor.navn) + antall);

            return { ok: true };
        }

        // sell
        const ownedQty = actorType === 'player' ? s.owned : getNpcOwned(s, actor.navn);
        if (antall > ownedQty) return { ok: false, reason: 'not_owned' };

        actor.saldo += total;
        s.available += antall;

        if (actorType === 'player') s.owned -= antall;
        else setNpcOwned(s, actor.navn, ownedQty - antall);

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
            alert("Børsen er stengt. Du kan ikkje kjøpa aksjar no.");
            if (confirm("Vil du planlegga kjøp/salg for neste virkedag?")) planPlayerTrade('buy', stockID);
            return;
        }

        const antall = promptAmount("Kor mange aksjar vil du kjøpa?");
        if (!antall) return alert("Ugyldig antall.");

        const res = trade({ actorType: 'player', actor: player, stockID, action: 'buy', antall });
        if (!res.ok) {
            if (res.reason === 'not_available') alert("Det er ikkje nok aksjar tilgjengeleg.");
            else if (res.reason === 'no_funds') alert("Du har ikkje nok peng for å kjøpa aksjane.");
            else alert("Kunne ikkje gjennomføra kjøp.");
            return;
        }
        updateGUI();
    }

    function sellStock(stockID) {
        if (!game.open) {
            alert("Børsen er stengt. Du kan ikkje selga aksjar no.");
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
       Price changes (weekday immediate, weekend planned)
       ============================================================ */

    function isWeekend(d) {
        const day = d.getDay();
        return day === 0 || day === 6;
    }

    function generateDailyPercentChange(s) {
        // base -30%..+30%
        let pct = (Math.random() * 0.6) - 0.3;

        // tiny bias: if price is far above original -> slight pull down; far below -> slight pull up
        const valuation = (s.price - s.originalPrice) / s.originalPrice; // +/- %
        pct += clamp(-valuation * 0.05, -0.05, 0.05);

        return clamp(pct, -0.35, 0.35);
    }

    function applyPercentChangeToStock(s, pct) {
        if (s.price === 0) return;

        s.prevPrice = s.price;

        let newPrice = s.price * (1 + pct);
        newPrice = Math.round(newPrice * 100) / 100;

        s.lastChangePct = pct;

        if (newPrice < 1) {
            s.price = 0;
            s.available = 0;
            s.owned = 0;
            s.npcOwned.clear();
            return;
        }

        s.price = newPrice;
    }

    function planWeekendPriceChangesForToday() {
        const dateISO = game.date.toISOString().slice(0, 10);

        const changes = new Map();
        for (let i = 0; i < stock.length; i++) {
            const s = stock[i];
            if (s.price === 0) continue;
            const pct = generateDailyPercentChange(s);
            changes.set(i, pct);
        }

        plannedPriceChanges.push({ dateISO, changes });
    }

    function applyPlannedPriceChanges() {
        if (plannedPriceChanges.length === 0) return;

        // Apply in order (compounding)
        for (const entry of plannedPriceChanges) {
            for (const [stockID, pct] of entry.changes.entries()) {
                const s = stock[stockID];
                if (!s) continue;
                applyPercentChangeToStock(s, pct);
            }
        }

        plannedPriceChanges.length = 0;
    }

    function calculateNewPricesToday() {
        for (let i = 0; i < stock.length; i++) {
            const s = stock[i];
            if (s.price === 0) continue;
            const pct = generateDailyPercentChange(s);
            applyPercentChangeToStock(s, pct);
        }
    }

    /* ============================================================
       Smarter NPC trading
       ============================================================ */

    function npcNetWorth(npc) {
        let value = npc.saldo;
        for (const s of stock) {
            const owned = getNpcOwned(s, npc.navn);
            if (owned) value += owned * s.price;
        }
        return value;
    }

    function scoreStockForBuy(npc, s) {
        const p = npc.profile;

        // Momentum: prefer going up (especially for risk-seekers)
        const momentum = s.prevPrice > 0 ? (s.price - s.prevPrice) / s.prevPrice : 0;

        // Valuation: if too high vs original, risk-averse avoids it; risk-loving doesn't care as much
        const valuation = (s.price - s.originalPrice) / s.originalPrice;

        // Category preference boost
        const catBoost = (p.favCategory === s.category) ? 0.10 : (p.favCategory === 0 ? 0.03 : 0);

        // Risk affects momentum preference + tolerance for overvaluation
        const score =
            (momentum * (0.6 + p.risk * 0.9)) +
            (catBoost) +
            ((-valuation) * (0.15 + (1 - p.risk) * 0.35)); // mean reversion factor stronger for low risk

        return score;
    }

    function scoreStockForSell(npc, s, ownedQty) {
        const p = npc.profile;

        const momentum = s.prevPrice > 0 ? (s.price - s.prevPrice) / s.prevPrice : 0;
        const valuation = (s.price - s.originalPrice) / s.originalPrice;

        // sell losers quicker if low patience
        const loserPenalty = (-momentum) * (0.5 + (1 - p.patience) * 0.8);

        // take profit if highly overvalued (especially for low risk)
        const profitTake = valuation * (0.25 + (1 - p.risk) * 0.6);

        // if they don't own much, selling is less likely
        const sizeFactor = clamp(ownedQty / 20, 0.2, 1);

        return (loserPenalty + profitTake) * sizeFactor;
    }

    function pickBestStockIndexByScore(scores) {
        // pick argmax, but require it to be positive-ish
        let bestI = -1;
        let best = -Infinity;
        for (let i = 0; i < scores.length; i++) {
            if (scores[i] > best) {
                best = scores[i];
                bestI = i;
            }
        }
        return { bestI, best };
    }

    function npcDecideTrades(npc) {
        const p = npc.profile;
        const trades = [];

        const worth = npcNetWorth(npc);
        const maxPositions = Math.round(3 + p.diversification * 7); // 3..10
        const maxPositionValue = worth * (0.15 + p.risk * 0.25); // 15%..40%

        // Decide how many actions today
        const actionsToday = 1 + (Math.random() < p.risk ? 1 : 0) + (Math.random() < (p.risk * 0.3) ? 1 : 0);
        const actionCount = clamp(actionsToday, 1, 3);

        for (let k = 0; k < actionCount; k++) {
            // 1) Consider sell first if portfolio heavy / bad momentum
            const ownedStocks = [];
            for (let i = 0; i < stock.length; i++) {
                const s = stock[i];
                const ownedQty = getNpcOwned(s, npc.navn);
                if (ownedQty > 0 && s.price > 0) ownedStocks.push({ i, s, ownedQty });
            }

            if (ownedStocks.length > 0 && (ownedStocks.length > maxPositions || Math.random() < 0.35)) {
                const sellScores = ownedStocks.map(o => scoreStockForSell(npc, o.s, o.ownedQty));
                const bestSellIdx = sellScores.indexOf(Math.max(...sellScores));
                const pick = ownedStocks[bestSellIdx];

                // sell if meaningful score or forced by too many positions
                if (sellScores[bestSellIdx] > 0.02 || ownedStocks.length > maxPositions) {
                    const ownedValue = pick.ownedQty * pick.s.price;
                    const targetSellValue = clamp(ownedValue * (0.25 + Math.random() * 0.5), pick.s.price, ownedValue);
                    const antall = clamp(Math.floor(targetSellValue / pick.s.price), 1, pick.ownedQty);

                    trades.push({ action: 'sell', stockID: pick.i, antall });
                    continue;
                }
            }

            // 2) Buy decision
            const buyScores = stock.map((s) => (s.price === 0 || s.available <= 0) ? -999 : scoreStockForBuy(npc, s));
            const { bestI, best } = pickBestStockIndexByScore(buyScores);
            if (bestI === -1 || best < 0.01) continue;

            const s = stock[bestI];

            // ensure not exceeding max position value
            const ownedQty = getNpcOwned(s, npc.navn);
            const currentValue = ownedQty * s.price;
            if (currentValue >= maxPositionValue && Math.random() > p.risk) continue;

            // spend budget: riskier NPC spends more
            const budget = npc.saldo * (0.10 + p.risk * 0.25); // 10%..35%
            const maxAffordable = Math.floor(budget / s.price);
            const maxBySupply = Math.min(maxAffordable, s.available);

            if (maxBySupply <= 0) continue;

            // antall: small for low risk, bigger for high risk
            const antall = clamp(randInt(1, 10 + Math.floor(p.risk * 20)), 1, maxBySupply);

            trades.push({ action: 'buy', stockID: bestI, antall });
        }

        return trades;
    }

    function queueNpcTrades(npc, trades) {
        const arr = plannedNPCTransactionsByNPC.get(npc.navn) || [];
        for (const t of trades) arr.push(t);
        plannedNPCTransactionsByNPC.set(npc.navn, arr);
    }

    function runNPCTransactions() {
        for (const npc of NPCBuyers) {
            if (!game.open) {
                // Market closed: NPCs plan smarter trades (often 1-2)
                const planned = npcDecideTrades(npc);
                if (planned.length) queueNpcTrades(npc, planned);
                continue;
            }

            // Market open: execute any planned trades first
            const planned = plannedNPCTransactionsByNPC.get(npc.navn);
            if (planned && planned.length) {
                for (const t of planned) {
                    trade({ actorType: 'npc', actor: npc, stockID: t.stockID, action: t.action, antall: t.antall });
                }
                plannedNPCTransactionsByNPC.delete(npc.navn);
            }

            // Then do today's live smarter trades
            const todays = npcDecideTrades(npc);
            for (const t of todays) {
                trade({ actorType: 'npc', actor: npc, stockID: t.stockID, action: t.action, antall: t.antall });
            }
        }
    }

    /* ============================================================
       Day progression (NOW: prices advance even on weekend; applied Monday)
       ============================================================ */

    function executePlannedPlayerTradesIfMonday() {
        if (game.date.getDay() !== 1) return;

        alert("Ny veka! Børsen er no åpen, planlagte transaksjoner for denne veka blir no gjennomført.");

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
                    `Planlagt ${t.type === 'buy' ? 'kjøp' : 'salg'} av ${t.antall} aksjar i ${s?.navn ?? '(ukjent)'} kunne ikkje gjennomføres.`
                );
            }
        }
        plannedTransactions.length = 0;
    }

    function runNewDay() {
        // advance day first
        game.date = new Date(game.date.getTime() + 24 * 60 * 60 * 1000);

        // determine open/closed
        game.open = !isWeekend(game.date);

        if (!game.open) {
            // Market closed: PLAN price changes instead of applying immediately
            planWeekendPriceChangesForToday();
        } else {
            // Market open:
            // 1) apply all weekend planned changes (compounded)
            applyPlannedPriceChanges();
            // 2) apply today's normal change
            calculateNewPricesToday();
            // 3) execute player planned trades if Monday
            executePlannedPlayerTradesIfMonday();
        }

        // NPCs plan/execute depending on market open
        runNPCTransactions();

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
            const actionLabelSell = game.open ? "Selg" : "Planlegg salg";
            const buttonStyle = game.open ? '' : '-outline';

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