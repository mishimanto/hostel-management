const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const formatMoney = (value) => `BDT ${Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
})}`;

const toast = (message, type = 'success') => {
    const wrapper = document.createElement('div');
    wrapper.className = `fixed right-4 top-4 z-50 rounded-md px-4 py-3 text-sm font-medium text-white shadow-lg ${
        type === 'error' ? 'bg-red-600' : 'bg-teal-600'
    }`;
    wrapper.textContent = message;
    document.body.appendChild(wrapper);
    setTimeout(() => wrapper.remove(), 3200);
};

const parseErrors = async (response) => {
    const payload = await response.json().catch(() => ({}));
    if (payload.errors) {
        return Object.values(payload.errors).flat().join(' ');
    }
    return payload.message || 'Something went wrong.';
};

const submitAjaxForm = async (form) => {
    const method = form.dataset.ajax?.toUpperCase() || 'POST';
    const formData = new FormData(form);
    if (method !== 'POST') {
        formData.append('_method', method);
    }

    const response = await fetch(form.action, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: formData,
    });

    if (!response.ok) {
        throw new Error(await parseErrors(response));
    }

    return response.json();
};

document.querySelectorAll('form[data-ajax]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"], button:not([type])');
        const original = button?.textContent;

        if (button) {
            button.disabled = true;
            button.textContent = 'Saving...';
        }

        try {
            const payload = await submitAjaxForm(form);
            toast(payload.message || 'Saved successfully.');
            if (window.Swal) {
                Swal.fire({ icon: 'success', title: 'Done', text: payload.message || 'Saved successfully.', timer: 1800, showConfirmButton: false });
            }
            if (form.id !== 'profileForm') {
                setTimeout(() => window.location.reload(), 900);
            }
        } catch (error) {
            toast(error.message, 'error');
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Please check', text: error.message });
            }
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = original;
            }
        }
    });
});

document.getElementById('seatSelect')?.addEventListener('change', async (event) => {
    const preview = document.getElementById('seatPreview');
    const seatId = event.target.value;

    preview.classList.add('hidden');
    preview.innerHTML = '';

    if (!seatId) {
        return;
    }

    const url = new URL(event.target.dataset.calculateUrl, window.location.origin);
    url.searchParams.set('seat_id', seatId);

    const response = await fetch(url, { headers: { Accept: 'application/json' } });
    const data = await response.json();

    preview.innerHTML = `
        <div class="grid gap-2 sm:grid-cols-2">
            <p><strong>Request type:</strong> ${data.type.replace('_', ' ')}</p>
            <p><strong>Requested:</strong> ${data.requested_label}</p>
            <p><strong>Current rent:</strong> ${formatMoney(data.current_rent)}</p>
            <p><strong>Requested rent:</strong> ${formatMoney(data.requested_rent)}</p>
            <p><strong>Payable now:</strong> ${formatMoney(data.payable_amount)}</p>
            <p><strong>Credit next rent:</strong> ${formatMoney(data.credit_to_next_rent)}</p>
        </div>
    `;
    preview.classList.remove('hidden');
});

document.getElementById('exitDate')?.addEventListener('change', async (event) => {
    const preview = document.getElementById('exitPreview');
    const exitDate = event.target.value;

    preview.classList.add('hidden');
    preview.innerHTML = '';

    if (!exitDate) {
        return;
    }

    const url = new URL(event.target.dataset.calculateUrl, window.location.origin);
    url.searchParams.set('requested_exit_date', exitDate);

    const response = await fetch(url, { headers: { Accept: 'application/json' } });
    const data = await response.json();

    preview.innerHTML = `
        <p><strong>Notice days:</strong> ${data.notice_days} (${data.notice_valid ? 'valid' : 'minimum 30 days required'})</p>
        <p><strong>Rent due:</strong> ${formatMoney(data.rent_due)}</p>
        <p><strong>Deposit adjustment:</strong> ${formatMoney(data.deposit_adjustment)}</p>
        <p><strong>Balance adjustment:</strong> ${formatMoney(data.balance_adjustment)}</p>
        <p><strong>Final payable:</strong> ${formatMoney(data.final_payable)}</p>
        <p><strong>Final refundable:</strong> ${formatMoney(data.final_refundable)}</p>
    `;
    preview.classList.remove('hidden');
});

const renderNotifications = (items = []) => {
    const list = document.getElementById('notificationList');
    if (!list) {
        return;
    }

    list.innerHTML = items.map((item) => `
        <div class="rounded-md border border-zinc-200 p-3 ${item.read_at ? '' : 'bg-teal-50'}">
            <p class="font-medium">${item.title}</p>
            <p class="mt-1 text-sm text-zinc-500">${item.body}</p>
        </div>
    `).join('');
};

const pollNotifications = async () => {
    if (!window.hostelRoutes?.notifications) {
        return;
    }

    const response = await fetch(window.hostelRoutes.notifications, { headers: { Accept: 'application/json' } });
    const payload = await response.json();
    document.getElementById('notificationCount').textContent = payload.unread_count;
    renderNotifications(payload.notifications);
};

document.getElementById('markNotifications')?.addEventListener('click', async () => {
    await fetch(window.hostelRoutes.markNotifications, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });
    await pollNotifications();
    toast('Notifications marked as read.');
});

pollNotifications();
setInterval(pollNotifications, 15000);

if (window.lucide) {
    lucide.createIcons();
}
