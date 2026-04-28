<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$agrupamento = $_GET['agrupamento'] ?? 'sexo';

$permitidos = ['sexo', 'raca'];
if (!in_array($agrupamento, $permitidos, true)) {
    echo json_encode(['erro' => 'Agrupamento inválido.']);
    exit;
}

try {
    if ($agrupamento === 'sexo') {
        $sql = "
            SELECT TRIM(sexo) AS label, COUNT(*) AS total
            FROM cavalos
            WHERE sexo IS NOT NULL AND TRIM(sexo) <> ''
            GROUP BY TRIM(sexo)
            ORDER BY total DESC, label ASC
        ";
    } else {
        $sql = "
            SELECT TRIM(raca) AS label, COUNT(*) AS total
            FROM cavalos
            WHERE raca IS NOT NULL AND TRIM(raca) <> ''
            GROUP BY TRIM(raca)
            ORDER BY total DESC, label ASC
        ";
    }

    $stmt = $conn->query($sql);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dadosFormatados = array_map(function ($item) {
        return [
            'label' => $item['label'] !== '' ? $item['label'] : 'Sem dados',
            'total' => (int) $item['total']
        ];
    }, $dados);

    $totalStmt = $conn->query("SELECT COUNT(*) AS total FROM cavalos");
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'total' => (int)($total['total'] ?? 0),
        'agrupamento' => $agrupamento,
        'dados' => $dadosFormatados
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao obter estatísticas dos cavalos: ' . $e->getMessage()
    ]);
}
?>