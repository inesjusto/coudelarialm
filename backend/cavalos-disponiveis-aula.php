<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$data = $_GET['data'] ?? '';

if ($data === '') {
    echo json_encode([]);
    exit;
}

try {
    $sql = "
        SELECT 
            c.id,
            c.nome
        FROM cavalos c
        WHERE NOT EXISTS (
            SELECT 1
            FROM alugueres a
            WHERE a.cavalo_id = c.id
              AND a.estado = 'ativo'
              AND :data_aula BETWEEN a.data_inicio AND a.data_fim
        )
        ORDER BY c.nome ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':data_aula' => $data
    ]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao carregar cavalos disponíveis: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>