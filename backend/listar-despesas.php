<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT 
            d.id,
            d.fornecedor_id,
            f.nome AS fornecedor_nome,
            d.cavalo_id,
            c.nome AS cavalo_nome,
            d.categoria,
            d.descricao,
            d.valor,
            d.data_despesa,
            d.metodo_pagamento,
            d.estado_pagamento,
            d.data_criacao
        FROM despesas d
        LEFT JOIN fornecedores f ON d.fornecedor_id = f.id
        LEFT JOIN cavalos c ON d.cavalo_id = c.id
        ORDER BY d.data_despesa DESC, d.id DESC";

    $stmt = $conn->query($sql);
    $despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($despesas);
} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao listar despesas: ' . $e->getMessage()
    ]);
}
?>