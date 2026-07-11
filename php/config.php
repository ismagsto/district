<?php

ob_start();

// CONFIGURACIÓN GLOBAL DEL PROYECTO

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

/* LOCALHOST
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'tienda');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('BASE_URL', '/tienda');
define('UPLOAD_DIR', __DIR__ . '/../img/uploads/');
define('UPLOAD_URL', rtrim(BASE_URL, '/') . '/img/uploads/');
*/

// AWARDSPACE
define('DB_HOST', 'fdb1032.awardspace.net');
define('DB_PORT', '3306');
define('DB_NAME', '4764063_tienda');
define('DB_USER', '4764063_tienda');
define('DB_PASS', '');
define('BASE_URL', 'http://district.atwebpages.com/');
define('UPLOAD_DIR', __DIR__ . '/../img/uploads/');
define('UPLOAD_URL', rtrim(BASE_URL, '/') . '/img/uploads/');


try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die('Error de conexión con la base de datos: ' . htmlspecialchars($e->getMessage()));
}

// FUNCIONES DE SEGURIDAD Y UTILIDADES

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base !== '' ? $base : '/';
    }

    return ($base !== '' ? $base : '') . '/' . $path;
}

function asset_url(?string $path): string
{
    $path = trim($path ?? '');

    if ($path === '') {
        return '';
    }

    if (preg_match('~^(?:https?:)?//|^data:~i', $path) || $path[0] === '/') {
        return $path;
    }

    return url($path);
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function is_logged(): bool
{
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return is_logged() && ($_SESSION['user']['rol'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged()) {
        flash('warning', 'Debes iniciar sesión para continuar.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('danger', 'No tienes permisos para acceder al panel de administración.');
        redirect('index.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('Token CSRF inválido. Vuelve atrás y recarga la página.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_messages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $messages;
}

function slugify(string $text): string
{
    $converted = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

    if ($converted !== false) {
        $text = $converted;
    }

    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);

    return $text ?: 'item-' . time();
}

function money(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' €';
}

function truncate_text(?string $text, int $limit = 84): string
{
    $text = $text ?? '';

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...', 'UTF-8');
    }

    if (strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(substr($text, 0, max(0, $limit - 3))) . '...';
}

function upload_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al subir la imagen.');
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato no permitido. Usa JPG, PNG o WEBP.');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('La imagen no puede superar 2MB.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }

    return UPLOAD_URL . $filename;
}

function cart_count(): int
{
    $count = 0;

    foreach ($_SESSION['cart'] ?? [] as $item) {
        $count += (int) $item['cantidad'];
    }

    return $count;
}

function get_cart_total(): float
{
    $total = 0;

    foreach ($_SESSION['cart'] ?? [] as $item) {
        $total += (float) $item['precio'] * (int) $item['cantidad'];
    }

    return $total;
}

function cart_item_key(int $productId, string $size, string $color): string
{
    return $productId . '|' . rawurlencode($size) . '|' . rawurlencode($color);
}

function stock_available(PDO $pdo, int $productId, string $size, ?string $color = null): int
{
    if ($color !== null && $color !== '') {
        $stmt = $pdo->prepare('SELECT unidades FROM stock WHERE producto_id = ? AND talla = ? AND color = ?');
        $stmt->execute([$productId, $size, $color]);

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    $stmt = $pdo->prepare('SELECT COALESCE(SUM(unidades), 0) FROM stock WHERE producto_id = ? AND talla = ?');
    $stmt->execute([$productId, $size]);

    return (int) ($stmt->fetchColumn() ?: 0);
}
