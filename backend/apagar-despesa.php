<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$id = isset($dados['id']) ? (int)$dados['id'] : 0;

if ($id <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM despesas WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['sucesso' => true, 'message' => 'Despesa apagada com sucesso']);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
?>