const usernameEl = document.getElementById('usernameDisplay');
const logoutBtn = document.getElementById('logoutBtn');
const passwordChangeBtn = document.getElementById('passwordChangeBtn');
const passwordChangeForm = document.getElementById('password-change');

const token = localStorage.getItem('token');

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
            usernameEl.textContent = data.username;
        })
        .catch(err => {
            console.error(err);
            // Token invalid, clear localStorage and redirect
            localStorage.removeItem('token');
            window.location.href = './login';
        });
}

// Logout button clears localStorage and redirects
logoutBtn.addEventListener('click', () => {
    localStorage.removeItem('token');
    window.location.href = './login';
});

passwordChangeBtn.addEventListener('click', () => {
    passwordChangeForm.style.display = passwordChangeForm.style.display === 'none' ? 'block' : 'none';
});
