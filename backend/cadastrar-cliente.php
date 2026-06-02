<?php
require_once 'proteger.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/adicionar-cliente.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$nif = trim($_POST['nif'] ?? '');
$tipo_interesse = trim($_POST['tipo_interesse'] ?? '');
$estado = trim($_POST['estado'] ?? '');
$interesse = trim($_POST['interesse'] ?? 'nao');

$cavalos = [];

if ($interesse === 'sim' && !empty($_POST['cavalos']) && is_array($_POST['cavalos'])) {
    $cavalos = array_map('intval', $_POST['cavalos']);
    $cavalos = array_filter($cavalos, fn($id) => $id > 0);
    $cavalos = array_unique($cavalos);
}

if ($nome === '' || $email === '' || $tipo_interesse === '' || $estado === '') {
    header('Location: ../admin/adicionar-cliente.php?erro=campos');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../admin/adicionar-cliente.php?erro=email');
    exit;
}

if ($nif !== '' && !preg_match('/^[0-9]{9}$/', $nif)) {
    header('Location: ../admin/adicionar-cliente.php?erro=nif');
    exit;
}

$tiposPermitidos = ['compra', 'informacao', 'visita'];
$estadosPermitidos = ['potencial', 'contactado', 'cliente'];

if (!in_array($tipo_interesse, $tiposPermitidos, true)) {
    header('Location: ../admin/adicionar-cliente.php?erro=tipo');
    exit;
}

if (!in_array($estado, $estadosPermitidos, true)) {
    header('Location: ../admin/adicionar-cliente.php?erro=estado');
    exit;
}

try {
    $conn->beginTransaction();

    $sql = "
        INSERT INTO clientes (
            nome, 
            email, 
            telefone, 
            nif,
            tipo_interesse, 
            estado
        ) VALUES (
            :nome, 
            :email, 
            :telefone, 
            :nif,
            :tipo_interesse, 
            :estado
        )
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telefone', $telefone !== '' ? $telefone : null);
    $stmt->bindValue(':nif', $nif !== '' ? $nif : null);
    $stmt->bindValue(':tipo_interesse', $tipo_interesse);
    $stmt->bindValue(':estado', $estado);
    $stmt->execute();

    $cliente_id = (int) $conn->lastInsertId();

    if (!empty($cavalos)) {
        $sqlCavalo = "
            INSERT INTO clientes_cavalos (
                cliente_id, 
                cavalo_id
            ) VALUES (
                :cliente_id, 
                :cavalo_id
            )
        ";

        $stmtCavalo = $conn->prepare($sqlCavalo);

        foreach ($cavalos as $cavalo_id) {
            $stmtCavalo->bindValue(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $stmtCavalo->bindValue(':cavalo_id', $cavalo_id, PDO::PARAM_INT);
            $stmtCavalo->execute();
        }
    }

    $conn->commit();

    header('Location: ../admin/clientes.php?sucesso=cliente_adicionado');
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    header('Location: ../admin/adicionar-cliente.php?erro=guardar');
    exit;
}
?>