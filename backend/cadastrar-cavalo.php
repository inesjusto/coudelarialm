<?php
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'sucesso' => false,
        'message' => 'Método inválido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$sexo = trim($_POST['sexo'] ?? '');
$data_nascimento = trim($_POST['data_nascimento'] ?? '');
$raca = trim($_POST['raca'] ?? '');
$altura = trim($_POST['altura'] ?? '');
$cor = trim($_POST['cor'] ?? '');
$preco = trim($_POST['preco'] ?? '');
$estado = trim($_POST['estado'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (
    $nome === '' ||
    $sexo === '' ||
    $data_nascimento === '' ||
    $raca === '' ||
    $altura === '' ||
    $cor === '' ||
    $preco === '' ||
    $estado === '' ||
    $descricao === ''
) {
    echo json_encode([
        'sucesso' => false,
        'message' => 'Preencha todos os campos obrigatórios.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'sucesso' => false,
        'message' => 'Selecione uma imagem válida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$tiposPermitidos = [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/webp'
];

$tamanhoMaximo = 20 * 1024 * 1024;

if ($_FILES['imagem']['size'] > $tamanhoMaximo) {
    echo json_encode([
        'sucesso' => false,
        'message' => 'A imagem excede o limite máximo de 20 MB.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['imagem']['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $tiposPermitidos, true)) {
    echo json_encode([
        'sucesso' => false,
        'message' => 'Formato de imagem inválido. Use JPG, PNG ou WEBP.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $diretorioDestino = __DIR__ . '/../public/assets/img/cavalos/';

    if (!is_dir($diretorioDestino)) {
        mkdir($diretorioDestino, 0777, true);
    }

    $imagemNome = uniqid('cavalo_', true) . '.webp';
    $caminhoDestino = $diretorioDestino . $imagemNome;

    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $imagemOriginal = imagecreatefromjpeg($_FILES['imagem']['tmp_name']);
            break;

        case 'image/png':
            $imagemOriginal = imagecreatefrompng($_FILES['imagem']['tmp_name']);
            imagepalettetotruecolor($imagemOriginal);
            imagealphablending($imagemOriginal, true);
            imagesavealpha($imagemOriginal, true);
            break;

        case 'image/webp':
            $imagemOriginal = imagecreatefromwebp($_FILES['imagem']['tmp_name']);
            break;

        default:
            throw new Exception('Formato de imagem não suportado.');
    }

    if (!$imagemOriginal) {
        throw new Exception('Não foi possível processar a imagem.');
    }

    $larguraOriginal = imagesx($imagemOriginal);
    $alturaOriginal = imagesy($imagemOriginal);

    $larguraMaxima = 1200;

    if ($larguraOriginal > $larguraMaxima) {
        $larguraNova = $larguraMaxima;
        $alturaNova = (int)(($alturaOriginal * $larguraNova) / $larguraOriginal);
    } else {
        $larguraNova = $larguraOriginal;
        $alturaNova = $alturaOriginal;
    }

    $imagemRedimensionada = imagecreatetruecolor($larguraNova, $alturaNova);

    imagecopyresampled(
        $imagemRedimensionada,
        $imagemOriginal,
        0,
        0,
        0,
        0,
        $larguraNova,
        $alturaNova,
        $larguraOriginal,
        $alturaOriginal
    );

    $qualidadeWebp = 80;

    if (!imagewebp($imagemRedimensionada, $caminhoDestino, $qualidadeWebp)) {
        throw new Exception('Erro ao guardar a imagem otimizada.');
    }

    imagedestroy($imagemOriginal);
    imagedestroy($imagemRedimensionada);

    $sql = "
        INSERT INTO cavalos 
        (nome, sexo, data_nascimento, raca, altura, cor, preco, estado, descricao, imagem)
        VALUES
        (:nome, :sexo, :data_nascimento, :raca, :altura, :cor, :preco, :estado, :descricao, :imagem)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':nome' => $nome,
        ':sexo' => $sexo,
        ':data_nascimento' => $data_nascimento,
        ':raca' => $raca,
        ':altura' => $altura,
        ':cor' => $cor,
        ':preco' => $preco,
        ':estado' => $estado,
        ':descricao' => $descricao,
        ':imagem' => $imagemNome
    ]);

    echo json_encode([
        'sucesso' => true,
        'message' => 'Cavalo adicionado com sucesso.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'message' => 'Erro ao adicionar cavalo: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>