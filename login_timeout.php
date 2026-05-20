<?php
session_start();
require_once 'config.php';
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    header('Location: listar_membros.php'); exit();
}
$erro = '';
if (isset($_GET['timeout'])) {
    $erro = 'Sessão expirada por inatividade. Faça login novamente.';
}
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
        $_SESSION['usuario_logado'] = true;
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_nivel'] = $usuario['nivel'];
        $_SESSION['ultimo_acesso'] = time();
        $stmt = $pdo->prepare("INSERT INTO logs_acesso (usuario, ip, data_hora, resultado, dispositivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$usuario['email'], $ip, $data_hora, 'Sucesso', $dispositivo]);
        header('Location: listar_membros.php'); exit();
    } else {
        $erro = 'Email ou senha incorretos.';
        try {
            $stmt = $pdo->prepare("INSERT INTO logs_acesso (usuario, ip, data_hora, resultado, dispositivo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email ?: '(vazio)', $ip, $data_hora, 'Falha', $dispositivo]);
        } catch (Exception $e) {}
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .btn:hover { background-color: #2980b9; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .warning { background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🙏 Sistema Igreja</h1>
        <?php if (isset($_GET['timeout'])): ?>
            <div class="warning">⏱️ Sessão expirada por inatividade. Faça login novamente.</div>
        <?php elseif ($erro): ?>
            <div class="error"><?php echo $erro; ?></div>
        <?php endif; ?>
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
</body>
</html>
