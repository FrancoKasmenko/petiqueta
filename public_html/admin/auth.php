<?php
function loginUser($usuario, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ? AND rol = 'admin' LIMIT 1");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_user'] = $user['id_usuario'];
        $_SESSION['admin_email'] = $user['email'];
        return true;
    }
    return false;
}

function checkLogin() {
    return isset($_SESSION['admin_user']);
}

function requireAdmin() {
    if (!checkLogin()) {
        header('Location: index.php');
        exit;
    }
}

function logoutUser() {
    session_destroy();
}
