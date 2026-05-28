document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const feedbackEl = document.getElementById('login-feedback');
    const submitBtn = document.getElementById('login-submit-btn');

    if (!loginForm) return;

    loginForm.addEventListener('submit', async (e) => {
        // 1. Avbryt den vanlege skjemasendinga (page reload)
        e.preventDefault();

        // 2. Hent ut verdiar frå input-felta
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        const username = usernameInput.value.trim();
        const password = passwordInput.value;

        // 3. Nullstill feedback-feltet og deaktiver knappen under lasting
        feedbackEl.className = 'd-none mb-3';
        feedbackEl.innerHTML = '';
        submitBtn.disabled = true;
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Logger inn...';

        try {
            // 4. Send forespørsel til API-en (POST /login)
            // Tilpass URL-en dersom API-en din ligg på ein annan relativ sti
            const response = await fetch('../api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8'
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            });

            const result = await response.json();

            // 5. Sjekk responsen basert på din create_response() struktur
            if (response.ok && result.success) {
                // Suksess! API-en set HTTP-only cookie, så vi kan videresende brukaren direkte
                // Endre 'admin.php' til filnamnet på administrasjonssida di dersom den heiter noko anna
                window.location.href = './';
            } else {
                // Feil i innlogging (f.eks. 401 Unauthorized eller 400 Bad Request)
                const errorMsg = result.message || 'Feil brukarnamn eller passord.';
                showError(errorMsg);
                passwordInput.value = ''; // Tømmer passordfeltet ved feil
                passwordInput.focus();
            }

        } catch (error) {
            // Nettverksfeil eller serverfeil
            console.error('Login error:', error);
            showError('Klarte ikkje å kople til serveren. Prøv igjen seinare.');
        } finally {
            // Gjenaktiver knappen
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });

    // Hjelpefunksjon for å vise Bootstrap alert-feilmeldingar
    function showError(message) {
        feedbackEl.innerHTML = message;
        feedbackEl.className = 'alert alert-danger small py-2 mb-3';
    }
});