<?php

require_once __DIR__ . '/php/config.php';

$pageTitle = 'Carrito';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (isset($_POST['remove_key'])) {
        unset($_SESSION['cart'][$_POST['remove_key']]);
        flash('success', 'Producto eliminado del carrito.');
        redirect('carrito.php');
    }

    if (isset($_POST['quantity'], $_POST['key'])) {
        $key = $_POST['key'];
        $quantity = max(1, (int) $_POST['quantity']);

        if (isset($_SESSION['cart'][$key])) {
            $item = $_SESSION['cart'][$key];
            $available = stock_available(
                $pdo,
                (int) $item['producto_id'],
                (string) $item['talla'],
                (string) ($item['color'] ?? '')
            );

            if ($available <= 0) {
                unset($_SESSION['cart'][$key]);
                flash('warning', 'Ese producto ya no tiene stock y se ha eliminado del carrito.');
            } elseif ($quantity > $available) {
                $_SESSION['cart'][$key]['cantidad'] = $available;
                flash('warning', 'Cantidad ajustada al stock disponible.');
            } else {
                $_SESSION['cart'][$key]['cantidad'] = $quantity;
                flash('success', 'Cantidad actualizada.');
            }
        }

        redirect('carrito.php');
    }
}

$cart = $_SESSION['cart'] ?? [];
$subtotal = get_cart_total();
$shipping = $subtotal >= 60 || $subtotal == 0 ? 0 : 4.95;
$total = $subtotal + $shipping;

require_once __DIR__ . '/php/header.php';
?>

<section class="container">
    <h1 class="fw-black display-5 mb-4">Carrito</h1>

    <?php if (!$cart): ?>
        <div class="surface-card p-5 text-center">
            <h2 class="h4">Tu carrito está vacío</h2>
            <p class="muted-text">Añade productos desde el catálogo para continuar.</p>
            <a class="btn btn-accent" href="<?= url('catalogo.php') ?>">Ver catálogo</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="surface-card p-3 p-lg-4">
                    <?php foreach ($cart as $key => $item): ?>
                        <div class="row g-3 align-items-center border-bottom border-secondary py-3">
                            <div class="col-3 col-md-2">
                                <img class="rounded-4" src="<?= e(asset_url($item['imagen'] ?? '')) ?>" alt="<?= e($item['nombre']) ?>">
                            </div>

                            <div class="col-9 col-md-4">
                                <h2 class="h6 mb-1"><?= e($item['nombre']) ?></h2>
                                <span class="muted-text small">
                                    <?= e($item['talla']) ?>
                                    <?php if (!empty($item['color'])): ?>
                                        &middot; <?= e($item['color']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="col-md-3">
                                <form method="post" class="d-flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="key" value="<?= e($key) ?>">
                                    <input class="form-control" name="quantity" type="number" value="<?= (int) $item['cantidad'] ?>" min="1">
                                    <button class="btn btn-outline-light" type="submit">OK</button>
                                </form>
                            </div>

                            <div class="col-md-2 fw-bold">
                                <?= money((float) $item['precio'] * (int) $item['cantidad']) ?>
                            </div>

                            <div class="col-md-1 text-md-end">
                                <form method="post" data-confirm="¿Eliminar este producto?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="remove_key" value="<?= e($key) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="surface-card p-4">
                    <h2 class="h4 fw-bold">Resumen</h2>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Envío</span><strong><?= $shipping == 0 ? 'Gratis' : money($shipping) ?></strong></div>
                    <hr>
                    <div class="d-flex justify-content-between fs-4"><span>Total</span><strong><?= money($total) ?></strong></div>
                    <a class="btn btn-accent w-100 mt-4" href="<?= url('checkout.php') ?>">Finalizar compra</a>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
