<?php

require_once __DIR__ . '/../php/config.php';
require_admin();

$pageTitle = $pageTitle ?? 'Panel admin';
require_once __DIR__ . '/../php/header.php';
?>

<div class="container">
    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="surface-card p-3 admin-sidebar">
                <h2 class="h5 fw-black px-2">Admin</h2>
                <nav>
                    <a class="admin-link" href="<?= url('admin/index.php') ?>">Dashboard</a>
                    <a class="admin-link" href="<?= url('admin/productos.php') ?>">Productos</a>
                    <a class="admin-link" href="<?= url('admin/categorias.php') ?>">Categorías</a>
                    <a class="admin-link" href="<?= url('admin/stock.php') ?>">Stock</a>
                    <a class="admin-link" href="<?= url('admin/pedidos.php') ?>">Pedidos</a>
                    <a class="admin-link" href="<?= url('admin/cupones.php') ?>">Cupones</a>
                    <a class="admin-link" href="<?= url('admin/usuarios.php') ?>">Usuarios</a>
                </nav>
            </div>
        </aside>
        <section class="col-lg-9">
