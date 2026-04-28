<?php
session_start();
include __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['erro_login'] = 'Preencha todos os campos.';
    header('Location: ../admin/login.php');
    exit;
}

try {
    $sql = "SELECT id, username, password
            FROM administradores
            WHERE username = :username
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $username]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        $_SESSION['erro_login'] = 'Utilizador não encontrado.';
        header('Location: ../admin/login.php');
        exit;
    }

    $passwordBD = (string)$admin['password'];
    $loginValido = false;

    if (password_verify($password, $passwordBD)) {
        $loginValido = true;
    } elseif ($password === $passwordBD) {
        $loginValido = true;

        $novoHash = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE administradores SET password = :password WHERE id = :id");
        $update->execute([
            ':password' => $novoHash,
            ':id' => $admin['id']
        ]);
    }

    if ($loginValido) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header('Location: ../admin/dashboard.php');
        exit;
    } else {
        $_SESSION['erro_login'] = 'Palavra-passe incorreta.';
        header('Location: ../admin/login.php');
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['erro_login'] = 'Erro na base de dados: ' . $e->getMessage();
    header('Location: ../admin/login.php');
    exit;
}