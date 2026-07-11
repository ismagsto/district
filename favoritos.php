<?php

require_once __DIR__ . '/php/config.php';
require_login();
$pageTitle = 'Favoritos';
require_once __DIR__ . '/php/header.php';

$stmt = $pdo->prepare('
    SELECT productos.*, categorias.nombre AS categoria_nombre
    FROM favoritos
    JOIN productos ON productos.id = favoritos.producto_id
    JOIN categorias ON categorias.id = productos.categoria_id
    WHERE favoritos.usuario_id = ?
    ORDER BY favoritos.creado_en DESC
');
$stmt->execute([current_user()['id']]);
$products = $stmt->fetchAll();
?>

<section class="container">
    <h1 class="fw-black display-5 mb-4">Favoritos</h1>

    <div class="row g-4">
        <?php if (!$products): ?>
            <div class="col-12"><div class="surface-card p-5 text-center"><p class="mb-0 muted-text">No tienes favoritos todavía.</p></div></div>
        <?php endif; ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-3"><?php require __DIR__ . '/php/product-card.php'; ?></div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
