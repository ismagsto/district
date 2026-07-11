<?php

require_once __DIR__ . '/config.php';

$categoriesStmt = $pdo->query('SELECT nombre, slug FROM categorias WHERE activa = 1 ORDER BY nombre');
$navCategories = $categoriesStmt->fetchAll();
$pageTitle = $pageTitle ?? 'Tienda Urbana';
$appBaseUrl = rtrim(url(), '/');

if ($appBaseUrl === '') {
    $appBaseUrl = '/';
}
?>
<!doctype html>
<html lang="es" data-theme="dark">
<head>
    <script>
        /* Aplica el tema antes de cargar el CSS para evitar parpadeos. */
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tienda de ropa urbana desarrollada con PHP, MySQL, Bootstrap y JavaScript.">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> | tienda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= url('css/styles.css') ?>" rel="stylesheet">

    <link rel="icon" type="image/png" href="<?= e(asset_url('img/favicon_district.png?v=1')) ?>">

</head>
<body data-base="<?= e($appBaseUrl) ?>">

<a href="#main-content" class="skip-link">Saltar al contenido principal</a>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top glass-nav" aria-label="Navegación principal">
    <div class="container">
        <a class="navbar-brand fw-black tracking" href="<?= url('index.php') ?>">
            district<span class="accent-dot">.</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('catalogo.php') ?>">Catálogo</a>
                </li>

                <?php foreach ($navCategories as $cat): ?>
                    <li class="nav-item d-none d-lg-block">
                        <a class="nav-link small" href="<?= e(url('catalogo.php?categoria=' . rawurlencode($cat['slug']))) ?>">
                            <?= e($cat['nombre']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <li class="nav-item">
                    <a class="nav-link" href="<?= url('favoritos.php') ?>">
                        <i class="fa-regular fa-heart"></i>
                        Favoritos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?= url('carrito.php') ?>">
                        <i class="fa-solid fa-bag-shopping"></i>
                        Carrito
                        <span class="cart-count badge rounded-pill bg-accent"><?= cart_count() ?></span>
                    </a>
                </li>

                <?php if (is_logged()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?= e(current_user()['nombre']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-soft">
                            <li><a class="dropdown-item" href="<?= url('cuenta.php') ?>">Mi cuenta</a></li>
                            <li><a class="dropdown-item" href="<?= url('pedidos.php') ?>">Mis pedidos</a></li>
                            <?php if (is_admin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= url('admin/index.php') ?>">Panel admin</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= url('logout.php') ?>">Cerrar sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="<?= url('login.php') ?>">Entrar</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <button class="btn btn-icon" id="themeToggle" type="button" aria-label="Cambiar tema">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main id="main-content" class="main-space">
    <div class="container flash-container">
        <?php foreach (get_flash_messages() as $message): ?>
            <div class="alert alert-<?= e($message['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endforeach; ?>
    </div>
