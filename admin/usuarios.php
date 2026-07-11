<?php

$pageTitle = 'Usuarios admin';
require_once __DIR__ . '/_header.php';

$users = $pdo->query('SELECT id, nombre, email, rol, activo, creado_en FROM usuarios ORDER BY creado_en DESC')->fetchAll();
?>

<h1 class="fw-black display-6 mb-4">Usuarios</h1>

<div class="surface-card p-4">
    <table class="table align-middle">
        <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Alta</th></tr></thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int) $user['id'] ?></td>
                    <td><?= e($user['nombre']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><span class="badge badge-soft"><?= e($user['rol']) ?></span></td>
                    <td><?= $user['activo'] ? 'Sí' : 'No' ?></td>
                    <td><?= e(date('d/m/Y', strtotime($user['creado_en']))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
