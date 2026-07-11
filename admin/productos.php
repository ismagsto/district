<?php

$pageTitle = 'Productos admin';
require_once __DIR__ . '/_header.php';

$categories = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
$editing = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM productos WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
        flash('success', 'Producto eliminado.');
        redirect('admin/productos.php');
    }

    $name = trim($_POST['nombre'] ?? '');
    $slug = slugify($name);
    $image = trim($_POST['imagen_principal'] ?? '');

    if ($name === '' || $image === '') {
        flash('danger', 'Completa el nombre y la imagen del producto.');
        redirect('admin/productos.php');
    }

    if ($action === 'create') {
        $stmt = $pdo->prepare('
            INSERT INTO productos (categoria_id, nombre, slug, descripcion, precio, precio_anterior, imagen_principal, destacado, activo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $_POST['categoria_id'], $name, $slug, $_POST['descripcion'], $_POST['precio'], $_POST['precio_anterior'] ?: null, $image, isset($_POST['destacado']) ? 1 : 0, isset($_POST['activo']) ? 1 : 0
        ]);
        flash('success', 'Producto creado.');
    }

    if ($action === 'update') {
        $stmt = $pdo->prepare('
            UPDATE productos
            SET categoria_id = ?, nombre = ?, slug = ?, descripcion = ?, precio = ?, precio_anterior = ?, imagen_principal = ?, destacado = ?, activo = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $_POST['categoria_id'], $name, $slug, $_POST['descripcion'], $_POST['precio'], $_POST['precio_anterior'] ?: null, $image, isset($_POST['destacado']) ? 1 : 0, isset($_POST['activo']) ? 1 : 0, $_POST['id']
        ]);
        flash('success', 'Producto actualizado.');
    }

    redirect('admin/productos.php');
}


$products = $pdo->query('
    SELECT productos.*, categorias.nombre AS categoria_nombre
    FROM productos
    JOIN categorias ON categorias.id = productos.categoria_id
    ORDER BY productos.creado_en DESC
')->fetchAll();
?>

<h1 class="fw-black display-6 mb-4">Productos</h1>

<div class="surface-card p-4 mb-4">
    <h2 class="h4 fw-bold"><?= $editing ? 'Editar producto' : 'Nuevo producto' ?></h2>

    <form method="post" enctype="multipart/form-data" class="row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

        <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?= e($editing['nombre'] ?? '') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Categoría</label><select class="form-select" name="categoria_id"><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($editing['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['nombre']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Precio</label><input class="form-control" name="precio" type="number" step="0.01" value="<?= e($editing['precio'] ?? '') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Precio anterior</label><input class="form-control" name="precio_anterior" type="number" step="0.01" value="<?= e($editing['precio_anterior'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="4" required><?= e($editing['descripcion'] ?? '') ?></textarea></div>
        <div class="col-md-6"><label class="form-label">Imagen por URL o Ruta</label><input class="form-control" name="imagen_principal" value="<?= e($editing['imagen_principal'] ?? '') ?>" required></div>
        <div class="col-12"><label class="me-3"><input type="checkbox" name="destacado" <?= !empty($editing['destacado']) ? 'checked' : '' ?>> Destacado</label><label><input type="checkbox" name="activo" <?= !isset($editing) || !empty($editing['activo']) ? 'checked' : '' ?>> Activo</label></div>
        <div class="col-12"><button class="btn btn-accent" type="submit"><?= $editing ? 'Guardar cambios' : 'Crear producto' ?></button><?php if ($editing): ?><a class="btn btn-outline-light" href="<?= url('admin/productos.php') ?>">Cancelar</a><?php endif; ?></div>
    </form>
</div>

<div class="surface-card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Imagen</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img src="<?= e(asset_url($product['imagen_principal'])) ?>" alt="<?= e($product['nombre']) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:12px">
                    </td>
                    <td><?= e($product['nombre']) ?></td>
                    <td><?= e($product['categoria_nombre']) ?></td>
                    <td><?= money((float) $product['precio']) ?></td>
                    <td><?= $product['activo'] ? 'Activo' : 'Oculto' ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-light" href="?edit=<?= $product['id'] ?>">Editar</a><form class="d-inline" method="post" data-confirm="¿Eliminar producto?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $product['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button></form></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
