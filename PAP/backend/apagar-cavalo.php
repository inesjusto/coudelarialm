<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'])) {
        echo json_encode(["erro" => "ID não enviado."]);
        exit;
    }

    $id = $dados['id'];

    $sql = "DELETE FROM cavalos WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(["sucesso" => true]);
} catch (PDOException $e) {
    echo json_encode(["erro" => "Erro ao apagar cavalo: " . $e->getMessage()]);
}
?>