<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido.');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$tipo_interesse = trim($_POST['tipo_interesse'] ?? 'compra');
$estado = trim($_POST['estado'] ?? 'potencial');
$interesse = trim($_POST['interesse'] ?? 'nao');

$cavalos = [];

if ($interesse === 'sim' && !empty($_POST['cavalos']) && is_array($_POST['cavalos'])) {
    $cavalos = array_map('intval', $_POST['cavalos']);
    $cavalos = array_filter($cavalos, fn($id) => $id > 0);
    $cavalos = array_unique($cavalos);
}

if ($id <= 0) {
    die('ID inválido.');
}

if ($nome === '' || $email === '') {
    die('Preencha os campos obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Email inválido.');
}

try {
    $conn->beginTransaction();

    $sql = "UPDATE clientes
            SET nome = :nome,
                email = :email,
                telefone = :telefone,
                tipo_interesse = :tipo_interesse,
                estado = :estado
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telefone', $telefone !== '' ? $telefone : null);
    $stmt->bindValue(':tipo_interesse', $tipo_interesse);
    $stmt->bindValue(':estado', $estado);
    $stmt->execute();

    $stmtApagar = $conn->prepare("DELETE FROM clientes_cavalos WHERE cliente_id = :cliente_id");
    $stmtApagar->bindValue(':cliente_id', $id, PDO::PARAM_INT);
    $stmtApagar->execute();

    if (!empty($cavalos)) {
        $sqlCavalo = "INSERT INTO clientes_cavalos (cliente_id, cavalo_id)
                      VALUES (:cliente_id, :cavalo_id)";

        $stmtCavalo = $conn->prepare($sqlCavalo);

        foreach ($cavalos as $cavalo_id) {
            $stmtCavalo->bindValue(':cliente_id', $id, PDO::PARAM_INT);
            $stmtCavalo->bindValue(':cavalo_id', $cavalo_id, PDO::PARAM_INT);
            $stmtCavalo->execute();
        }
    }

    $conn->commit();

    header('Location: ../admin/clientes.php');
    exit;
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die('Erro ao editar cliente: ' . $e->getMessage());
}
?>