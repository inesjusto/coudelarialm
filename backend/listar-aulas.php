<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT 
                a.id,
                a.cliente_id,
                cl.nome AS cliente_nome,
                a.cavalo_id,
                c.nome AS cavalo_nome,
                a.data_aula,
                a.hora_inicio,
                a.hora_fim,
                a.tipo_aula,
                a.preco,
                a.estado,
                a.observacoes,
                a.data_criacao
            FROM aulas a
            LEFT JOIN clientes cl ON a.cliente_id = cl.id
            LEFT JOIN cavalos c ON a.cavalo_id = c.id
            ORDER BY a.data_aula DESC, a.hora_inicio DESC";

    $stmt = $conn->query($sql);
    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($aulas);

} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao listar aulas: ' . $e->getMessage()
    ]);
}
?>