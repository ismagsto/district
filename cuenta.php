<?php

require_once __DIR__ . '/php/config.php';
require_login();

$pageTitle = 'Mi cuenta';
require_once __DIR__ . '/php/header.php';

$userId = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $stmt = $pdo->prepare('
        UPDATE usuarios
        SET nombre = ?, telefono = ?, direccion = ?, ciudad = ?, provincia = ?, codigo_postal = ?
        WHERE id = ?
    ');

    $stmt->execute([
        trim($_POST['nombre'] ?? ''),
        trim($_POST['telefono'] ?? ''),
        trim($_POST['direccion'] ?? ''),
        trim($_POST['ciudad'] ?? ''),
        trim($_POST['provincia'] ?? ''),
        trim($_POST['codigo_postal'] ?? ''),
        $userId
    ]);

    $_SESSION['user']['nombre'] = trim($_POST['nombre'] ?? current_user()['nombre']);
    flash('success', 'Datos actualizados.');
    redirect('cuenta.php');
}

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>

<section class="container">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="surface-card p-4">
                <h1 class="h3 fw-black">Mi cuenta</h1>
                <p class="muted-text mb-0">Gestiona tus datos, pedidos y favoritos.</p>
                <hr>
                <a class="btn btn-outline-light w-100 mb-2" href="<?= url('pedidos.php') ?>">Mis pedidos</a>
                <a class="btn btn-outline-light w-100" href="<?= url('favoritos.php') ?>">Favoritos</a>
            </div>
        </div>

        <div class="col-lg-8">
            <form class="surface-card p-4" method="post">
                <?= csrf_field() ?>
                <h2 class="h4 fw-bold mb-3">Mis datos</h2>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input class="form-control" name="nombre" value="<?= e($user['nombre']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" value="<?= e($user['email']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input class="form-control" name="telefono" value="<?= e($user['telefono']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Código postal</label>
                        <input class="form-control" name="codigo_postal" value="<?= e($user['codigo_postal']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Dirección</label>
                        <input class="form-control" name="direccion" value="<?= e($user['direccion']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ciudad</label>
                        <input class="form-control" name="ciudad" value="<?= e($user['ciudad']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Provincia</label>
                        <input class="form-control" name="provincia" value="<?= e($user['provincia']) ?>">
                    </div>
                </div>

                <button class="btn btn-accent mt-4" type="submit">Guardar cambios</button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
