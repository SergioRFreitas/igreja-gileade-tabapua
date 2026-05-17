<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $endereco = trim($_POST['endereco']);
        $status = trim($_POST['status']);
        $observacao = trim($_POST['observacao']);

        // Validar
        if (empty($nome)) {
            throw new Exception("O nome é obrigatório");
        }

        // Atualizar no banco
        $sql = "UPDATE membros SET nome = ?, email = ?, telefone = ?, endereco = ?, status = ?, observacao = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $telefone, $endereco, $status, $observacao, $id]);

        $_SESSION['success'] = "Membro '$nome' atualizado com sucesso!";
        header('Location: listar_membros.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao atualizar: " . $e->getMessage();
        header('Location: editar_membro.php?id=' . $_POST['id']);
        exit();
    }
}

// Buscar membro para edição
if (!isset($_GET['id'])) {
    header('Location: listar_membros.php');
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM membros WHERE id = ?");
$stmt->execute([$id]);
$membro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$membro) {
    die("Membro não encontrado");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Membro - Igreja</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        input[type="text"], input[type="email"], input[type="tel"], textarea, select { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; 
        }
        .btn { background-color: #3498db; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background-color: #2980b9; }
        .btn-secondary { background-color: #95a5a6; }
        .btn-secondary:hover { background-color: #7f8c8d; }
        .btn-success { background-color: #27ae60; }
        .btn-success:hover { background-color: #229954; }
        .success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Editar Membro</h1>
            <div>
                <a href="listar_membros.php" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="editar_membro.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $membro['id']; ?>">
            
            <!-- Dados Pessoais -->
            <div style="margin-bottom: 20px;">
                <h4 style="color: #2c3e50; margin-bottom: 15px;">👤 Dados Pessoais</h4>
                
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($membro['nome']); ?>" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($membro['email']); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($membro['telefone']); ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($membro['data_nascimento']); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="sexo">Sexo</label>
                            <select id="sexo" name="sexo">
                                <option value="">Selecione</option>
                                <option value="Masculino" <?php echo $membro['sexo'] == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                <option value="Feminino" <?php echo $membro['sexo'] == 'Feminino' ? 'selected' : ''; ?>>Feminino</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="naturalidade">Naturalidade</label>
                            <input type="text" id="naturalidade" name="naturalidade" value="<?php echo htmlspecialchars($membro['naturalidade']); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div style="margin-bottom: 20px;">
                <h4 style="color: #2c3e50; margin-bottom: 15px;">🏠 Endereço</h4>
                
                <div class="form-group">
                    <label for="endereco">Endereço Completo</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($membro['endereco']); ?>">
                </div>
            </div>

            <!-- Dados Familiares -->
            <div style="margin-bottom: 20px;">
                <h4 style="color: #2c3e50; margin-bottom: 15px;">👨‍👩‍👧‍👦 Dados Familiares</h4>
                
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="estado_civil">Estado Civil</label>
                            <select id="estado_civil" name="estado_civil">
                                <option value="">Selecione</option>
                                <option value="Solteiro(a)" <?php echo $membro['estado_civil'] == 'Solteiro(a)' ? 'selected' : ''; ?>>Solteiro(a)</option>
                                <option value="Casado(a)" <?php echo $membro['estado_civil'] == 'Casado(a)' ? 'selected' : ''; ?>>Casado(a)</option>
                                <option value="Viúvo(a)" <?php echo $membro['estado_civil'] == 'Viúvo(a)' ? 'selected' : ''; ?>>Viúvo(a)</option>
                                <option value="Divorciado(a)" <?php echo $membro['estado_civil'] == 'Divorciado(a)' ? 'selected' : ''; ?>>Divorciado(a)</option>
                                <option value="União Estável" <?php echo $membro['estado_civil'] == 'União Estável' ? 'selected' : ''; ?>>União Estável</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="nome_conjuge">Nome do Cônjuge/Parceiro(a)</label>
                            <input type="text" id="nome_conjuge" name="nome_conjuge" value="<?php echo htmlspecialchars($membro['nome_conjuge']); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="telefone_conjuge">Telefone do Cônjuge/Parceiro(a)</label>
                            <input type="tel" id="telefone_conjuge" name="telefone_conjuge" value="<?php echo htmlspecialchars($membro['telefone_conjuge']); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados Espirituais -->
            <div style="margin-bottom: 20px;">
                <h4 style="color: #2c3e50; margin-bottom: 15px;">🙏 Dados Espirituais</h4>
                
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="batismo_aguas">Batismo nas Águas</label>
                            <select id="batismo_aguas" name="batismo_aguas">
                                <option value="">Selecione</option>
                                <option value="Sim" <?php echo $membro['batismo_aguas'] == 'Sim' ? 'selected' : ''; ?>>Sim</option>
                                <option value="Não" <?php echo $membro['batismo_aguas'] == 'Não' ? 'selected' : ''; ?>>Não</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="data_filiacao">Data de Filiação</label>
                            <input type="date" id="data_filiacao" name="data_filiacao" value="<?php echo htmlspecialchars($membro['data_filiacao']); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="ministrio">Ministério</label>
                            <select id="ministrio" name="ministrio">
                                <option value="">Nenhum</option>
                                <?php
                                try {
                                    $stmt = $pdo->query("SELECT nome FROM ministerios WHERE ativo = 1 ORDER BY nome");
                                    while ($ministerio = $stmt->fetch()) {
                                        echo "<option value='" . htmlspecialchars($ministerio['nome']) . "' " . ($membro['ministrio'] == $ministerio['nome'] ? 'selected' : '') . ">" . htmlspecialchars($ministerio['nome']) . "</option>";
                                    }
                                } catch (Exception $e) {
                                    // Silenciar erro
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="funcao">Função na Igreja</label>
                            <select id="funcao" name="funcao">
                                <option value="">Selecione</option>
                                <option value="Membro" <?php echo $membro['funcao'] == 'Membro' ? 'selected' : ''; ?>>Membro</option>
                                <option value="Diácono" <?php echo $membro['funcao'] == 'Diácono' ? 'selected' : ''; ?>>Diácono</option>
                                <option value="Diaconisa" <?php echo $membro['funcao'] == 'Diaconisa' ? 'selected' : ''; ?>>Diaconisa</option>
                                <option value="Pastor" <?php echo $membro['funcao'] == 'Pastor' ? 'selected' : ''; ?>>Pastor</option>
                                <option value="Pastora" <?php echo $membro['funcao'] == 'Pastora' ? 'selected' : ''; ?>>Pastora</option>
                                <option value="Auxiliar" <?php echo $membro['funcao'] == 'Auxiliar' ? 'selected' : ''; ?>>Auxiliar</option>
                                <option value="Ajudante" <?php echo $membro['funcao'] == 'Ajudante' ? 'selected' : ''; ?>>Ajudante</option>
                                <option value="Presbítero" <?php echo $membro['funcao'] == 'Presbítero' ? 'selected' : ''; ?>>Presbítero</option>
                                <option value="Presbítera" <?php echo $membro['funcao'] == 'Presbítera' ? 'selected' : ''; ?>>Presbítera</option>
                                <option value="Levista" <?php echo $membro['funcao'] == 'Levista' ? 'selected' : ''; ?>>Levista</option>
                                <option value="Evangelista" <?php echo $membro['funcao'] == 'Evangelista' ? 'selected' : ''; ?>>Evangelista</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="situacao">Situação do Membro</label>
                            <select id="situacao" name="situacao">
                                <option value="Ativo" <?php echo $membro['situacao'] == 'Ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="Transferido" <?php echo $membro['situacao'] == 'Transferido' ? 'selected' : ''; ?>>Transferido</option>
                                <option value="Inativo" <?php echo $membro['situacao'] == 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="data_saida">Data de Saída (Transferência)</label>
                            <input type="date" id="data_saida" name="data_saida" value="<?php echo htmlspecialchars($membro['data_saida']); ?>" <?php echo $membro['situacao'] == 'Transferido' ? '' : 'disabled'; ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div style="margin-bottom: 20px;">
                <h4 style="color: #2c3e50; margin-bottom: 15px;">📝 Observações</h4>
                
                <div class="form-group">
                    <label for="observacao">Observações</label>
                    <textarea id="observacao" name="observacao" rows="3"><?php echo htmlspecialchars($membro['observacao']); ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                <a href="listar_membros.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>

        <script>
            // Habilitar campo de data de saída quando situação for "Transferido"
            document.getElementById('situacao').addEventListener('change', function() {
                const dataSaida = document.getElementById('data_saida');
                if (this.value === 'Transferido') {
                    dataSaida.disabled = false;
                } else {
                    dataSaida.disabled = true;
                    dataSaida.value = '';
                }
            });
        </script>
    </div>
</body>
</html>