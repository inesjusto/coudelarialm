<?php
require_once __DIR__ . '/proteger.php';
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes-formatacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/aulas.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

$clienteId = isset($_POST['cliente_id']) && $_POST['cliente_id'] !== '' ? (int) $_POST['cliente_id'] : null;
$cavaloId = isset($_POST['cavalo_id']) && $_POST['cavalo_id'] !== '' ? (int) $_POST['cavalo_id'] : null;

$dataAula = trim($_POST['data_aula'] ?? '');
$horaInicio = trim($_POST['hora_inicio'] ?? '');
$horaFim = trim($_POST['hora_fim'] ?? '');
$tipoAula = trim($_POST['tipo_aula'] ?? '');
$preco = normalizarValorMonetario($_POST['preco'] ?? '');
$estado = trim($_POST['estado'] ?? 'marcada');
$observacoes = trim($_POST['observacoes'] ?? '');

$estadosPermitidos = ['marcada', 'realizada', 'cancelada'];

if ($id <= 0) {
    die('ID da aula inválido.');
}

if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = 'marcada';
}

if ($dataAula === '' || $horaInicio === '' || $horaFim === '') {
    die('Preencha todos os campos obrigatórios.');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAula)) {
    die('Data da aula inválida.');
}

if ($horaFim <= $horaInicio) {
    die('A hora de fim tem de ser posterior à hora de início.');
}

if ($preco < 0) {
    die('O preço não pode ser negativo.');
}

try {
    $stmtAula = $conn->prepare("
        SELECT id, cavalo_id
        FROM aulas
        WHERE id = :id
        LIMIT 1
    ");

    $stmtAula->execute([
        ':id' => $id
    ]);

    $aulaAtual = $stmtAula->fetch(PDO::FETCH_ASSOC);

    if (!$aulaAtual) {
        die('Aula não encontrada.');
    }

    if ($clienteId !== null) {
        $stmtCliente = $conn->prepare("
            SELECT id
            FROM clientes
            WHERE id = :id
              AND TRIM(LOWER(estado)) = 'cliente'
            LIMIT 1
        ");

        $stmtCliente->execute([
            ':id' => $clienteId
        ]);

        if (!$stmtCliente->fetch(PDO::FETCH_ASSOC)) {
            die('O cliente selecionado não é válido ou não tem estado Cliente.');
        }
    }

    if ($cavaloId !== null) {
        $stmtCavalo = $conn->prepare("
            SELECT c.id
            FROM cavalos c
            WHERE c.id = :cavalo_id
              AND (
                    TRIM(LOWER(c.estado)) IN ('disponível', 'disponivel')
                    OR c.id = :cavalo_atual
              )
              AND NOT EXISTS (
                  SELECT 1
                  FROM alugueres a
                  WHERE a.cavalo_id = c.id
                    AND COALESCE(TRIM(LOWER(a.estado)), '') != 'cancelado'
                    AND DATE(:data_aula) BETWEEN DATE(a.data_inicio) AND DATE(COALESCE(a.data_fim, '9999-12-31'))
              )
            LIMIT 1
        ");

        $stmtCavalo->execute([
            ':cavalo_id' => $cavaloId,
            ':cavalo_atual' => $aulaAtual['cavalo_id'] ?? 0,
            ':data_aula' => $dataAula
        ]);

        if (!$stmtCavalo->fetch(PDO::FETCH_ASSOC)) {
            die('Este cavalo não está disponível na data selecionada.');
        }
    }

    $stmt = $conn->prepare("
        UPDATE aulas
        SET
            cliente_id = :cliente_id,
            cavalo_id = :cavalo_id,
            data_aula = :data_aula,
            hora_inicio = :hora_inicio,
            hora_fim = :hora_fim,
            tipo_aula = :tipo_aula,
            preco = :preco,
            estado = :estado,
            observacoes = :observacoes
        WHERE id = :id
    ");

    $stmt->execute([
        ':cliente_id' => $clienteId,
        ':cavalo_id' => $cavaloId,
        ':data_aula' => $dataAula,
        ':hora_inicio' => $horaInicio,
        ':hora_fim' => $horaFim,
        ':tipo_aula' => $tipoAula !== '' ? $tipoAula : null,
        ':preco' => $preco,
        ':estado' => $estado,
        ':observacoes' => $observacoes !== '' ? $observacoes : null,
        ':id' => $id
    ]);

    header('Location: ../admin/aulas.php?sucesso=aula_editada');
    exit;

} catch (PDOException $e) {
    die('Erro ao editar aula: ' . $e->getMessage());
}
?>
