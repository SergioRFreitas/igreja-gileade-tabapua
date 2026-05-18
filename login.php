<?php
session_start();
require_once 'config.php';
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    header('Location: listar_membros.php'); exit();
}
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    $dispositivo = $_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido';
    $data_hora = date('d/m/Y H:i:s');
    $pdo = conectar_banco();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario && $usuario['senha'] === $senha) {
        $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expira = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $_SESSION['codigo_2fa'] = $codigo;
        $_SESSION['codigo_expira'] = $expira;
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['ip'] = $ip;
        $_SESSION['dispositivo'] = $dispositivo;
        require '/var/www/igreja/vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'igileadetabapua@gmail.com';
            $mail->Password = 'fwouowrgxnptqqbe';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('igileadetabapua@gmail.com', 'Sistema Igreja');
            $mail->addAddress($usuario['email'], $usuario['nome']);
            $mail->Subject = 'Seu codigo de acesso';
            $mail->Body = "Ola {$usuario['nome']}! Seu codigo de acesso e: {$codigo}. Expira em 5 minutos.";
            $mail->send();
            $stmt = $pdo->prepare("INSERT INTO logs_acesso (usuario, ip, data_hora, resultado, dispositivo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$usuario['email'], $ip, $data_hora, 'Codigo enviado', $dispositivo]);
            header('Location: verificar_codigo.php'); exit();
        } catch (Exception $e) {
            $erro = 'Erro ao enviar email: ' . $mail->ErrorInfo;
        }
    } else {
        $erro = 'Email ou senha incorretos.';
        $stmt = $pdo->prepare("INSERT INTO logs_acesso (usuario, ip, data_hora, resultado, dispositivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$email ?: '(vazio)', $ip, $data_hora, 'Falha', $dispositivo]);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <title>Login - Sistema Igreja</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 380px; }
        h1 { color: #2c3e50; text-align: center; font-size: 1.5rem; margin-bottom: 25px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        .btn { width: 100%; background-color: #3498db; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 5px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🙏 Sistema Igreja</h1>
        <?php if ($erro): ?><div class="error"><?php echo $erro; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="seu@email.com" autofocus>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="Digite sua senha">
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>

    <script>
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("sw.js");
        }
    </script>

</body>
</html>
