<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido.');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$estado = trim($_POST['estado'] ?? '');

$estadosPermitidos = ['ativo', 'concluido', 'cancelado'];

if ($id <= 0) {
    die('ID de aluguer inválido.');
}

if (!in_array($estado, $estadosPermitidos, true)) {
    die('Estado inválido.');
}

try {
    $stmt = $conn->prepare("
        UPDATE alugueres
        SET estado = :estado
        WHERE id = :id
    ");

    $stmt->bindValue(':estado', $estado);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: ../admin/alugueres.php');
    exit;
} catch (PDOException $e) {
    die('Erro ao alterar estado do aluguer: ' . $e->getMessage());
}
?>