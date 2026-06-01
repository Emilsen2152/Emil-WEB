document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const feedbackEl = document.getElementById('login-feedback');
    const submitBtn = document.getElementById('login-submit-btn');

    if (!loginForm) return;

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        const username = usernameInput.value.trim();
        const password = passwordInput.value;

        feedbackEl.className = 'd-none mb-3';
        feedbackEl.innerHTML = '';
        submitBtn.disabled = true;
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Logger inn...';

        try {
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

            if (response.ok && result.success) {
                window.location.href = './';
            } else {
                const errorMsg = result.message || 'Feil brukarnamn eller passord.';
                showError(errorMsg);
                passwordInput.value = '';
                passwordInput.focus();
            }

        } catch (error) {
            console.error('Login error:', error);
            showError('Klarte ikkje å kople til serveren. Prøv igjen seinare.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });

    function showError(message) {
        feedbackEl.innerHTML = message;
        feedbackEl.className = 'alert alert-danger small py-2 mb-3';
    }
});