const form = document.getElementById('loginForm');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    try {
        const response = await fetch('https://emil-web-api-production.up.railway.app/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });

        const data = await response.json();

        if (!response.ok) {
            alert(`Feil: ${data.error || 'Noe gikk galt.'}`);
            return;
        }

        // Save token in Bearer format
        localStorage.setItem('token', `Bearer ${data.token}`);

        // Save username as well (optional)
        localStorage.setItem('username', data.user?.username || username);

        // Redirect to main konto page
        window.location.href = '../';
    } catch (err) {
        console.error(err);
        alert('En feil oppstod. Prøv igjen senere.');
    }
});
