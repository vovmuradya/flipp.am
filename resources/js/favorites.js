document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-favorite-toggle]').forEach((form) => {
        if (form.dataset.favoriteReady === '1') {
            return;
        }

        form.dataset.favoriteReady = '1';
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('button[type=\"submit\"]');
            if (button) {
                button.disabled = true;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('input[name=\"_token\"]').value,
                    },
                    body: new FormData(form),
                });

                const payload = await response.json();
                const icon = form.querySelector('i');

                if (payload && typeof payload.favorited !== 'undefined' && icon) {
                    icon.classList.toggle('fa-solid', payload.favorited);
                    icon.classList.toggle('fa-regular', !payload.favorited);
                }
            } catch (error) {
                console.error('Favorite toggle failed', error);
            } finally {
                if (button) {
                    button.disabled = false;
                }
            }
        });
    });
});
