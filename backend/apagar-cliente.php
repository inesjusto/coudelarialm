<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Método inválido.'
    ]);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);

$id = isset($dados['id']) ? (int) $dados['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'ID inválido.'
    ]);
    exit;
}

try {
    $sql = "DELETE FROM clientes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'sucesso' => true,
            'message' => 'Cliente apagado com sucesso.'
        ]);
    } else {
        echo json_encode([
            'sucesso' => false,
            'erro' => 'Cliente não encontrado.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao apagar cliente: ' . $e->getMessage()
    ]);
}
?>