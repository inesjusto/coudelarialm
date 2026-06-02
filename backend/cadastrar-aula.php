<?php
require_once 'proteger.php';
require_once 'conexao.php';

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
$precoTexto = trim($_POST['preco'] ?? '');
$estado = $_POST['estado'] ?? 'marcada';
$observacoes = trim($_POST['observacoes'] ?? '');

$cliente_id = empty($cliente_id) ? null : (int)$cliente_id;
$cavalo_id = empty($cavalo_id) ? null : (int)$cavalo_id;

$precoTexto = str_replace('€', '', $precoTexto);
$precoTexto = str_replace(' ', '', $precoTexto);

if (strpos($precoTexto, ',') !== false) {
    $precoTexto = str_replace('.', '', $precoTexto);
    $precoTexto = str_replace(',', '.', $precoTexto);
}

$preco = (float)$precoTexto;

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
        $stmtCliente = $conn->prepare("
            SELECT id
            FROM clientes
            WHERE id = :cliente_id
              AND TRIM(LOWER(estado)) = 'cliente'
            LIMIT 1
        ");

        $stmtCliente->execute([
            ':cliente_id' => $cliente_id
        ]);

        if (!$stmtCliente->fetch(PDO::FETCH_ASSOC)) {
            die('O cliente selecionado não é válido ou não tem estado Cliente.');
        }
    }

    if ($cavalo_id !== null) {
        $stmtCavalo = $conn->prepare("
            SELECT id
            FROM cavalos c
            WHERE c.id = :cavalo_id
              AND TRIM(LOWER(c.estado)) IN ('disponível', 'disponivel')
              AND NOT EXISTS (
                  SELECT 1
                  FROM alugueres a
                  WHERE a.cavalo_id = c.id
                    AND a.estado = 'ativo'
                    AND :data_aula BETWEEN a.data_inicio AND a.data_fim
              )
            LIMIT 1
        ");

        $stmtCavalo->execute([
            ':cavalo_id' => $cavalo_id,
            ':data_aula' => $data_aula
        ]);

        if (!$stmtCavalo->fetch(PDO::FETCH_ASSOC)) {
            die('Este cavalo não está disponível na data selecionada.');
        }
    }

    $sql = "
        INSERT INTO aulas
        (cliente_id, cavalo_id, data_aula, hora_inicio, hora_fim, tipo_aula, preco, estado, observacoes)
        VALUES
        (:cliente_id, :cavalo_id, :data_aula, :hora_inicio, :hora_fim, :tipo_aula, :preco, :estado, :observacoes)
    ";

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