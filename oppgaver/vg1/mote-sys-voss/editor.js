const allForms = document.querySelectorAll('.update-form');

allForms.forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // FIKS: Hent navnet fra det skjulte input-feltet i stedet for tittelen
        const nameInput = form.querySelector('input[name="name"]');
        const dataName = nameInput ? nameInput.value : '';
        
        const valueInput = form.querySelector('input[name="value"]');
        const newValue = valueInput.value.trim();

        try {
            const response = await fetch(`./api/data/${encodeURIComponent(dataName)}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ value: newValue })
            });

            const result = await response.json();

            if (result.status === 200) {
                form.reset();
                // Oppdater kortene umiddelbart etter endring
                updateCards();
            } else {
                alert('Feil ved oppdatering: ' + (result.message || 'Ukjent feil'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('En teknisk feil oppstod.');
        }
    });
});

async function updateCards() {
    try {
        const response = await fetch("api/data");
        const jsonData = await response.json();

        if (jsonData.status !== 200) {
            console.error('Kunne ikke hente data:', jsonData);
            return;
        }

        const dataMap = {};
        jsonData.data.forEach(item => {
            dataMap[item.data_name] = item.data_value;
        });

        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            const titleElement = card.querySelector('.card-title');
            if (titleElement) {
                const dataName = titleElement.textContent.trim();
                const value = dataMap[dataName];
                
                if (value !== undefined) {
                    const valueElement = card.querySelector('.card-text strong');
                    if (valueElement) {
                        valueElement.textContent = value;
                    }
                }
            }
        });
    } catch (err) {
        console.error("Nettverksfeil ved oppdatering:", err);
    }
}

// Deretter hvert 5. sekund
setInterval(updateCards, 5000);