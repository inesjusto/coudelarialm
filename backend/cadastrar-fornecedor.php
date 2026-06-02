<?php
require_once 'proteger.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/fornecedores.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$nif = trim($_POST['nif'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$morada = trim($_POST['morada'] ?? '');
$tipo_fornecedor = trim($_POST['tipo_fornecedor'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if (empty($nome)) {
    die('O nome do fornecedor é obrigatório.');
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Email inválido.');
}

try {
    $sql = "INSERT INTO fornecedores 
            (nome, nif, telefone, email, morada, tipo_fornecedor, observacoes)
            VALUES
            (:nome, :nif, :telefone, :email, :morada, :tipo_fornecedor, :observacoes)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':nome' => $nome,
        ':nif' => $nif ?: null,
        ':telefone' => $telefone ?: null,
        ':email' => $email ?: null,
        ':morada' => $morada ?: null,
        ':tipo_fornecedor' => $tipo_fornecedor ?: null,
        ':observacoes' => $observacoes ?: null
    ]);

    header('Location: ../admin/fornecedores.php?sucesso=fornecedor_adicionado');
    exit;

} catch (PDOException $e) {
    die('Erro ao adicionar fornecedor: ' . $e->getMessage());
}
?>