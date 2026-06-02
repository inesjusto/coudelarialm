<?php
require_once __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$ano = isset($_GET['ano']) ? (int) $_GET['ano'] : (int) date('Y');
$mes = isset($_GET['mes']) && $_GET['mes'] !== '' ? (int) $_GET['mes'] : null;

if ($ano < 2020 || $ano > (int) date('Y') + 1) {
    $ano = (int) date('Y');
}

if ($mes !== null && ($mes < 1 || $mes > 12)) {
    $mes = null;
}

$meses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

if ($mes === null) {
    $dataInicio = $ano . '-01-01';
    $dataFim = $ano . '-12-31';
    $tituloPeriodo = 'Ano ' . $ano;
    $nomeFicheiro = 'relatorio-financeiro-' . $ano . '.pdf';
} else {
    $dataInicio = date('Y-m-01', strtotime($ano . '-' . $mes . '-01'));
    $dataFim = date('Y-m-t', strtotime($ano . '-' . $mes . '-01'));
    $tituloPeriodo = $meses[$mes] . ' ' . $ano;
    $nomeFicheiro = 'relatorio-financeiro-' . strtolower($meses[$mes]) . '-' . $ano . '.pdf';
}

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

try {
    $stmtReceitaAulas = $conn->prepare("
        SELECT COALESCE(SUM(preco), 0) AS total
        FROM aulas
        WHERE estado = 'realizada'
          AND data_aula BETWEEN :data_inicio AND :data_fim
    ");

    $stmtReceitaAulas->execute([
        ':data_inicio' => $dataInicio,
        ':data_fim' => $dataFim
    ]);

    $receitaAulas = $stmtReceitaAulas->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtReceitaAlugueres = $conn->prepare("
        SELECT COALESCE(SUM(
            (
                DATEDIFF(
                    LEAST(
                        CASE
                            WHEN estado = 'ativo' THEN CURDATE()
                            ELSE data_fim
                        END,
                        :data_fim_1
                    ),
                    GREATEST(data_inicio, :data_inicio_1)
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
          AND data_inicio <= :data_fim_2
          AND CASE
                WHEN estado = 'ativo' THEN CURDATE()
                ELSE data_fim
              END >= :data_inicio_2
    ");

    $stmtReceitaAlugueres->execute([
        ':data_inicio_1' => $dataInicio,
        ':data_inicio_2' => $dataInicio,
        ':data_fim_1' => $dataFim,
        ':data_fim_2' => $dataFim
    ]);

    $receitaAlugueres = $stmtReceitaAlugueres->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtDespesasTotal = $conn->prepare("
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE estado_pagamento != 'cancelado'
          AND data_despesa BETWEEN :data_inicio AND :data_fim
    ");

    $stmtDespesasTotal->execute([
        ':data_inicio' => $dataInicio,
        ':data_fim' => $dataFim
    ]);

    $despesasTotal = $stmtDespesasTotal->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtCategorias = $conn->prepare("
        SELECT 
            categoria,
            COALESCE(SUM(valor), 0) AS total
        FROM despesas
        WHERE estado_pagamento != 'cancelado'
          AND data_despesa BETWEEN :data_inicio AND :data_fim
        GROUP BY categoria
        ORDER BY total DESC
    ");

    $stmtCategorias->execute([
        ':data_inicio' => $dataInicio,
        ':data_fim' => $dataFim
    ]);

    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

    $stmtCustosCavalos = $conn->prepare("
        SELECT 
            c.nome,
            COALESCE(SUM(d.valor), 0) AS total
        FROM despesas d
        INNER JOIN cavalos c ON d.cavalo_id = c.id
        WHERE d.estado_pagamento != 'cancelado'
          AND d.data_despesa BETWEEN :data_inicio AND :data_fim
        GROUP BY c.id, c.nome
        ORDER BY total DESC
    ");

    $stmtCustosCavalos->execute([
        ':data_inicio' => $dataInicio,
        ':data_fim' => $dataFim
    ]);

    $custosCavalos = $stmtCustosCavalos->fetchAll(PDO::FETCH_ASSOC);

    $receitaTotal = (float) $receitaAulas + (float) $receitaAlugueres;
    $despesasTotal = (float) $despesasTotal;
    $lucroGeral = $receitaTotal - $despesasTotal;

    $totalCustosCavalos = 0;

    foreach ($custosCavalos as $cavalo) {
        $totalCustosCavalos += (float) $cavalo['total'];
    }

    ob_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            padding: 30px;
        }

        .topo {
            border-bottom: 3px solid #22c55e;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        h1 {
            color: #16a34a;
            margin: 0;
            font-size: 28px;
        }

        .subtitulo {
            color: #6b7280;
            font-size: 13px;
            margin-top: 6px;
            line-height: 1.6;
        }

        .cards {
            margin-bottom: 30px;
        }

        .card {
            display: inline-block;
            width: 30%;
            margin-right: 2%;
            padding: 15px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            text-align: center;
            vertical-align: top;
        }

        .card span {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .card strong {
            font-size: 22px;
            color: #111827;
        }

        .positivo {
            color: #16a34a !important;
        }

        .negativo {
            color: #dc2626 !important;
        }

        h2 {
            border-left: 5px solid #22c55e;
            padding-left: 10px;
            margin-top: 30px;
            font-size: 20px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th {
            background: #16a34a;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 13px;
        }

        td {
            padding: 9px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .linha-total th {
            background: #111827;
            color: #ffffff;
            font-size: 14px;
        }

        .rodape {
            margin-top: 40px;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }
    </style>
</head>
<body>

    <div class="topo">
        <h1>Coudelaria Lima Monteiro</h1>

        <div class="subtitulo">
            Relatório Financeiro - <?= htmlspecialchars($tituloPeriodo) ?><br>
            Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> até <?= date('d/m/Y', strtotime($dataFim)) ?><br>
            Gerado em <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <div class="cards">
        <div class="card">
            <span>Receita Total</span>
            <strong><?= number_format($receitaTotal, 2, ',', '.') ?> €</strong>
        </div>

        <div class="card">
            <span>Despesas Totais</span>
            <strong><?= number_format($despesasTotal, 2, ',', '.') ?> €</strong>
        </div>

        <div class="card">
            <span>Lucro Geral</span>
            <strong class="<?= $lucroGeral >= 0 ? 'positivo' : 'negativo' ?>">
                <?= number_format($lucroGeral, 2, ',', '.') ?> €
            </strong>
        </div>
    </div>

    <h2>Resumo das Receitas</h2>

    <table>
        <thead>
            <tr>
                <th>Origem</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Aulas realizadas</td>
                <td><?= number_format((float) $receitaAulas, 2, ',', '.') ?> €</td>
            </tr>

            <tr>
                <td>Alugueres do período</td>
                <td><?= number_format((float) $receitaAlugueres, 2, ',', '.') ?> €</td>
            </tr>

            <tr class="linha-total">
                <th>Total das Receitas</th>
                <th><?= number_format($receitaTotal, 2, ',', '.') ?> €</th>
            </tr>
        </tbody>
    </table>

    <h2>Despesas por Categoria</h2>

    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($categorias)): ?>
                <tr>
                    <td colspan="2">Sem despesas registadas neste período.</td>
                </tr>
                <tr class="linha-total">
                    <th>Total das Despesas</th>
                    <th><?= number_format($despesasTotal, 2, ',', '.') ?> €</th>
                </tr>
            <?php else: ?>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?= htmlspecialchars($categoria['categoria']) ?></td>
                        <td><?= number_format((float) $categoria['total'], 2, ',', '.') ?> €</td>
                    </tr>
                <?php endforeach; ?>

                <tr class="linha-total">
                    <th>Total das Despesas</th>
                    <th><?= number_format($despesasTotal, 2, ',', '.') ?> €</th>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Custos por Cavalo</h2>

    <table>
        <thead>
            <tr>
                <th>Cavalo</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($custosCavalos)): ?>
                <tr>
                    <td colspan="2">Sem custos associados a cavalos neste período.</td>
                </tr>
                <tr class="linha-total">
                    <th>Total dos Custos por Cavalo</th>
                    <th><?= number_format($totalCustosCavalos, 2, ',', '.') ?> €</th>
                </tr>
            <?php else: ?>
                <?php foreach ($custosCavalos as $cavalo): ?>
                    <tr>
                        <td><?= htmlspecialchars($cavalo['nome']) ?></td>
                        <td><?= number_format((float) $cavalo['total'], 2, ',', '.') ?> €</td>
                    </tr>
                <?php endforeach; ?>

                <tr class="linha-total">
                    <th>Total dos Custos por Cavalo</th>
                    <th><?= number_format($totalCustosCavalos, 2, ',', '.') ?> €</th>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="rodape">
        Documento gerado automaticamente pelo sistema da Coudelaria Lima Monteiro.
    </div>

</body>
</html>
<?php
    $html = ob_get_clean();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream($nomeFicheiro, [
        'Attachment' => false
    ]);

} catch (Exception $e) {
    die('Erro ao gerar PDF: ' . $e->getMessage());
}
?>