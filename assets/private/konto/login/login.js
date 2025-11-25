const form = document.getElementById('loginForm');

const redirect = new URLSearchParams(window.location.search).get('redirect');

if (redirect) {
    const redirectLinks = document.querySelectorAll('.forward-redirect');
    redirectLinks.forEach(link => {
        link.href += `?redirect=${redirect}`;
    });
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    try {
        const response = await fetch('https://emil-web-api-production.up.railway.app/login', {
            method: 'POST',
            credentials: 'include', // Include cookies automatically
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });

        const data = await response.json();

        if (!response.ok) {
            alert(`Feil: ${data.message || 'Noe gikk galt.'}`);
            return;
        }

        // No need to save token manually — cookie is set by server

        // Redirect to main konto page
        if (redirect) {
            window.location.href = `../../${redirect}`;
        } else {
            window.location.href = '../';
        }
    } catch (err) {
        console.error(err);
        alert('En feil oppstod. Prøv igjen senere.');
    }
});
