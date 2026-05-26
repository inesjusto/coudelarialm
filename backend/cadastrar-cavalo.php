<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método de requisição inválido.'
    ]);
    exit;
}

function normalizarPreco($valor) {
    if ($valor === null) return null;

    $valor = trim((string)$valor);
    if ($valor === '') return null;

    $valor = str_replace(' ', '', $valor);
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);

    return is_numeric($valor) ? (float)$valor : null;
}

function normalizarAltura($valor) {
    if ($valor === null) return null;

    $valor = trim((string)$valor);
    if ($valor === '') return null;

    $valor = str_replace(' ', '', $valor);
    $valor = str_replace(',', '.', $valor);

    return is_numeric($valor) ? (float)$valor : null;
}

try {

    $nome = trim($_POST['nome'] ?? '');
    $raca = trim($_POST['raca'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');
    $altura = normalizarAltura($_POST['altura'] ?? null);
    $cor = trim($_POST['cor'] ?? '');
    $preco = normalizarPreco($_POST['preco'] ?? null);
    $estado = trim($_POST['estado'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (
        $nome === '' ||
        $raca === '' ||
        $data_nascimento === '' ||
        $preco === null
    ) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Preencha todos os campos obrigatórios.'
        ]);
        exit;
    }

    if ($preco < 0) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'O preço não pode ser negativo.'
        ]);
        exit;
    }

    if ($altura !== null && $altura < 0) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'A altura não pode ser negativa.'
        ]);
        exit;
    }

    $imagemNome = null;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao enviar a imagem.'
            ]);
            exit;
        }

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        $tamanhoMaximo = 20 * 1024 * 1024;

        if ($_FILES['imagem']['size'] > $tamanhoMaximo) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'A imagem excede o tamanho máximo de 20 MB.'
            ]);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['imagem']['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mimeType, $tiposPermitidos)) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Formato de imagem inválido.'
            ]);
            exit;
        }

        $extensao = $tiposPermitidos[$mimeType];
        $imagemNome = uniqid('cavalo_', true) . '.' . $extensao;

        $diretorioDestino = __DIR__ . '/../public/assets/img/cavalos/';

        if (!is_dir($diretorioDestino)) {
            mkdir($diretorioDestino, 0777, true);
        }

        $caminhoDestino = $diretorioDestino . $imagemNome;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoDestino)) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Não foi possível guardar a imagem.'
            ]);
            exit;
        }
    }

    $sql = "INSERT INTO cavalos
        (
            nome,
            raca,
            sexo,
            data_nascimento,
            altura,
            cor,
            preco,
            estado,
            descricao,
            imagem
        )
        VALUES
        (
            :nome,
            :raca,
            :sexo,
            :data_nascimento,
            :altura,
            :cor,
            :preco,
            :estado,
            :descricao,
            :imagem
        )";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':raca', $raca);
    $stmt->bindValue(':sexo', $sexo !== '' ? $sexo : null);
    $stmt->bindValue(':data_nascimento', $data_nascimento);
    $stmt->bindValue(':altura', $altura);
    $stmt->bindValue(':cor', $cor !== '' ? $cor : null);
    $stmt->bindValue(':preco', $preco);
    $stmt->bindValue(':estado', $estado !== '' ? $estado : null);
    $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null);
    $stmt->bindValue(':imagem', $imagemNome);

    $stmt->execute();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Cavalo cadastrado com sucesso.'
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro na base de dados: ' . $e->getMessage()
    ]);

} catch (Exception $e) {

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro: ' . $e->getMessage()
    ]);
}
?>