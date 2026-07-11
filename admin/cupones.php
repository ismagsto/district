<?php

$pageTitle = 'Cupones admin';
require_once __DIR__ . '/_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $code = strtoupper(trim($_POST['codigo'] ?? ''));
    $type = $_POST['tipo'] ?? 'porcentaje';
    $value = max(0, (float) ($_POST['valor'] ?? 0));
    $minimum = max(0, (float) ($_POST['minimo'] ?? 0));

    if ($code === '' || !in_array($type, ['porcentaje', 'fijo'], true)) {
        flash('danger', 'Cupón no válido.');
        redirect('admin/cupones.php');
    }

    $stmt = $pdo->prepare('INSERT INTO cupones (codigo, tipo, valor, minimo, caduca_en, activo) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$code, $type, $value, $minimum, $_POST['caduca_en'] ?: null, isset($_POST['activo']) ? 1 : 0]);
    flash('success', 'Cupón creado.');
    redirect('admin/cupones.php');
}

$coupons = $pdo->query('SELECT * FROM cupones ORDER BY creado_en DESC')->fetchAll();
?>

<h1 class="fw-black display-6 mb-4">Cupones</h1>

<div class="surface-card p-4 mb-4">
    <form method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-md-3"><label class="form-label">Código</label><input class="form-control" name="codigo" required></div>
        <div class="col-md-2"><label class="form-label">Tipo</label><select class="form-select" name="tipo"><option value="porcentaje">%</option><option value="fijo">€</option></select></div>
        <div class="col-md-2"><label class="form-label">Valor</label><input class="form-control" name="valor" type="number" step="0.01" required></div>
        <div class="col-md-2"><label class="form-label">Mínimo</label><input class="form-control" name="minimo" type="number" step="0.01" value="0"></div>
        <div class="col-md-2"><label class="form-label">Caduca</label><input class="form-control" name="caduca_en" type="date"></div>
        <div class="col-md-1 d-flex align-items-end"><button class="btn btn-accent">OK</button></div>
        <div class="col-12"><label><input type="checkbox" name="activo" checked> Activo</label></div>
    </form>
</div>

<div class="surface-card p-4">
    <table class="table"><thead><tr><th>Código</th><th>Tipo</th><th>Valor</th><th>Mínimo</th><th>Caduca</th><th>Activo</th></tr></thead><tbody><?php foreach ($coupons as $c): ?><tr><td><?= e($c['codigo']) ?></td><td><?= e($c['tipo']) ?></td><td><?= e($c['valor']) ?></td><td><?= e($c['minimo']) ?></td><td><?= e($c['caduca_en']) ?></td><td><?= $c['activo'] ? 'Sí' : 'No' ?></td></tr><?php endforeach; ?></tbody></table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
