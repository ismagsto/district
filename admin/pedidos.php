<?php

$pageTitle = 'Pedidos admin';
require_once __DIR__ . '/_header.php';

$allowedStatuses = ['pendiente', 'pagado', 'preparando', 'enviado', 'entregado', 'cancelado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (!in_array($_POST['estado'] ?? '', $allowedStatuses, true)) {
        flash('danger', 'Estado no válido.');
        redirect('admin/pedidos.php');
    }

    $stmt = $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
    $stmt->execute([$_POST['estado'], (int) $_POST['id']]);
    flash('success', 'Estado actualizado.');
    redirect('admin/pedidos.php');
}

$status = $_GET['estado'] ?? '';
$params = [];
$where = '';

if ($status !== '' && in_array($status, $allowedStatuses, true)) {
    $where = 'WHERE estado = ?';
    $params[] = $status;
} else {
    $status = '';
}

$stmt = $pdo->prepare("SELECT * FROM pedidos $where ORDER BY creado_en DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<h1 class="fw-black display-6 mb-4">Pedidos</h1>

<form class="surface-card p-3 mb-4" method="get">
    <div class="row g-2">
        <div class="col-md-4">
            <select class="form-select" name="estado">
                <option value="">Todos los estados</option>
                <?php foreach ($allowedStatuses as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-accent w-100">Filtrar</button></div>
    </div>
</form>

<div class="surface-card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Número</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Cambiar</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= e($order['numero']) ?></td>
                    <td><?= e($order['nombre_envio']) ?><br><span class="small muted-text"><?= e($order['email_envio']) ?></span></td>
                    <td><?= money((float) $order['total']) ?></td>
                    <td><span class="badge badge-soft"><?= e($order['estado']) ?></span></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($order['creado_en']))) ?></td>
                    <td>
                        <form method="post" class="d-flex gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                            <select class="form-select form-select-sm" name="estado">
                                <?php foreach ($allowedStatuses as $s): ?>
                                    <option value="<?= $s ?>" <?= $order['estado'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-outline-light">OK</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
