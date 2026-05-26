<?php
require_once 'proteger.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/aulas.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    die('ID da aula inválido.');
}

try {
    $stmt = $conn->prepare("
        DELETE FROM aulas
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    header('Location: ../admin/aulas.php?sucesso=aula_apagada');
    exit;

} catch (PDOException $e) {
    die('Erro ao apagar aula: ' . $e->getMessage());
}
?>