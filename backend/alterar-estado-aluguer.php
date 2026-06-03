<?php
require_once 'proteger.php';
require_once 'conexao.php';
require_once 'atualizar-alugueres.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido.');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$estado = trim($_POST['estado'] ?? '');

$estadosPermitidos = ['ativo', 'reservado', 'concluido', 'cancelado'];

if ($id <= 0) {
    die('ID de aluguer inválido.');
}

if (!in_array($estado, $estadosPermitidos, true)) {
    die('Estado inválido.');
}

try {
    $conn->beginTransaction();

    $stmtBuscar = $conn->prepare("
        SELECT 
            id,
            cavalo_id,
            data_inicio,
            data_fim,
            estado
        FROM alugueres
        WHERE id = :id
        LIMIT 1
        FOR UPDATE
    ");

    $stmtBuscar->execute([
        ':id' => $id
    ]);

    $aluguer = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

    if (!$aluguer) {
        $conn->rollBack();
        die('Aluguer não encontrado.');
    }

    $cavalo_id = (int) $aluguer['cavalo_id'];


    if ($estado === 'concluido') {
        $stmtAtualizarAluguer = $conn->prepare("
            UPDATE alugueres
            SET 
                estado = 'concluido',
                data_fim = CURDATE()
            WHERE id = :id
        ");

        $stmtAtualizarAluguer->execute([
            ':id' => $id
        ]);
    } else {
        $stmtAtualizarAluguer = $conn->prepare("
            UPDATE alugueres
            SET estado = :estado
            WHERE id = :id
        ");

        $stmtAtualizarAluguer->execute([
            ':estado' => $estado,
            ':id' => $id
        ]);
    }

    if ($estado === 'ativo') {
        $stmtAtualizarCavalo = $conn->prepare("
            UPDATE cavalos
            SET estado = 'Alugado'
            WHERE id = :cavalo_id
              AND TRIM(LOWER(estado)) NOT IN (
                  'vendido',
                  'reservado',
                  'indisponível',
                  'indisponivel',
                  'em tratamento',
                  'reformado'
              )
        ");

        $stmtAtualizarCavalo->execute([
            ':cavalo_id' => $cavalo_id
        ]);
    }

    if ($estado === 'concluido' || $estado === 'cancelado') {
        $stmtVerificarAtivoHoje = $conn->prepare("
            SELECT id
            FROM alugueres
            WHERE cavalo_id = :cavalo_id
              AND id != :id
              AND TRIM(LOWER(estado)) = 'ativo'
              AND data_inicio <= CURDATE()
              AND data_fim >= CURDATE()
            LIMIT 1
        ");

        $stmtVerificarAtivoHoje->execute([
            ':cavalo_id' => $cavalo_id,
            ':id' => $id
        ]);

        if (!$stmtVerificarAtivoHoje->fetch(PDO::FETCH_ASSOC)) {
            $stmtAtualizarCavalo = $conn->prepare("
                UPDATE cavalos
                SET estado = 'Disponível'
                WHERE id = :cavalo_id
                  AND TRIM(LOWER(estado)) = 'alugado'
            ");

            $stmtAtualizarCavalo->execute([
                ':cavalo_id' => $cavalo_id
            ]);
        }
    }

    if ($estado === 'reservado') {
        $stmtVerificarAtivoHoje = $conn->prepare("
            SELECT id
            FROM alugueres
            WHERE cavalo_id = :cavalo_id
              AND id != :id
              AND TRIM(LOWER(estado)) = 'ativo'
              AND data_inicio <= CURDATE()
              AND data_fim >= CURDATE()
            LIMIT 1
        ");

        $stmtVerificarAtivoHoje->execute([
            ':cavalo_id' => $cavalo_id,
            ':id' => $id
        ]);

        if (!$stmtVerificarAtivoHoje->fetch(PDO::FETCH_ASSOC)) {
            $stmtAtualizarCavalo = $conn->prepare("
                UPDATE cavalos
                SET estado = 'Disponível'
                WHERE id = :cavalo_id
                  AND TRIM(LOWER(estado)) = 'alugado'
            ");

            $stmtAtualizarCavalo->execute([
                ':cavalo_id' => $cavalo_id
            ]);
        }
    }

    $conn->commit();

    header('Location: ../admin/alugueres.php');
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die('Erro ao alterar estado do aluguer: ' . $e->getMessage());
}
?>