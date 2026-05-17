<?php
// config.php - Configuração do banco de dados
$db_file = 'igreja.db';

// Função para conectar ao banco
function conectar_banco() {
    global $db_file;
    try {
        $pdo = new PDO("sqlite:$db_file");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro ao conectar ao banco: " . $e->getMessage());
    }
}

// Função para formatar data
function formatar_data($data) {
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

// Função para verificar se o banco existe
function banco_existe() {
    global $db_file;
    return file_exists($db_file);
}

// Verificar se o banco existe e criar as tabelas se necessário
if (!banco_existe()) {
    // Criar o banco e tabelas
    $pdo = conectar_banco();
    
    $sql = "CREATE TABLE membros (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT,
        telefone TEXT,
        endereco TEXT,
        data_cadastro DATE DEFAULT CURRENT_DATE,
        status TEXT DEFAULT 'ativo',
        observacao TEXT,
        data_nascimento DATE,
        sexo TEXT,
        naturalidade TEXT,
        estado_civil TEXT,
        nome_conjuge TEXT,
        telefone_conjuge TEXT,
        batismo_aguas DATE,
        ministrio TEXT,
        funcao TEXT,
        data_filiacao DATE,
        situacao TEXT,
        data_saida DATE
    )";

    $stmt = $pdo->exec($sql);
    echo "Banco de dados criado com sucesso!";
}

?>