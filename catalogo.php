<?php

$pageTitle = 'Catálogo';
require_once __DIR__ . '/php/header.php';


$categoriesStmt = $pdo->query('
    SELECT id, nombre, slug, descripcion, imagen
    FROM categorias
    WHERE activa = 1
    ORDER BY nombre ASC
');

$categories = $categoriesStmt->fetchAll();

$selectedCategorySlug = trim($_GET['categoria'] ?? '');
$selectedCategory = null;

foreach ($categories as $category) {
    if ($category['slug'] === $selectedCategorySlug) {
        $selectedCategory = $category;
        break;
    }
}

$allowedOrders = [
    'recientes',
    'antiguos',
    'precio_asc',
    'precio_desc',
    'nombre_asc',
    'nombre_desc'
];

$currentOrder = $_GET['orden'] ?? 'recientes';

if (!in_array($currentOrder, $allowedOrders, true)) {
    $currentOrder = 'recientes';
}

$currentSearch = trim($_GET['q'] ?? '');
$isCategoryPage = $selectedCategory !== null;

$categoryVisuals = [
    'camisetas' => [
        'label' => 'Streetwear superior',
        'title' => 'Camisetas',
        'text' => 'Oversize, gráficos potentes y básicos premium para crear looks diarios con identidad.',
        'image' => 'img/productos/camiseta1.png'
    ],
    'pantalones' => [
        'label' => 'Corte urbano',
        'title' => 'Pantalones y cargos',
        'text' => 'Siluetas cargo, denim ancho y pantalones cómodos pensados para outfits de calle.',
        'image' => 'img/productos/chandal1.png'
    ],
    'sudaderas' => [
        'label' => 'Capas esenciales',
        'title' => 'Sudaderas y hoodies',
        'text' => 'Prendas amplias, cálidas y fáciles de combinar con estética urbana moderna.',
        'image' => 'img/productos/sudadera2.png'
    ],
    'accesorios' => [
        'label' => 'Detalles finales',
        'title' => 'Accesorios urbanos',
        'text' => 'Gorras, bolsos y complementos para rematar el estilo sin recargar el outfit.',
        'image' => 'img/productos/bolso1.png'
    ]
];

$currentVisual = $categoryVisuals[$selectedCategorySlug] ?? [
    'label' => $selectedCategory['nombre'] ?? 'Shop',
    'title' => $selectedCategory['nombre'] ?? 'Categoría',
    'text' => $selectedCategory['descripcion'] ?? 'Productos seleccionados de esta categoría.',
    'image' => $selectedCategory['imagen'] ?? 'img/fondo_tienda.png'
];
?>

<section class="container catalog-page">
    <?php if ($isCategoryPage): ?>
        <header class="category-landing surface-card mb-4">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-7 p-4 p-lg-5 d-flex flex-column justify-content-center">
                    <span class="text-accent small text-uppercase fw-bold mb-2">
                        <?= e($currentVisual['label']) ?>
                    </span>

                    <h1 class="fw-black display-4 mb-3">
                        <?= e($currentVisual['title']) ?>
                    </h1>

                    <p class="muted-text lead mb-4">
                        <?= e($currentVisual['text']) ?>
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-accent" href="#catalogResults">
                            Ver productos
                        </a>

                        <a class="btn btn-outline-light" href="<?= url('catalogo.php') ?>">
                            Volver al catálogo
                        </a>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div
                        class="category-landing-image"
                        style="background-image: url('<?= e(asset_url($currentVisual['image'])) ?>');"
                        role="img"
                        aria-label="Imagen de <?= e($selectedCategory['nombre']) ?>"
                    ></div>
                </div>
            </div>
        </header>
    <?php else: ?>
        <header class="catalog-hero surface-card p-4 p-lg-5 mb-4">
            <div class="row align-items-end g-4">
                <div class="col-lg-8">
                    <span class="text-accent small text-uppercase fw-bold">Shop</span>
                    <h1 class="fw-black display-5 mb-2">Catálogo completo</h1>
                    <p class="muted-text mb-0">
                        Todos los productos de la tienda. Busca por nombre, entra en una categoría o cambia el orden.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <span class="badge bg-accent text-dark rounded-pill px-3 py-2">
                        Todos los productos
                    </span>
                </div>
            </div>
        </header>
    <?php endif; ?>

    <section class="catalog-filter-bar surface-card p-3 p-lg-4 mb-4" aria-label="Filtros del catálogo">
        <form id="filtersForm" class="row g-3 align-items-end" method="get" action="<?= url('catalogo.php') ?>">
            <?php if ($isCategoryPage): ?>
                <input type="hidden" id="categoria" name="categoria" value="<?= e($selectedCategorySlug) ?>">
            <?php else: ?>
                <input type="hidden" id="categoria" name="categoria" value="<?= e($selectedCategorySlug) ?>">
            <?php endif; ?>

            <div class="col-lg-7">
                <label class="form-label" for="q">Buscar producto</label>
                <input
                    class="form-control"
                    id="q"
                    name="q"
                    type="search"
                    value="<?= e($currentSearch) ?>"
                    placeholder="camiseta, sudadera, cargo..."
                >
            </div>

            <div class="col-md-7 col-lg-3">
                <label class="form-label" for="orden">Ordenar por</label>
                <select class="form-select" id="orden" name="orden">
                    <option value="recientes" <?= $currentOrder === 'recientes' ? 'selected' : '' ?>>Más recientes</option>
                    <option value="antiguos" <?= $currentOrder === 'antiguos' ? 'selected' : '' ?>>Más antiguos</option>
                    <option value="precio_asc" <?= $currentOrder === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
                    <option value="precio_desc" <?= $currentOrder === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
                    <option value="nombre_asc" <?= $currentOrder === 'nombre_asc' ? 'selected' : '' ?>>Nombre: A-Z</option>
                    <option value="nombre_desc" <?= $currentOrder === 'nombre_desc' ? 'selected' : '' ?>>Nombre: Z-A</option>
                </select>
            </div>

            <div class="col-md-5 col-lg-2 d-grid">
                <button class="btn btn-accent" type="submit">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>
                    Buscar
                </button>
            </div>
        </form>
    </section>

    <?php if (!$isCategoryPage): ?>
        <section class="category-shortcuts mb-4" aria-label="Accesos rápidos por categoría">
            <div class="category-shortcuts-grid">
                <button
                    class="category-pill <?= $selectedCategorySlug === '' ? 'active' : '' ?>"
                    type="button"
                    data-category=""
                >
                    Todo
                </button>

                <?php foreach ($categories as $category): ?>
                    <button
                        class="category-pill <?= $selectedCategorySlug === $category['slug'] ? 'active' : '' ?>"
                        type="button"
                        data-category="<?= e($category['slug']) ?>"
                    >
                        <?= e($category['nombre']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section>
        <div class="catalog-status d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 fw-bold mb-0">
                Resultados
            </h2>

            <span class="small muted-text" id="catalogStatusText">
                <?php if ($currentSearch !== '' || $currentOrder !== 'recientes'): ?>
                    Filtros aplicados.
                <?php else: ?>
                    Mostrando productos disponibles.
                <?php endif; ?>
            </span>
        </div>

        <div class="row g-4" id="catalogResults">
            <?php require __DIR__ . '/api/products.php'; ?>
        </div>
    </section>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
