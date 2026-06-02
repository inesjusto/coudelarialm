<?php
require_once __DIR__ . '/conexao.php';

try {
    $stmt = $conn->prepare("
        UPDATE alugueres
        SET estado = 'concluido'
        WHERE estado = 'ativo'
          AND data_fim IS NOT NULL
          AND data_fim <= CURDATE()
    ");
    $stmt->execute();

    $stmtCavalos = $conn->prepare("
        UPDATE cavalos c
        SET c.estado = 'Disponível'
        WHERE c.estado = 'Alugado'
          AND NOT EXISTS (
              SELECT 1
              FROM alugueres a
              WHERE a.cavalo_id = c.id
                AND a.estado = 'ativo'
          )
    ");
    $stmtCavalos->execute();

} catch (PDOException $e) {
    error_log('Erro ao atualizar alugueres: ' . $e->getMessage());
}
?>