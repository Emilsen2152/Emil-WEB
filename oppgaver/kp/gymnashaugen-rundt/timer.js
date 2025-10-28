// Mål-dato: 8. april 2026 kl. 14:00 CET
const targetDate = new Date("2026-04-08T14:00:00+01:00").getTime();
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

    countdownElement.textContent = `${days} dagar, ${hours} timar, ${minutes} minutt og ${seconds} sekund`;
}

const interval = setInterval(oppdaterNedtelling, 1000);
oppdaterNedtelling(); // køyr ein gong med ein gong sidan setInterval ventar 1 sekund først


