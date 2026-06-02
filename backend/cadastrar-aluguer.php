<?php
require_once 'proteger.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido.');
}

function normalizarValorMonetario($valor) {
    $valor = trim((string)$valor);

    if ($valor === '') {
        return 0;
    }

    $valor = str_replace('€', '', $valor);
    $valor = str_replace(' ', '', $valor);

    /*
        Aceita formatos:
        25
        25,00
        25.00
        1.000,00
        1000.00
    */

    $temVirgula = strpos($valor, ',') !== false;
    $temPonto = strpos($valor, '.') !== false;

    if ($temVirgula && $temPonto) {
        /*
            Formato português:
            1.000,00
            2.500,50
        */
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif ($temVirgula && !$temPonto) {
        /*
            Formato português simples:
            25,00
        */
        $valor = str_replace(',', '.', $valor);
    } elseif ($temPonto && !$temVirgula) {
        /*
            Pode ser:
            25.00 = decimal
            1000.00 = decimal
            1.000 = milhar português

            Se houver exatamente 3 dígitos depois do ponto,
            tratamos como separador de milhar.
        */
        $partes = explode('.', $valor);

        if (count($partes) === 2 && strlen($partes[1]) === 3) {
            $valor = str_replace('.', '', $valor);
        }
    }

    if (!is_numeric($valor)) {
        return 0;
    }

    return (float)$valor;
}

$cliente_id = isset($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : 0;
$cavalo_id = isset($_POST['cavalo_id']) ? (int) $_POST['cavalo_id'] : 0;
$data_inicio = trim($_POST['data_inicio'] ?? '');
$data_fim = trim($_POST['data_fim'] ?? '');
$preco_diario_texto = trim($_POST['preco_diario'] ?? $_POST['preco'] ?? '0');
$estado = trim($_POST['estado'] ?? 'ativo');

$preco_diario = normalizarValorMonetario($preco_diario_texto);

if ($cliente_id <= 0 || $cavalo_id <= 0 || $data_inicio === '') {
    die('Preencha os campos obrigatórios.');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio)) {
    die('Data de início inválida.');
}

if ($data_fim !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
    die('Data de fim inválida.');
}

if ($data_fim !== '' && strtotime($data_fim) < strtotime($data_inicio)) {
    die('A data de fim não pode ser anterior à data de início.');
}

if ($preco_diario <= 0) {
    die('O preço diário deve ser superior a 0.');
}

$estadosPermitidos = ['ativo', 'concluido', 'cancelado'];

if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = 'ativo';
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
        die('Só é possível criar alugueres para clientes com estado Cliente.');
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
        die('Só é possível alugar cavalos com estado Disponível.');
    }

    $stmtVerificar = $conn->prepare("
        SELECT id 
        FROM alugueres 
        WHERE cavalo_id = :cavalo_id 
          AND estado = 'ativo'
        LIMIT 1
    ");
    $stmtVerificar->execute([
        ':cavalo_id' => $cavalo_id
    ]);

    if ($stmtVerificar->fetch(PDO::FETCH_ASSOC)) {
        $conn->rollBack();
        die('Este cavalo já tem um aluguer ativo.');
    }

    $sql = "
        INSERT INTO alugueres 
        (cliente_id, cavalo_id, data_inicio, data_fim, preco_diario, estado)
        VALUES 
        (:cliente_id, :cavalo_id, :data_inicio, :data_fim, :preco_diario, :estado)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':cliente_id' => $cliente_id,
        ':cavalo_id' => $cavalo_id,
        ':data_inicio' => $data_inicio,
        ':data_fim' => $data_fim !== '' ? $data_fim : null,
        ':preco_diario' => $preco_diario,
        ':estado' => $estado
    ]);

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

    header('Location: ../admin/alugueres.php');
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die('Erro ao criar aluguer: ' . $e->getMessage());
}
?>