const form = document.getElementById('registerForm');

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

    // Validate username is all lowercase
    if (username !== username.toLowerCase()) {
        alert('Brukernavn må kun være små bokstaver.');
        return;
    }

    try {
        const response = await fetch('https://emil.elevweb.no/users', {
            method: 'POST',
            credentials: 'include',
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
