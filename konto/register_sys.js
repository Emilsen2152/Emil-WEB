const form = document.getElementById('register-form');
const { register_account } = await import('./local_account.js');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const username = form.username.value.trim();
    const password = form.password.value;

    const result = await register_account(username, password);

    if (result.ok) {
        window.location.href = './';
        return;
    }

    // Vis “Brukarnamn eksisterer allereie.” når status=409
    alert(result.message || 'Feil ved registrering.');
});
