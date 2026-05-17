<?php
// Configuração do banco de dados
$db_file = 'igreja.db';
$pdo = new PDO("sqlite:$db_file");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Iniciar sessão para mensagens
session_start();

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitizar dados
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $data_nascimento = trim($_POST['data_nascimento'] ?? '');
        $sexo = trim($_POST['sexo'] ?? '');
        $naturalidade = trim($_POST['naturalidade'] ?? '');
        $estado_civil = trim($_POST['estado_civil'] ?? '');
        $nome_conjuge = trim($_POST['nome_conjuge'] ?? '');
        $telefone_conjuge = trim($_POST['telefone_conjuge'] ?? '');
        $batismo_aguas = trim($_POST['batismo_aguas'] ?? '');
        $ministrio = trim($_POST['ministrio'] ?? '');
        $funcao = trim($_POST['funcao'] ?? '');
        $data_filiacao = trim($_POST['data_filiacao'] ?? '');
        $situacao = trim($_POST['situacao'] ?? 'Ativo');
        $data_saida = trim($_POST['data_saida'] ?? '');
        $observacao = trim($_POST['observacao'] ?? '');

        // Validação básica
        if (empty($nome)) {
            throw new Exception("O nome é obrigatório");
        }

        if (strlen($nome) < 3) {
            throw new Exception("O nome deve ter pelo menos 3 caracteres");
        }

        // Se situação for "Transferido" e data de saída não for informada
        if ($situacao === 'Transferido' && empty($data_saida)) {
            throw new Exception("É obrigatório informar a data de saída para membros transferidos");
        }

        // Verificar se e-mail já existe (opcional)
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM membros WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_POST['id'] ?? 0]);
            if ($stmt->fetch()) {
                throw new Exception("Este e-mail já está cadastrado");
            }
        }

        // Inserir no banco
        $sql = "INSERT INTO membros (
            nome, email, telefone, endereco, data_nascimento, sexo, naturalidade, 
            estado_civil, nome_conjuge, telefone_conjuge, batismo_aguas, 
            ministrio, funcao, data_filiacao, situacao, data_saida, observacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome, $email, $telefone, $endereco, $data_nascimento, $sexo, $naturalidade,
            $estado_civil, $nome_conjuge, $telefone_conjuge, $batismo_aguas,
            $ministrio, $funcao, $data_filiacao, $situacao, $data_saida, $observacao
        ]);

        $_SESSION['success'] = "Membro '$nome' cadastrado com sucesso!";
        
        // Redirecionar para a lista de membros ou para o formulário vazio
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao cadastrar: " . $e->getMessage();
        header('Location: index.php');
        exit();
    }
} else {
    // Se não for POST, redirecionar para o formulário
    header('Location: index.php');
    exit();
}
?>