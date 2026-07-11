<?php

$pageTitle = 'Inicio';
require_once __DIR__ . '/php/header.php';

$featuredStmt = $pdo->query('
    SELECT productos.*, categorias.nombre AS categoria_nombre
    FROM productos
    JOIN categorias ON categorias.id = productos.categoria_id
    WHERE productos.activo = 1 AND productos.destacado = 1
    ORDER BY productos.creado_en DESC
    LIMIT 4
');
$featuredProducts = $featuredStmt->fetchAll();

$categoryStmt = $pdo->query('SELECT * FROM categorias WHERE activa = 1 ORDER BY nombre');
$categories = $categoryStmt->fetchAll();
?>

<section class="container">
    <div class="hero">
        <div class="hero-card">
            <span class="badge badge-soft mb-3">Nueva colección urbana</span>
            <h1 class="hero-title fw-black mb-4">Streetwear para destacar.</h1>
            <div class="d-flex flex-wrap gap-3">
                <a class="btn btn-accent btn-lg" href="<?= url('catalogo.php') ?>">Ver catálogo</a>
            </div>
        </div>
    </div>
</section>

<section class="container py-5" id="destacados">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="text-accent small text-uppercase fw-bold">Selección</span>
            <h2 class="fw-black display-6 mb-0">Productos destacados</h2>
        </div>
        <a class="btn btn-outline-light" href="<?= url('catalogo.php') ?>">Todo el catálogo</a>
    </div>

    <div class="row g-4">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="col-sm-6 col-lg-3">
                <?php require __DIR__ . '/php/product-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="container py-5">
    <div class="row g-4 align-items-stretch">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-6 col-lg-3">
                <a class="surface-card d-block h-100 p-3" href="<?= e(url('catalogo.php?categoria=' . rawurlencode($category['slug']))) ?>">
                    <img class="rounded-4 mb-3" src="<?= e(asset_url($category['imagen'])) ?>" alt="<?= e($category['nombre']) ?>" style="height: 180px; width: 100%; object-fit: cover;">
                    <h3 class="h5 fw-bold"><?= e($category['nombre']) ?></h3>
                    <p class="muted-text small mb-0"><?= e($category['descripcion']) ?></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>


<?php require_once __DIR__ . '/php/footer.php'; ?>
