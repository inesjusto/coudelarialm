<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT id, nome, sexo, idade, raca, preco, descricao, imagem
            FROM cavalos
            ORDER BY id DESC";

    $stmt = $conn->query($sql);
    $cavalos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cavalos);
} catch (PDOException $e) {
    echo json_encode([
        "erro" => "Erro ao listar cavalos: " . $e->getMessage()
    ]);
}
?>