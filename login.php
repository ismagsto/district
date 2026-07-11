<?php

require_once __DIR__ . '/php/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('
        SELECT *
        FROM usuarios
        WHERE email = ? AND activo = 1
        LIMIT 1
    ');

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'     => $user['id'],
            'nombre' => $user['nombre'],
            'email'  => $user['email'],
            'rol'    => $user['rol']
        ];

        flash('success', 'Sesión iniciada correctamente.');

        redirect($user['rol'] === 'admin' ? 'admin/index.php' : 'cuenta.php');
    }

    flash('danger', 'Email o contraseña incorrectos.');

    redirect('login.php');
}

$pageTitle = 'Login';

require_once __DIR__ . '/php/header.php';

?>

<section class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="surface-card p-4 p-lg-5">
                <h1 class="fw-black mb-3">Entrar</h1>

                <p class="muted-text">
                    Demo admin: admin@tienda.local / Admin1234
                </p>

                <form method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="email">
                            Email
                        </label>

                        <input 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            type="email" 
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">
                            Contraseña
                        </label>

                        <input 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            type="password" 
                            required
                        >
                    </div>

                    <button class="btn btn-accent w-100" type="submit">
                        Iniciar sesión
                    </button>
                </form>

                <p class="mt-3 mb-0 muted-text">
                    ¿No tienes cuenta? 
                    <a class="text-accent" href="<?= url('registro.php') ?>">
                        Regístrate
                    </a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
