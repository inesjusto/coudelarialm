<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {

    // RECEITA DE AULAS REALIZADAS
    $sqlReceitaAulas = "
        SELECT COALESCE(SUM(preco), 0) AS total
        FROM aulas
        WHERE estado = 'realizada'
    ";

    $receitaAulas = $conn->query($sqlReceitaAulas)->fetch(PDO::FETCH_ASSOC)['total'];

    // RECEITA DE ALUGUERES ATÉ HOJE
    // Conta alugueres ativos e concluídos.
    // Ativos contam só até à data de hoje.
    // Concluídos contam até à data real de fim.
    $sqlReceitaAlugueres = "
        SELECT COALESCE(SUM(
            (
                DATEDIFF(
                    CASE
                        WHEN estado = 'ativo' THEN CURDATE()
                        ELSE data_fim
                    END,
                    data_inicio
                ) + 1
            ) * preco_diario
        ), 0) AS total
        FROM alugueres
        WHERE estado IN ('ativo', 'concluido')
          AND data_inicio IS NOT NULL
          AND (
              estado = 'ativo'
              OR data_fim IS NOT NULL
          )
    ";

    $receitaAlugueres = $conn->query($sqlReceitaAlugueres)->fetch(PDO::FETCH_ASSOC)['total'];

    // TOTAL DE DESPESAS
    $sqlDespesasTotal = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE estado_pagamento != 'cancelado'
    ";

    $despesasTotal = $conn->query($sqlDespesasTotal)->fetch(PDO::FETCH_ASSOC)['total'];

    // DESPESAS DO MÊS
    $sqlDespesasMes = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE estado_pagamento != 'cancelado'
          AND MONTH(data_despesa) = MONTH(CURDATE())
          AND YEAR(data_despesa) = YEAR(CURDATE())
    ";

    $despesasMes = $conn->query($sqlDespesasMes)->fetch(PDO::FETCH_ASSOC)['total'];

    // DESPESAS DO ANO
    $sqlDespesasAno = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE estado_pagamento != 'cancelado'
          AND YEAR(data_despesa) = YEAR(CURDATE())
    ";

    $despesasAno = $conn->query($sqlDespesasAno)->fetch(PDO::FETCH_ASSOC)['total'];

    // DESPESAS PENDENTES
    $sqlPendentes = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE estado_pagamento = 'pendente'
    ";

    $despesasPendentes = $conn->query($sqlPendentes)->fetch(PDO::FETCH_ASSOC)['total'];

    // DESPESAS POR CATEGORIA
    $sqlCategorias = "
        SELECT 
            categoria,
            COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE estado_pagamento != 'cancelado'
        GROUP BY categoria
        ORDER BY total DESC
    ";

    $categorias = $conn->query($sqlCategorias)->fetchAll(PDO::FETCH_ASSOC);

    // CUSTO POR CAVALO
    $sqlCustosCavalos = "
        SELECT 
            c.nome AS cavalo,
            COALESCE(SUM(d.valor), 0) AS total
        FROM despesas d
        INNER JOIN cavalos c ON d.cavalo_id = c.id
        WHERE d.estado_pagamento != 'cancelado'
        GROUP BY d.cavalo_id, c.nome
        ORDER BY total DESC
    ";

    $custosCavalos = $conn->query($sqlCustosCavalos)->fetchAll(PDO::FETCH_ASSOC);

    // RECEITA TOTAL
    $receitaTotal = (float)$receitaAulas + (float)$receitaAlugueres;

    // LUCRO GERAL
    $lucroGeral = $receitaTotal - (float)$despesasTotal;

    echo json_encode([
        'sucesso' => true,

        'receita_aulas' => (float)$receitaAulas,
        'receita_alugueres' => (float)$receitaAlugueres,
        'receita_total' => (float)$receitaTotal,

        'total_mes' => (float)$despesasMes,
        'total_ano' => (float)$despesasAno,
        'total_pendente' => (float)$despesasPendentes,
        'despesas_total' => (float)$despesasTotal,

        'lucro_geral' => (float)$lucroGeral,

        'categorias' => $categorias,
        'custos_cavalos' => $custosCavalos

    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao carregar estatísticas financeiras: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>