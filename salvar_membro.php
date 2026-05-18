<?php
require_once 'config.php';
$pdo = conectar_banco();
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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

        if (empty($nome)) throw new Exception("O nome é obrigatório");
        if (strlen($nome) < 3) throw new Exception("O nome deve ter pelo menos 3 caracteres");
        if ($situacao === 'Transferido' && empty($data_saida)) {
            throw new Exception("É obrigatório informar a data de saída para membros transferidos");
        }
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM membros WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_POST['id'] ?? 0]);
            if ($stmt->fetch()) throw new Exception("Este e-mail já está cadastrado");
        }

        // Inserir membro
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

        $membro_id = $pdo->lastInsertId();

        // Salvar filhos se existirem
        if (!empty($_POST['filhos']) && is_array($_POST['filhos'])) {
            $stmt_filho = $pdo->prepare("INSERT INTO filhos (membro_id, nome, data_nascimento, sexo) VALUES (?, ?, ?, ?)");
            foreach ($_POST['filhos'] as $filho) {
                $nome_filho = trim($filho['nome'] ?? '');
                if (!empty($nome_filho)) {
                    $stmt_filho->execute([
                        $membro_id,
                        $nome_filho,
                        trim($filho['data_nascimento'] ?? ''),
                        trim($filho['sexo'] ?? '')
                    ]);
                }
            }
        }

        $_SESSION['success'] = "Membro '$nome' cadastrado com sucesso!";
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao cadastrar: " . $e->getMessage();
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>
