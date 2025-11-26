const API_BASE = 'https://emil.elevweb.no';

// --- Fetch user info on page load ---
try {
    const res = await fetch(`${API_BASE}/user`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
    });

    if (!res.ok) throw new Error('Feil ved henting av brukerinfo.');

    const data = await res.json();

    if (data.user.username !== 'admin' && !data.user.permissions.includes('admin')) {
        alert('Du har ikkje tilgang til denne sida.');
        window.location.href = '../';
    }
} catch (err) {
    console.error(err);
    window.location.href = '../login';
}

// --- Available permissions ---
const availablePermissions = ['admin', 'pingpanik'];

// --- Fetch users ---
async function fetchUsers() {
    try {
        const res = await fetch(`${API_BASE}/users`, {
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();

        if (!res.ok) return alert(data.message || 'Feil ved henting av brukarar.');

        const tbody = document.querySelector('#userTable tbody');
        tbody.innerHTML = '';

        data.users.forEach(user => {
            const row = document.createElement('tr');

            // Create badges for permissions
            const badges = availablePermissions.map(p => {
                const checked = user.permissions.includes(p);
                const span = document.createElement('span');
                span.className = 'permission-badge';
                if (checked) span.classList.add('checked');
                span.dataset.user = user.username;
                span.dataset.perm = p;
                span.textContent = p;

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.checked = checked;
                span.appendChild(input);

                // Toggle badge
                span.addEventListener('click', () => {
                    span.classList.toggle('checked');
                    input.checked = !input.checked;
                });

                return span;
            });

            // Username
            const userTd = document.createElement('td');
            userTd.textContent = user.username;

            // Permissions
            const permTd = document.createElement('td');
            badges.forEach(b => permTd.appendChild(b));

            // Action buttons
            const actionTd = document.createElement('td');

            const updateBtn = document.createElement('button');
            updateBtn.textContent = 'Lagre';
            updateBtn.className = 'btn-link';
            updateBtn.addEventListener('click', () => updatePermissions(user.username));

            const deleteBtn = document.createElement('button');
            deleteBtn.textContent = 'Slett';
            deleteBtn.className = 'btn-link';
            deleteBtn.style.backgroundColor = '#b71c1c';
            deleteBtn.addEventListener('click', () => deleteUser(user.username));

            actionTd.appendChild(updateBtn);
            actionTd.appendChild(deleteBtn);

            row.appendChild(userTd);
            row.appendChild(permTd);
            row.appendChild(actionTd);

            tbody.appendChild(row);
        });

    } catch (err) {
        console.error(err);
        alert('Klarte ikkje å hente brukarar.');
    }
}

// --- Update permissions ---
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
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
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

// --- Delete user ---
async function deleteUser(username) {
    if (!confirm(`Er du sikker på at du vil slette "${username}"?`)) return;
    if (username === 'admin') return alert('Admin-brukaren kan ikkje slettast.');

    try {
        const res = await fetch(`${API_BASE}/users/${username}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();
        alert(data.message);
        if (res.ok) fetchUsers();

    } catch (err) {
        console.error(err);
        alert('Feil ved sletting av brukar.');
    }
}

// --- Init ---
fetchUsers();
