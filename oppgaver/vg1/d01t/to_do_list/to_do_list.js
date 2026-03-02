// to_do_list.js
(function () {
    const addItemForm = document.getElementById('add-item-form');
    const itemsEl = document.getElementById('items');
    const itemsEmptyEl = document.getElementById('items-empty');

    // Share UI (exists only when $canShare = true)
    const shareForm = document.getElementById('share-form');
    const shareAlert = document.getElementById('share-alert');
    const sharedUsersListEl = document.getElementById('shared-users-list');

    // Delete list UI (exists only when $canDelete = true)
    const deleteBtn = document.getElementById('confirm-delete-list');
    const deleteListAlert = document.getElementById('delete-list-alert');

    const listId = addItemForm
        ? parseInt(addItemForm.querySelector('input[name="to_do_list_id"]').value, 10)
        : null;

    function showAlert(el, type, message) {
        if (!el) return;
        el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
        el.classList.add(`alert-${type}`);
        el.textContent = message;
    }

    function hideAlert(el) {
        if (!el) return;
        el.classList.add('d-none');
        el.textContent = '';
        el.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info');
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    async function apiFetch(url, options = {}) {
        const res = await fetch(url, {
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
            ...options,
        });

        let data = null;
        try {
            data = await res.json();
        } catch (_) {
            // ignore
        }

        if (!res.ok) {
            const msg = (data && (data.error || data.message)) || `HTTP ${res.status}`;
            const err = new Error(msg);
            err.status = res.status;
            err.payload = data;
            throw err;
        }

        return data;
    }

    async function loadItems() {
        if (!listId || !itemsEl) return;

        const data = await apiFetch(`../api/to-do-lists/${listId}/items`, { method: 'GET' });
        const items = data?.data?.items ?? [];

        itemsEl.innerHTML = '';

        if (!items.length) {
            itemsEmptyEl?.classList.remove('d-none');
            return;
        }

        itemsEmptyEl?.classList.add('d-none');

        for (const item of items) {
            const id = item.id;
            const completed = !!item.completed;

            const row = document.createElement('div');
            row.className = 'list-group-item d-flex align-items-center justify-content-between gap-2';

            row.innerHTML = `
        <div class="d-flex align-items-center gap-2 flex-grow-1">
          <input class="form-check-input m-0" type="checkbox"
                 data-action="toggle" data-id="${id}" ${completed ? 'checked' : ''}>
          <span class="flex-grow-1 ${completed ? 'text-decoration-line-through text-muted' : ''}"
            data-action="edit"
            data-id="${id}">
        ${escapeHtml(item.description)}
        </span>
        </div>
        <button class="btn btn-sm btn-outline-danger" data-action="delete-item" data-id="${id}">
          Slett
        </button>
      `;

            itemsEl.appendChild(row);
        }
    }

    // ✅ Shares list + "Fjern" button using:
    // GET    /to-do-lists/{listId}/shares
    // DELETE /to-do-lists/{listId}/share   body: { username }
    async function loadShares() {
        if (!listId || !sharedUsersListEl) return;

        const data = await apiFetch(`../api/to-do-lists/${listId}/shares`, { method: 'GET' });

        const usersRaw = data?.data?.shared_with ?? data?.data ?? [];
        const users = Array.isArray(usersRaw) ? usersRaw : [];

        sharedUsersListEl.innerHTML = '';

        if (!users.length) {
            sharedUsersListEl.innerHTML = `<li class="list-group-item text-muted">Ingen delingar endå.</li>`;
            return;
        }

        for (const u of users) {
            const username = typeof u === 'string' ? u : (u?.username ?? '');
            const safeUsername = escapeHtml(username || '(ukjent brukar)');

            const li = document.createElement('li');
            li.className = 'list-group-item d-flex align-items-center justify-content-between gap-2';

            // data-action="unshare" triggers DELETE endpoint
            li.innerHTML = `
        <span class="text-truncate">${safeUsername}</span>
        <button type="button"
                class="btn btn-sm btn-outline-danger"
                data-action="unshare"
                data-username="${escapeHtml(username)}">
          Fjern
        </button>
      `;

            sharedUsersListEl.appendChild(li);
        }
    }

    // Add item (reload afterwards)
    if (addItemForm) {
        addItemForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(addItemForm);
            const to_do_list_id = formData.get('to_do_list_id');
            const description = String(formData.get('description') || '').trim();
            if (!description) return;

            try {
                await apiFetch(`../api/to-do-lists/${to_do_list_id}/items`, {
                    method: 'POST',
                    body: JSON.stringify({ description }),
                });

                loadItems().catch((err) => {
                    console.error('Feil ved oppretting av oppgåve:', err);
                    window.location.reload();
                });
            } catch (err) {
                alert(err.message || 'Feil ved oppretting av oppgåve.');
            }
        });
    }

    // Toggle completed + delete item (delegation)
    if (itemsEl) {
        itemsEl.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-action="delete-item"]');
            if (!btn) return;

            const itemId = parseInt(btn.dataset.id, 10);
            if (!itemId) return;

            try {
                await apiFetch(`../api/to-do-items/${itemId}`, { method: 'DELETE' });
                loadItems().catch((err) => {
                    console.error('Feil ved oppdatering av oppgåve:', err);
                    window.location.reload();
                });
            } catch (err) {
                alert(err.message || 'Feil ved sletting.');
            }
        });

        itemsEl.addEventListener('change', async (e) => {
            const input = e.target.closest('input[data-action="toggle"]');
            if (!input) return;

            const itemId = parseInt(input.dataset.id, 10);
            if (!itemId) return;

            try {
                await apiFetch(`../api/to-do-items/${itemId}/completed`, {
                    method: 'PATCH',
                    body: JSON.stringify({ completed: !!input.checked }),
                });

                loadItems().catch((err) => {
                    console.error('Feil ved oppdatering av oppgåve:', err);
                    window.location.reload();
                });
            } catch (err) {
                alert(err.message || 'Feil ved oppdatering.');
            }
        });

        // Edit description (double click on text)
        itemsEl.addEventListener('dblclick', async (e) => {
            const el = e.target.closest('[data-action="edit"]');
            if (!el) return;

            const itemId = parseInt(el.dataset.id, 10);
            if (!itemId) return;

            const current = el.textContent.trim();
            const next = prompt('Rediger oppgåve:', current);
            if (next === null) return;

            const description = String(next).trim();
            if (!description || description === current) return;

            try {
                await apiFetch(`../api/to-do-items/${itemId}/description`, {
                    method: 'PATCH',
                    body: JSON.stringify({ description }),
                });

                loadItems().catch((err) => {
                    console.error('Feil ved oppdatering:', err);
                    window.location.reload();
                });
            } catch (err) {
                alert(err.message || 'Feil ved oppdatering.');
            }
        });
    }

    // Share list
    if (shareForm && listId) {
        shareForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert(shareAlert);

            const username = String(new FormData(shareForm).get('username') || '').trim();
            if (!username) return;

            try {
                const res = await apiFetch(`../api/to-do-lists/${listId}/share`, {
                    method: 'POST',
                    body: JSON.stringify({ username }),
                });

                const d = res?.data ?? {};
                if (d.already_shared) {
                    showAlert(shareAlert, 'info', 'Lista er allereie delt med denne brukaren.');
                } else {
                    showAlert(shareAlert, 'success', 'Lista vart delt!');
                }

                loadShares().catch((err) => {
                    console.error('Feil ved oppdatering av delingar:', err);
                    window.location.reload();
                });
            } catch (err) {
                showAlert(shareAlert, 'danger', err.message || 'Feil ved deling.');
            }
        });
    }

    // ✅ Unshare (remove sharing) — delegated on the <ul>
    if (sharedUsersListEl && listId) {
        sharedUsersListEl.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-action="unshare"]');
            if (!btn) return;

            const username = String(btn.dataset.username || '').trim();
            if (!username) return;

            // Optional: quick confirm (remove if you don't want prompts)
            if (!confirm(`Fjerna deling med "${username}"?`)) return;

            try {
                await apiFetch(`../api/to-do-lists/${listId}/share`, {
                    method: 'DELETE',
                    body: JSON.stringify({ username }),
                });

                loadShares().catch((err) => {
                    console.error('Feil ved oppdatering av delingar:', err);
                    window.location.reload();
                });
            } catch (err) {
                // show in share modal alert if present, else normal alert
                if (shareAlert) showAlert(shareAlert, 'danger', err.message || 'Feil ved fjerning av deling.');
                else alert(err.message || 'Feil ved fjerning av deling.');
            }
        });
    }

    // Delete list
    if (deleteBtn && listId) {
        deleteBtn.addEventListener('click', async () => {
            hideAlert(deleteListAlert);

            try {
                await apiFetch(`../api/to-do-lists/${listId}`, { method: 'DELETE' });
                window.location.href = '../'; // change if your overview route differs
            } catch (err) {
                showAlert(deleteListAlert, 'danger', err.message || 'Feil ved sletting av lista.');
            }
        });
    }

    const leaveBtn = document.getElementById('confirm-leave-list');
    const leaveAlert = document.getElementById('leave-list-alert');

    if (leaveBtn && listId) {
        leaveBtn.addEventListener('click', async () => {
            hideAlert(leaveAlert);

            try {
                await apiFetch(`../api/to-do-lists/${listId}/leave`, { method: 'DELETE' });
                window.location.href = '../';
            } catch (err) {
                showAlert(leaveAlert, 'danger', err.message || 'Feil ved forlat liste.');
            }
        });
    }

    // Initial load
    loadItems().catch((err) => {
        console.error(err);
        alert(err.message || 'Feil ved lasting av oppgåver.');
    });

    loadShares().catch((err) => {
        console.warn('Kunne ikkje lasta delingar:', err);
    });
})();