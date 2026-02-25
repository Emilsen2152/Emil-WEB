const form = document.getElementById('login-form');
const { login_account } = await import('./local_account.js');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const username = form.username.value.trim();
    const password = form.password.value;

    const result = await login_account(username, password);

    if (result.ok) {
        window.location.href = './';
        return;
    }

    // Vis “Brukarnamn eksisterer allereie.” når status=409
    alert(result.message || 'Feil ved innlogging.');
});
