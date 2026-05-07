<?php
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT 
                id,
                nome,
                nif,
                telefone,
                email,
                morada,
                tipo_fornecedor,
                observacoes,
                data_criacao
            FROM fornecedores
            ORDER BY id DESC";

    $stmt = $conn->query($sql);
    $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($fornecedores);
} catch (PDOException $e) {
    echo json_encode([
        "erro" => "Erro ao listar fornecedores: " . $e->getMessage()
    ]);
}
?>