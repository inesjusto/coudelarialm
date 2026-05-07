<?php
require_once 'proteger.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/despesas.php');
    exit;
}

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
$valor = str_replace(',', '.', $valor);

if (empty($categoria) || empty($valor) || empty($data_despesa)) {
    die('Categoria, valor e data são obrigatórios.');
}

if (!is_numeric($valor) || $valor <= 0) {
    die('O valor da despesa deve ser válido.');
}

try {
    $sql = "INSERT INTO despesas
            (fornecedor_id, cavalo_id, categoria, descricao, valor, data_despesa, metodo_pagamento, estado_pagamento)
            VALUES
            (:fornecedor_id, :cavalo_id, :categoria, :descricao, :valor, :data_despesa, :metodo_pagamento, :estado_pagamento)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':fornecedor_id' => $fornecedor_id,
        ':cavalo_id' => $cavalo_id,
        ':categoria' => $categoria,
        ':descricao' => $descricao ?: null,
        ':valor' => $valor,
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