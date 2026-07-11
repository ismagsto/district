/* =====================
   JAVASCRIPT GENERAL 
   ---------------------
   Funciones globales de la tienda como:
   - Toasts
   - Carrito
   - Favoritos
   - Catálogo
   - Tema claro/oscuro
   ====================== */

const appBase = document.body.dataset.base || '/tienda';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';

    return div.innerHTML;
}

/* TOASTS */

function showToast(message, type = 'success') {
    const toastArea = document.getElementById('toastArea');

    if (!toastArea) {
        alert(message);
        return;
    }

    const toastId = `toast-${Date.now()}`;
    const bgClass = type === 'danger'
        ? 'text-bg-danger'
        : type === 'warning'
            ? 'text-bg-warning'
            : 'text-bg-success';

    toastArea.insertAdjacentHTML('beforeend', `
        <div id="${toastId}" class="toast ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${escapeHtml(message)}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    `);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}

/* Función para enviar datos sin recargar la página */

async function postForm(url, data) {
    const payload = { ...data };
    const csrfToken = getCsrfToken();

    if (csrfToken && !payload.csrf_token) {
        payload.csrf_token = csrfToken;
    }

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(payload)
    });

    return response.json();
}

/* CARRITO */

async function addToCart(button) {
    const form = button.closest('form');

    if (!form) {
        showToast('No se ha encontrado el formulario del producto.', 'danger');
        return;
    }

    const data = new FormData(form);

    button.disabled = true;

    try {
        const json = await postForm(`${appBase}/api/cart.php`, Object.fromEntries(data.entries()));

        if (json.ok) {
            const count = json.cart_count ?? json.count ?? 0;

            document.querySelectorAll('.cart-count').forEach((item) => {
                item.textContent = count;
            });

            showToast(json.message, 'success');
        } else {
            showToast(json.message || 'No se pudo añadir al carrito', 'warning');
        }
    } catch (error) {
        showToast('Error de comunicación con el servidor', 'danger');
    } finally {
        button.disabled = false;
    }
}

function handleCartSubmit(event) {
    const form = event.target.closest('form');
    const cartButton = form?.querySelector('[data-add-cart]');

    if (!cartButton) {
        return;
    }

    event.preventDefault();
    addToCart(cartButton);
}

/* FAVORITOS */

async function toggleFavorite(button) {
    const productId = button.dataset.product || button.dataset.productId || '';

    if (!productId) {
        showToast('No se ha encontrado el producto.', 'danger');
        return;
    }

    try {
        const json = await postForm(`${appBase}/api/favorite.php`, {
            product_id: productId,
            producto_id: productId
        });

        if (json.ok) {
            button.classList.toggle('btn-accent', json.active);
            button.classList.toggle('btn-outline-light', !json.active);

            const icon = button.querySelector('i');

            if (icon) {
                icon.classList.toggle('fa-solid', json.active);
                icon.classList.toggle('fa-regular', !json.active);
            }

            button.setAttribute('aria-pressed', json.active ? 'true' : 'false');
            showToast(json.message, 'success');
        } else {
            showToast(json.message || 'No se pudo actualizar favoritos', 'warning');
        }
    } catch (error) {
        showToast('No se pudo actualizar favoritos', 'danger');
    }
}

/* CATÁLOGO */

function initCatalogFilters() {
    const form = document.getElementById('filtersForm');
    const statusText = document.getElementById('catalogStatusText');

    if (!form) {
        return;
    }

    form.addEventListener('submit', () => {
        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Buscando';
        }

        if (statusText) {
            statusText.textContent = 'Aplicando filtros...';
        }
    });
}

/* TEMA CLARO / OSCURO */

function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    const toggle = document.getElementById('themeToggle');

    document.documentElement.setAttribute('data-theme', savedTheme);
    document.body.dataset.theme = savedTheme;

    if (!toggle) {
        return;
    }

    toggle.setAttribute('aria-pressed', savedTheme === 'light' ? 'true' : 'false');

    toggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', nextTheme);
        document.body.dataset.theme = nextTheme;
        localStorage.setItem('theme', nextTheme);
        toggle.setAttribute('aria-pressed', nextTheme === 'light' ? 'true' : 'false');
    });
}

/* CANTIDADES Y VARIANTES */

function updateQuantity(button) {
    const control = button.closest('.quantity-control');
    const input = control?.querySelector('input[type="number"]');

    if (!input || input.disabled) {
        return;
    }

    const min = Number(input.min || 1);
    const max = Number(input.max || 99);
    const current = Number(input.value || min);
    const direction = button.dataset.qtyAction === 'plus' ? 1 : -1;
    const nextValue = Math.min(max, Math.max(min, current + direction));

    input.value = String(nextValue);
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

function initVariantStockLimit() {
    const variantSelect = document.querySelector('select[name="variant"]');
    const quantityInput = document.querySelector('.quantity-control input[name="cantidad"]');

    if (!variantSelect || !quantityInput) {
        return;
    }

    const syncLimit = () => {
        const selected = variantSelect.options[variantSelect.selectedIndex];
        const stock = Number(selected?.dataset.stock || quantityInput.max || 1);
        const max = Math.max(1, stock);

        quantityInput.max = String(max);

        if (Number(quantityInput.value || 1) > max) {
            quantityInput.value = String(max);
        }
    };

    variantSelect.addEventListener('change', syncLimit);
    syncLimit();
}

/* CONFIRMACIONES */

async function confirmFormSubmit(event) {
    const form = event.target.closest('form[data-confirm]');

    if (!form || form.dataset.confirmed === 'true') {
        return;
    }

    event.preventDefault();

    const message = form.dataset.confirm || '¿Confirmar la acción?';
    let confirmed = false;

    if (window.Swal) {
        const result = await Swal.fire({
            title: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí',
            cancelButtonText: 'Cancelar'
        });

        confirmed = result.isConfirmed;
    } else {
        confirmed = window.confirm(message);
    }

    if (confirmed) {
        form.dataset.confirmed = 'true';

        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }
}

/* EVENTOS GLOBALES */

document.addEventListener('click', (event) => {
    const cartButton = event.target.closest('[data-add-cart]');
    const favoriteButton = event.target.closest('[data-favorite]');
    const quantityButton = event.target.closest('[data-qty-action]');

    if (quantityButton) {
        event.preventDefault();
        updateQuantity(quantityButton);
    }

    if (cartButton) {
        event.preventDefault();
        addToCart(cartButton);
    }

    if (favoriteButton) {
        event.preventDefault();
        toggleFavorite(favoriteButton);
    }
});

document.addEventListener('submit', confirmFormSubmit);
document.addEventListener('submit', handleCartSubmit);

document.addEventListener('DOMContentLoaded', function () {
    const filtersForm = document.getElementById('filtersForm');
    const categorySelect = document.getElementById('categoria');
    const categoryButtons = document.querySelectorAll('.category-pill[data-category]');

    if (!filtersForm || !categorySelect || categoryButtons.length === 0) {
        return;
    }

    categoryButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            categorySelect.value = button.dataset.category || '';

            if (filtersForm.requestSubmit) {
                filtersForm.requestSubmit();
            } else {
                filtersForm.submit();
            }
        });
    });
});

/* INICIO */

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initCatalogFilters();
    initVariantStockLimit();
});
