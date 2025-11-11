const form = document.getElementById('loginForm');

const redirects = {
    'film-søk': '../../eigenprosjekt/film-søk/',
    'vossamessa': '../../pingpanik/vossamessa/',
}

const redirect = new URLSearchParams(window.location.search).get('redirect');

if (redirect) {
    const redirectLinks = document.querySelectorAll('.forward-redirect');
    redirectLinks.forEach(link => {
        link.href += `?redirect=${redirect}`;
    });
};

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
            alert(`Feil: ${data.message || 'Noe gikk galt.'}`);
            return;
        }

        // Save token in Bearer format
        localStorage.setItem('emil-web-token', `Bearer ${data.user.token}`);

        // Redirect to main konto page
        if (redirect && redirects[redirect]) {
            window.location.href = redirects[redirect];
        } else {
            window.location.href = '../';
        }
    } catch (err) {
        console.error(err);
        alert('En feil oppstod. Prøv igjen senere.');
    }
});
