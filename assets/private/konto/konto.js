const usernameEl = document.getElementById('usernameDisplay');
const logoutBtn = document.getElementById('logoutBtn');
const passwordChangeBtn = document.getElementById('passwordChangeBtn');
const passwordChangeForm = document.getElementById('password-change');

const token = localStorage.getItem('emil-web-token');

if (!token) {
    // No token, redirect to register/login page
    window.location.href = './login';
} else {
    // Fetch user from API
    fetch('https://emil-web-api-production.up.railway.app/user', {
        method: 'GET',
        headers: {
            'Authorization': token,
            'Content-Type': 'application/json'
        }
    })
        .then(res => {
            if (!res.ok) throw new Error('Ugyldig token eller feil ved henting av bruker.');
            return res.json();
        })
        .then(data => {
            usernameEl.textContent = data.user.username;
        })
        .catch(err => {
            console.error(err);
            // Token invalid, clear localStorage and redirect
            localStorage.removeItem('emil-web-token');
            window.location.href = './login';
        });
}

// Logout button clears localStorage and redirects
logoutBtn.addEventListener('click', () => {
    localStorage.removeItem('emil-web-token');
    window.location.href = './login';
});

passwordChangeBtn.addEventListener('click', () => {
    passwordChangeForm.style.display = passwordChangeForm.style.display === 'none' ? 'block' : 'none';
});

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
        };

        alert('Passord oppdatert.');

        localStorage.setItem('emil-web-token', `Bearer ${data.user.token}`);

        // Reload
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert('En feil oppstod. Prøv igjen senere.');
    }
});
