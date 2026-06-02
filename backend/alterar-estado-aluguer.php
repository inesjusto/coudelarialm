<?php
require_once 'proteger.php';
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
        ");

        $stmtAtualizarCavalo->execute([
            ':cavalo_id' => $cavalo_id
        ]);
    }

    if ($estado === 'concluido' || $estado === 'cancelado') {
        $stmtVerificar = $conn->prepare("
            SELECT id
            FROM alugueres
            WHERE cavalo_id = :cavalo_id
              AND estado = 'ativo'
              AND id != :id
            LIMIT 1
        ");

        $stmtVerificar->execute([
            ':cavalo_id' => $cavalo_id,
            ':id' => $id
        ]);

        if (!$stmtVerificar->fetch()) {
            $stmtAtualizarCavalo = $conn->prepare("
                UPDATE cavalos
                SET estado = 'Disponível'
                WHERE id = :cavalo_id
            ");

            $stmtAtualizarCavalo->execute([
                ':cavalo_id' => $cavalo_id
            ]);
        }
    }

    header('Location: ../admin/alugueres.php');
    exit;

} catch (PDOException $e) {
    die('Erro ao alterar estado do aluguer: ' . $e->getMessage());
}
?>