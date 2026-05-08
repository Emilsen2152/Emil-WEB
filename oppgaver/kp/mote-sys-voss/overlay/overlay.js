/**
 * Oppdaterer tekst og styrer synlegheit basert på om felta er tomme
 */
async function updateText() {
    try {
        const response = await fetch('../api/data');
        const result = await response.json();

        if (!result.success || !result.data) return;

        // Vi brukar eit Set for å halde styr på kva kontainerar vi har sjekka
        const containersToProcess = new Set();

        result.data.forEach(row => {
            const element = document.getElementById(row.data_name);
            if (element) {
                const newValue = row.data_value.trim();
                
                if (element.innerText !== newValue) {
                    element.innerText = newValue;
                }
                
                // Finn forelder-kontaineren (.sak-info eller .nameplate)
                const container = element.closest('.background');
                if (container) {
                    containersToProcess.add(container);
                }
            }
        });

        // Etter at all tekst er oppdatert, sjekkar vi synlegheit for kvar kontainer
        containersToProcess.forEach(container => {
            checkContainerVisibility(container);
        });

    } catch (error) {
        console.error("Feil ved oppdatering:", error);
    }
}

/**
 * Sjekkar om ein kontainer har noko innhald i det heile teke.
 * Viss ALLE element med tekst inne i kontaineren er tomme, skjulast den.
 */
function checkContainerVisibility(container) {
    // Finn alle div-ar eller p-ar inne i kontaineren som skal innehalde tekst
    // Juster selectorane her viss du har andre klassenamn
    const textElements = container.querySelectorAll('.speaker-name, .speaker-group, .sak-tittel, .sak-innhold');
    
    // Sjekk om i det minste éitt av elementa har tekst
    const hasContent = Array.from(textElements).some(el => el.innerText.trim() !== "");

    if (hasContent) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

// Intervall og oppstart
setInterval(updateText, 5000); // Oppdater kvar 5. sekund
document.addEventListener('DOMContentLoaded', updateText);