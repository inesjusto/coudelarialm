<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido.');
}

$cliente_id = isset($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : 0;
$cavalo_id = isset($_POST['cavalo_id']) ? (int) $_POST['cavalo_id'] : 0;
$data_inicio = trim($_POST['data_inicio'] ?? '');
$data_fim = trim($_POST['data_fim'] ?? '');
$preco_diario = trim($_POST['preco_diario'] ?? $_POST['preco'] ?? '0');
$estado = trim($_POST['estado'] ?? 'ativo');

$preco_diario = str_replace([' ', '.'], ['', ''], $preco_diario);
$preco_diario = str_replace(',', '.', $preco_diario);

if ($cliente_id <= 0 || $cavalo_id <= 0 || $data_inicio === '') {
    die('Preencha os campos obrigatórios.');
}

if ($data_fim !== '' && strtotime($data_fim) < strtotime($data_inicio)) {
    die('A data de fim não pode ser anterior à data de início.');
}

$estadosPermitidos = ['ativo', 'concluido', 'cancelado'];

if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = 'ativo';
}

try {
    $stmtVerificar = $conn->prepare("
        SELECT id 
        FROM alugueres 
        WHERE cavalo_id = :cavalo_id 
        AND estado = 'ativo'
        LIMIT 1
    ");
    $stmtVerificar->execute([':cavalo_id' => $cavalo_id]);

    if ($stmtVerificar->fetch()) {
        die('Este cavalo já tem um aluguer ativo.');
    }

    $sql = "INSERT INTO alugueres 
            (cliente_id, cavalo_id, data_inicio, data_fim, preco_diario, estado)
            VALUES 
            (:cliente_id, :cavalo_id, :data_inicio, :data_fim, :preco_diario, :estado)";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $stmt->bindValue(':cavalo_id', $cavalo_id, PDO::PARAM_INT);
    $stmt->bindValue(':data_inicio', $data_inicio);
    $stmt->bindValue(':data_fim', $data_fim !== '' ? $data_fim : null);
    $stmt->bindValue(':preco_diario', $preco_diario !== '' ? $preco_diario : 0);
    $stmt->bindValue(':estado', $estado);
    $stmt->execute();

    header('Location: ../admin/alugueres.php');
    exit;
} catch (PDOException $e) {
    die('Erro ao criar aluguer: ' . $e->getMessage());
}
?>