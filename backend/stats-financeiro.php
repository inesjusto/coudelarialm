<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {

    // RECEITA DE AULAS REALIZADAS
    $sqlReceitaAulas = "
        SELECT COALESCE(SUM(preco), 0) AS total
        FROM aulas
        WHERE TRIM(LOWER(estado)) = 'realizada'
    ";

    $receitaAulas = $conn->query($sqlReceitaAulas)->fetch(PDO::FETCH_ASSOC)['total'];

    /*
        RECEITA DE ALUGUERES ATÉ HOJE

        Regras:
        - reservado/futuro não conta.
        - cancelado não conta.
        - ativo conta só até hoje.
        - concluido conta até data_fim.
        - nunca calcula valores negativos.
        - usa a regra dos alugueres:
          10/06 até 20/06 = 12 dias.
    */
    $sqlReceitaAlugueres = "
        SELECT COALESCE(SUM(
            CASE
                WHEN fim_calculo < inicio_calculo THEN 0
                ELSE (
                    (
                        DATEDIFF(fim_calculo, inicio_calculo) + 1
                        + CASE 
                            WHEN fim_calculo > inicio_calculo THEN 1
                            ELSE 0
                          END
                    ) * preco_diario
                )
            END
        ), 0) AS total
        FROM (
            SELECT
                preco_diario,
                data_inicio AS inicio_calculo,
                CASE
                    WHEN TRIM(LOWER(estado)) = 'ativo' THEN CURDATE()
                    ELSE data_fim
                END AS fim_calculo
            FROM alugueres
            WHERE TRIM(LOWER(estado)) IN ('ativo', 'concluido')
              AND data_inicio IS NOT NULL
              AND data_fim IS NOT NULL
              AND data_inicio <= CURDATE()
        ) AS calculo_alugueres
    ";

    $receitaAlugueres = $conn->query($sqlReceitaAlugueres)->fetch(PDO::FETCH_ASSOC)['total'];

    // RECEITA DE VENDAS DE CAVALOS
    $sqlReceitaVendas = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM vendas_cavalos
    ";

    $receitaVendas = $conn->query($sqlReceitaVendas)->fetch(PDO::FETCH_ASSOC)['total'];

    // NÚMERO DE VENDAS
    $sqlNumeroVendas = "
        SELECT COUNT(*) AS total
        FROM vendas_cavalos
    ";

    $numeroVendas = $conn->query($sqlNumeroVendas)->fetch(PDO::FETCH_ASSOC)['total'];

    // TOTAL DE DESPESAS
    $sqlDespesasTotal = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE TRIM(LOWER(estado_pagamento)) != 'cancelado'
    ";

    $despesasTotal = $conn->query($sqlDespesasTotal)->fetch(PDO::FETCH_ASSOC)['total'];

    // DESPESAS DO MÊS
    $sqlDespesasMes = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE TRIM(LOWER(estado_pagamento)) != 'cancelado'
          AND MONTH(data_despesa) = MONTH(CURDATE())
          AND YEAR(data_despesa) = YEAR(CURDATE())
    ";

    $despesasMes = $conn->query($sqlDespesasMes)->fetch(PDO::FETCH_ASSOC)['total'];

    // DESPESAS DO ANO
    $sqlDespesasAno = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE TRIM(LOWER(estado_pagamento)) != 'cancelado'
          AND YEAR(data_despesa) = YEAR(CURDATE())
    ";

    $despesasAno = $conn->query($sqlDespesasAno)->fetch(PDO::FETCH_ASSOC)['total'];

    // DESPESAS PENDENTES
    $sqlPendentes = "
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE TRIM(LOWER(estado_pagamento)) = 'pendente'
    ";

    $despesasPendentes = $conn->query($sqlPendentes)->fetch(PDO::FETCH_ASSOC)['total'];

    // DESPESAS POR CATEGORIA
    $sqlCategorias = "
        SELECT 
            categoria,
            COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE TRIM(LOWER(estado_pagamento)) != 'cancelado'
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
        WHERE TRIM(LOWER(d.estado_pagamento)) != 'cancelado'
        GROUP BY d.cavalo_id, c.nome
        ORDER BY total DESC
    ";

    $custosCavalos = $conn->query($sqlCustosCavalos)->fetchAll(PDO::FETCH_ASSOC);

    // RECEITA TOTAL
    $receitaTotal = (float)$receitaAulas + (float)$receitaAlugueres + (float)$receitaVendas;

    // LUCRO GERAL
    $lucroGeral = $receitaTotal - (float)$despesasTotal;

    echo json_encode([
        'sucesso' => true,

        'receita_aulas' => (float)$receitaAulas,
        'receita_alugueres' => (float)$receitaAlugueres,
        'receita_vendas' => (float)$receitaVendas,
        'numero_vendas' => (int)$numeroVendas,
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