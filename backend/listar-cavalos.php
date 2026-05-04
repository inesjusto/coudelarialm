<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT 
                c.id,
                c.nome,
                c.sexo,
                c.idade,
                c.raca,
                c.preco,
                c.descricao,
                c.imagem,
                CASE 
                    WHEN a.id IS NOT NULL THEN 'alugado'
                    ELSE 'disponivel'
                END AS estado_aluguer
            FROM cavalos c
            LEFT JOIN alugueres a 
                ON c.id = a.cavalo_id 
                AND a.estado = 'ativo'
            ORDER BY c.id DESC";

    $stmt = $conn->query($sql);
    $cavalos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cavalos);
} catch (PDOException $e) {
    echo json_encode([
        "erro" => "Erro ao listar cavalos: " . $e->getMessage()
    ]);
}
?>