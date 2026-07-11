<?php

require_once __DIR__ . '/../php/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido.']);
    exit;
}

verify_csrf();

if (!is_logged()) {
    echo json_encode(['ok' => false, 'message' => 'Inicia sesión para guardar favoritos.']);
    exit;
}

$productId = (int) ($_POST['product_id'] ?? $_POST['producto_id'] ?? 0);

if ($productId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Producto no válido.']);
    exit;
}

$productStmt = $pdo->prepare('SELECT 1 FROM productos WHERE id = ? AND activo = 1');
$productStmt->execute([$productId]);

if (!$productStmt->fetchColumn()) {
    echo json_encode(['ok' => false, 'message' => 'Producto no disponible.']);
    exit;
}

$userId = current_user()['id'];

$stmt = $pdo->prepare('SELECT 1 FROM favoritos WHERE usuario_id = ? AND producto_id = ?');
$stmt->execute([$userId, $productId]);
$exists = (bool) $stmt->fetchColumn();

if ($exists) {
    $delete = $pdo->prepare('DELETE FROM favoritos WHERE usuario_id = ? AND producto_id = ?');
    $delete->execute([$userId, $productId]);

    echo json_encode(['ok' => true, 'active' => false, 'message' => 'Eliminado de favoritos.']);
    exit;
}

$insert = $pdo->prepare('INSERT IGNORE INTO favoritos (usuario_id, producto_id) VALUES (?, ?)');
$insert->execute([$userId, $productId]);

echo json_encode(['ok' => true, 'active' => true, 'message' => 'Añadido a favoritos.']);
