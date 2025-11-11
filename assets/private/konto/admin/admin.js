const API_BASE = 'https://emil-web-api-production.up.railway.app';
const token = localStorage.getItem('emil-web-token');

if (!token) window.location.href = '../login';

// Tilgjengelige permissions
const availablePermissions = [
    'admin',
    'pingpanik'
];

// Hent brukarar
async function fetchUsers() {
    try {
        const res = await fetch(`${API_BASE}/users`, {
            headers: { Authorization: token }
        });
        const data = await res.json();
        if (!res.ok) return alert(data.message || 'Feil ved henting av brukarar.');

        const tbody = document.querySelector('#userTable tbody');
        tbody.innerHTML = '';

        data.users.forEach(user => {
            const row = document.createElement('tr');

            // Lag badges
            const badges = availablePermissions.map(p => {
                const checked = user.permissions.includes(p) ? 'checked' : '';
                return `<span class="permission-badge ${checked}" data-user="${user.username}" data-perm="${p}">
                            ${p} <input type="checkbox" ${checked}>
                        </span>`;
            }).join('');

            row.innerHTML = `
                <td>${user.username}</td>
                <td>${badges}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-link" onclick="updatePermissions('${user.username}')">Lagre</button>
                        <button class="btn-link" style="background-color:#b71c1c;" onclick="deleteUser('${user.username}')">Slett</button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Legg til click-event for badges
        document.querySelectorAll('.permission-badge').forEach(badge => {
            badge.addEventListener('click', () => {
                badge.classList.toggle('checked');
                const checkbox = badge.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
            });
        });

    } catch (err) {
        console.error(err);
        alert('Klarte ikkje å hente brukarar.');
    }
}

// Oppdater permissions basert på badges
async function updatePermissions(username) {
    const badges = document.querySelectorAll(`.permission-badge[data-user="${username}"]`);
    const perms = [];
    badges.forEach(badge => {
        if (badge.classList.contains('checked')) perms.push(badge.dataset.perm);
    });

    if (username === 'admin' && !perms.includes('admin')) {
        return alert('Du kan ikkje fjerne admin-rettigheter frå admin-brukaren.');
    }

    try {
        const res = await fetch(`${API_BASE}/users/permissions/${username}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                Authorization: token
            },
            body: JSON.stringify({ permissions: perms })
        });
        const data = await res.json();
        alert(data.message);
        if (res.ok) fetchUsers();
    } catch (err) {
        console.error(err);
        alert('Feil ved oppdatering av permissions.');
    }
}

// Slett ein brukar
async function deleteUser(username) {
    if (!confirm(`Er du sikker på at du vil slette "${username}"?`)) return;
    if (username === 'admin') return alert('Admin-brukaren kan ikkje slettast.');

    try {
        const res = await fetch(`${API_BASE}/users/${username}`, {
            method: 'DELETE',
            headers: { Authorization: token }
        });
        const data = await res.json();
        alert(data.message);
        if (res.ok) fetchUsers();
    } catch (err) {
        console.error(err);
        alert('Feil ved sletting av brukar.');
    }
}

// Init
fetchUsers();
