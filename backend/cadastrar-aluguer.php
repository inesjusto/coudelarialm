<?php
require_once 'proteger.php';
require_once 'conexao.php';
require_once 'funcoes-formatacao.php';
require_once 'atualizar-alugueres.php';

function redirecionarErro($erro) {
    header('Location: ../admin/alugueres.php?erro=' . urlencode($erro));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionarErro('metodo');
}

$cliente_id = isset($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : 0;
$cavalo_id = isset($_POST['cavalo_id']) ? (int) $_POST['cavalo_id'] : 0;

$data_inicio = trim($_POST['data_inicio'] ?? '');
$data_fim = trim($_POST['data_fim'] ?? '');

$preco_diario = normalizarValorMonetario($_POST['preco_diario'] ?? $_POST['preco'] ?? '0');

if ($cliente_id <= 0 || $cavalo_id <= 0 || $data_inicio === '' || $data_fim === '') {
    redirecionarErro('campos');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio)) {
    redirecionarErro('data_inicio');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
    redirecionarErro('data_fim');
}

if (strtotime($data_fim) < strtotime($data_inicio)) {
    redirecionarErro('datas');
}

if ($preco_diario <= 0) {
    redirecionarErro('preco');
}

/*
    Estado automático:
    - Se hoje está dentro do período: ativo
    - Se começa no futuro: reservado
    - Se já terminou: concluido
*/
$hoje = date('Y-m-d');

if ($data_inicio <= $hoje && $data_fim >= $hoje) {
    $estado = 'ativo';
} elseif ($data_inicio > $hoje) {
    $estado = 'reservado';
} else {
    $estado = 'concluido';
}

try {
    $conn->beginTransaction();

    $stmtClienteValido = $conn->prepare("
        SELECT id
        FROM clientes
        WHERE id = :cliente_id
          AND TRIM(LOWER(estado)) = 'cliente'
        LIMIT 1
    ");

    $stmtClienteValido->execute([
        ':cliente_id' => $cliente_id
    ]);

    if (!$stmtClienteValido->fetch(PDO::FETCH_ASSOC)) {
        $conn->rollBack();
        redirecionarErro('cliente');
    }

    $stmtCavaloValido = $conn->prepare("
        SELECT id
        FROM cavalos
        WHERE id = :cavalo_id
          AND TRIM(LOWER(estado)) IN ('disponível', 'disponivel')
        LIMIT 1
        FOR UPDATE
    ");

    $stmtCavaloValido->execute([
        ':cavalo_id' => $cavalo_id
    ]);

    if (!$stmtCavaloValido->fetch(PDO::FETCH_ASSOC)) {
        $conn->rollBack();
        redirecionarErro('cavalo');
    }

    /*
        Bloqueia alugueres sobrepostos:
        Se já existir um aluguer ativo ou reservado do mesmo cavalo
        dentro do mesmo intervalo de datas, não deixa criar.
    */
    $stmtVerificarSobreposicao = $conn->prepare("
        SELECT id
        FROM alugueres
        WHERE cavalo_id = :cavalo_id
          AND TRIM(LOWER(estado)) IN ('ativo', 'reservado')
          AND data_inicio <= :data_fim
          AND COALESCE(data_fim, '9999-12-31') >= :data_inicio
        LIMIT 1
    ");

    $stmtVerificarSobreposicao->execute([
        ':cavalo_id' => $cavalo_id,
        ':data_inicio' => $data_inicio,
        ':data_fim' => $data_fim
    ]);

    if ($stmtVerificarSobreposicao->fetch(PDO::FETCH_ASSOC)) {
        $conn->rollBack();
        redirecionarErro('sobreposicao');
    }

    $sql = "
        INSERT INTO alugueres (
            cliente_id,
            cavalo_id,
            data_inicio,
            data_fim,
            preco_diario,
            estado
        ) VALUES (
            :cliente_id,
            :cavalo_id,
            :data_inicio,
            :data_fim,
            :preco_diario,
            :estado
        )
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':cliente_id' => $cliente_id,
        ':cavalo_id' => $cavalo_id,
        ':data_inicio' => $data_inicio,
        ':data_fim' => $data_fim,
        ':preco_diario' => $preco_diario,
        ':estado' => $estado
    ]);

    /*
        Só muda o cavalo para Alugado se o aluguer estiver ativo hoje.
        Aluguer futuro fica reservado, mas o cavalo continua Disponível.
    */
    if ($estado === 'ativo') {
        $stmtAtualizarCavalo = $conn->prepare("
            UPDATE cavalos
            SET estado = 'Alugado'
            WHERE id = :cavalo_id
        ");

        $stmtAtualizarCavalo->execute([
            ':cavalo_id' => $cavalo_id
        ]);
    }

    $conn->commit();

    header('Location: ../admin/alugueres.php?sucesso=criado');
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    redirecionarErro('guardar');
}
?>