<?php
require_once 'proteger.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/fornecedores.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

$nome = trim($_POST['nome'] ?? '');
$nif = trim($_POST['nif'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$morada = trim($_POST['morada'] ?? '');
$tipoFornecedor = trim($_POST['tipo_fornecedor'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if ($id <= 0) {
    die('ID do fornecedor inválido.');
}

if ($nome === '') {
    die('O nome do fornecedor é obrigatório.');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Email inválido.');
}

try {
    $stmtFornecedor = $conn->prepare("
        SELECT id
        FROM fornecedores
        WHERE id = :id
        LIMIT 1
    ");

    $stmtFornecedor->execute([
        ':id' => $id
    ]);

    if (!$stmtFornecedor->fetch(PDO::FETCH_ASSOC)) {
        die('Fornecedor não encontrado.');
    }

    $stmt = $conn->prepare("
        UPDATE fornecedores
        SET
            nome = :nome,
            nif = :nif,
            telefone = :telefone,
            email = :email,
            morada = :morada,
            tipo_fornecedor = :tipo_fornecedor,
            observacoes = :observacoes
        WHERE id = :id
    ");

    $stmt->execute([
        ':nome' => $nome,
        ':nif' => $nif !== '' ? $nif : null,
        ':telefone' => $telefone !== '' ? $telefone : null,
        ':email' => $email !== '' ? $email : null,
        ':morada' => $morada !== '' ? $morada : null,
        ':tipo_fornecedor' => $tipoFornecedor !== '' ? $tipoFornecedor : null,
        ':observacoes' => $observacoes !== '' ? $observacoes : null,
        ':id' => $id
    ]);

    header('Location: ../admin/fornecedores.php?sucesso=fornecedor_editado');
    exit;

} catch (PDOException $e) {
    die('Erro ao editar fornecedor: ' . $e->getMessage());
}
?>