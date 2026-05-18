<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['codigo_2fa'])) {
    header('Location: login.php');
    exit();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_digitado = trim($_POST['codigo'] ?? '');
    $agora = date('Y-m-d H:i:s');
    $pdo = conectar_banco();

    if ($agora > $_SESSION['codigo_expira']) {
        $erro = 'Código expirado! Faça login novamente.';
        session_destroy();
    } elseif ($codigo_digitado === $_SESSION['codigo_2fa']) {
        // Código correto!
        $_SESSION['usuario_logado'] = true;
        unset($_SESSION['codigo_2fa']);
        unset($_SESSION['codigo_expira']);

        // Registrar acesso bem sucedido
        $stmt = $pdo->prepare("INSERT INTO logs_acesso (usuario, ip, data_hora, resultado, dispositivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['usuario_email'],
            $_SESSION['ip'],
            date('d/m/Y H:i:s'),
            'Sucesso',
            $_SESSION['dispositivo']
        ]);

        header('Location: listar_membros.php');
        exit();
    } else {
        $erro = 'Código incorreto! Tente novamente.';
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
    <title>Verificação - Sistema Igreja</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 380px; text-align: center; }
        h1 { color: #2c3e50; font-size: 1.5rem; margin-bottom: 10px; }
        p { color: #7f8c8d; font-size: 14px; margin-bottom: 25px; }
        .form-group { margin-bottom: 18px; }
        input { width: 100%; padding: 15px; border: 2px solid #3498db; border-radius: 5px; font-size: 28px; text-align: center; letter-spacing: 10px; font-weight: bold; color: #2c3e50; }
        input:focus { outline: none; border-color: #2980b9; }
        .btn { width: 100%; background-color: #3498db; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 5px; }
        .btn:hover { background-color: #2980b9; }
        .btn-link { background: none; border: none; color: #3498db; cursor: pointer; font-size: 14px; margin-top: 15px; text-decoration: underline; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .info { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 13px; }
        #timer { font-weight: bold; color: #e74c3c; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔐 Verificação</h1>
        <p>Enviamos um código de 6 dígitos para<br><strong><?php echo htmlspecialchars($_SESSION['usuario_email'] ?? ''); ?></strong></p>

        <?php if ($erro): ?>
            <div class="error"><?php echo $erro; ?></div>
        <?php else: ?>
            <div class="info">O código expira em <span id="timer">5:00</span></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="codigo" maxlength="6" placeholder="000000" autofocus>
            </div>
            <button type="submit" class="btn">Verificar</button>
        </form>
        <br>
        <a href="login.php">← Voltar ao login</a>
    </div>

    <script>
        // Countdown timer
        const expira = new Date('<?php echo $_SESSION['codigo_expira'] ?? ''; ?>');
        function updateTimer() {
            const agora = new Date();
            const diff = Math.max(0, Math.floor((expira - agora) / 1000));
            const min = Math.floor(diff / 60);
            const seg = diff % 60;
            const el = document.getElementById('timer');
            if (el) el.textContent = min + ':' + String(seg).padStart(2, '0');
            if (diff <= 0 && el) el.textContent = 'Expirado!';
        }
        setInterval(updateTimer, 1000);
        updateTimer();

        // Auto-submit quando digitar 6 dígitos
        document.querySelector('input[name="codigo"]').addEventListener('input', function() {
            if (this.value.length === 6) this.closest('form').submit();
        });
    </script>

    <script>
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("sw.js");
        }
    </script>

</body>
</html>
