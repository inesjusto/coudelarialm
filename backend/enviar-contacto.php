<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/conexao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método inválido.'
    ]);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');

if ($nome === '' || $email === '' || $mensagem === '') {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Preencha o nome, email e mensagem.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Email inválido.'
    ]);
    exit;
}

try {
    $sql = "INSERT INTO clientes 
                (nome, email, telefone, mensagem, tipo_interesse, estado, cavalo_id)
            VALUES 
                (:nome, :email, :telefone, :mensagem, :tipo_interesse, :estado, :cavalo_id)";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telefone', $telefone !== '' ? $telefone : null);
    $stmt->bindValue(':mensagem', $mensagem);
    $stmt->bindValue(':tipo_interesse', 'Informação');
    $stmt->bindValue(':estado', 'Novo');
    $stmt->bindValue(':cavalo_id', null, PDO::PARAM_NULL);
    $stmt->execute();

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = 'coudelarialimamonteiro@gmail.com';
    $mail->Password = 'sjpmqaycsqjzzdqc';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('coudelarialimamonteiro@gmail.com', 'Website Coudelaria Lima Monteiro');
    $mail->addAddress('coudelarialimamonteiro@gmail.com');
    $mail->addReplyTo($email, $nome);

    $mail->isHTML(true);
    $mail->Subject = 'Novo pedido de contacto | Coudelaria Lima Monteiro';

    $mail->Body = "
<!DOCTYPE html>
<html lang='pt'>
<body style='margin:0; padding:0; background:#1A1D2B; font-family:Arial, sans-serif;'>
  <div style='max-width:680px; margin:30px auto; padding:22px;'>
    <div style='background:#24283B; border:1px solid rgba(255,255,255,0.08); border-radius:18px; overflow:hidden;'>

      <div style='padding:34px 30px; text-align:center; background:#171B2B;'>
        <h1 style='margin:0; color:#FFFFFF; font-size:26px;'>
          Coudelaria <span style='color:#00C853;'>Lima Monteiro</span>
        </h1>
        <p style='margin:12px 0 0; color:#00C853; font-size:13px; text-transform:uppercase; letter-spacing:1.5px;'>
          Novo pedido de contacto
        </p>
      </div>

      <div style='padding:34px 30px;'>
        <p style='margin:0 0 28px; color:#94A3B8; font-size:16px; line-height:1.6; text-align:center;'>
          Foi recebida uma nova mensagem através do formulário de contacto do website.
        </p>

        <div style='margin-bottom:18px; padding:18px; background:#1A1D2B; border-radius:14px; border:1px solid rgba(255,255,255,0.08);'>
          <strong style='display:block; color:#FFFFFF; margin-bottom:6px;'>Nome</strong>
          <span style='color:#94A3B8;'>" . htmlspecialchars($nome) . "</span>
        </div>

        <div style='margin-bottom:18px; padding:18px; background:#1A1D2B; border-radius:14px; border:1px solid rgba(255,255,255,0.08);'>
          <strong style='display:block; color:#FFFFFF; margin-bottom:6px;'>Email</strong>
          <a href='mailto:" . htmlspecialchars($email) . "' style='color:#00C853; text-decoration:none;'>
            " . htmlspecialchars($email) . "
          </a>
        </div>

        <div style='margin-bottom:18px; padding:18px; background:#1A1D2B; border-radius:14px; border:1px solid rgba(255,255,255,0.08);'>
          <strong style='display:block; color:#FFFFFF; margin-bottom:6px;'>Telefone</strong>
          <span style='color:#94A3B8;'>" . htmlspecialchars($telefone ?: 'Não indicado') . "</span>
        </div>

        <div style='margin-top:24px; padding:22px; background:#1A1D2B; border-radius:14px; border-left:4px solid #00C853;'>
          <strong style='display:block; color:#FFFFFF; margin-bottom:12px;'>Mensagem</strong>
          <p style='margin:0; color:#94A3B8; line-height:1.7;'>
            " . nl2br(htmlspecialchars($mensagem)) . "
          </p>
        </div>

        <div style='margin-top:30px; text-align:center;'>
          <a href='mailto:" . htmlspecialchars($email) . "' 
             style='display:inline-block; background:#00C853; color:#FFFFFF; padding:13px 24px; border-radius:999px; text-decoration:none; font-weight:bold;'>
            Responder ao cliente
          </a>
        </div>
      </div>

      <div style='padding:18px; text-align:center; background:#171B2B; border-top:1px solid rgba(255,255,255,0.08);'>
        <p style='margin:0; color:#94A3B8; font-size:12px;'>
          Mensagem enviada automaticamente através do website da Coudelaria Lima Monteiro.
        </p>
      </div>

    </div>
  </div>
</body>
</html>
";

    $mail->AltBody =
        "Novo pedido de contacto - Coudelaria Lima Monteiro\n\n" .
        "Nome: $nome\n" .
        "Email: $email\n" .
        "Telefone: " . ($telefone ?: 'Não indicado') . "\n\n" .
        "Mensagem:\n$mensagem";

    $mail->send();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Mensagem enviada com sucesso.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao guardar o contacto na base de dados.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível enviar a mensagem.'
    ]);
}
?>