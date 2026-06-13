/**
 * MAB Shop - Main JavaScript
 * Theme toggle, cart, search, chat assistant, and AJAX interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initSearchAutocomplete();
    initCartActions();
    initChatWidget();
    initNewsletter();
    initPasswordToggles();
    registerServiceWorker();
});

/** Dark/Light mode toggle with localStorage persistence */
function initThemeToggle() {
    const toggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    const saved = localStorage.getItem('mab_theme') || html.dataset.theme || 'light';
    setTheme(saved);

    toggle?.addEventListener('click', () => {
        const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
        setTheme(next);
        localStorage.setItem('mab_theme', next);
        // Sync to server if logged in
        fetch(`${APP_URL}/api/user.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'toggle_theme', theme: next })
        }).catch(() => {});
    });
}

function setTheme(theme) {
    document.documentElement.dataset.theme = theme;
    const icon = document.querySelector('#themeToggle i');
    if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
}

/** Search autocomplete suggestions */
function initSearchAutocomplete() {
    const input = document.getElementById('searchInput');
    const suggestions = document.getElementById('searchSuggestions');
    if (!input || !suggestions) return;

    let debounce;
    input.addEventListener('input', () => {
        clearTimeout(debounce);
        const q = input.value.trim();
        if (q.length < 2) { suggestions.classList.remove('show'); return; }

        debounce = setTimeout(async () => {
            try {
                const res = await fetch(`${APP_URL}/api/search.php?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                if (data.suggestions?.length) {
                    suggestions.innerHTML = data.suggestions.map(s =>
                        `<a class="search-suggestion-item d-block text-decoration-none text-body" href="${APP_URL}/product.php?slug=${s.slug}">
                            <strong>${escapeHtml(s.name)}</strong> <span class="text-muted">${s.price}</span>
                        </a>`
                    ).join('');
                    suggestions.classList.add('show');
                } else {
                    suggestions.classList.remove('show');
                }
            } catch (e) { suggestions.classList.remove('show'); }
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.classList.remove('show');
        }
    });
}

/** Cart, wishlist, and quick view actions */
function initCartActions() {
    document.body.addEventListener('click', async (e) => {
        const addBtn = e.target.closest('.add-to-cart-btn');
        const wishBtn = e.target.closest('.wishlist-btn');
        const quickBtn = e.target.closest('.quick-view-btn');

        if (addBtn) {
            e.preventDefault();
            if (addBtn.disabled || addBtn.dataset.loading === '1') return;

            const id = addBtn.dataset.id;
            const qtyInput = addBtn.dataset.quantityInput ? document.getElementById(addBtn.dataset.quantityInput) : null;
            const quantity = Math.max(1, parseInt(qtyInput?.value || addBtn.dataset.quantity || '1', 10) || 1);
            setButtonLoading(addBtn, true);

            try {
                const res = await apiPost('cart.php', { action: 'add', product_id: id, quantity });
                showToast(res.message || (res.success ? 'Added to cart.' : 'Unable to add to cart.'), res.success ? 'success' : 'danger');
                if (res.count !== undefined) updateCartBadge(res.count);
            } catch (error) {
                showToast(error.message || 'Unable to add to cart. Please try again.', 'danger');
            } finally {
                setButtonLoading(addBtn, false);
            }
        }

        if (wishBtn) {
            e.preventDefault();
            if (wishBtn.disabled || wishBtn.dataset.loading === '1') return;
            setButtonLoading(wishBtn, true);
            try {
                const res = await apiPost('wishlist.php', { action: 'add', product_id: wishBtn.dataset.id });
                showToast(res.message || 'Wishlist updated.', res.success ? 'success' : 'info');
                setButtonLoading(wishBtn, false);
                wishBtn.querySelector('i')?.classList.toggle('bi-heart-fill', !!res.success);
            } catch (error) {
                showToast(error.message || 'Unable to update wishlist.', 'danger');
                setButtonLoading(wishBtn, false);
            }
        }

        if (quickBtn) {
            e.preventDefault();
            const slug = quickBtn.dataset.slug;
            try {
                const res = await fetch(`${APP_URL}/api/product.php?slug=${encodeURIComponent(slug)}`);
                const data = await parseJsonResponse(res);
                if (data.product) showQuickViewModal(data.product);
            } catch (error) {
                showToast(error.message || 'Unable to load product preview.', 'danger');
            }
        }
    });
}

/** AI Chat assistant widget */
function initChatWidget() {
    const toggle = document.getElementById('chatToggle');
    const panel = document.getElementById('chatPanel');
    const close = document.getElementById('chatClose');
    const form = document.getElementById('chatForm');
    const messages = document.getElementById('chatMessages');
    const input = document.getElementById('chatInput');

    const setOpen = (open) => {
        panel?.classList.toggle('open', open);
        panel?.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) setTimeout(() => input?.focus(), 50);
    };

    toggle?.addEventListener('click', () => setOpen(!panel?.classList.contains('open')));
    close?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && panel?.classList.contains('open')) setOpen(false);
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;

        appendChatMessage(msg, 'user');
        input.value = '';
        input.disabled = true;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        const pending = appendChatMessage('Thinking...', 'bot status');

        try {
            const res = await apiPost('chat.php', { message: msg });
            pending.remove();
            appendChatMessage(res.reply || 'I am here and ready to help.', 'bot');
            if (res.products?.length) {
                const links = res.products.slice(0, 3).map(p =>
                    `<a href="${APP_URL}/product.php?slug=${p.slug}" class="d-block small">${escapeHtml(p.name)} - ${p.price}</a>`
                ).join('');
                appendChatMessage(links, 'bot');
            }
        } catch (error) {
            pending.remove();
            appendChatMessage(error.message || 'Sorry, I had trouble processing that. Please try again.', 'bot');
        } finally {
            input.disabled = false;
            submitBtn.disabled = false;
            input.focus();
        }
    });
}

function appendChatMessage(text, type) {
    const div = document.createElement('div');
    div.className = `chat-msg ${type}`;
    div.innerHTML = text;
    document.getElementById('chatMessages')?.appendChild(div);
    div.scrollIntoView({ behavior: 'smooth' });
    return div;
}

/** Newsletter subscription */
function initNewsletter() {
    document.getElementById('newsletterForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = e.target.email.value;
        const button = e.target.querySelector('button[type="submit"]');
        setButtonLoading(button, true);
        try {
            const res = await apiPost('newsletter.php', { email });
            showToast(res.message, res.success ? 'success' : 'danger');
            if (res.success) e.target.reset();
        } catch (error) {
            showToast(error.message || 'Unable to subscribe right now.', 'danger');
        } finally {
            setButtonLoading(button, false);
        }
    });
}

/** Password visibility toggle */
function initPasswordToggles() {
    document.querySelectorAll('.password-field').forEach((field) => {
        const input = field.querySelector('input[type="password"], input[type="text"]');
        const button = field.querySelector('.password-toggle');
        const icon = button?.querySelector('i');
        if (!input || !button || !icon) return;

        button.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            input.focus();
        });
    });
}

/** Quick view modal */
function showQuickViewModal(product) {
    let modal = document.getElementById('quickViewModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'quickViewModal';
        modal.className = 'modal fade';
        modal.innerHTML = `<div class="modal-dialog modal-lg"><div class="modal-content" id="quickViewContent"></div></div>`;
        document.body.appendChild(modal);
    }
    document.getElementById('quickViewContent').innerHTML = `
        <div class="modal-header"><h5 class="modal-title">${escapeHtml(product.name)}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body row g-4">
            <div class="col-md-6"><img src="${APP_URL}/${product.image || 'assets/images/placeholder.svg'}" class="img-fluid rounded" alt=""></div>
            <div class="col-md-6">
                <p class="text-primary fs-4 fw-bold">${product.price_formatted}</p>
                <p>${escapeHtml(product.short_description || '')}</p>
                <button class="btn btn-primary add-to-cart-btn" data-id="${product.id}"><i class="bi bi-cart-plus"></i> Add to Cart</button>
                <a href="${APP_URL}/product.php?slug=${product.slug}" class="btn btn-outline-primary ms-2">View Details</a>
            </div>
        </div>`;
    new bootstrap.Modal(modal).show();
}

/** PWA service worker registration */
function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(`${APP_URL}/sw.js`).catch(() => {});
    }
}

/** Utility: API POST helper */
async function apiPost(endpoint, data) {
    const res = await fetch(`${APP_URL}/api/${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(data)
    });
    return parseJsonResponse(res);
}

async function parseJsonResponse(res) {
    let data;
    try {
        data = await res.json();
    } catch {
        throw new Error('The server returned an unexpected response.');
    }
    if (!res.ok || data.success === false) {
        throw new Error(data.message || 'Request failed. Please try again.');
    }
    return data;
}

function updateCartBadge(count) {
    const badge = document.getElementById('cartCountBadge');
    if (badge) badge.textContent = count;
    const cart = document.getElementById('stickyCart');
    cart?.classList.remove('cart-bump');
    void cart?.offsetWidth;
    cart?.classList.add('cart-bump');
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer') || (() => {
        const c = document.createElement('div');
        c.id = 'toastContainer';
        c.className = 'position-fixed bottom-0 end-0 p-3';
        c.style.zIndex = '1100';
        document.body.appendChild(c);
        return c;
    })();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 show`;
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${escapeHtml(message)}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function setButtonLoading(button, loading) {
    if (!button) return;
    if (loading) {
        button.dataset.loading = '1';
        button.dataset.originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>';
    } else {
        button.dataset.loading = '0';
        button.disabled = false;
        if (button.dataset.originalHtml) button.innerHTML = button.dataset.originalHtml;
    }
}
