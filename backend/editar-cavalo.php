<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

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

    if (
        !isset($_POST['id']) ||
        !isset($_POST['nome']) ||
        !isset($_POST['raca']) ||
        !isset($_POST['preco']) ||
        !isset($_POST['data_nascimento'])
    ) {
        echo json_encode(["erro" => "Dados incompletos."]);
        exit;
    }

    $id = (int) $_POST['id'];

    $nome = trim($_POST['nome']);
    $raca = trim($_POST['raca']);
    $sexo = trim($_POST['sexo'] ?? '');
    $data_nascimento = trim($_POST['data_nascimento']);
    $altura = normalizarAltura($_POST['altura'] ?? null);
    $cor = trim($_POST['cor'] ?? '');
    $preco = normalizarPreco($_POST['preco'] ?? null);
    $estado = trim($_POST['estado'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (
        $id <= 0 ||
        $nome === '' ||
        $raca === '' ||
        $data_nascimento === '' ||
        $preco === null
    ) {
        echo json_encode(["erro" => "Preencha todos os campos obrigatórios."]);
        exit;
    }

    if ($preco < 0) {
        echo json_encode(["erro" => "O preço não pode ser negativo."]);
        exit;
    }

    if ($altura !== null && $altura < 0) {
        echo json_encode(["erro" => "A altura não pode ser negativa."]);
        exit;
    }

    $sqlAtual = "SELECT imagem FROM cavalos WHERE id = :id";

    $stmtAtual = $conn->prepare($sqlAtual);
    $stmtAtual->execute([':id' => $id]);

    $cavaloAtual = $stmtAtual->fetch(PDO::FETCH_ASSOC);

    if (!$cavaloAtual) {
        echo json_encode(["erro" => "Cavalo não encontrado."]);
        exit;
    }

    $nomeImagem = $cavaloAtual['imagem'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(["erro" => "Erro ao enviar a nova imagem."]);
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
            echo json_encode(["erro" => "A imagem excede 20 MB."]);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mimeType = finfo_file($finfo, $_FILES['imagem']['tmp_name']);

        finfo_close($finfo);

        if (!array_key_exists($mimeType, $tiposPermitidos)) {
            echo json_encode(["erro" => "Formato inválido."]);
            exit;
        }

        $extensao = $tiposPermitidos[$mimeType];

        $novoNomeImagem = uniqid('cavalo_', true) . '.' . $extensao;

        $diretorioDestino = __DIR__ . '/../public/assets/img/cavalos/';

        if (!is_dir($diretorioDestino)) {
            mkdir($diretorioDestino, 0777, true);
        }

        $caminhoFisico = $diretorioDestino . $novoNomeImagem;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoFisico)) {
            echo json_encode(["erro" => "Erro ao guardar imagem."]);
            exit;
        }

        if (!empty($nomeImagem)) {

            $imagemAntiga = __DIR__ . '/../public/assets/img/cavalos/' . $nomeImagem;

            if (file_exists($imagemAntiga)) {
                unlink($imagemAntiga);
            }
        }

        $nomeImagem = $novoNomeImagem;
    }

    $sql = "UPDATE cavalos
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
        WHERE id = :id";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':raca', $raca);
    $stmt->bindValue(':sexo', $sexo !== '' ? $sexo : null);
    $stmt->bindValue(':data_nascimento', $data_nascimento);
    $stmt->bindValue(':altura', $altura);
    $stmt->bindValue(':cor', $cor !== '' ? $cor : null);
    $stmt->bindValue(':preco', $preco);
    $stmt->bindValue(':estado', $estado !== '' ? $estado : null);
    $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null);
    $stmt->bindValue(':imagem', $nomeImagem);

    $stmt->execute();

    echo json_encode([
        "sucesso" => true,
        "message" => "Cavalo editado com sucesso."
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "erro" => "Erro ao editar cavalo: " . $e->getMessage()
    ]);
}
?>