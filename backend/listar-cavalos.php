<?php
require_once __DIR__ . '/conexao.php';

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
                WHEN EXISTS (
                    SELECT 1
                    FROM alugueres a
                    WHERE a.cavalo_id = c.id
                      AND TRIM(LOWER(a.estado)) = 'ativo'
                ) THEN 'alugado'
                ELSE 'disponivel'
            END AS estado_aluguer
        FROM cavalos c
        ORDER BY c.id DESC
    ";

    $stmt = $conn->query($sql);
    $cavalos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cavalos as &$cavalo) {
        if (!empty($cavalo['data_nascimento'])) {
            try {
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
            } catch (Exception $e) {
                $cavalo['idade'] = '-';
            }
        } else {
            $cavalo['idade'] = '-';
        }

        $cavalo['preco_formatado'] = number_format((float)$cavalo['preco'], 2, ',', '.') . ' €';

        if (empty($cavalo['imagem'])) {
            $cavalo['imagem'] = 'default.jpg';
        }
    }

    unset($cavalo);

    echo json_encode($cavalos, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao listar cavalos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>