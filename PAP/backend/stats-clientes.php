<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$agrupamento = $_GET['agrupamento'] ?? 'tipo';

if ($agrupamento !== 'tipo') {
    echo json_encode(['erro' => 'Agrupamento inválido.']);
    exit;
}

try {
    $sql = "
        SELECT TRIM(tipo) AS label, COUNT(*) AS total
        FROM clientes
        WHERE tipo IS NOT NULL AND TRIM(tipo) <> ''
        GROUP BY TRIM(tipo)
        ORDER BY total DESC, label ASC
    ";

    $stmt = $conn->query($sql);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dadosFormatados = array_map(function ($item) {
        return [
            'label' => $item['label'] !== '' ? $item['label'] : 'Sem tipo',
            'total' => (int) $item['total']
        ];
    }, $dados);

    $totalStmt = $conn->query("SELECT COUNT(*) AS total FROM clientes");
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'total' => (int)($total['total'] ?? 0),
        'agrupamento' => $agrupamento,
        'dados' => $dadosFormatados
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao obter estatísticas dos clientes: ' . $e->getMessage()
    ]);
}
?>