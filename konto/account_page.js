const logoutButton = document.getElementById('logout-btn');
const { logout_account } = await import('./local_account.js');

logoutButton.addEventListener('click', async (event) => {
    event.preventDefault();
    const result = await logout_account();

    if (result.ok) {
        window.location.href = './';
        return;
    }
    alert(result.message || 'Feil ved utlogging.');
});