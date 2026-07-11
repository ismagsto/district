<?php

require_once __DIR__ . '/php/config.php';

$pageTitle = 'Registro';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        flash('danger', 'Revisa los datos. La contraseña debe tener mínimo 8 caracteres.');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Cuenta creada. Ya puedes iniciar sesión.');
            redirect('login.php');
        } catch (PDOException $e) {
            flash('danger', 'Ese email ya está registrado.');
        }
    }
}

require_once __DIR__ . '/php/header.php';
?>

<section class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="surface-card p-4 p-lg-5">
                <h1 class="fw-black mb-3">Crear cuenta</h1>

                <form method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input class="form-control" id="nombre" name="nombre" required minlength="2">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Contraseña</label>
                        <input class="form-control" id="password" name="password" type="password" required minlength="8">
                    </div>

                    <button class="btn btn-accent w-100" type="submit">Registrarme</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
