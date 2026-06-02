<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["erro" => "ID do fornecedor não enviado."]);
    exit;
}

try {
    $sql = "SELECT * FROM fornecedores WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);

    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fornecedor) {
        echo json_encode(["erro" => "Fornecedor não encontrado."]);
        exit;
    }

    echo json_encode($fornecedor);
} catch (PDOException $e) {
    echo json_encode(["erro" => "Erro ao buscar fornecedor."]);
}
?>