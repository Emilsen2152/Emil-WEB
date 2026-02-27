(function () {
  const addItemForm = document.getElementById('add-item-form');
  const itemsEl = document.getElementById('items');
  const itemsEmptyEl = document.getElementById('items-empty');

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
      // ignore json parse errors
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

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  async function loadItems() {
    if (!listId) return;

    const data = await apiFetch(`../api/to-do-lists/${listId}/items`, { method: 'GET' });
    const items = (data && data.data && data.data.items) ? data.data.items : [];

    itemsEl.innerHTML = '';

    if (!items.length) {
      itemsEmptyEl.classList.remove('d-none');
      return;
    }

    itemsEmptyEl.classList.add('d-none');

    for (const item of items) {
      const id = item.id;
      const completed = !!item.completed;

      const row = document.createElement('div');
      row.className = 'list-group-item d-flex align-items-center justify-content-between gap-2';

      row.innerHTML = `
        <div class="d-flex align-items-center gap-2 flex-grow-1">
          <input class="form-check-input m-0" type="checkbox" data-action="toggle" data-id="${id}" ${completed ? 'checked' : ''}>
          <span class="flex-grow-1 ${completed ? 'text-decoration-line-through text-muted' : ''}">
            ${escapeHtml(item.description ?? '')}
          </span>
        </div>
        <button class="btn btn-sm btn-outline-danger" data-action="delete-item" data-id="${id}">
          Slett
        </button>
      `;

      itemsEl.appendChild(row);
    }
  }

  // Add item (reload etterpå)
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

        window.location.reload();
      } catch (err) {
        alert(err.message || 'Feil ved oppretting av oppgåve.');
      }
    });
  }

  // Delegert click: toggle + delete item
  if (itemsEl) {
    itemsEl.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;

      const action = btn.dataset.action;
      const itemId = parseInt(btn.dataset.id, 10);
      if (!itemId) return;

      try {
        if (action === 'delete-item') {
          await apiFetch(`../api/to-do-items/${itemId}`, { method: 'DELETE' });
          window.location.reload();
        }
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

        window.location.reload();
      } catch (err) {
        alert(err.message || 'Feil ved oppdatering.');
      }
    });
  }

  // Share
  const shareForm = document.getElementById('share-form');
  const shareAlert = document.getElementById('share-alert');

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

        // API kan returnera {already_shared:true} eller {shared:true}
        const d = res && res.data ? res.data : {};
        if (d.already_shared) {
          showAlert(shareAlert, 'info', 'Lista er allereie delt med denne brukaren.');
        } else {
          showAlert(shareAlert, 'success', 'Lista vart delt!');
        }

        // Du vil reload: gjer det etter ein kort “OK” (utan timeout: reload direkte)
        window.location.reload();
      } catch (err) {
        const msg = err.message || 'Feil ved deling.';
        showAlert(shareAlert, 'danger', msg);
      }
    });
  }

  // Delete list
  const deleteBtn = document.getElementById('confirm-delete-list');
  const deleteListAlert = document.getElementById('delete-list-alert');

  if (deleteBtn && listId) {
    deleteBtn.addEventListener('click', async () => {
      hideAlert(deleteListAlert);

      try {
        await apiFetch(`../api/to-do-lists/${listId}`, { method: 'DELETE' });

        // Etter sletting: send brukaren til oversikt (tilpass om du har annan URL)
        window.location.href = '../';
      } catch (err) {
        showAlert(deleteListAlert, 'danger', err.message || 'Feil ved sletting av lista.');
      }
    });
  }

  // Initial load
  loadItems().catch((err) => {
    console.error(err);
    alert(err.message || 'Feil ved lasting av oppgåver.');
  });
})();