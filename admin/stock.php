<?php

$pageTitle = 'Stock admin';

require_once __DIR__ . '/_header.php';

$tallasPermitidas = ['Única', 'XS', 'S', 'M', 'L', 'XL'];

$products = $pdo->query('
    SELECT id, nombre
    FROM productos
    ORDER BY nombre
')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $productoId = (int) ($_POST['producto_id'] ?? 0);
    $talla = trim($_POST['talla'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $unidades = max(0, (int) ($_POST['unidades'] ?? 0));

    if ($productoId <= 0) {
        flash('danger', 'Selecciona un producto válido.');
        redirect('admin/stock.php');
    }

    if (!in_array($talla, $tallasPermitidas, true)) {
        flash('danger', 'Selecciona una talla válida.');
        redirect('admin/stock.php');
    }

    if ($color === '') {
        flash('danger', 'Indica un color válido.');
        redirect('admin/stock.php');
    }

    $stmt = $pdo->prepare('
        INSERT INTO stock (producto_id, talla, color, unidades)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE unidades = VALUES(unidades)
    ');

    $stmt->execute([
        $productoId,
        $talla,
        $color,
        $unidades
    ]);

    flash('success', 'Stock actualizado correctamente.');

    redirect('admin/stock.php');
}

$stockRows = $pdo->query('
    SELECT 
        stock.id,
        stock.producto_id,
        stock.talla,
        stock.color,
        stock.unidades,
        productos.nombre AS producto_nombre
    FROM stock
    JOIN productos ON productos.id = stock.producto_id
    ORDER BY productos.nombre, stock.talla, stock.color
')->fetchAll();

?>

<h1 class="fw-black display-6 mb-4">Gestión de stock</h1>

<div class="surface-card p-4 mb-4">
    <h2 class="h4 fw-bold mb-3">Actualizar stock</h2>

    <form method="post" class="row g-3">
        <?= csrf_field() ?>

        <div class="col-md-4">
            <label class="form-label" for="producto_id">Producto</label>

            <select class="form-select" id="producto_id" name="producto_id" required>
                <option value="">Selecciona un producto</option>

                <?php foreach ($products as $product): ?>
                    <option value="<?= (int) $product['id'] ?>">
                        <?= e($product['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label" for="talla">Talla</label>

            <select class="form-select" id="talla" name="talla" required>
                <?php foreach ($tallasPermitidas as $talla): ?>
                    <option value="<?= e($talla) ?>">
                        <?= e($talla) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label" for="color">Color</label>

            <input
                class="form-control"
                id="color"
                name="color"
                list="colores-stock"
                required
                placeholder="Negro"
            >

            <datalist id="colores-stock">
                <option value="Negro">
                <option value="Blanco">
                <option value="Gris">
                <option value="Azul">
                <option value="Verde">
                <option value="Beige">
                <option value="Arena">
            </datalist>
        </div>

        <div class="col-md-2">
            <label class="form-label" for="unidades">Unidades</label>

            <input 
                class="form-control" 
                id="unidades" 
                name="unidades" 
                type="number" 
                min="0" 
                value="0" 
                required
            >
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-accent w-100" type="submit">
                Guardar
            </button>
        </div>
    </form>
</div>

<div class="surface-card p-4">
    <h2 class="h4 fw-bold mb-3">Stock actual</h2>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Color</th>
                    <th>Unidades</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($stockRows as $row): ?>
                    <tr>
                        <td><?= e($row['producto_nombre']) ?></td>
                        <td><?= e($row['talla']) ?></td>
                        <td><?= e($row['color']) ?></td>
                        <td><?= (int) $row['unidades'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
