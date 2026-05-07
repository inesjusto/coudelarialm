<?php
require_once 'proteger.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/despesas.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$fornecedor_id = $_POST['fornecedor_id'] ?? null;
$categoria = trim($_POST['categoria'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$valor = trim($_POST['valor'] ?? '');
$data_despesa = $_POST['data_despesa'] ?? '';
$metodo_pagamento = trim($_POST['metodo_pagamento'] ?? '');
$estado_pagamento = $_POST['estado_pagamento'] ?? 'pendente';

$fornecedor_id = empty($fornecedor_id) ? null : (int)$fornecedor_id;
$valor = str_replace(',', '.', $valor);

if ($id <= 0 || empty($categoria) || empty($valor) || empty($data_despesa)) {
    die('Dados inválidos.');
}

if (!is_numeric($valor) || $valor <= 0) {
    die('O valor da despesa deve ser válido.');
}

try {
    $sql = "UPDATE despesas SET
                fornecedor_id = :fornecedor_id,
                categoria = :categoria,
                descricao = :descricao,
                valor = :valor,
                data_despesa = :data_despesa,
                metodo_pagamento = :metodo_pagamento,
                estado_pagamento = :estado_pagamento
            WHERE id = :id";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':id' => $id,
        ':fornecedor_id' => $fornecedor_id,
        ':categoria' => $categoria,
        ':descricao' => $descricao ?: null,
        ':valor' => $valor,
        ':data_despesa' => $data_despesa,
        ':metodo_pagamento' => $metodo_pagamento ?: null,
        ':estado_pagamento' => $estado_pagamento
    ]);

    header('Location: ../admin/despesas.php?sucesso=despesa_editada');
    exit;

} catch (PDOException $e) {
    die('Erro ao editar despesa: ' . $e->getMessage());
}
?>