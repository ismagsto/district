<?php

require_once __DIR__ . '/php/config.php';
require_login();
$pageTitle = 'Mis pedidos';
require_once __DIR__ . '/php/header.php';

$stmt = $pdo->prepare('SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY creado_en DESC');
$stmt->execute([current_user()['id']]);
$orders = $stmt->fetchAll();
?>

<section class="container">
    <h1 class="fw-black display-5 mb-4">Mis pedidos</h1>

    <div class="surface-card p-4">
        <?php if (!$orders): ?>
            <p class="muted-text mb-0">Todavía no tienes pedidos.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Número</th><th>Estado</th><th>Fecha</th><th>Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= e($order['numero']) ?></td>
                            <td><span class="badge badge-soft"><?= e($order['estado']) ?></span></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($order['creado_en']))) ?></td>
                            <td><?= money((float) $order['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
