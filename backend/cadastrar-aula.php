<?php
require_once 'proteger.php';
require_once 'conexao.php';
require_once 'funcoes-formatacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/aulas.php');
    exit;
}

$cliente_id = $_POST['cliente_id'] ?? null;
$cavalo_id = $_POST['cavalo_id'] ?? null;
$data_aula = trim($_POST['data_aula'] ?? '');
$hora_inicio = trim($_POST['hora_inicio'] ?? '');
$hora_fim = trim($_POST['hora_fim'] ?? '');
$tipo_aula = trim($_POST['tipo_aula'] ?? '');
$preco = normalizarValorMonetario($_POST['preco'] ?? '');
$estado = $_POST['estado'] ?? 'marcada';
$observacoes = trim($_POST['observacoes'] ?? '');

$cliente_id = empty($cliente_id) ? null : (int)$cliente_id;
$cavalo_id = empty($cavalo_id) ? null : (int)$cavalo_id;

$estadosPermitidos = ['marcada', 'realizada', 'cancelada'];

if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = 'marcada';
}

if (
    empty($data_aula) ||
    empty($hora_inicio) ||
    empty($hora_fim) ||
    $preco < 0 ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_aula)
) {
    die('Dados inválidos.');
}

if ($hora_fim <= $hora_inicio) {
    die('A hora de fim tem de ser posterior à hora de início.');
}

try {
    if ($cliente_id !== null) {
        $stmtCliente = $conn->prepare("\n            SELECT id\n            FROM clientes\n            WHERE id = :cliente_id\n              AND TRIM(LOWER(estado)) = 'cliente'\n            LIMIT 1\n        ");
        $stmtCliente->execute([
            ':cliente_id' => $cliente_id
        ]);

        if (!$stmtCliente->fetch(PDO::FETCH_ASSOC)) {
            die('O cliente selecionado não é válido ou não tem estado Cliente.');
        }
    }

    if ($cavalo_id !== null) {
        $stmtCavalo = $conn->prepare("\n            SELECT id\n            FROM cavalos c\n            WHERE c.id = :cavalo_id\n              AND TRIM(LOWER(c.estado)) IN ('disponível', 'disponivel')\n              AND NOT EXISTS (\n                  SELECT 1\n                  FROM alugueres a\n                  WHERE a.cavalo_id = c.id\n                    AND a.estado = 'ativo'\n                    AND :data_aula BETWEEN a.data_inicio AND a.data_fim\n              )\n            LIMIT 1\n        ");

        $stmtCavalo->execute([
            ':cavalo_id' => $cavalo_id,
            ':data_aula' => $data_aula
        ]);

        if (!$stmtCavalo->fetch(PDO::FETCH_ASSOC)) {
            die('Este cavalo não está disponível na data selecionada.');
        }
    }

    $sql = "\n        INSERT INTO aulas\n        (cliente_id, cavalo_id, data_aula, hora_inicio, hora_fim, tipo_aula, preco, estado, observacoes)\n        VALUES\n        (:cliente_id, :cavalo_id, :data_aula, :hora_inicio, :hora_fim, :tipo_aula, :preco, :estado, :observacoes)\n    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':cliente_id' => $cliente_id,
        ':cavalo_id' => $cavalo_id,
        ':data_aula' => $data_aula,
        ':hora_inicio' => $hora_inicio,
        ':hora_fim' => $hora_fim,
        ':tipo_aula' => $tipo_aula ?: null,
        ':preco' => $preco,
        ':estado' => $estado,
        ':observacoes' => $observacoes ?: null
    ]);

    header('Location: ../admin/aulas.php?sucesso=aula_adicionada');
    exit;

} catch (PDOException $e) {
    die('Erro ao adicionar aula: ' . $e->getMessage());
}
?>
