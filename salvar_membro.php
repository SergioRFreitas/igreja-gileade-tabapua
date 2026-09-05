<?php
require_once 'config.php';
$pdo = conectar_banco();
session_start();

// Valida CPF de verdade (calcula os 2 dígitos verificadores), não só a quantidade de números.
// Recebe apenas os 11 dígitos (sem pontos/traço).
function cpfValido(string $cpf): bool {
    if (strlen($cpf) !== 11) return false;
    // Rejeita sequências repetidas (111.111.111-11, 000.000.000-00, etc.), que passam
    // no cálculo mas nunca são CPFs reais.
    if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;

    for ($t = 9; $t <= 10; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int)$cpf[$i] * (($t + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int)$cpf[$t] !== $digito) return false;
    }
    return true;
}

// Padroniza nomes próprios: cada palavra com inicial maiúscula, exceto
// preposições comuns (de, da, do, dos, das, e), que ficam em minúsculo.
function formatarNomeProprio(string $nome): string {
    $nome = mb_strtolower(trim($nome), 'UTF-8');
    $nome = mb_convert_case($nome, MB_CASE_TITLE, 'UTF-8');
    $preposicoes = ['De', 'Da', 'Do', 'Dos', 'Das', 'E'];
    foreach ($preposicoes as $prep) {
        $nome = preg_replace('/\b' . $prep . '\b/u', mb_strtolower($prep, 'UTF-8'), $nome);
    }
    // Garante que a primeira palavra do nome nunca fique em minúsculo
    // (evita "da Silva" sozinho, caso alguém comece o nome com uma preposição por engano)
    return mb_convert_case(mb_substr($nome, 0, 1, 'UTF-8'), MB_CASE_UPPER, 'UTF-8') . mb_substr($nome, 1, null, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = formatarNomeProprio(trim($_POST['nome']));
        $email = trim($_POST['email'] ?? '');
        $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $data_nascimento = trim($_POST['data_nascimento'] ?? '');
        $sexo = trim($_POST['sexo'] ?? '');
        $naturalidade = trim($_POST['naturalidade'] ?? '');
        $estado_civil = trim($_POST['estado_civil'] ?? '');
        $nome_conjuge = !empty($_POST['nome_conjuge']) ? formatarNomeProprio(trim($_POST['nome_conjuge'])) : '';
        $telefone_conjuge = trim($_POST['telefone_conjuge'] ?? '');
        $batismo_aguas = trim($_POST['batismo_aguas'] ?? '');
        $ministrio = trim($_POST['ministrio'] ?? '');
        $funcao = trim($_POST['funcao'] ?? '');
        $data_filiacao = trim($_POST['data_filiacao'] ?? '');
        $situacao = trim($_POST['situacao'] ?? 'Ativo');
        $data_saida = trim($_POST['data_saida'] ?? '');
        $observacao = trim($_POST['observacao'] ?? '');
        $data_cadastro = date('Y-m-d');

        if (empty($nome)) throw new Exception("O nome é obrigatório");
        if (strlen($nome) < 3) throw new Exception("O nome deve ter pelo menos 3 caracteres");
        if (!cpfValido($cpf)) throw new Exception("O CPF informado não é válido");
        if ($situacao === 'Transferido' && empty($data_saida)) {
            throw new Exception("É obrigatório informar a data de saída para membros transferidos");
        }
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM membros WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_POST['id'] ?? 0]);
            if ($stmt->fetch()) throw new Exception("Este e-mail já está cadastrado");
        }
        $stmt = $pdo->prepare("SELECT id FROM membros WHERE cpf = ? AND id != ?");
        $stmt->execute([$cpf, $_POST['id'] ?? 0]);
        if ($stmt->fetch()) throw new Exception("Este CPF já está cadastrado");

        // Inserir membro
        $sql = "INSERT INTO membros (
            nome, email, cpf, telefone, endereco, data_nascimento, sexo, naturalidade, 
            estado_civil, nome_conjuge, telefone_conjuge, batismo_aguas, 
            ministrio, funcao, data_filiacao, situacao, data_saida, observacao, data_cadastro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome, $email, $cpf, $telefone, $endereco, $data_nascimento, $sexo, $naturalidade,
            $estado_civil, $nome_conjuge, $telefone_conjuge, $batismo_aguas,
            $ministrio, $funcao, $data_filiacao, $situacao, $data_saida, $observacao, $data_cadastro
        ]);

        $membro_id = $pdo->lastInsertId();

        // Salvar filhos se existirem
        if (!empty($_POST['filhos']) && is_array($_POST['filhos'])) {
            $stmt_filho = $pdo->prepare("INSERT INTO filhos (membro_id, nome, data_nascimento, sexo) VALUES (?, ?, ?, ?)");
            foreach ($_POST['filhos'] as $filho) {
                $nome_filho = trim($filho['nome'] ?? '');
                if (!empty($nome_filho)) {
                    $nome_filho = formatarNomeProprio($nome_filho);
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