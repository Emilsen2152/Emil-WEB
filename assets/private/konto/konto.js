const usernameEl = document.getElementById('usernameDisplay');
const logoutBtn = document.getElementById('logoutBtn');
const passwordChangeBtn = document.getElementById('passwordChangeBtn');
const passwordChangeForm = document.getElementById('password-change');
const userAdminBtn = document.getElementById('userAdminBtn');

// --- Fetch user info on page load ---
try {
    const res = await fetch('https://emil-web-api-production.up.railway.app/user', {
        method: 'GET',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
    });

    if (!res.ok) throw new Error('Feil ved henting av brukerinfo.');

    const data = await res.json();

    usernameEl.textContent = data.user.username;

    if (data.user.username === 'admin' || data.user.permissions.includes('admin')) {
        userAdminBtn.classList.remove('hidden');
    }
} catch (err) {
    console.error(err);
    // Redirect to login if user not authenticated
    window.location.href = './login';
}

// --- Logout ---
logoutBtn.addEventListener('click', async () => {
    try {
        await fetch('https://emil-web-api-production.up.railway.app/logout', {
            method: 'POST',
            credentials: 'include',
        });
    } catch (err) {
        console.error('Feil ved utlogging:', err);
    } finally {
        window.location.href = './login';
    }
});

// --- Show / hide password change form ---
passwordChangeBtn.addEventListener('click', () => {
    passwordChangeForm.classList.toggle('hidden');
});

// --- Change password ---
passwordChangeForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;

    try {
        const response = await fetch('https://emil-web-api-production.up.railway.app/user/password', {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ currentPassword, newPassword })
        });

        const data = await response.json();

        if (!response.ok) {
            alert(`Feil: ${data.error || 'Noe gikk galt ved oppdatering av passord.'}`);
            return;
        }

        alert('Passord oppdatert.');
        window.location.reload(); // No need to update token manually
    } catch (err) {
        console.error(err);
        alert('En feil oppstod. Prøv igjen senere.');
    }
});
