document.addEventListener('DOMContentLoaded', () => {
    const API_BASE = './api'; 

    const registrationForm = document.getElementById('registration-form');
    const formMessage = document.getElementById('form-message');
    const formHeader = document.getElementById('form-header');
    const pageTitle = document.getElementById('page-title');
    const submitBtn = document.getElementById('submit-btn');
    
    const activitySelects = [
        document.getElementById('activity1'),
        document.getElementById('activity2'),
        document.getElementById('activity3')
    ];

    // Hent ut hash frå form-taggen (satt av PHP)
    const currentHash = registrationForm ? registrationForm.dataset.hash : '';

    /* ============================================================
       1. Viss det finst ein hash: Hent data og endre til oppdateringsmodus
       ============================================================ */
    if (currentHash) {
        loadExistingRegistration(currentHash);
    }

    async function loadExistingRegistration(hash) {
        try {
            const response = await fetch(`${API_BASE}/registration-by-hash?hash=${encodeURIComponent(hash)}`);
            const result = await response.json();

            if (result.success && result.data) {
                const p = result.data.participant;
                const choices = result.data.choices; // Array med ID-ar f.eks. [2, 5, 1]

                // Oppdater tekst i GUI så eleven ser dei er i "endre-modus"
                if (pageTitle) pageTitle.textContent = "Temadag - Endre påmelding";
                if (formHeader) formHeader.innerHTML = `<h2 class="h5 mb-0 bg-warning text-dark p-2 rounded-top">Endre di registrering</h2>`;
                if (submitBtn) {
                    submitBtn.textContent = "Oppdater påmelding";
                    submitBtn.classList.replace('btn-primary', 'btn-warning');
                }

                // Fyll ut namn og e-post felta
                document.getElementById('name').value = p.name || '';
                document.getElementById('email').value = p.email || '';

                // Fyll ut dei tre nedtrekksmenyane basert på rekkefølga i arrayen
                choices.forEach((activityId, index) => {
                    if (activitySelects[index]) {
                        activitySelects[index].value = activityId;
                    }
                });

            } else {
                formMessage.innerHTML = `
                    <div class="alert alert-danger border-0 shadow-sm">
                        <strong>Feil:</strong> ${result.message || 'Kunne ikkje hente påmeldinga di. Lenka kan vera utgått.'}
                    </div>
                `;
            }
        } catch (error) {
            console.error('Kunne ikkje hente påmeldingsdata:', error);
        }
    }

    /* ============================================================
       2. Validering: Hindre doble val i sanntid
       ============================================================ */
    activitySelects.forEach(select => {
        if (select) {
            select.addEventListener('change', () => {
                const values = activitySelects
                    .map(s => s.value)
                    .filter(val => val !== "");

                const hasDuplicates = new Set(values).size !== values.length;

                if (hasDuplicates) {
                    formMessage.innerHTML = `
                        <div class="alert alert-warning py-2 small border-0 shadow-sm">
                            <strong>Obs!</strong> Du kan ikkje velge same aktivitet fleire gongar. Vennligst velg unike aktivitetar.
                        </div>
                    `;
                    select.value = ""; // Nullstill
                } else {
                    formMessage.innerHTML = "";
                }
            });
        }
    });

    /* ============================================================
       3. Send inn skjemaet (Enten som POST eller PUT)
       ============================================================ */
    if (registrationForm) {
        registrationForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Sjekk unike val
            const choices = activitySelects.map(s => s.value).filter(val => val !== "");
            if (choices.length !== 3 || (new Set(choices).size !== 3)) {
                formMessage.innerHTML = `
                    <div class="alert alert-danger border-0 shadow-sm">
                        <strong>Feil:</strong> Du må velge tre *forskjellige* aktivitetar.
                    </div>
                `;
                return;
            }

            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Lagrar...';

            // Generell payload-struktur som passar både POST og PUT i ditt API
            const payload = {
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                choices: choices.map(id => parseInt(id, 10)) // Sender flatt array: [id1, id2, id3]
            };

            let url = `${API_BASE}/register`;
            let httpMethod = 'POST';

            // Viss vi har ein hash, endrar vi endepunktet og HTTP-metoden til PUT
            if (currentHash) {
                payload.hash = currentHash;
                url = `${API_BASE}/registrations/update-registration`;
                httpMethod = 'PUT';
            }

            try {
                const response = await fetch(url, {
                    method: httpMethod,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    if (httpMethod === 'POST') {
                        // Viss fyrstegongs, gi dei den nye lenka deira så dei kan ta vare på den
                        const editUrl = window.location.origin + window.location.pathname + '?hash=' + result.data.hash;
                        formMessage.innerHTML = `
                            <div class="alert alert-success border-0 shadow-sm">
                                <strong>Suksess!</strong> Påmeldinga di er registrert.<br>
                                <small>Ta vare på denne lenka om du vil endre på vala dine seinare: 
                                <a href="${editUrl}" class="alert-link">${editUrl}</a></small>
                            </div>
                        `;
                        registrationForm.reset();
                    } else {
                        // Ved oppdatering
                        formMessage.innerHTML = `
                            <div class="alert alert-success border-0 shadow-sm">
                                <strong>Oppdatert!</strong> ${result.message || 'Utsjåande endringar har vorte lagra.'}
                            </div>
                        `;
                    }
                } else {
                    formMessage.innerHTML = `
                        <div class="alert alert-danger border-0 shadow-sm">
                            <strong>Feil:</strong> ${result.message || 'Kunne ikkje fullføre forespurnaden.'}
                        </div>
                    `;
                }

            } catch (error) {
                console.error('Nettverksfeil:', error);
                formMessage.innerHTML = `
                    <div class="alert alert-danger border-0 shadow-sm">
                        <strong>Nettverksfeil:</strong> Kunne ikkje opprette kontakt med API-et.
                    </div>
                `;
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        });
    }
});