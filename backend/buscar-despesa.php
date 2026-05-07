<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['erro' => 'ID inválido.']);
    exit;
}

try {
    $sql = "SELECT * FROM despesas WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);

    $despesa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$despesa) {
        echo json_encode(['erro' => 'Despesa não encontrada.']);
        exit;
    }

    echo json_encode($despesa);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao buscar despesa.']);
}
?>