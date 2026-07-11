<?php

$favActive = false;

if (is_logged()) {
    $favStmt = $pdo->prepare('SELECT 1 FROM favoritos WHERE usuario_id = ? AND producto_id = ?');
    $favStmt->execute([current_user()['id'], $product['id']]);
    $favActive = (bool) $favStmt->fetchColumn();
}

$productUrl = url('producto.php?slug=' . rawurlencode((string) $product['slug']));
$productImage = asset_url($product['imagen_principal'] ?? '');
?>
<div class="surface-card product-card p-3">
    <a href="<?= e($productUrl) ?>" aria-label="Ver <?= e($product['nombre']) ?>">
        <img src="<?= e($productImage) ?>" alt="<?= e($product['nombre']) ?>" loading="lazy">
    </a>

    <div class="pt-3">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
                <span class="badge badge-soft mb-2"><?= e($product['categoria_nombre'] ?? 'Producto') ?></span>
                <h3 class="h5 mb-1">
                    <a href="<?= e($productUrl) ?>">
                        <?= e($product['nombre']) ?>
                    </a>
                </h3>
            </div>

            <button class="btn btn-sm <?= $favActive ? 'btn-accent' : 'btn-outline-light' ?>" data-favorite data-product="<?= (int) $product['id'] ?>" type="button" aria-label="Favorito" aria-pressed="<?= $favActive ? 'true' : 'false' ?>">
                <i class="<?= $favActive ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
            </button>
        </div>

        <p class="muted-text small mb-3">
            <?= e(truncate_text($product['descripcion'], 84)) ?>
        </p>

        <div class="d-flex justify-content-between align-items-center gap-2">
            <div>
                <span class="product-price"><?= money((float) $product['precio']) ?></span>
                <?php if (!empty($product['precio_anterior'])): ?>
                    <span class="old-price ms-1"><?= money((float) $product['precio_anterior']) ?></span>
                <?php endif; ?>
            </div>

            <a class="btn btn-accent btn-sm" href="<?= e($productUrl) ?>">
                Comprar
            </a>
        </div>
    </div>
</div>
