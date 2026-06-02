<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT 
                c.id,
                c.nome,
                c.email,
                c.telefone,
                c.tipo_interesse,
                c.estado,
                GROUP_CONCAT(cv.nome SEPARATOR ', ') AS cavalos
            FROM clientes c
            LEFT JOIN clientes_cavalos cc ON c.id = cc.cliente_id
            LEFT JOIN cavalos cv ON cc.cavalo_id = cv.id
            GROUP BY c.id
            ORDER BY c.id DESC";

    $stmt = $conn->query($sql);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clientes);
} catch (PDOException $e) {
    echo json_encode([
        "erro" => "Erro ao listar clientes: " . $e->getMessage()
    ]);
}
?>