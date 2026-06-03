<?php
require_once __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: vendas.php?erro=venda_nao_encontrada');
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        v.id,
        v.data_venda,
        v.valor,
        v.metodo_pagamento,
        v.observacoes,
        v.data_criacao,

        cl.nome AS cliente_nome,
        cl.email AS cliente_email,
        cl.telefone AS cliente_telefone,
        cl.nif AS cliente_nif,

        c.nome AS cavalo_nome,
        c.raca AS cavalo_raca,
        c.sexo AS cavalo_sexo,
        c.data_nascimento AS cavalo_data_nascimento,
        c.altura AS cavalo_altura,
        c.cor AS cavalo_cor,
        c.estado AS cavalo_estado,
        c.descricao AS cavalo_descricao

    FROM vendas_cavalos v
    INNER JOIN clientes cl ON cl.id = v.cliente_id
    INNER JOIN cavalos c ON c.id = v.cavalo_id
    WHERE v.id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    header('Location: vendas.php?erro=venda_nao_encontrada');
    exit;
}

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function formatarData($data) {
    if (!$data) return '-';

    return date('d/m/Y', strtotime($data));
}

function formatarValor($valor) {
    return number_format((float) $valor, 2, ',', '.') . ' €';
}

function calcularIdade($dataNascimento) {
    if (!$dataNascimento) {
        return '-';
    }

    try {
        $nascimento = new DateTime($dataNascimento);
        $hoje = new DateTime();
        $idade = $hoje->diff($nascimento)->y;

        return $idade === 1 ? '1 ano' : $idade . ' anos';
    } catch (Exception $e) {
        return '-';
    }
}

$numeroFatura = 'VC-' . str_pad((string) $venda['id'], 5, '0', STR_PAD_LEFT);
$dataAtual = date('d/m/Y');
$idadeCavalo = calcularIdade($venda['cavalo_data_nascimento']);

$nifCoudelaria = '512345678';
$nifCliente = trim((string) ($venda['cliente_nif'] ?? ''));

$taxaIva = 0.23;
$totalComIva = (float) $venda['valor'];
$valorSemIva = $totalComIva / (1 + $taxaIva);
$valorIva = $totalComIva - $valorSemIva;

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

ob_start();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            padding: 28px;
            font-size: 12px;
            line-height: 1.45;
        }

        .topo {
            border-bottom: 3px solid #22c55e;
            padding-bottom: 16px;
            margin-bottom: 22px;
        }

        h1 {
            color: #16a34a;
            margin: 0;
            font-size: 27px;
        }

        .subtitulo {
            color: #6b7280;
            font-size: 13px;
            margin-top: 6px;
            line-height: 1.6;
        }

        .titulo-fatura {
            margin-top: 12px;
            font-size: 19px;
            color: #111827;
            font-weight: bold;
            text-transform: uppercase;
        }

        h2 {
            border-left: 5px solid #22c55e;
            padding-left: 9px;
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #111827;
        }

        .info-fatura {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .info-fatura td {
            padding: 7px 5px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #374151;
            width: 28%;
        }

        .bloco {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 10px;
            padding: 13px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .blocos-linha {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }

        .blocos-linha > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0 7px 0 0;
        }

        .blocos-linha > tbody > tr > td:last-child {
            padding-right: 0;
            padding-left: 7px;
        }

        .tabela-dados {
            width: 100%;
            border-collapse: collapse;
        }

        .tabela-dados td {
            padding: 6px 4px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .tabela-dados tr:last-child td {
            border-bottom: none;
        }

        .tabela-total {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .tabela-total th {
            background: #16a34a;
            color: #ffffff;
            padding: 10px;
            text-align: left;
            font-size: 13px;
        }

        .tabela-total td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .linha-total th {
            background: #111827;
            color: #ffffff;
            font-size: 14px;
        }

        .declaracao {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 13px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .declaracao p {
            margin: 0 0 8px 0;
            text-align: justify;
        }

        .declaracao p:last-child {
            margin-bottom: 0;
        }

        .observacoes {
            white-space: pre-line;
        }

        .nota {
            color: #6b7280;
            font-size: 11px;
            margin-top: 6px;
        }

        .assinaturas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 34px;
            page-break-inside: avoid;
        }

        .assinaturas td {
            width: 50%;
            text-align: center;
            padding-top: 32px;
            font-size: 12px;
        }

        .linha-assinatura {
            border-top: 1px solid #374151;
            padding-top: 7px;
            display: inline-block;
            width: 230px;
        }

        .rodape {
            margin-top: 26px;
            text-align: center;
            color: #6b7280;
            font-size: 10.5px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="topo">
        <h1>Coudelaria Lima Monteiro</h1>

        <div class="subtitulo">
            Fatura de Venda de Cavalo<br>
            NIF da Coudelaria: <?= e($nifCoudelaria) ?><br>
            Documento gerado pelo sistema administrativo no dia <?= e($dataAtual) ?>
        </div>

        <div class="titulo-fatura">
            Fatura <?= e($numeroFatura) ?>
        </div>
    </div>

    <table class="info-fatura">
        <tr>
            <td class="label">N.º Fatura</td>
            <td><?= e($numeroFatura) ?></td>
            <td class="label">Data de emissão</td>
            <td><?= e($dataAtual) ?></td>
        </tr>

        <tr>
            <td class="label">Data da venda</td>
            <td><?= e(formatarData($venda['data_venda'])) ?></td>
            <td class="label">Método de pagamento</td>
            <td><?= e($venda['metodo_pagamento'] ?: '-') ?></td>
        </tr>
    </table>

    <table class="blocos-linha">
        <tr>
            <td>
                <div class="bloco">
                    <h2>Dados do Cliente</h2>

                    <table class="tabela-dados">
                        <tr>
                            <td class="label">Nome</td>
                            <td><?= e($venda['cliente_nome']) ?></td>
                        </tr>

                        <tr>
                            <td class="label">NIF</td>
                            <td><?= e($nifCliente !== '' ? $nifCliente : 'Consumidor final') ?></td>
                        </tr>

                        <tr>
                            <td class="label">Email</td>
                            <td><?= e($venda['cliente_email'] ?: '-') ?></td>
                        </tr>

                        <tr>
                            <td class="label">Telefone</td>
                            <td><?= e($venda['cliente_telefone'] ?: '-') ?></td>
                        </tr>
                    </table>
                </div>
            </td>

            <td>
                <div class="bloco">
                    <h2>Dados do Cavalo</h2>

                    <table class="tabela-dados">
                        <tr>
                            <td class="label">Nome</td>
                            <td><?= e($venda['cavalo_nome']) ?></td>
                        </tr>

                        <tr>
                            <td class="label">Raça</td>
                            <td><?= e($venda['cavalo_raca'] ?: '-') ?></td>
                        </tr>

                        <tr>
                            <td class="label">Sexo</td>
                            <td><?= e($venda['cavalo_sexo'] ?: '-') ?></td>
                        </tr>

                        <tr>
                            <td class="label">Idade</td>
                            <td><?= e($idadeCavalo) ?></td>
                        </tr>

                        <tr>
                            <td class="label">Pelagem</td>
                            <td><?= e($venda['cavalo_cor'] ?: '-') ?></td>
                        </tr>

                        <tr>
                            <td class="label">Altura</td>
                            <td><?= e($venda['cavalo_altura'] ?: '-') ?></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="tabela-total">
        <thead>
            <tr>
                <th>Descrição</th>
                <th style="width: 160px;">Valor</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Venda do cavalo <?= e($venda['cavalo_nome']) ?> — valor sem IVA</td>
                <td><?= e(formatarValor($valorSemIva)) ?></td>
            </tr>

            <tr>
                <td>IVA 23%</td>
                <td><?= e(formatarValor($valorIva)) ?></td>
            </tr>

            <tr class="linha-total">
                <th>Total com IVA</th>
                <th><?= e(formatarValor($totalComIva)) ?></th>
            </tr>
        </tbody>
    </table>

    <div class="declaracao">
        <h2>Declaração de Venda</h2>

        <p>
            A Coudelaria Lima Monteiro, com o NIF
            <strong><?= e($nifCoudelaria) ?></strong>, declara que vendeu ao cliente
            <strong><?= e($venda['cliente_nome']) ?></strong>
            <?php if ($nifCliente !== ''): ?>
                , com o NIF <strong><?= e($nifCliente) ?></strong>,
            <?php else: ?>
                , identificado como <strong>Consumidor final</strong>,
            <?php endif; ?>
            o cavalo <strong><?= e($venda['cavalo_nome']) ?></strong>,
            pelo valor total de <strong><?= e(formatarValor($totalComIva)) ?></strong>,
            incluindo IVA à taxa de 23%, na data de
            <strong><?= e(formatarData($venda['data_venda'])) ?></strong>.
        </p>

        <p>
            Com a emissão deste documento, fica registada a transferência de propriedade do cavalo para o cliente comprador,
            ficando o animal identificado pelos dados apresentados nesta fatura.
        </p>

        <p class="nota">
            Nota: este documento serve como comprovativo interno da venda registada no sistema administrativo da coudelaria.
        </p>
    </div>

    <div class="bloco">
        <h2>Condições da Venda</h2>

        <table class="tabela-dados">
            <tr>
                <td class="label">Estado do cavalo após venda</td>
                <td>Vendido</td>
            </tr>

            <tr>
                <td class="label">Forma de pagamento</td>
                <td><?= e($venda['metodo_pagamento'] ?: '-') ?></td>
            </tr>

            <tr>
                <td class="label">Valor sem IVA</td>
                <td><?= e(formatarValor($valorSemIva)) ?></td>
            </tr>

            <tr>
                <td class="label">IVA aplicado</td>
                <td>23% — <?= e(formatarValor($valorIva)) ?></td>
            </tr>

            <tr>
                <td class="label">Total com IVA</td>
                <td><?= e(formatarValor($totalComIva)) ?></td>
            </tr>

            <tr>
                <td class="label">Data de registo no sistema</td>
                <td><?= e(formatarData($venda['data_criacao'])) ?></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($venda['observacoes'])): ?>
        <div class="bloco">
            <h2>Observações</h2>
            <div class="observacoes"><?= nl2br(e($venda['observacoes'])) ?></div>
        </div>
    <?php endif; ?>

    <table class="assinaturas">
        <tr>
            <td>
                <span class="linha-assinatura">Assinatura da Coudelaria</span>
            </td>

            <td>
                <span class="linha-assinatura">Assinatura do Cliente</span>
            </td>
        </tr>
    </table>

    <div class="rodape">
        Documento gerado automaticamente pelo sistema da Coudelaria Lima Monteiro.
    </div>
</body>
</html>

<?php
$html = ob_get_clean();

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nomeFicheiro = 'fatura-venda-' . $numeroFatura . '.pdf';

$dompdf->stream($nomeFicheiro, [
    'Attachment' => false
]);

exit;