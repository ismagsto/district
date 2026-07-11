<?php

require_once __DIR__ . '/php/config.php';
require_login();

if (empty($_SESSION['cart'])) {
    flash('warning', 'Tu carrito está vacío.');
    redirect('carrito.php');
}

$userId = current_user()['id'];
$userStmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

$cart = $_SESSION['cart'];
$subtotal = get_cart_total();
$shipping = $subtotal >= 60 ? 0 : 4.95;
$discount = 0;
$couponCode = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $couponCode = strtoupper(trim($_POST['cupon'] ?? '')) ?: null;

    if ($couponCode) {
        $couponStmt = $pdo->prepare('SELECT * FROM cupones WHERE codigo = ? AND activo = 1 AND (caduca_en IS NULL OR caduca_en >= CURDATE())');
        $couponStmt->execute([$couponCode]);
        $coupon = $couponStmt->fetch();

        if ($coupon && $subtotal >= (float) $coupon['minimo']) {
            $discount = $coupon['tipo'] === 'porcentaje'
                ? $subtotal * ((float) $coupon['valor'] / 100)
                : (float) $coupon['valor'];
            $discount = min($discount, $subtotal);
        } elseif ($couponCode) {
            flash('warning', 'Cupón no válido o no alcanza el mínimo.');
        }
    }

    if (isset($_POST['confirm_order'])) {
        $required = ['nombre_envio', 'email_envio', 'telefono_envio', 'direccion_envio', 'ciudad_envio', 'provincia_envio', 'cp_envio'];

        foreach ($required as $field) {
            if (trim($_POST[$field] ?? '') === '') {
                flash('danger', 'Completa todos los datos de envío.');
                redirect('checkout.php');
            }
        }

        try {
            $pdo->beginTransaction();

            foreach ($cart as $item) {
                $color = trim((string) ($item['color'] ?? ''));

                if ($color === '') {
                    throw new RuntimeException('Vuelve a añadir "' . $item['nombre'] . '" al carrito para seleccionar color.');
                }

                $available = stock_available(
                    $pdo,
                    (int) $item['producto_id'],
                    (string) $item['talla'],
                    $color
                );

                if ($available < (int) $item['cantidad']) {
                    throw new RuntimeException('Stock insuficiente para ' . $item['nombre']);
                }
            }

            $total = max(0, $subtotal - $discount) + $shipping;
            $number = 'PED-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $orderStmt = $pdo->prepare('
                INSERT INTO pedidos
                (usuario_id, numero, estado, nombre_envio, email_envio, telefono_envio, direccion_envio, ciudad_envio, provincia_envio, cp_envio, metodo_pago, subtotal, descuento, envio, total, cupon_codigo, notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $orderStmt->execute([
                $userId,
                $number,
                'pagado',
                trim($_POST['nombre_envio']),
                trim($_POST['email_envio']),
                trim($_POST['telefono_envio']),
                trim($_POST['direccion_envio']),
                trim($_POST['ciudad_envio']),
                trim($_POST['provincia_envio']),
                trim($_POST['cp_envio']),
                $_POST['metodo_pago'] ?? 'tarjeta_demo',
                $subtotal,
                $discount,
                $shipping,
                $total,
                $couponCode,
                trim($_POST['notas'] ?? '')
            ]);

            $orderId = (int) $pdo->lastInsertId();

            $detailStmt = $pdo->prepare('
                INSERT INTO pedido_detalles
                (pedido_id, producto_id, nombre_producto, talla, color, cantidad, precio_unitario, total_linea)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $stockStmt = $pdo->prepare('
                UPDATE stock
                SET unidades = unidades - ?
                WHERE producto_id = ? AND talla = ? AND color = ? AND unidades >= ?
            ');

            foreach ($cart as $item) {
                $lineTotal = (float) $item['precio'] * (int) $item['cantidad'];
                $color = (string) ($item['color'] ?? '');

                $detailStmt->execute([
                    $orderId,
                    $item['producto_id'],
                    $item['nombre'],
                    $item['talla'],
                    $color,
                    $item['cantidad'],
                    $item['precio'],
                    $lineTotal
                ]);

                $stockStmt->execute([
                    $item['cantidad'],
                    $item['producto_id'],
                    $item['talla'],
                    $color,
                    $item['cantidad'],
                ]);

                if ($stockStmt->rowCount() === 0) {
                    throw new RuntimeException('Stock insuficiente para ' . $item['nombre']);
                }
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            flash('success', 'Pedido creado correctamente: ' . $number);
            redirect('pedido-confirmacion.php?numero=' . urlencode($number));
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            flash('danger', $e->getMessage());
            redirect('checkout.php');
        }
    }
}

$total = max(0, $subtotal - $discount) + $shipping;
$pageTitle = 'Checkout';
require_once __DIR__ . '/php/header.php';
?>

<section class="container">
    <h1 class="fw-black display-5 mb-4">Checkout</h1>

    <form method="post" class="row g-4">
        <?= csrf_field() ?>
        <div class="col-lg-8">
            <div class="surface-card p-4">
                <h2 class="h4 fw-bold mb-3">Datos de envío</h2>

                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre_envio" value="<?= e($user['nombre']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email_envio" type="email" value="<?= e($user['email']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="telefono_envio" value="<?= e($user['telefono']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Código postal</label><input class="form-control" name="cp_envio" value="<?= e($user['codigo_postal']) ?>" required></div>
                    <div class="col-12"><label class="form-label">Dirección</label><input class="form-control" name="direccion_envio" value="<?= e($user['direccion']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Ciudad</label><input class="form-control" name="ciudad_envio" value="<?= e($user['ciudad']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Provincia</label><input class="form-control" name="provincia_envio" value="<?= e($user['provincia']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Método de pago demo</label><select class="form-select" name="metodo_pago"><option value="tarjeta_demo">Tarjeta demo</option><option value="paypal_demo">PayPal demo</option><option value="contrareembolso">Contrareembolso</option></select></div>
                    <div class="col-12"><label class="form-label">Notas</label><textarea class="form-control" name="notas" rows="3"></textarea></div>
                </div>
            </div>
        </div>

        <aside class="col-lg-4">
            <div class="surface-card p-4">
                <h2 class="h4 fw-bold mb-3">Resumen</h2>

                <?php foreach ($cart as $item): ?>
                    <div class="d-flex justify-content-between gap-3 small mb-2">
                        <span>
                            <?= e($item['nombre']) ?> x <?= (int) $item['cantidad'] ?>
                            <span class="muted-text d-block">
                                <?= e($item['talla']) ?>
                                <?php if (!empty($item['color'])): ?>
                                    &middot; <?= e($item['color']) ?>
                                <?php endif; ?>
                            </span>
                        </span>
                        <strong><?= money((float) $item['precio'] * (int) $item['cantidad']) ?></strong>
                    </div>
                <?php endforeach; ?>

                <hr>

                <label class="form-label">Cupón</label>
                <div class="input-group mb-3">
                    <input class="form-control" name="cupon" value="<?= e($couponCode) ?>" placeholder="DAM10">
                    <button class="btn btn-outline-light" type="submit" formnovalidate>Aplicar</button>
                </div>

                <div class="d-flex justify-content-between"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Descuento</span><strong>-<?= money($discount) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Envío</span><strong><?= $shipping == 0 ? 'Gratis' : money($shipping) ?></strong></div>
                <hr>
                <div class="d-flex justify-content-between fs-4"><span>Total</span><strong><?= money($total) ?></strong></div>

                <button class="btn btn-accent w-100 mt-4" name="confirm_order" value="1" type="submit">Confirmar pedido</button>
            </div>
        </aside>
    </form>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
