<?php
require_once 'proteger.php';
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$agrupamento = $_GET['agrupamento'] ?? 'tipo';

$permitidos = ['tipo', 'estado', 'interesse_cavalo'];

if (!in_array($agrupamento, $permitidos, true)) {
    echo json_encode(['erro' => 'Agrupamento inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function formatarEstadoCliente($estado) {
    $estado = trim((string)$estado);
    $estadoLower = mb_strtolower($estado, 'UTF-8');

    if ($estadoLower === 'cliente') {
        return 'Cliente';
    }

    if ($estadoLower === 'potencial') {
        return 'Potencial';
    }

    if ($estadoLower === 'contactado') {
        return 'Contactado';
    }

    return $estadoLower !== '' ? ucfirst($estadoLower) : 'Sem estado';
}

function formatarTipoInteresse($tipo) {
    $tipo = trim((string)$tipo);
    $tipoLower = mb_strtolower($tipo, 'UTF-8');

    if ($tipoLower === 'informacao' || $tipoLower === 'informação') {
        return 'Informação';
    }

    if ($tipoLower === 'compra') {
        return 'Compra';
    }

    if ($tipoLower === 'visita') {
        return 'Visita';
    }

    return $tipoLower !== '' ? ucfirst($tipoLower) : 'Sem tipo';
}

try {
    if ($agrupamento === 'estado') {
        $sql = "
            SELECT 
                TRIM(LOWER(estado)) AS label, 
                COUNT(*) AS total
            FROM clientes
            WHERE estado IS NOT NULL 
              AND TRIM(estado) <> ''
            GROUP BY TRIM(LOWER(estado))
            ORDER BY total DESC, label ASC
        ";
    } elseif ($agrupamento === 'interesse_cavalo') {
        $sql = "
            SELECT 
                CASE 
                    WHEN cc.cliente_id IS NOT NULL THEN 'Sim'
                    ELSE 'Não'
                END AS label,
                COUNT(DISTINCT c.id) AS total
            FROM clientes c
            LEFT JOIN clientes_cavalos cc 
                ON c.id = cc.cliente_id
            GROUP BY label
            ORDER BY label DESC
        ";
    } else {
        $sql = "
            SELECT 
                TRIM(LOWER(tipo_interesse)) AS label, 
                COUNT(*) AS total
            FROM clientes
            WHERE tipo_interesse IS NOT NULL 
              AND TRIM(tipo_interesse) <> ''
            GROUP BY TRIM(LOWER(tipo_interesse))
            ORDER BY total DESC, label ASC
        ";
    }

    $stmt = $conn->query($sql);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dadosFormatados = array_map(function ($item) use ($agrupamento) {
        $label = trim((string)($item['label'] ?? ''));

        if ($agrupamento === 'estado') {
            $label = formatarEstadoCliente($label);
        } elseif ($agrupamento === 'tipo') {
            $label = formatarTipoInteresse($label);
        } elseif ($label === '') {
            $label = 'Sem tipo';
        }

        return [
            'label' => $label,
            'total' => (int) $item['total']
        ];
    }, $dados);

    $totalStmt = $conn->query("
        SELECT COUNT(*) AS total 
        FROM clientes
    ");
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC);

    $clientesStmt = $conn->query("
        SELECT COUNT(*) AS total 
        FROM clientes 
        WHERE TRIM(LOWER(estado)) = 'cliente'
    ");
    $clientes = $clientesStmt->fetch(PDO::FETCH_ASSOC);

    $potenciaisStmt = $conn->query("
        SELECT COUNT(*) AS total 
        FROM clientes 
        WHERE TRIM(LOWER(estado)) = 'potencial'
    ");
    $potenciais = $potenciaisStmt->fetch(PDO::FETCH_ASSOC);

    $contactadosStmt = $conn->query("
        SELECT COUNT(*) AS total 
        FROM clientes 
        WHERE TRIM(LOWER(estado)) = 'contactado'
    ");
    $contactados = $contactadosStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'total' => (int)($total['total'] ?? 0),
        'clientes' => (int)($clientes['total'] ?? 0),
        'potenciais' => (int)($potenciais['total'] ?? 0),
        'contactados' => (int)($contactados['total'] ?? 0),
        'agrupamento' => $agrupamento,
        'dados' => $dadosFormatados
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'erro' => 'Erro ao obter estatísticas dos clientes: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>