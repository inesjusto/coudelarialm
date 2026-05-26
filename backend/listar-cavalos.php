<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $sql = "
        SELECT 
            c.id,
            c.nome,
            c.sexo,
            c.data_nascimento,
            c.raca,
            c.preco,
            c.estado,
            c.descricao,
            c.imagem,
            CASE 
                WHEN a.id IS NOT NULL THEN 'alugado'
                ELSE 'disponivel'
            END AS estado_aluguer
        FROM cavalos c
        LEFT JOIN alugueres a 
            ON c.id = a.cavalo_id 
            AND a.estado = 'ativo'
        ORDER BY c.id DESC
    ";

    $stmt = $conn->query($sql);

    $cavalos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cavalos as &$cavalo) {

        if (!empty($cavalo['data_nascimento'])) {

            $dataNascimento = new DateTime($cavalo['data_nascimento']);
            $hoje = new DateTime();

            $diferenca = $hoje->diff($dataNascimento);

            if ($diferenca->y > 0) {
                $cavalo['idade'] = $diferenca->y === 1
                    ? '1 ano'
                    : $diferenca->y . ' anos';
            } else {
                $cavalo['idade'] = $diferenca->m === 1
                    ? '1 mês'
                    : $diferenca->m . ' meses';
            }

        } else {
            $cavalo['idade'] = '-';
        }
    }

    echo json_encode($cavalos, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    echo json_encode([
        "erro" => "Erro ao listar cavalos: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>