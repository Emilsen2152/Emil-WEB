const usernameEl = document.getElementById('usernameDisplay');
const logoutBtn = document.getElementById('logoutBtn');
const passwordChangeBtn = document.getElementById('passwordChangeBtn');
const passwordChangeForm = document.getElementById('password-change');
const userAdminBtn = document.getElementById('userAdminBtn');

const token = localStorage.getItem('emil-web-token');

if (!token) {
    // Ingen token → send til login
    window.location.href = './login';
} else {
    try {
        const res = await fetch('https://emil-web-api-production.up.railway.app/user', {
            method: 'GET',
            headers: {
                'Authorization': token,
                'Content-Type': 'application/json'
            }
        });

        if (!res.ok) throw new Error('Ugyldig token eller feil ved henting av bruker.');

        const data = await res.json();

        usernameEl.textContent = data.user.username;

        if (data.user.username === 'admin' || data.user.permissions.includes('admin')) {
            userAdminBtn.classList.remove('hidden');
        }
    } catch (err) {
        console.error(err);
        localStorage.removeItem('emil-web-token');
        window.location.href = './login';
    }
}

// Logout
logoutBtn.addEventListener('click', () => {
    localStorage.removeItem('emil-web-token');
    window.location.href = './login';
});

// Vis / skjul passordskjema
passwordChangeBtn.addEventListener('click', () => {
    passwordChangeForm.classList.toggle('hidden');
});

// Endre passord
passwordChangeForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;

    try {
        const response = await fetch('https://emil-web-api-production.up.railway.app/user/password', {
            method: 'PUT',
            headers: {
                'Authorization': token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ currentPassword, newPassword })
        });

        const data = await response.json();

        if (!response.ok) {
            alert(`Feil: ${data.error || 'Noe gikk galt ved oppdatering av passord.'}`);
            return;
        }

        alert('Passord oppdatert.');

        localStorage.setItem('emil-web-token', `Bearer ${data.user.token}`);
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert('En feil oppstod. Prøv igjen senere.');
    }
});
