<?php

require_once __DIR__ . '/../php/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido.']);
    exit;
}

verify_csrf();

$action = $_POST['action'] ?? '';

try {
    if ($action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? $_POST['producto_id'] ?? 0);
        $size = trim($_POST['talla'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $variant = trim($_POST['variant'] ?? '');
        $quantity = max(1, (int) ($_POST['cantidad'] ?? 1));

        if ($variant !== '') {
            [$size, $color] = array_pad(explode('|', $variant, 2), 2, '');
            $size = trim($size);
            $color = trim($color);
        }

        if ($productId <= 0) {
            throw new RuntimeException('Producto no encontrado.');
        }

        if ($size === '' || $color === '') {
            throw new RuntimeException('Selecciona una talla y un color.');
        }

        $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ? AND activo = 1');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new RuntimeException('Producto no encontrado.');
        }

        $available = stock_available($pdo, $productId, $size, $color);
        $key = cart_item_key($productId, $size, $color);
        $currentQuantity = (int) ($_SESSION['cart'][$key]['cantidad'] ?? 0);
        $newQuantity = $currentQuantity + $quantity;

        if ($available < $newQuantity) {
            throw new RuntimeException('No hay suficiente stock para esta variante.');
        }

        if (!isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key] = [
                'producto_id' => $productId,
                'nombre' => $product['nombre'],
                'slug' => $product['slug'],
                'imagen' => $product['imagen_principal'],
                'precio' => (float) $product['precio'],
                'talla' => $size,
                'color' => $color,
                'cantidad' => 0
            ];
        }

        $_SESSION['cart'][$key]['cantidad'] += $quantity;

        echo json_encode([
            'ok' => true,
            'message' => 'Producto añadido al carrito.',
            'cart_count' => cart_count()
        ]);
        exit;
    }

    if ($action === 'remove') {
        $key = $_POST['key'] ?? '';
        unset($_SESSION['cart'][$key]);
        flash('success', 'Producto eliminado del carrito.');
        echo json_encode(['ok' => true, 'cart_count' => cart_count()]);
        exit;
    }

    throw new RuntimeException('Acción no válida.');
} catch (Throwable $e) {
    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage(),
        'cart_count' => cart_count()
    ]);
}
