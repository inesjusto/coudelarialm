<?php
require_once 'proteger.php';
require_once 'conexao.php';
require_once 'funcoes-formatacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido.');
}

$cliente_id = isset($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : 0;
$cavalo_id = isset($_POST['cavalo_id']) ? (int) $_POST['cavalo_id'] : 0;
$data_inicio = trim($_POST['data_inicio'] ?? '');
$data_fim = trim($_POST['data_fim'] ?? '');
$preco_diario = normalizarValorMonetario($_POST['preco_diario'] ?? $_POST['preco'] ?? '0');
$estado = trim($_POST['estado'] ?? 'ativo');

if ($cliente_id <= 0 || $cavalo_id <= 0 || $data_inicio === '') {
    die('Preencha os campos obrigatórios.');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio)) {
    die('Data de início inválida.');
}

if ($data_fim !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
    die('Data de fim inválida.');
}

if ($data_fim !== '' && strtotime($data_fim) < strtotime($data_inicio)) {
    die('A data de fim não pode ser anterior à data de início.');
}

if ($preco_diario <= 0) {
    die('O preço diário deve ser superior a 0.');
}

$estadosPermitidos = ['ativo', 'concluido', 'cancelado'];

if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = 'ativo';
}

try {
    $conn->beginTransaction();

    $stmtClienteValido = $conn->prepare("\n        SELECT id\n        FROM clientes\n        WHERE id = :cliente_id\n          AND TRIM(LOWER(estado)) = 'cliente'\n        LIMIT 1\n    ");
    $stmtClienteValido->execute([
        ':cliente_id' => $cliente_id
    ]);

    if (!$stmtClienteValido->fetch(PDO::FETCH_ASSOC)) {
        $conn->rollBack();
        die('Só é possível criar alugueres para clientes com estado Cliente.');
    }

    $stmtCavaloValido = $conn->prepare("\n        SELECT id\n        FROM cavalos\n        WHERE id = :cavalo_id\n          AND TRIM(LOWER(estado)) IN ('disponível', 'disponivel')\n        LIMIT 1\n        FOR UPDATE\n    ");
    $stmtCavaloValido->execute([
        ':cavalo_id' => $cavalo_id
    ]);

    if (!$stmtCavaloValido->fetch(PDO::FETCH_ASSOC)) {
        $conn->rollBack();
        die('Só é possível alugar cavalos com estado Disponível.');
    }

    $stmtVerificar = $conn->prepare("\n        SELECT id \n        FROM alugueres \n        WHERE cavalo_id = :cavalo_id \n          AND estado = 'ativo'\n        LIMIT 1\n    ");
    $stmtVerificar->execute([
        ':cavalo_id' => $cavalo_id
    ]);

    if ($stmtVerificar->fetch(PDO::FETCH_ASSOC)) {
        $conn->rollBack();
        die('Este cavalo já tem um aluguer ativo.');
    }

    $sql = "\n        INSERT INTO alugueres \n        (cliente_id, cavalo_id, data_inicio, data_fim, preco_diario, estado)\n        VALUES \n        (:cliente_id, :cavalo_id, :data_inicio, :data_fim, :preco_diario, :estado)\n    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':cliente_id' => $cliente_id,
        ':cavalo_id' => $cavalo_id,
        ':data_inicio' => $data_inicio,
        ':data_fim' => $data_fim !== '' ? $data_fim : null,
        ':preco_diario' => $preco_diario,
        ':estado' => $estado
    ]);

    if ($estado === 'ativo') {
        $stmtAtualizarCavalo = $conn->prepare("\n            UPDATE cavalos\n            SET estado = 'Alugado'\n            WHERE id = :cavalo_id\n        ");
        $stmtAtualizarCavalo->execute([
            ':cavalo_id' => $cavalo_id
        ]);
    }

    $conn->commit();

    header('Location: ../admin/alugueres.php');
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die('Erro ao criar aluguer: ' . $e->getMessage());
}
?>
