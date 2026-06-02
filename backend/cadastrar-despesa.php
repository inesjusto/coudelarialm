<?php
require_once 'proteger.php';
require_once 'conexao.php';
require_once 'funcoes-formatacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/despesas.php');
    exit;
}

$tipo_registo = $_POST['tipo_registo'] ?? 'manual';

$fornecedor_id = $_POST['fornecedor_id'] ?? null;
$cavalo_id = $_POST['cavalo_id'] ?? null;
$categoria = trim($_POST['categoria'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$valor = trim($_POST['valor'] ?? '');
$data_despesa = $_POST['data_despesa'] ?? '';
$metodo_pagamento = trim($_POST['metodo_pagamento'] ?? '');
$estado_pagamento = $_POST['estado_pagamento'] ?? 'pendente';

$fornecedor_id = empty($fornecedor_id) ? null : (int)$fornecedor_id;
$cavalo_id = empty($cavalo_id) ? null : (int)$cavalo_id;

$categoriasComCalculo = ['Ração', 'Feno', 'Palha', 'Suplementos'];

if (empty($categoria) || empty($data_despesa)) {
    die('Categoria e data são obrigatórias.');
}

if ($tipo_registo === 'cavalo' && empty($cavalo_id)) {
    die('Para uma despesa de cavalo, deve selecionar um cavalo.');
}

$temCalculoAutomatico = $tipo_registo === 'cavalo' && in_array($categoria, $categoriasComCalculo, true);

if ($temCalculoAutomatico) {
    $consumo_diario = normalizarNumeroDecimal($_POST['consumo_diario'] ?? '');
    $unidade = trim($_POST['unidade'] ?? 'kg');
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    $quantidade_por_embalagem = normalizarNumeroDecimal($_POST['quantidade_por_embalagem'] ?? '');
    $preco_embalagem = normalizarValorMonetario($_POST['preco_embalagem'] ?? '');

    if (
        empty($data_inicio) ||
        empty($data_fim) ||
        !is_numeric($consumo_diario) ||
        !is_numeric($quantidade_por_embalagem) ||
        !is_numeric($preco_embalagem) ||
        $consumo_diario <= 0 ||
        $quantidade_por_embalagem <= 0 ||
        $preco_embalagem < 0
    ) {
        die('Dados inválidos no cálculo da despesa.');
    }

    $inicio = new DateTime($data_inicio);
    $fim = new DateTime($data_fim);

    if ($fim < $inicio) {
        die('A data de fim não pode ser anterior à data de início.');
    }

    $dias = $inicio->diff($fim)->days + 1;
    $quantidade_total = (float)$consumo_diario * $dias;
    $embalagens_necessarias = (int) ceil($quantidade_total / (float)$quantidade_por_embalagem);
    $valor_final = $embalagens_necessarias * (float)$preco_embalagem;

    $descricaoCalculo = "Cálculo automático - {$categoria}: {$dias} dias, {$quantidade_total} {$unidade}, {$embalagens_necessarias} embalagem/ns.";

    if (empty($descricao)) {
        $descricao = $descricaoCalculo;
    } else {
        $descricao .= " | " . $descricaoCalculo;
    }

    if (empty($metodo_pagamento)) {
        $metodo_pagamento = 'Automático';
    }
} else {
    $valor_final = normalizarValorMonetario($valor);

    if ($valor_final <= 0) {
        die('O valor da despesa deve ser válido.');
    }
}

try {
    $sql = "INSERT INTO despesas
            (
                fornecedor_id,
                cavalo_id,
                categoria,
                descricao,
                valor,
                data_despesa,
                metodo_pagamento,
                estado_pagamento
            )
            VALUES
            (
                :fornecedor_id,
                :cavalo_id,
                :categoria,
                :descricao,
                :valor,
                :data_despesa,
                :metodo_pagamento,
                :estado_pagamento
            )";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':fornecedor_id' => $fornecedor_id,
        ':cavalo_id' => $tipo_registo === 'cavalo' ? $cavalo_id : null,
        ':categoria' => $categoria,
        ':descricao' => $descricao ?: null,
        ':valor' => $valor_final,
        ':data_despesa' => $data_despesa,
        ':metodo_pagamento' => $metodo_pagamento ?: null,
        ':estado_pagamento' => $estado_pagamento
    ]);

    header('Location: ../admin/despesas.php?sucesso=despesa_adicionada');
    exit;

} catch (PDOException $e) {
    die('Erro ao adicionar despesa: ' . $e->getMessage());
}
?>