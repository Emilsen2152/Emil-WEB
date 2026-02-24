const userTableBody = document.getElementById('user-table-body');
const alertArea = document.getElementById('alert-area');

// Juster denne om du har annan URL-struktur
// - Med rewrite kan du ofte bruke: const API_BASE = '/api';
const API_BASE = '../../api/';

function esc(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function fmtDate(s) {
    if (!s) return '—';
    // API-et ditt returnerer truleg "YYYY-MM-DD HH:MM:SS"
    // Dette er "godt nok" å vise direkte, men vi gjer det litt penare:
    return esc(String(s).replace('T', ' ').replace('.000Z', ''));
}

function showAlert(type, message) {
    const html = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${esc(message)}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Lukk"></button>
    </div>
  `;
    alertArea.innerHTML = html;
}

async function apiFetch(path, opts = {}) {
    const res = await fetch(`${API_BASE}${path}`, {
        credentials: 'include', // viktig for cookie-auth
        headers: {
            'Content-Type': 'application/json',
            ...(opts.headers || {}),
        },
        ...opts,
    });

    let data = null;
    try {
        data = await res.json();
    } catch (_) { }

    if (!res.ok) {
        const msg = data?.message || `HTTP ${res.status}`;
        throw new Error(msg);
    }

    console.log('API-svar:', { path, opts, res, data });
    return data;
}

function render(users) {
    if (!Array.isArray(users) || users.length === 0) {
        userTableBody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center text-muted py-4">Ingen brukere funne.</td>
      </tr>
    `;
        return;
    }

    userTableBody.innerHTML = users.map(u => {
        const perms = Array.isArray(u.permissions) ? u.permissions : [];
        const permsText = perms.join(', ');

        return `
      <tr data-username="${esc(u.username)}">
        <td>${esc(u.id)}</td>
        <td><code>${esc(u.username)}</code></td>
        <td>${fmtDate(u.createdAt)}</td>
        <td>${fmtDate(u.lastLogin)}</td>
        <td>
          <input
            class="form-control form-control-sm perm-input"
            type="text"
            value="${esc(permsText)}"
            placeholder="t.d. admin,pingpanik"
          />
          <div class="form-text">Kommaseparert liste</div>
        </td>
        <td>
          <div class="d-grid gap-2">
            <button class="btn btn-success btn-sm action-save">Lagre</button>
            <button class="btn btn-danger btn-sm action-delete">Slett</button>
          </div>
        </td>
      </tr>
    `;
    }).join('');
}

async function loadUsers() {
    userTableBody.innerHTML = `
    <tr>
      <td colspan="6" class="text-center text-muted py-4">Laster brukere…</td>
    </tr>
  `;

    const data = await apiFetch('/users', { method: 'GET' });
    render(data.users);
}

function parsePermissions(inputValue) {
    return String(inputValue || '')
        .split(',')
        .map(s => s.trim())
        .filter(Boolean);
}

userTableBody.addEventListener('click', async (e) => {
    const row = e.target.closest('tr[data-username]');
    if (!row) return;

    const username = row.getAttribute('data-username');
    const permInput = row.querySelector('.perm-input');

    // Slett
    if (e.target.classList.contains('action-delete')) {
        if (!confirm(`Slette brukaren "${username}"?`)) return;

        try {
            await apiFetch(`/users/${encodeURIComponent(username)}`, { method: 'DELETE' });
            showAlert('success', `Brukaren "${username}" er sletta.`);
            await loadUsers();
        } catch (err) {
            showAlert('danger', err.message);
        }
        return;
    }

    // Lagre permissions
    if (e.target.classList.contains('action-save')) {
        const permissions = parsePermissions(permInput.value);

        try {
            await apiFetch(`/users/${encodeURIComponent(username)}`, {
                method: 'PATCH',
                body: JSON.stringify({ permissions }),
            });

            showAlert('success', `Rettigheiter oppdatert for "${username}".`);
            await loadUsers();
        } catch (err) {
            showAlert('danger', err.message);
        }
        return;
    }
});

loadUsers().catch(err => {
    showAlert('danger', err.message);
    userTableBody.innerHTML = `
    <tr>
      <td colspan="6" class="text-center text-danger py-4">
        Klarte ikkje å laste brukere: ${esc(err.message)}
      </td>
    </tr>
  `;
});