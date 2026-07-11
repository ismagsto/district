<?php

$pageTitle = 'Categorías admin';
require_once __DIR__ . '/_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $categoryId = (int) $_POST['id'];
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM productos WHERE categoria_id = ?');
        $countStmt->execute([$categoryId]);

        if ((int) $countStmt->fetchColumn() > 0) {
            flash('warning', 'No puedes eliminar una categoría con productos asociados.');
            redirect('admin/categorias.php');
        }

        $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = ?');
        $stmt->execute([$categoryId]);
        flash('success', 'Categoría eliminada.');
        redirect('admin/categorias.php');
    }

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['nombre']);
        $slug = slugify($name);

        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE categorias SET nombre = ?, slug = ?, descripcion = ?, imagen = ?, activa = ? WHERE id = ?');
            $stmt->execute([$name, $slug, $_POST['descripcion'], $_POST['imagen'], isset($_POST['activa']) ? 1 : 0, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO categorias (nombre, slug, descripcion, imagen, activa) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $slug, $_POST['descripcion'], $_POST['imagen'], isset($_POST['activa']) ? 1 : 0]);
        }

        flash('success', 'Categoría guardada.');
        redirect('admin/categorias.php');
    }
}

$categories = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
?>

<h1 class="fw-black display-6 mb-4">Categorías</h1>

<div class="surface-card p-4 mb-4">
    <h2 class="h4 fw-bold">Nueva categoría</h2>
    <form method="post" class="row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre" required></div>
        <div class="col-md-6"><label class="form-label">Imagen URL</label><input class="form-control" name="imagen"></div>
        <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion"></textarea></div>
        <div class="col-12"><label><input type="checkbox" name="activa" checked> Activa</label></div>
        <div class="col-12"><button class="btn btn-accent">Crear categoría</button></div>
    </form>
</div>

<div class="surface-card p-4">
    <table class="table align-middle">
        <thead><tr><th>Nombre</th><th>Slug</th><th>Activa</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= e($cat['nombre']) ?></td>
                    <td><?= e($cat['slug']) ?></td>
                    <td><?= $cat['activa'] ? 'Sí' : 'No' ?></td>
                    <td class="text-end">
                        <form method="post" class="d-inline" data-confirm="¿Eliminar categoría?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
