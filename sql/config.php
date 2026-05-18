<?php
// config.php - Configuração do banco de dados MySQL
// Igreja Gileade Tabapuã - Locaweb

define('DB_HOST', '179.188.16.38');
define('DB_NAME', 'igrejagileade');
define('DB_USER', 'igrejagileade');
define('DB_PASS', 'Igrej@2026');
define('DB_CHARSET', 'utf8mb4');

// Função para conectar ao banco
function conectar_banco() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro ao conectar ao banco: " . $e->getMessage());
    }
}

// Função para formatar data
function formatar_data($data) {
    if (!$data) return '';
    return date('d/m/Y', strtotime($data));
}

// Função para formatar telefone
function formatar_telefone($telefone) {
    if (preg_match('/^\d{10,11}$/', $telefone)) {
        $ddd = substr($telefone, 0, 2);
        $numero = substr($telefone, -8);
        if (strlen($telefone) == 11) {
            return "($ddd) 9$numero";
        } else {
            return "($ddd) $numero";
        }
    }
    return $telefone;
}
?>
