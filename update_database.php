<?php
// update_database.php - Script para atualizar o banco de dados com novos campos

require_once 'config.php';

try {
    $pdo = conectar_banco();
    
    // SQLite não permite ADD COLUMN múltipos, precisamos fazer um por um
    $columns = [
        'data_nascimento DATE',
        'sexo TEXT',
        'naturalidade TEXT',
        'estado_civil TEXT',
        'nome_conjuge TEXT',
        'telefone_conjuge TEXT',
        'batismo_aguas DATE',
        'ministrio TEXT',
        'funcao TEXT',
        'data_filiacao DATE',
        'situacao TEXT',
        'data_saida DATE'
    ];
    
    foreach ($columns as $column) {
        try {
            $sql = "ALTER TABLE membros ADD COLUMN $column";
            $pdo->exec($sql);
            echo "Campo $column adicionado com sucesso!\n";
        } catch (PDOException $e) {
            // Ignora erro se a coluna já existe
            if ($e->getCode() != 1) {
                echo "Erro ao adicionar campo $column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Criar tabela de ministérios se não existir
    $sql = "CREATE TABLE IF NOT EXISTS ministerios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT UNIQUE NOT NULL,
        descricao TEXT,
        ativo BOOLEAN DEFAULT 1
    )";
    
    $pdo->exec($sql);
    
    // Inserir ministérios padrão
    $ministerios = [
        'Louvor e Adoração' => 'Ministério de música e louvor',
        'Mídia e Comunicação' => 'Produção de conteúdo digital',
        'Infantil' => 'Atendimento para crianças',
        'Juvenil' => 'Atendimento para jovens',
        'Evangelismo' => 'Ministério de evangelização',
        'Células' => 'Liderança de células',
        'Finanças' => 'Administração financeira',
        'Obras e Manutenção' => 'Manutenção do templo',
        'Hospitalidade' => 'Recepção e acolhida',
        'Intercessão' => 'Oração e intercessão'
    ];
    
    foreach ($ministerios as $nome => $descricao) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ministerios (nome, descricao) VALUES (?, ?)");
            $stmt->execute([$nome, $descricao]);
        } catch (PDOException $e) {
            // Ignora erro de chave duplicada
            if ($e->getCode() != 23000) {
                echo "Erro ao inserir ministério $nome: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Banco de dados atualizado com sucesso!\n";
    echo "Novos campos adicionados:\n";
    echo "- data_nascimento\n";
    echo "- sexo\n";
    echo "- naturalidade\n";
    echo "- estado_civil\n";
    echo "- nome_conjuge\n";
    echo "- telefone_conjuge\n";
    echo "- batismo_aguas\n";
    echo "- ministrio\n";
    echo "- funcao\n";
    echo "- data_filiacao\n";
    echo "- situacao\n";
    echo "- data_saida\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>