<?php
include 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (
        !isset($_POST['id']) ||
        !isset($_POST['nome']) ||
        !isset($_POST['sexo']) ||
        !isset($_POST['idade']) ||
        !isset($_POST['raca']) ||
        !isset($_POST['preco']) ||
        !isset($_POST['descricao'])
    ) {
        echo json_encode(["erro" => "Dados incompletos."]);
        exit;
    }

    $id = (int) $_POST['id'];
    $nome = trim($_POST['nome']);
    $sexo = trim($_POST['sexo']);
    $idade = (int) $_POST['idade'];
    $raca = trim($_POST['raca']);
    $preco = $_POST['preco'];
    $descricao = trim($_POST['descricao']);

    $sqlAtual = "SELECT imagem FROM cavalos WHERE id = :id";
    $stmtAtual = $conn->prepare($sqlAtual);
    $stmtAtual->execute([':id' => $id]);
    $cavaloAtual = $stmtAtual->fetch(PDO::FETCH_ASSOC);

    if (!$cavaloAtual) {
        echo json_encode(["erro" => "Cavalo não encontrado."]);
        exit;
    }

    $nomeImagem = $cavaloAtual['imagem'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extensao, $extensoesPermitidas)) {
            echo json_encode(["erro" => "Formato de imagem inválido."]);
            exit;
        }

        $novoNomeImagem = uniqid('cavalo_', true) . '.' . $extensao;
        $caminhoFisico = __DIR__ . '/../public/assets/img/cavalos/' . $novoNomeImagem;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoFisico)) {
            echo json_encode(["erro" => "Erro ao guardar a nova imagem."]);
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
            SET nome = :nome,
                sexo = :sexo,
                idade = :idade,
                raca = :raca,
                preco = :preco,
                descricao = :descricao,
                imagem = :imagem
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':nome' => $nome,
        ':sexo' => $sexo,
        ':idade' => $idade,
        ':raca' => $raca,
        ':preco' => $preco,
        ':descricao' => $descricao,
        ':imagem' => $nomeImagem
    ]);

    echo json_encode(["sucesso" => true]);
} catch (PDOException $e) {
    echo json_encode(["erro" => "Erro ao editar cavalo: " . $e->getMessage()]);
}
?>