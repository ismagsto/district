<?php

require_once __DIR__ . '/php/config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('
    SELECT productos.*, categorias.nombre AS categoria_nombre
    FROM productos
    JOIN categorias ON categorias.id = productos.categoria_id
    WHERE productos.slug = ? AND productos.activo = 1
');
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Producto no encontrado';
    require_once __DIR__ . '/php/header.php';
    echo '<section class="container"><div class="surface-card p-5 text-center"><h1>Producto no encontrado</h1></div></section>';
    require_once __DIR__ . '/php/footer.php';
    exit;
}

$pageTitle = $product['nombre'];
$productImage = asset_url($product['imagen_principal']);
require_once __DIR__ . '/php/header.php';

$stockStmt = $pdo->prepare('SELECT talla, color, unidades FROM stock WHERE producto_id = ? AND unidades > 0 ORDER BY talla, color');
$stockStmt->execute([$product['id']]);
$stockRows = $stockStmt->fetchAll();
$maxStock = $stockRows ? max(array_map('intval', array_column($stockRows, 'unidades'))) : 0;

$reviewStmt = $pdo->prepare('SELECT * FROM reviews WHERE producto_id = ? AND aprobado = 1 ORDER BY creado_en DESC');
$reviewStmt->execute([$product['id']]);
$reviews = $reviewStmt->fetchAll();

$favActive = false;

if (is_logged()) {
    $favStmt = $pdo->prepare('SELECT 1 FROM favoritos WHERE usuario_id = ? AND producto_id = ?');
    $favStmt->execute([current_user()['id'], $product['id']]);
    $favActive = (bool) $favStmt->fetchColumn();
}
?>

<section class="container">
    <div class="row g-5">
        <div class="col-lg-6">
            <img 
                class="gallery-main" 
                src="<?= e($productImage) ?>" 
                alt="<?= e($product['nombre']) ?>"
            >
        </div>

        <div class="col-lg-6">
            <span class="badge badge-soft mb-3"><?= e($product['categoria_nombre']) ?></span>
            <h1 class="fw-black display-5"><?= e($product['nombre']) ?></h1>

            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="product-price fs-3"><?= money((float) $product['precio']) ?></span>
                <?php if ($product['precio_anterior']): ?>
                    <span class="old-price"><?= money((float) $product['precio_anterior']) ?></span>
                <?php endif; ?>
            </div>

            <p class="lead muted-text"><?= e($product['descripcion']) ?></p>

            <form class="surface-card p-4 mt-4" method="post">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="variant">Talla / color</label>
                        <select class="form-select" id="variant" name="variant" required <?= !$stockRows ? 'disabled' : '' ?>>
                            <?php if (!$stockRows): ?>
                                <option value="">Sin stock disponible</option>
                            <?php endif; ?>

                            <?php foreach ($stockRows as $row): ?>
                                <option value="<?= e($row['talla'] . '|' . $row['color']) ?>" data-stock="<?= (int) $row['unidades'] ?>">
                                    <?= e($row['talla']) ?> &middot; <?= e($row['color']) ?> (<?= (int) $row['unidades'] ?> uds)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">Cantidad</label>
                        <div class="quantity-control d-flex">
                            <button type="button" data-qty-action="minus">-</button>
                            <input name="cantidad" type="number" value="1" min="1" max="<?= max(1, $maxStock) ?>" aria-label="Cantidad" <?= !$stockRows ? 'disabled' : '' ?>>
                            <button type="button" data-qty-action="plus">+</button>
                        </div>
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2 mt-3">
                        <button class="btn btn-accent btn-lg" data-add-cart type="submit" <?= !$stockRows ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-bag-shopping me-2"></i>Añadir al carrito
                        </button>

                        <button class="btn <?= $favActive ? 'btn-accent' : 'btn-outline-light' ?> btn-lg" data-favorite data-product="<?= (int) $product['id'] ?>" type="button" aria-pressed="<?= $favActive ? 'true' : 'false' ?>">
                            <i class="<?= $favActive ? 'fa-solid' : 'fa-regular' ?> fa-heart me-2"></i>Favorito
                        </button>
                    </div>
                </div>
            </form>

            <div class="surface-card p-4 mt-4">
                <h2 class="h5 fw-bold">Stock disponible</h2>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($stockRows as $row): ?>
                        <span class="badge text-bg-dark border">
                            <?= e($row['talla']) ?> &middot; <?= e($row['color']) ?> &middot; <?= (int) $row['unidades'] ?> uds
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5">
    <div class="surface-card p-4">
        <h2 class="h4 fw-bold mb-4">Opiniones</h2>

        <?php if (!$reviews): ?>
            <p class="muted-text mb-0">Aún no hay opiniones para este producto.</p>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($reviews as $review): ?>
                <div class="col-md-4">
                    <div class="surface-card p-3 h-100">
                        <div class="text-accent mb-2"><?= str_repeat('★', (int) $review['puntuacion']) ?></div>
                        <strong><?= e($review['nombre']) ?></strong>
                        <p class="muted-text small mb-0"><?= e($review['comentario']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
