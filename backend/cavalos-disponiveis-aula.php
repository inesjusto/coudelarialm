<?php
require_once __DIR__ . '/proteger.php';
require_once __DIR__ . '/conexao.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$data = trim($_GET['data'] ?? '');

if ($data === '') {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    echo json_encode([
        'erro' => 'Data inválida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    
    $stmt = $conn->prepare("
        SELECT 
            c.id,
            c.nome
        FROM cavalos c
        WHERE TRIM(LOWER(c.estado)) IN ('disponível', 'disponivel')
          AND NOT EXISTS (
              SELECT 1
              FROM alugueres a
              WHERE a.cavalo_id = c.id
                AND COALESCE(TRIM(LOWER(a.estado)), '') != 'cancelado'
                AND DATE(:data_aula) >= DATE(a.data_inicio)
                AND DATE(:data_aula) <= DATE(COALESCE(a.data_fim, '9999-12-31'))
          )
        ORDER BY c.nome ASC
    ");

    $stmt->execute([
        ':data_aula' => $data
    ]);

    $cavalos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cavalos, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao carregar cavalos disponíveis: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
