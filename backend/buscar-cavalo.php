<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_GET['id'])) {
        echo json_encode(["erro" => "ID não fornecido."]);
        exit;
    }

    $id = (int) $_GET['id'];

    if ($id <= 0) {
        echo json_encode(["erro" => "ID inválido."]);
        exit;
    }

    $sql = "SELECT 
                id,
                nome,
                raca,
                sexo,
                idade,
                altura,
                cor,
                preco,
                estado,
                descricao,
                imagem
            FROM cavalos
            WHERE id = :id
            LIMIT 1";

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