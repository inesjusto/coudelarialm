<?php
require_once 'proteger.php';
require_once 'conexao.php';
require_once 'funcoes-formatacao.php';

header('Content-Type: application/json; charset=utf-8');

function normalizarAltura($valor) {
    if ($valor === null) {
        return null;
    }

    $valor = trim((string)$valor);

    if ($valor === '') {
        return null;
    }

    $valor = str_replace(' ', '', $valor);
    $valor = str_replace(',', '.', $valor);

    return is_numeric($valor) ? (float)$valor : null;
}

function criarImagemOrigem($caminhoTemporario, $tipoImagem) {
    if ($tipoImagem === 'image/jpeg' || $tipoImagem === 'image/jpg') {
        return imagecreatefromjpeg($caminhoTemporario);
    }

    if ($tipoImagem === 'image/png') {
        return imagecreatefrompng($caminhoTemporario);
    }

    if ($tipoImagem === 'image/webp') {
        return imagecreatefromwebp($caminhoTemporario);
    }

    return false;
}

function guardarImagemWebpOtimizada($ficheiro, $pastaDestino, $nomeBase) {
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $tamanhoMaximo = 20 * 1024 * 1024;

    if (!isset($ficheiro) || $ficheiro['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($ficheiro['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload da imagem.');
    }

    if ($ficheiro['size'] > $tamanhoMaximo) {
        throw new Exception('A imagem excede o tamanho máximo permitido.');
    }

    if (!extension_loaded('gd')) {
        throw new Exception('A extensão GD não está ativa no PHP.');
    }

    $tipoImagem = mime_content_type($ficheiro['tmp_name']);

    if (!in_array($tipoImagem, $tiposPermitidos, true)) {
        throw new Exception('Formato de imagem inválido. Use JPG, JPEG, PNG ou WEBP.');
    }

    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0777, true);
    }

    $imagemOrigem = criarImagemOrigem($ficheiro['tmp_name'], $tipoImagem);

    if (!$imagemOrigem) {
        throw new Exception('Não foi possível processar a imagem.');
    }

    $larguraOriginal = imagesx($imagemOrigem);
    $alturaOriginal = imagesy($imagemOrigem);

    $larguraMaxima = 1200;

    if ($larguraOriginal > $larguraMaxima) {
        $novaLargura = $larguraMaxima;
        $novaAltura = (int)(($alturaOriginal / $larguraOriginal) * $novaLargura);
    } else {
        $novaLargura = $larguraOriginal;
        $novaAltura = $alturaOriginal;
    }

    $imagemFinal = imagecreatetruecolor($novaLargura, $novaAltura);

    imagealphablending($imagemFinal, false);
    imagesavealpha($imagemFinal, true);

    $transparente = imagecolorallocatealpha($imagemFinal, 0, 0, 0, 127);
    imagefilledrectangle($imagemFinal, 0, 0, $novaLargura, $novaAltura, $transparente);

    imagecopyresampled(
        $imagemFinal,
        $imagemOrigem,
        0,
        0,
        0,
        0,
        $novaLargura,
        $novaAltura,
        $larguraOriginal,
        $alturaOriginal
    );

    $nomeFicheiro = $nomeBase . '.webp';
    $caminhoFinal = rtrim($pastaDestino, '/\\') . DIRECTORY_SEPARATOR . $nomeFicheiro;

    $guardou = imagewebp($imagemFinal, $caminhoFinal, 80);

    imagedestroy($imagemOrigem);
    imagedestroy($imagemFinal);

    if (!$guardou) {
        throw new Exception('Erro ao guardar a imagem em WEBP.');
    }

    return $nomeFicheiro;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'erro' => 'Método de requisição inválido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (
        !isset($_POST['id']) ||
        !isset($_POST['nome']) ||
        !isset($_POST['raca']) ||
        !isset($_POST['preco']) ||
        !isset($_POST['data_nascimento'])
    ) {
        echo json_encode([
            'erro' => 'Dados incompletos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id = (int) $_POST['id'];

    $nome = trim($_POST['nome'] ?? '');
    $raca = trim($_POST['raca'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $dataNascimento = trim($_POST['data_nascimento'] ?? '');
    $altura = normalizarAltura($_POST['altura'] ?? null);
    $cor = trim($_POST['cor'] ?? '');
    $preco = normalizarValorMonetario($_POST['preco'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (
        $id <= 0 ||
        $nome === '' ||
        $raca === '' ||
        $dataNascimento === '' ||
        trim($_POST['preco'] ?? '') === ''
    ) {
        echo json_encode([
            'erro' => 'Preencha todos os campos obrigatórios.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($preco < 0) {
        echo json_encode([
            'erro' => 'O preço não pode ser negativo.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($altura !== null && $altura < 0) {
        echo json_encode([
            'erro' => 'A altura não pode ser negativa.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataNascimento)) {
        echo json_encode([
            'erro' => 'Data de nascimento inválida.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmtAtual = $conn->prepare("
        SELECT imagem
        FROM cavalos
        WHERE id = :id
        LIMIT 1
    ");

    $stmtAtual->execute([
        ':id' => $id
    ]);

    $cavaloAtual = $stmtAtual->fetch(PDO::FETCH_ASSOC);

    if (!$cavaloAtual) {
        echo json_encode([
            'erro' => 'Cavalo não encontrado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $nomeImagem = $cavaloAtual['imagem'] ?? null;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
        $diretorioDestino = __DIR__ . '/../public/assets/img/cavalos';
        $nomeBase = 'cavalo_' . $id . '_' . time();

        $novaImagem = guardarImagemWebpOtimizada(
            $_FILES['imagem'],
            $diretorioDestino,
            $nomeBase
        );

        if ($novaImagem !== null) {
            if (!empty($nomeImagem)) {
                $imagemAntiga = $diretorioDestino . DIRECTORY_SEPARATOR . $nomeImagem;

                if (file_exists($imagemAntiga)) {
                    unlink($imagemAntiga);
                }
            }

            $nomeImagem = $novaImagem;
        }
    }

    $stmt = $conn->prepare("
        UPDATE cavalos
        SET
            nome = :nome,
            raca = :raca,
            sexo = :sexo,
            data_nascimento = :data_nascimento,
            altura = :altura,
            cor = :cor,
            preco = :preco,
            estado = :estado,
            descricao = :descricao,
            imagem = :imagem
        WHERE id = :id
    ");

    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':raca', $raca);
    $stmt->bindValue(':sexo', $sexo !== '' ? $sexo : null);
    $stmt->bindValue(':data_nascimento', $dataNascimento);
    $stmt->bindValue(':altura', $altura);
    $stmt->bindValue(':cor', $cor !== '' ? $cor : null);
    $stmt->bindValue(':preco', $preco);
    $stmt->bindValue(':estado', $estado !== '' ? $estado : null);
    $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null);
    $stmt->bindValue(':imagem', $nomeImagem);

    $stmt->execute();

    echo json_encode([
        'sucesso' => true,
        'message' => 'Cavalo editado com sucesso.',
        'imagem' => $nomeImagem
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'erro' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>