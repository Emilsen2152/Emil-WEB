async function api_json(response) {
    // Prøv å lese JSON uansett status, så du får "message" frå backend
    let data = null;
    try {
        data = await response.json();
    } catch {
        // ignorér dersom server ikkje sendte JSON
    }
    return data;
}

async function register_account(username, password) {
    const response = await fetch('/api/users', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    const data = await api_json(response);

    if (!response.ok) {
        // returnér strukturert feil i staden for å kaste generisk
        return {
            ok: false,
            status: response.status,
            message: data?.message || 'Ukjent feil ved registrering.'
        };
    }

    return { ok: true, status: response.status, ...data };
}

async function login_account(username, password) {
    const response = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    const data = await api_json(response);

    if (!response.ok) {
        return {
            ok: false,
            status: response.status,
            message: data?.message || 'Ugyldig brukarnamn eller passord.'
        };
    }

    return { ok: true, status: response.status, ...data };
}

async function logout_account() {
    const response = await fetch('/api/logout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    });

    const data = await api_json(response);

    if (!response.ok) {
        return {
            ok: false,
            status: response.status,
            message: data?.message || 'Klarte ikkje å logge ut.'
        };
    }

    return { ok: true, status: response.status, ...data };
}

export { register_account, login_account, logout_account };
