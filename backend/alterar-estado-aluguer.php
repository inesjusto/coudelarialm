<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido.');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$estado = trim($_POST['estado'] ?? '');

$estadosPermitidos = ['ativo', 'concluido', 'cancelado'];

if ($id <= 0) {
    die('ID de aluguer inválido.');
}

if (!in_array($estado, $estadosPermitidos, true)) {
    die('Estado inválido.');
}

try {

    $conn->beginTransaction();

    $stmtBuscar = $conn->prepare("
        SELECT cavalo_id
        FROM alugueres
        WHERE id = :id
        LIMIT 1
    ");

    $stmtBuscar->execute([
        ':id' => $id
    ]);

    $aluguer = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

    if (!$aluguer) {
        $conn->rollBack();
        die('Aluguer não encontrado.');
    }

    $cavalo_id = (int)$aluguer['cavalo_id'];

    // CONCLUIR ALUGUER
    if ($estado === 'concluido') {

        $stmt = $conn->prepare("
            UPDATE alugueres
            SET 
                estado = 'concluido',
                data_fim = CURDATE()
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);

    } else {

        $stmt = $conn->prepare("
            UPDATE alugueres
            SET estado = :estado
            WHERE id = :id
        ");

        $stmt->execute([
            ':estado' => $estado,
            ':id' => $id
        ]);
    }

    // LIBERTAR CAVALO
    if ($estado === 'concluido' || $estado === 'cancelado') {

        $stmtAtualizarCavalo = $conn->prepare("
            UPDATE cavalos
            SET estado = 'Disponível'
            WHERE id = :cavalo_id
        ");

        $stmtAtualizarCavalo->execute([
            ':cavalo_id' => $cavalo_id
        ]);
    }

    // VOLTAR A ALUGAR
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

    die('Erro ao alterar estado do aluguer: ' . $e->getMessage());
}
?>