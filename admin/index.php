<?php

$pageTitle = 'Dashboard admin';
require_once __DIR__ . '/_header.php';

$stats = [
    'ventas' => (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado <> 'cancelado'")->fetchColumn(),
    'pedidos' => (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
    'productos' => (int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn(),
    'stock_bajo' => (int) $pdo->query('SELECT COUNT(*) FROM stock WHERE unidades <= 3')->fetchColumn()
];

$monthly = $pdo->query('
    SELECT DATE_FORMAT(creado_en, "%Y-%m") AS mes, SUM(total) AS total
    FROM pedidos
    WHERE estado <> "cancelado"
    GROUP BY mes
    ORDER BY mes
    LIMIT 12
')->fetchAll();

$topProducts = $pdo->query('
    SELECT nombre_producto, SUM(cantidad) AS unidades
    FROM pedido_detalles
    GROUP BY nombre_producto
    ORDER BY unidades DESC
    LIMIT 5
')->fetchAll();
?>

<h1 class="fw-black display-6 mb-4">Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3"><div class="surface-card p-4 kpi"><span class="muted-text">Ventas</span><h2><?= money($stats['ventas']) ?></h2></div></div>
    <div class="col-md-6 col-xl-3"><div class="surface-card p-4 kpi"><span class="muted-text">Pedidos</span><h2><?= $stats['pedidos'] ?></h2></div></div>
    <div class="col-md-6 col-xl-3"><div class="surface-card p-4 kpi"><span class="muted-text">Productos</span><h2><?= $stats['productos'] ?></h2></div></div>
    <div class="col-md-6 col-xl-3"><div class="surface-card p-4 kpi"><span class="muted-text">Stock bajo</span><h2><?= $stats['stock_bajo'] ?></h2></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="h5 fw-bold">Ventas por mes</h2>
            <canvas id="salesChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="surface-card p-4">
            <h2 class="h5 fw-bold">Top productos</h2>
            <?php foreach ($topProducts as $product): ?>
                <div class="d-flex justify-content-between border-bottom border-secondary py-2">
                    <span><?= e($product['nombre_producto']) ?></span>
                    <strong><?= (int) $product['unidades'] ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const salesLabels = <?= json_encode(array_column($monthly, 'mes')) ?>;
const salesData = <?= json_encode(array_map('floatval', array_column($monthly, 'total'))) ?>;

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: salesLabels,
        datasets: [{
            label: 'Ventas',
            data: salesData,
            tension: 0.35
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
