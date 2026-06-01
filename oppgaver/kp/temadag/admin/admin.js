document.addEventListener('DOMContentLoaded', () => {
    // API-adresse relativt til kvar du står
    const API_BASE = '../api';

    // DOM-element for Aktivitetsskjema
    const activityForm = document.getElementById('activity-form');
    const formTitle = document.getElementById('activity-form-title');
    const submitBtn = document.getElementById('activity-submit-btn');
    const cancelBtn = document.getElementById('activity-cancel-btn');
    
    const activityIdInput = document.getElementById('activity-id');
    const activityNameInput = document.getElementById('activity-name');
    const activityDescInput = document.getElementById('activity-description');
    const activityStartInput = document.getElementById('activity-start');
    const activityEndInput = document.getElementById('activity-end');
    const activityMaxInput = document.getElementById('activity-max-participants');

    // DOM-element for Brukarskjema (Superbrukar)
    const userForm = document.getElementById('user-form');

    // DOM-element for Utlogging
    const logoutBtn = document.getElementById('logout-btn');

    // DOM-element for Påmeldingsmodal (Deltakarliste)
    const participantsModalEl = document.getElementById('participantsModal');
    let participantsModal = null;
    if (participantsModalEl) {
        participantsModal = new bootstrap.Modal(participantsModalEl);
    }
    const modalTitleName = document.getElementById('modal-activity-name');
    const tableBody = document.getElementById('participants-table-body');
    const noParticipantsMsg = document.getElementById('no-participants-msg');

    /* ============================================================
       1. Handtering av Aktivitetsskjema (Opprett / Endre)
       ============================================================ */
    if (activityForm) {
        activityForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = activityIdInput.value;
            const isEdit = id !== '';

            const payload = {
                name: activityNameInput.value,
                description: activityDescInput.value,
                start_time: activityStartInput.value,
                end_time: activityEndInput.value,
                max_participants: parseInt(activityMaxInput.value, 10)
            };

            const url = isEdit ? `${API_BASE}/activities/${id}` : `${API_BASE}/activities`;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    alert(isEdit ? 'Aktiviteten vart oppdatert!' : 'Aktiviteten vart oppretta!');
                    window.location.reload(); 
                } else {
                    alert('Feil: ' + (result.message || 'Kunne ikkje lagre aktiviteten.'));
                }
            } catch (error) {
                console.error('Nettverksfeil:', error);
                alert('Det oppstod ein feil under kommunikasjon med serveren.');
            }
        });
    }

    /* ============================================================
       2. Lytt etter klikk i aktivitetslista (Endre & Sjå påmeldte)
       ============================================================ */
    document.addEventListener('click', async (e) => {
        
        // SJEKK A: Klikk på "Endre"
        if (e.target.classList.contains('edit-activity-trigger')) {
            const btn = e.target;

            activityIdInput.value = btn.getAttribute('data-id');
            activityNameInput.value = btn.getAttribute('data-name');
            activityDescInput.value = btn.getAttribute('data-description');
            activityStartInput.value = btn.getAttribute('data-start');
            activityEndInput.value = btn.getAttribute('data-end');
            activityMaxInput.value = btn.getAttribute('data-max');

            formTitle.textContent = 'Endre aktivitet: ' + btn.getAttribute('data-name');
            submitBtn.textContent = 'Lagre endringar';
            submitBtn.classList.replace('btn-success', 'btn-primary');
            cancelBtn.classList.remove('d-none');

            activityForm.scrollIntoView({ behavior: 'smooth' });
        }

        // SJEKK B: Klikk på "Sjå påmeldte"
        if (e.target.classList.contains('view-participants-trigger')) {
            const btn = e.target;
            const activityId = btn.getAttribute('data-id');
            const activityName = btn.getAttribute('data-name');

            if (!participantsModal) return;

            modalTitleName.textContent = `Aktivitet: ${activityName}`;
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Lastar deltakarliste...</td></tr>';
            noParticipantsMsg.classList.add('d-none');
            
            participantsModal.show();

            try {
                const response = await fetch(`${API_BASE}/activities/${activityId}/participants`);
                const result = await response.json();

                if (result.success) {
                    const list = result.data.participants || [];
                    tableBody.innerHTML = ''; 

                    if (list.length === 0) {
                        noParticipantsMsg.classList.remove('d-none');
                    } else {
                        list.forEach((participant, index) => {
                            const date = new Date(participant.timestamp);
                            const formattedTime = date.toLocaleDateString('no-NO', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            let priorityBadge = '';
                            if (participant.priority === '1') priorityBadge = '<span class="badge bg-success">1. val</span>';
                            else if (participant.priority === '2') priorityBadge = '<span class="badge bg-warning text-dark">2. val</span>';
                            else priorityBadge = '<span class="badge bg-secondary">3. val</span>';

                            const row = `
                                <tr>
                                    <th scope="row">${index + 1}</th>
                                    <td>${escapeHtml(participant.name)}</td>
                                    <td>${escapeHtml(participant.email)}</td>
                                    <td>${priorityBadge}</td>
                                    <td class="small text-muted">${formattedTime}</td>
                                </tr>
                            `;
                            tableBody.insertAdjacentHTML('beforeend', row);
                        });
                    }
                } else {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">Feil: ${result.message}</td></tr>`;
                }
            } catch (error) {
                console.error('Kunne ikkje hente deltakarliste:', error);
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Det oppstod ein nettverksfeil.</td></tr>';
            }
        }
    });

    // Avbryt-knapp for aktivitetsskjemaet
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            activityForm.reset();
            activityIdInput.value = '';
            formTitle.textContent = 'Opprett ny aktivitet';
            submitBtn.textContent = 'Lagre aktivitet';
            submitBtn.classList.replace('btn-primary', 'btn-success');
            cancelBtn.classList.add('d-none');
        });
    }

    /* ============================================================
       3. Opprett ny brukar (Kun Superbrukar)
       ============================================================ */
    if (userForm) {
        userForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const superUserCheckbox = document.getElementById('user-superuser');

            const payload = {
                name: document.getElementById('user-name').value,
                username: document.getElementById('user-username').value,
                password: document.getElementById('user-password').value,
                super_user: superUserCheckbox && superUserCheckbox.checked ? 1 : 0
            };

            try {
                const response = await fetch(`${API_BASE}/organizers`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Ny brukar er oppretta!');
                    userForm.reset();
                } else {
                    alert('Feil: ' + (result.message || 'Kunne ikkje opprette brukar.'));
                }
            } catch (error) {
                console.error('Nettverksfeil:', error);
                alert('Ein feil oppstod under oppretting av brukar.');
            }
        });
    }

    /* ============================================================
       4. Utlogging
       ============================================================ */
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            if (!confirm('Er du sikker på at du vil logge ut?')) return;

            try {
                const response = await fetch(`${API_BASE}/logout`, { method: 'POST' });
                const result = await response.json();

                if (result.success) {
                    window.location.href = 'login';
                } else {
                    alert('Feil under utlogging: ' + result.message);
                }
            } catch (error) {
                console.error('Nettverksfeil ved utlogging:', error);
                window.location.href = 'login'; 
            }
        });
    }

    // Sikring mot XSS i tabellrader
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }
});