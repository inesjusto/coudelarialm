<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT 
                a.id,
                a.data_aula,
                a.hora_inicio,
                a.hora_fim,
                a.tipo_aula,
                a.estado,
                a.preco,
                cl.nome AS cliente_nome,
                c.nome AS cavalo_nome
            FROM aulas a
            LEFT JOIN clientes cl ON a.cliente_id = cl.id
            LEFT JOIN cavalos c ON a.cavalo_id = c.id
            ORDER BY a.data_aula ASC, a.hora_inicio ASC";

    $stmt = $conn->query($sql);
    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $eventos = [];

    foreach ($aulas as $aula) {
        $titulo = $aula['tipo_aula'] ?: 'Aula';

        if (!empty($aula['cliente_nome'])) {
            $titulo .= ' - ' . $aula['cliente_nome'];
        }

        if (!empty($aula['cavalo_nome'])) {
            $titulo .= ' / ' . $aula['cavalo_nome'];
        }

        $eventos[] = [
            'id' => $aula['id'],
            'title' => $titulo,
            'start' => $aula['data_aula'] . 'T' . $aula['hora_inicio'],
            'end' => $aula['data_aula'] . 'T' . $aula['hora_fim'],
            'url' => 'editar-aula.php?id=' . $aula['id'],
            'extendedProps' => [
                'estado' => $aula['estado'],
                'preco' => $aula['preco']
            ]
        ];
    }

    echo json_encode($eventos);

} catch (PDOException $e) {
    echo json_encode([]);
}
?>