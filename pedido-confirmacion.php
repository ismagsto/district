<?php

require_once __DIR__ . '/php/config.php';
require_login();

$number = $_GET['numero'] ?? '';
$params = [$number];
$where = 'numero = ?';

if (!is_admin()) {
    $where .= ' AND usuario_id = ?';
    $params[] = current_user()['id'];
}

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE $where");
$stmt->execute($params);
$order = $stmt->fetch();

$pageTitle = 'Pedido confirmado';
require_once __DIR__ . '/php/header.php';
?>

<section class="container">
    <div class="surface-card p-5 text-center">
        <?php if ($order): ?>
            <i class="fa-solid fa-circle-check text-accent display-1 mb-3"></i>
            <h1 class="fw-black">Pedido confirmado</h1>
            <p class="lead muted-text">Número de pedido: <strong><?= e($order['numero']) ?></strong></p>
            <a class="btn btn-accent" href="<?= url('pedidos.php') ?>">Ver mis pedidos</a>
        <?php else: ?>
            <h1>Pedido no encontrado</h1>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
