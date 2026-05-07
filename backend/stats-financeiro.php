<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Total de despesas do mês atual
    $sqlMes = "SELECT COALESCE(SUM(valor), 0) AS total
               FROM despesas
               WHERE MONTH(data_despesa) = MONTH(CURDATE())
               AND YEAR(data_despesa) = YEAR(CURDATE())";

    $totalMes = $conn->query($sqlMes)->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de despesas do ano atual
    $sqlAno = "SELECT COALESCE(SUM(valor), 0) AS total
               FROM despesas
               WHERE YEAR(data_despesa) = YEAR(CURDATE())";

    $totalAno = $conn->query($sqlAno)->fetch(PDO::FETCH_ASSOC)['total'];

    // Total pendente
    $sqlPendente = "SELECT COALESCE(SUM(valor), 0) AS total
                    FROM despesas
                    WHERE estado_pagamento = 'pendente'";

    $totalPendente = $conn->query($sqlPendente)->fetch(PDO::FETCH_ASSOC)['total'];

    // Despesas por categoria
    $sqlCategorias = "SELECT 
                        categoria,
                        COALESCE(SUM(valor), 0) AS total
                      FROM despesas
                      GROUP BY categoria
                      ORDER BY total DESC";

    $categorias = $conn->query($sqlCategorias)->fetchAll(PDO::FETCH_ASSOC);

    // Custo por cavalo no mês atual
    $sqlCavalos = "SELECT 
                        c.nome AS cavalo,
                        COALESCE(SUM(d.valor), 0) AS total
                   FROM despesas d
                   INNER JOIN cavalos c ON d.cavalo_id = c.id
                   WHERE MONTH(d.data_despesa) = MONTH(CURDATE())
                   AND YEAR(d.data_despesa) = YEAR(CURDATE())
                   GROUP BY d.cavalo_id, c.nome
                   ORDER BY total DESC";

    $custosCavalos = $conn->query($sqlCavalos)->fetchAll(PDO::FETCH_ASSOC);

    // Estados de pagamento
    $sqlEstados = "SELECT 
                        estado_pagamento,
                        COUNT(*) AS quantidade,
                        COALESCE(SUM(valor), 0) AS total
                   FROM despesas
                   GROUP BY estado_pagamento";

    $estados = $conn->query($sqlEstados)->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'sucesso' => true,
        'total_mes' => (float) $totalMes,
        'total_ano' => (float) $totalAno,
        'total_pendente' => (float) $totalPendente,
        'categorias' => $categorias,
        'custos_cavalos' => $custosCavalos,
        'estados' => $estados
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao carregar estatísticas financeiras: ' . $e->getMessage()
    ]);
}
?>x