const API_BASE = "https://emil.elevweb.no/pingpanik/timerboard-open";
const resultsList = document.getElementById("results-list");

const scrollSpeed = 1; // px per frame
const pauseTime = 5000; // 5 sekund pause
let scrollingDown = true;
let scrollPos = 0;
let maxScroll = 0;
let autoScrollRunning = false;

const autoScroll = new URLSearchParams(window.location.search).get('scroll');

if (autoScroll === 'true') {
    resultsList.classList.add("scroll-on");
}

// Hjelpefunksjon: sjekk om scrollen er nær bunnen
function isNearBottom(element, threshold = 50) {
    return element.scrollHeight - element.scrollTop - element.clientHeight < threshold;
}

// Funksjon for å hente og vise resultatlista
async function loadEntries() {
    try {
        const res = await fetch(API_BASE);
        const data = await res.json();

        if (!res.ok) {
            console.error("Feil ved henting:", data.message || data);
            return;
        }

        updateEntries(data.entries);

        // Start autoscroll etter første lasting av data
        if (!autoScrollRunning && autoScroll === 'true') {
            autoScrollRunning = true;
            startAutoScroll();
        }
    } catch (err) {
        console.error("Feil ved henting:", err);
    }
}

// Oppdater eksisterende liste uten å tømme scroll helt
function updateEntries(entries) {
    const header = resultsList.querySelector(".result-header");

    const nearBottom = isNearBottom(resultsList);

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

        if (index === 0) div.classList.add("first-place");
        if (index === 1) div.classList.add("second-place");
        if (index === 2) div.classList.add("third-place");

        div.innerHTML = `
            <span class="result-rank">${index + 1}.</span>
            <span class="result-name">${entry.name}</span>
            <span class="result-time">${entry.time}</span>
        `;
        resultsList.appendChild(div);
    });

    maxScroll = resultsList.scrollHeight - resultsList.clientHeight;

    if (nearBottom) {
        resultsList.scrollTo({ top: resultsList.scrollHeight, behavior: "smooth" });
    }
}

// ------------------------------
// AUTOSCROLL MED PAUSE PÅ TOPP OG BUNN
// ------------------------------
function startAutoScroll() {
    let pausing = true; // start pause på toppen
    scrollPos = 0;
    scrollingDown = true;

    function step() {
        if (pausing) return;

        if (scrollingDown) {
            scrollPos += scrollSpeed;
            if (scrollPos >= maxScroll) {
                scrollPos = maxScroll;
                scrollingDown = false;
                pausing = true;
                setTimeout(() => { pausing = false; requestAnimationFrame(step); }, pauseTime);
                return;
            }
        } else {
            // Scroll opp raskt: her bruker vi større hastighet, eks. 8 px/frame
            scrollPos -= scrollSpeed * 8; 
            if (scrollPos <= 0) {
                scrollPos = 0;
                scrollingDown = true;
                pausing = true;
                setTimeout(() => { pausing = false; requestAnimationFrame(step); }, pauseTime);
                return;
            }
        }

        resultsList.scrollTop = scrollPos;
        requestAnimationFrame(step);
    }

    // start pause på toppen
    setTimeout(() => { pausing = false; requestAnimationFrame(step); }, pauseTime);
}

// ------------------------------
// INITIALISERING
// ------------------------------
loadEntries();
setInterval(loadEntries, 15000);
