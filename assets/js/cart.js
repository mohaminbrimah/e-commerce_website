/**
 * MAB Shop - Cart Page JavaScript
 * Quantity updates, remove, save for later, coupon application
 */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.cart-item').forEach(item => {
        const id = item.dataset.id;

        item.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = item.querySelector('.qty-input');
                const newQty = Math.max(1, (parseInt(input.value, 10) || 1) + parseInt(btn.dataset.delta, 10));
                updateCart(id, newQty, item);
            });
        });

        item.querySelector('.qty-input')?.addEventListener('change', (e) => {
            updateCart(id, Math.max(1, parseInt(e.target.value, 10) || 1), item);
        });

        item.querySelector('.remove-btn')?.addEventListener('click', () => removeItem(id));
        item.querySelector('.save-later-btn')?.addEventListener('click', () => saveLater(id));
    });

    document.querySelectorAll('.saved-item').forEach(item => {
        item.querySelector('.move-cart-btn')?.addEventListener('click', async () => {
            try {
                await apiPost('cart.php', { action: 'move_to_cart', cart_item_id: item.dataset.id });
                location.reload();
            } catch (error) {
                showToast(error.message || 'Unable to move item to cart.', 'danger');
            }
        });
    });

    document.getElementById('couponForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const button = e.target.querySelector('button[type="submit"]');
        button.disabled = true;

        try {
            const res = await apiPost('cart.php', { action: 'apply_coupon', code: e.target.code.value });
            showToast(res.message || 'Coupon applied.', 'success');
            if (res.totals) {
                document.getElementById('discount').textContent = '-' + formatMoney(res.totals.discount);
                document.getElementById('tax').textContent = formatMoney(res.totals.tax);
                document.getElementById('shipping').textContent = res.totals.shipping > 0 ? formatMoney(res.totals.shipping) : 'FREE';
                document.getElementById('total').textContent = formatMoney(res.totals.total);
            }
        } catch (error) {
            showToast(error.message || 'Unable to apply coupon.', 'danger');
        } finally {
            button.disabled = false;
        }
    });
});

async function updateCart(id, qty, el) {
    el.classList.add('opacity-75');

    try {
        await apiPost('cart.php', { action: 'update', cart_item_id: id, quantity: qty });
        el.querySelector('.qty-input').value = qty;
        location.reload();
    } catch (error) {
        showToast(error.message || 'Unable to update cart.', 'danger');
        el.classList.remove('opacity-75');
    }
}

async function removeItem(id) {
    try {
        await apiPost('cart.php', { action: 'remove', cart_item_id: id });
        location.reload();
    } catch (error) {
        showToast(error.message || 'Unable to remove item.', 'danger');
    }
}

async function saveLater(id) {
    try {
        await apiPost('cart.php', { action: 'save_later', cart_item_id: id });
        location.reload();
    } catch (error) {
        showToast(error.message || 'Unable to save item for later.', 'danger');
    }
}

function formatMoney(n) {
    return 'GH₵' + parseFloat(n || 0).toFixed(2);
}
