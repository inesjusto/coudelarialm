<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$agrupamento = $_GET['agrupamento'] ?? 'sexo';

$permitidos = ['sexo', 'raca', 'idade'];

if (!in_array($agrupamento, $permitidos, true)) {
    echo json_encode(['erro' => 'Agrupamento inválido.']);
    exit;
}

try {
    if ($agrupamento === 'sexo') {
        $sql = "
            SELECT 
                TRIM(sexo) AS label, 
                COUNT(*) AS total
            FROM cavalos
            WHERE sexo IS NOT NULL 
              AND TRIM(sexo) <> ''
            GROUP BY TRIM(sexo)
            ORDER BY total DESC, label ASC
        ";
    } elseif ($agrupamento === 'idade') {
        $sql = "
            SELECT
                CASE
                    WHEN data_nascimento IS NULL THEN 'Sem data'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) = 0 THEN 'Menos de 1 ano'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) = 1 THEN '1 ano'
                    ELSE CONCAT(TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()), ' anos')
                END AS label,
                COUNT(*) AS total,
                CASE
                    WHEN data_nascimento IS NULL THEN 999
                    ELSE TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE())
                END AS ordem
            FROM cavalos
            GROUP BY label, ordem
            ORDER BY ordem ASC
        ";
    } else {
        $sql = "
            SELECT 
                TRIM(raca) AS label, 
                COUNT(*) AS total
            FROM cavalos
            WHERE raca IS NOT NULL 
              AND TRIM(raca) <> ''
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

    $disponiveisStmt = $conn->query("
        SELECT COUNT(*) AS total 
        FROM cavalos 
        WHERE TRIM(estado) = 'Disponível'
    ");
    $disponiveis = $disponiveisStmt->fetch(PDO::FETCH_ASSOC);

    $indisponiveisStmt = $conn->query("
        SELECT COUNT(*) AS total 
        FROM cavalos 
        WHERE estado IS NULL 
           OR TRIM(estado) = '' 
           OR TRIM(estado) <> 'Disponível'
    ");
    $indisponiveis = $indisponiveisStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'total' => (int)($total['total'] ?? 0),
        'disponiveis' => (int)($disponiveis['total'] ?? 0),
        'indisponiveis' => (int)($indisponiveis['total'] ?? 0),
        'agrupamento' => $agrupamento,
        'dados' => $dadosFormatados
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao obter estatísticas dos cavalos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>