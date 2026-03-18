// Mål-dato: 8. april 2026 kl. 14:45 CET
const targetDate = new Date("2026-04-08T14:45:00+01:00").getTime(); // Standard
// const targetDate = new Date("2025-10-28T18:11:00+01:00").getTime(); // Test
const countdownElement = document.getElementById("nedtelling");

function oppdaterNedtelling() {
    const now = new Date().getTime();
    const diff = targetDate - now;

    if (diff <= 0) {
        countdownElement.textContent = "Startar no!";
        clearInterval(interval);
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    const parts = [];

    if (days > 0) {
        parts.push(`${days} dag${days === 1 ? '' : 'ar'}`);
    }

    if (hours > 0) {
        parts.push(`${hours} time${hours === 1 ? '' : 'r'}`);
    }

    if (minutes > 0) {
        parts.push(`${minutes} minutt`);
    }

    // Vis alltid sekund om alt anna er 0, eller viss du vil alltid ha dei med:
    if (seconds > 0 || parts.length === 0) {
        parts.push(`${seconds} sekund`);
    }

    // Set saman tekst
    countdownElement.textContent = parts.join(', ').replace(/, ([^,]*)$/, ' og $1');
}

const interval = setInterval(oppdaterNedtelling, 1000);
oppdaterNedtelling(); // køyr ein gong med ein gong sidan setInterval ventar 1 sekund først


