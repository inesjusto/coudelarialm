<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_GET['id'])) {
        echo json_encode(["erro" => "ID não fornecido."]);
        exit;
    }

    $id = (int) $_GET['id'];

    $sql = "SELECT id, nome, sexo, idade, raca, preco, descricao, imagem
            FROM cavalos
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $cavalo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cavalo) {
        echo json_encode(["erro" => "Cavalo não encontrado."]);
        exit;
    }

    echo json_encode($cavalo);
} catch (PDOException $e) {
    echo json_encode(["erro" => "Erro ao buscar cavalo: " . $e->getMessage()]);
}
?>