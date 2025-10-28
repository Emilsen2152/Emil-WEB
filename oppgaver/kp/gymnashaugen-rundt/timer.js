// Mål-dato: 8. april 2026 kl. 14:00 CET
const targetDate = new Date("2026-04-08T14:00:00+01:00").getTime();
const countdownElement = document.getElementById("nedtelling");
const countdownHeadElement = document.getElementById("nedtelling-head");

function oppdaterNedtelling() {
    const now = new Date().getTime();
    const diff = targetDate - now;

    if (diff <= 0) {
        countdownElement.textContent = "Startar no!";
        countdownHeadElement.textContent = "Nedtellinga er over!";
        clearInterval(interval);
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    countdownElement.textContent = `${days} dagar, ${hours} timar, ${minutes} minutt og ${seconds} sekund`;
    if (days > 0) {
        countdownHeadElement.textContent = `Startar om ${days} dagar`;
    } else if (seconds > 0) {
        countdownHeadElement.textContent = `${hours}:${minutes}:${seconds} til start`;
    }
}

const interval = setInterval(oppdaterNedtelling, 1000);
oppdaterNedtelling(); // køyr ein gong med ein gong sidan setInterval ventar 1 sekund først


