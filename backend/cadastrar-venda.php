<?php
require_once __DIR__ . '/proteger.php';
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes-formatacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/adicionar-venda.php?erro=metodo');
    exit;
}

$clienteId = isset($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : 0;
$cavaloId = isset($_POST['cavalo_id']) ? (int) $_POST['cavalo_id'] : 0;
$dataVenda = trim($_POST['data_venda'] ?? '');
$valor = normalizarValorMonetario($_POST['valor'] ?? '');
$metodoPagamento = trim($_POST['metodo_pagamento'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if ($clienteId <= 0 || $cavaloId <= 0 || $dataVenda === '' || $valor <= 0) {
    header('Location: ../admin/adicionar-venda.php?erro=campos');
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataVenda)) {
    header('Location: ../admin/adicionar-venda.php?erro=campos');
    exit;
}

try {
    $conn->beginTransaction();

    /*
        Validar cliente:
        aceita "Cliente", "cliente", " CLIENTE ", etc.
    */
    $stmtCliente = $conn->prepare("
        SELECT id, nome, estado
        FROM clientes
        WHERE id = :id
          AND TRIM(LOWER(estado)) = 'cliente'
        LIMIT 1
    ");
    $stmtCliente->execute([
        ':id' => $clienteId
    ]);
    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        $conn->rollBack();
        header('Location: ../admin/adicionar-venda.php?erro=cliente');
        exit;
    }

    /*
        Validar cavalo:
        aceita Disponível e Disponivel.
    */
    $stmtCavalo = $conn->prepare("
        SELECT id, nome, estado
        FROM cavalos
        WHERE id = :id
          AND TRIM(LOWER(estado)) IN ('disponível', 'disponivel')
        LIMIT 1
        FOR UPDATE
    ");
    $stmtCavalo->execute([
        ':id' => $cavaloId
    ]);
    $cavalo = $stmtCavalo->fetch(PDO::FETCH_ASSOC);

    if (!$cavalo) {
        $conn->rollBack();
        header('Location: ../admin/adicionar-venda.php?erro=cavalo');
        exit;
    }

    /*
        Confirmar se este cavalo ainda não foi vendido.
    */
    $stmtVendaExistente = $conn->prepare("
        SELECT id
        FROM vendas_cavalos
        WHERE cavalo_id = :cavalo_id
        LIMIT 1
    ");
    $stmtVendaExistente->execute([
        ':cavalo_id' => $cavaloId
    ]);

    if ($stmtVendaExistente->fetch(PDO::FETCH_ASSOC)) {
        $conn->rollBack();
        header('Location: ../admin/adicionar-venda.php?erro=duplicado');
        exit;
    }

    /*
        Inserir venda.
    */
    $stmtInserir = $conn->prepare("
        INSERT INTO vendas_cavalos (
            cliente_id,
            cavalo_id,
            data_venda,
            valor,
            metodo_pagamento,
            observacoes
        ) VALUES (
            :cliente_id,
            :cavalo_id,
            :data_venda,
            :valor,
            :metodo_pagamento,
            :observacoes
        )
    ");

    $stmtInserir->execute([
        ':cliente_id' => $clienteId,
        ':cavalo_id' => $cavaloId,
        ':data_venda' => $dataVenda,
        ':valor' => $valor,
        ':metodo_pagamento' => $metodoPagamento !== '' ? $metodoPagamento : null,
        ':observacoes' => $observacoes !== '' ? $observacoes : null
    ]);

    /*
        Atualizar estado do cavalo.
    */
    $stmtAtualizarCavalo = $conn->prepare("
        UPDATE cavalos
        SET estado = 'Vendido'
        WHERE id = :id
    ");
    $stmtAtualizarCavalo->execute([
        ':id' => $cavaloId
    ]);

    $conn->commit();

    header('Location: ../admin/vendas.php?sucesso=venda_criada');
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    if ($e->getCode() === '23000') {
        header('Location: ../admin/adicionar-venda.php?erro=duplicado');
        exit;
    }

    header('Location: ../admin/adicionar-venda.php?erro=geral');
    exit;
}