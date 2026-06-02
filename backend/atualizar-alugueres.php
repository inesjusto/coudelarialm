<?php
require_once __DIR__ . '/conexao.php';

try {
    $conn->beginTransaction();

    /*
        1. Ativar alugueres reservados que começam hoje ou já começaram.

        Exemplo:
        Hoje: 2026-06-10
        Aluguer: 2026-06-10 até 2026-06-20
        Resultado: reservado -> ativo
    */
    $stmtAtivarReservados = $conn->prepare("
        UPDATE alugueres
        SET estado = 'ativo'
        WHERE TRIM(LOWER(estado)) = 'reservado'
          AND data_inicio <= CURDATE()
          AND data_fim >= CURDATE()
    ");
    $stmtAtivarReservados->execute();

    /*
        2. Colocar como Alugado os cavalos que têm aluguer ativo hoje.
    */
    $stmtCavalosAlugados = $conn->prepare("
        UPDATE cavalos c
        SET c.estado = 'Alugado'
        WHERE EXISTS (
            SELECT 1
            FROM alugueres a
            WHERE a.cavalo_id = c.id
              AND TRIM(LOWER(a.estado)) = 'ativo'
              AND a.data_inicio <= CURDATE()
              AND a.data_fim >= CURDATE()
        )
          AND TRIM(LOWER(c.estado)) NOT IN ('vendido', 'reservado', 'indisponível', 'indisponivel', 'em tratamento', 'reformado')
    ");
    $stmtCavalosAlugados->execute();

    /*
        3. Concluir alugueres ativos que já passaram da data final.
    */
    $stmtConcluirAtivos = $conn->prepare("
        UPDATE alugueres
        SET estado = 'concluido'
        WHERE TRIM(LOWER(estado)) = 'ativo'
          AND data_fim IS NOT NULL
          AND data_fim < CURDATE()
    ");
    $stmtConcluirAtivos->execute();

    /*
        4. Concluir alugueres reservados que por algum motivo já passaram
           sem terem ficado ativos.
    */
    $stmtConcluirReservadosExpirados = $conn->prepare("
        UPDATE alugueres
        SET estado = 'concluido'
        WHERE TRIM(LOWER(estado)) = 'reservado'
          AND data_fim IS NOT NULL
          AND data_fim < CURDATE()
    ");
    $stmtConcluirReservadosExpirados->execute();

    /*
        5. Libertar cavalos que estão como Alugado, mas já não têm
           nenhum aluguer ativo no dia atual.
    */
    $stmtLibertarCavalos = $conn->prepare("
        UPDATE cavalos c
        SET c.estado = 'Disponível'
        WHERE TRIM(LOWER(c.estado)) = 'alugado'
          AND NOT EXISTS (
              SELECT 1
              FROM alugueres a
              WHERE a.cavalo_id = c.id
                AND TRIM(LOWER(a.estado)) = 'ativo'
                AND a.data_inicio <= CURDATE()
                AND a.data_fim >= CURDATE()
          )
    ");
    $stmtLibertarCavalos->execute();

    $conn->commit();

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('Erro ao atualizar alugueres: ' . $e->getMessage());
}
?>