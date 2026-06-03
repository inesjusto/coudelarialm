<?php
require_once __DIR__ . '/proteger.php';
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes-formatacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/aulas.php');
    exit;
}

$clienteId = isset($_POST['cliente_id']) && $_POST['cliente_id'] !== '' 
    ? (int) $_POST['cliente_id'] 
    : null;

$cavaloId = isset($_POST['cavalo_id']) && $_POST['cavalo_id'] !== '' 
    ? (int) $_POST['cavalo_id'] 
    : null;

$dataAula = trim($_POST['data_aula'] ?? '');
$horaInicio = trim($_POST['hora_inicio'] ?? '');
$horaFim = trim($_POST['hora_fim'] ?? '');
$tipoAula = trim($_POST['tipo_aula'] ?? '');
$preco = normalizarValorMonetario($_POST['preco'] ?? '');
$estado = trim($_POST['estado'] ?? 'marcada');
$observacoes = trim($_POST['observacoes'] ?? '');

$estadosPermitidos = ['marcada', 'realizada', 'cancelada'];

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
    /*
        Validar cliente, se existir cliente selecionado.
        Só permite clientes com estado Cliente.
    */
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

    /*
        Validar cavalo, se existir cavalo selecionado.

        Regras:
        - Só pode marcar aula com cavalo Disponível.
        - Se o cavalo tiver aluguer ativo ou reservado nessa data, bloqueia.
        - Aluguer cancelado não bloqueia.
        - Aluguer concluído não bloqueia.
    */
    if ($cavaloId !== null) {
        $stmtCavalo = $conn->prepare("
            SELECT c.id
            FROM cavalos c
            WHERE c.id = :cavalo_id
              AND TRIM(LOWER(c.estado)) IN ('disponível', 'disponivel')
              AND NOT EXISTS (
                  SELECT 1
                  FROM alugueres a
                  WHERE a.cavalo_id = c.id
                    AND TRIM(LOWER(a.estado)) IN ('ativo', 'reservado')
                    AND DATE(:data_aula) >= DATE(a.data_inicio)
                    AND DATE(:data_aula) <= DATE(COALESCE(a.data_fim, '9999-12-31'))
              )
            LIMIT 1
        ");

        $stmtCavalo->execute([
            ':cavalo_id' => $cavaloId,
            ':data_aula' => $dataAula
        ]);

        if (!$stmtCavalo->fetch(PDO::FETCH_ASSOC)) {
            die('Este cavalo não está disponível na data selecionada.');
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO aulas (
            cliente_id,
            cavalo_id,
            data_aula,
            hora_inicio,
            hora_fim,
            tipo_aula,
            preco,
            estado,
            observacoes
        ) VALUES (
            :cliente_id,
            :cavalo_id,
            :data_aula,
            :hora_inicio,
            :hora_fim,
            :tipo_aula,
            :preco,
            :estado,
            :observacoes
        )
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
        ':observacoes' => $observacoes !== '' ? $observacoes : null
    ]);

    header('Location: ../admin/aulas.php?sucesso=aula_adicionada');
    exit;

} catch (PDOException $e) {
    die('Erro ao adicionar aula: ' . $e->getMessage());
}
?>