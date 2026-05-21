const createListForm = document.getElementById('create-list-form');
const createListAlert = document.getElementById('create-list-alert');

if (createListForm) {
    createListForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        createListAlert.classList.add('d-none');

        const formData = new FormData(createListForm);
        const name = String(formData.get('name') || '').trim();
        const isPrivate = formData.get('private') ? true : false;

        if (!name) return;

        try {
            const res = await fetch('api/to-do-lists', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: name,
                    private: isPrivate
                })
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || 'Feil ved oppretting.');
            }

            // Redirect til ny liste
            const listId = data.data.list.id;
            window.location.href = `to_do_list/${listId}`;

        } catch (err) {
            createListAlert.classList.remove('d-none');
            createListAlert.classList.add('alert-danger');
            createListAlert.textContent = err.message;
        }
    });
}