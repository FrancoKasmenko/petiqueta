<?php

session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: perfil.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../app/core/config.php';

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Completa todos los campos.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM usuario WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            header('Location: perfil');
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Login - Petiqueta</title>
    <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif !important;
            background-color: #fcf4e7;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --marron: #7a5c39;
            --caramelo: #c89e6a;
            --beige: #fcf4e7;
            --blanco-caldo: #fffaf3;
        }
    </style>
</head>

<body class="bg-[var(--beige)] min-h-screen font-sans flex flex-col">

    <?php include '../templates/navbar.php'; ?>

    <main class="flex-grow flex items-center justify-center px-4 py-16">
        <section class="bg-[var(--blanco-caldo)] rounded-2xl shadow-lg max-w-md w-full p-8">
            <h1 class="text-3xl font-extrabold mb-8 text-[var(--marron)] text-center">Iniciar Sesión</h1>

            <?php if ($error): ?>
                <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded border border-red-400"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="flex flex-col gap-6" novalidate>
                <label class="flex flex-col text-[var(--marron)] font-semibold">
                    Correo electrónico
                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="tu@correo.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        class="mt-1 rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--caramelo)]" />
                </label>

                <label class="flex flex-col text-[var(--marron)] font-semibold">
                    Contraseña
                    <input
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        minlength="6"
                        class="mt-1 rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--caramelo)]" />
                </label>

                <button
                    type="submit"
                    class="bg-[var(--caramelo)] text-white py-3 rounded-lg font-bold hover:bg-[var(--marron)] transition shadow-md active:scale-95">
                    Entrar
                </button>

                <div class="mt-4 text-center">
                    <a href="recuperar_password" class="text-[var(--caramelo)] font-semibold text-sm hover:underline hover:text-[var(--marron)] transition">
                        Olvidé mi contraseña
                    </a>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-[var(--marron)]">
                ¿No tenés cuenta?
                <a href="registro" class="text-[var(--caramelo)] font-semibold hover:underline">Registrate acá</a>
            </p>
        </section>
    </main>

    <?php include '../templates/footer.php'; ?>

</body>

</html>
