<?php
// Iniciar sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o utilizador está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/login.php');
    exit;
}
?>