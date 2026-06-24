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
    const text = await response.text();
    let payload = {};

    try {
        payload = text ? JSON.parse(text) : {};
    } catch {
        return 'Server returned an HTML error page. Please refresh and try again.';
    }

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

    const text = await response.text();
    return text ? JSON.parse(text) : {};
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

const refreshRoomChangePreview = async () => {
    const roomSelect = document.getElementById('roomSelect');
    const bookingInput = document.getElementById('roomBookingSelect');
    const preview = document.getElementById('roomPreview');
    const bookingId = bookingInput?.value;
    const roomId = roomSelect?.value;

    preview.classList.add('hidden');
    preview.innerHTML = '';

    if (!bookingId || !roomId) {
        return;
    }

    const url = new URL(roomSelect.dataset.calculateUrl, window.location.origin);
    url.searchParams.set('room_booking_id', bookingId);
    url.searchParams.set('room_id', roomId);

    const response = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!response.ok) {
        const message = await parseErrors(response);
        toast(message, 'error');
        if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Room change unavailable', text: message });
        }
        return;
    }
    const data = await response.json();

    preview.innerHTML = `
        <div class="grid gap-2 sm:grid-cols-2">
            <p><strong>Requested:</strong> ${data.requested_label}</p>
            <p><strong>System change date:</strong> ${data.change_date}</p>
            <p><strong>Old rent:</strong> ${formatMoney(data.old_monthly_rent)}</p>
            <p><strong>New rent:</strong> ${formatMoney(data.new_monthly_rent)}</p>
            <p><strong>Remaining paid days:</strong> ${data.remaining_paid_days}</p>
            <p><strong>Additional payable:</strong> ${formatMoney(data.additional_payable)}</p>
            <p><strong>Extra days:</strong> ${data.extra_days}</p>
            <p><strong>New paid until:</strong> ${data.new_paid_until}</p>
        </div>
    `;
    preview.classList.remove('hidden');
};

document.getElementById('roomSelect')?.addEventListener('change', refreshRoomChangePreview);
document.getElementById('roomBookingSelect')?.addEventListener('change', refreshRoomChangePreview);

const syncLeaveDateRange = () => {
    const bookingSelect = document.getElementById('leaveBookingSelect');
    const leaveDateInput = document.getElementById('leaveDate');
    const hint = document.getElementById('leaveDateHint');
    const option = bookingSelect?.selectedOptions?.[0];
    const start = option?.dataset.start || '';
    const end = option?.dataset.end || '';

    if (!leaveDateInput) {
        return;
    }

    leaveDateInput.value = '';
    leaveDateInput.min = start;
    leaveDateInput.max = end;

    if (hint) {
        hint.textContent = start && end
            ? `Allowed leave range: ${start} to ${end}.`
            : 'Select a booking first. Leave dates must stay inside that booking range.';
    }
};

document.getElementById('leaveBookingSelect')?.addEventListener('change', syncLeaveDateRange);
syncLeaveDateRange();

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
