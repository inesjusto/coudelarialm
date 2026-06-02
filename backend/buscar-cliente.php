<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(["erro" => "ID inválido."]);
    exit;
}

try {
    $sql = "SELECT * FROM clientes WHERE id = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);

    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        echo json_encode(["erro" => "Cliente não encontrado."]);
        exit;
    }

    $sqlCavalos = "SELECT cavalo_id 
                   FROM clientes_cavalos 
                   WHERE cliente_id = :cliente_id";

    $stmtCavalos = $conn->prepare($sqlCavalos);
    $stmtCavalos->execute([':cliente_id' => $id]);

    $cliente['cavalos'] = array_map('intval', $stmtCavalos->fetchAll(PDO::FETCH_COLUMN));

    echo json_encode($cliente);
} catch (PDOException $e) {
    echo json_encode(["erro" => "Erro ao buscar cliente: " . $e->getMessage()]);
}
?>