<?php
header('Content-Type: application/json; charset=utf-8');

include 'conexao.php';

function mensagemErroUpload(int $codigo): string
{
    switch ($codigo) {
        case UPLOAD_ERR_INI_SIZE:
            return 'A imagem excede o limite definido no php.ini.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'A imagem excede o limite definido pelo formulário.';
        case UPLOAD_ERR_PARTIAL:
            return 'A imagem foi enviada apenas parcialmente.';
        case UPLOAD_ERR_NO_FILE:
            return 'Nenhuma imagem foi enviada.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'A pasta temporária do servidor não existe.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Falha ao escrever a imagem no disco.';
        case UPLOAD_ERR_EXTENSION:
            return 'Uma extensão do PHP bloqueou o upload da imagem.';
        case UPLOAD_ERR_OK:
            return 'Sem erro.';
        default:
            return 'Erro desconhecido no upload da imagem.';
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Método não permitido.'
        ]);
        exit;
    }

    $nome = trim($_POST['nome'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $idade = trim($_POST['idade'] ?? '');
    $raca = trim($_POST['raca'] ?? '');
    $preco = trim($_POST['preco'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($nome === '' || $sexo === '' || $idade === '' || $raca === '') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Os campos nome, sexo, idade e raça são obrigatórios.'
        ]);
        exit;
    }

    if (!is_numeric($idade)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'A idade tem de ser numérica.'
        ]);
        exit;
    }

    if (!isset($_FILES['imagem'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'O campo de imagem não foi recebido pelo servidor.',
            'debug' => [
                'files_keys' => array_keys($_FILES),
                'post_keys' => array_keys($_POST)
            ]
        ]);
        exit;
    }

    $ficheiro = $_FILES['imagem'];

    if ($ficheiro['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => mensagemErroUpload((int)$ficheiro['error']),
            'debug' => [
                'upload_error_code' => $ficheiro['error'],
                'upload_name' => $ficheiro['name'] ?? null,
                'upload_size' => $ficheiro['size'] ?? null,
                'tmp_name' => $ficheiro['tmp_name'] ?? null
            ]
        ]);
        exit;
    }

    if (!isset($ficheiro['tmp_name']) || $ficheiro['tmp_name'] === '') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'O ficheiro temporário do upload não foi criado.',
            'debug' => [
                'upload_name' => $ficheiro['name'] ?? null,
                'upload_size' => $ficheiro['size'] ?? null
            ]
        ]);
        exit;
    }

    if (!is_uploaded_file($ficheiro['tmp_name'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'O ficheiro recebido não é reconhecido como upload válido.',
            'debug' => [
                'tmp_name' => $ficheiro['tmp_name']
            ]
        ]);
        exit;
    }

    if ($ficheiro['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'A imagem excede o tamanho máximo de 5MB.'
        ]);
        exit;
    }

    $extensao = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extensao, $extensoesPermitidas, true)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Formato inválido. Usa JPG, JPEG, PNG ou WEBP.',
            'debug' => [
                'extensao' => $extensao
            ]
        ]);
        exit;
    }

    $pastaDestino = __DIR__ . '/../public/assets/img/cavalos/';

    if (!is_dir($pastaDestino)) {
        if (!mkdir($pastaDestino, 0777, true) && !is_dir($pastaDestino)) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Não foi possível criar a pasta de destino.',
                'debug' => [
                    'pastaDestino' => $pastaDestino
                ]
            ]);
            exit;
        }
    }

    if (!is_writable($pastaDestino)) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'A pasta de destino não tem permissão de escrita.',
            'debug' => [
                'pastaDestino' => $pastaDestino
            ]
        ]);
        exit;
    }

    $nomeImagem = uniqid('cavalo_', true) . '.' . $extensao;
    $caminhoDestino = $pastaDestino . $nomeImagem;

    if (!move_uploaded_file($ficheiro['tmp_name'], $caminhoDestino)) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Não foi possível guardar a imagem no destino final.',
            'debug' => [
                'tmp_name' => $ficheiro['tmp_name'],
                'destino' => $caminhoDestino
            ]
        ]);
        exit;
    }

    $sql = "INSERT INTO cavalos (nome, sexo, idade, raca, preco, descricao, imagem)
            VALUES (:nome, :sexo, :idade, :raca, :preco, :descricao, :imagem)";

    $stmt = $conn->prepare($sql);
    $ok = $stmt->execute([
        ':nome' => $nome,
        ':sexo' => $sexo,
        ':idade' => (int)$idade,
        ':raca' => $raca,
        ':preco' => $preco !== '' ? $preco : null,
        ':descricao' => $descricao !== '' ? $descricao : null,
        ':imagem' => $nomeImagem
    ]);

    if (!$ok) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Falha ao inserir o cavalo na base de dados.',
            'debug' => $stmt->errorInfo()
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Cavalo adicionado com sucesso.',
        'imagem' => $nomeImagem
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro de base de dados ao adicionar cavalo.',
        'debug' => $e->getMessage()
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro inesperado ao adicionar cavalo.',
        'debug' => $e->getMessage()
    ]);
    exit;
}