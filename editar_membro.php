<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
$pdo = conectar_banco();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $endereco = trim($_POST['endereco']);
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
        if ($situacao === 'Transferido' && empty($data_saida)) {
            throw new Exception("É obrigatório informar a data de saída para membros transferidos");
        }

        // Atualizar membro
        $sql = "UPDATE membros SET nome=?, email=?, telefone=?, endereco=?, data_nascimento=?, sexo=?, naturalidade=?,
                estado_civil=?, nome_conjuge=?, telefone_conjuge=?, batismo_aguas=?, ministrio=?, funcao=?,
                data_filiacao=?, situacao=?, data_saida=?, observacao=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome,
            $email,
            $telefone,
            $endereco,
            $data_nascimento,
            $sexo,
            $naturalidade,
            $estado_civil,
            $nome_conjuge,
            $telefone_conjuge,
            $batismo_aguas,
            $ministrio,
            $funcao,
            $data_filiacao,
            $situacao,
            $data_saida,
            $observacao,
            $id
        ]);

        // Remover filhos marcados para exclusão
        if (!empty($_POST['filhos_remover']) && is_array($_POST['filhos_remover'])) {
            foreach ($_POST['filhos_remover'] as $filho_id) {
                $pdo->prepare("DELETE FROM filhos WHERE id = ? AND membro_id = ?")->execute([$filho_id, $id]);
            }
        }

        // Atualizar filhos existentes
        if (!empty($_POST['filhos_existentes']) && is_array($_POST['filhos_existentes'])) {
            $stmt_upd = $pdo->prepare("UPDATE filhos SET nome=?, data_nascimento=?, sexo=? WHERE id=? AND membro_id=?");
            foreach ($_POST['filhos_existentes'] as $filho_id => $filho) {
                $nome_filho = trim($filho['nome'] ?? '');
                if (!empty($nome_filho)) {
                    $stmt_upd->execute([
                        $nome_filho,
                        trim($filho['data_nascimento'] ?? ''),
                        trim($filho['sexo'] ?? ''),
                        $filho_id,
                        $id
                    ]);
                }
            }
        }

        // Inserir novos filhos
        if (!empty($_POST['filhos_novos']) && is_array($_POST['filhos_novos'])) {
            $stmt_ins = $pdo->prepare("INSERT INTO filhos (membro_id, nome, data_nascimento, sexo) VALUES (?, ?, ?, ?)");
            foreach ($_POST['filhos_novos'] as $filho) {
                $nome_filho = trim($filho['nome'] ?? '');
                if (!empty($nome_filho)) {
                    $stmt_ins->execute([
                        $id,
                        $nome_filho,
                        trim($filho['data_nascimento'] ?? ''),
                        trim($filho['sexo'] ?? '')
                    ]);
                }
            }
        }

        $_SESSION['success'] = "Membro '$nome' atualizado com sucesso!";
        header('Location: listar_membros.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao atualizar: " . $e->getMessage();
        header('Location: editar_membro.php?id=' . $_POST['id']);
        exit();
    }
}

if (!isset($_GET['id'])) {
    header('Location: listar_membros.php');
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM membros WHERE id = ?");
$stmt->execute([$id]);
$membro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$membro) die("Membro não encontrado");

// Buscar filhos existentes
$stmt_filhos = $pdo->prepare("SELECT * FROM filhos WHERE membro_id = ? ORDER BY nome");
$stmt_filhos->execute([$id]);
$filhos = $stmt_filhos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <title>Editar Membro - Igreja</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            font-size: clamp(1.2rem, 5vw, 1.8rem);
        }

        h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        input:disabled,
        select:disabled {
            background-color: #f0f0f0;
            color: #aaa;
            cursor: not-allowed;
        }

        .btn {
            background-color: #3498db;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
            text-decoration: none;
            display: inline-block;
            margin: 4px;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
        }

        .btn-success {
            background-color: #27ae60;
        }

        .btn-success:hover {
            background-color: #229954;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 5px 12px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .col {
            flex: 1;
            min-width: 200px;
        }

        .filho-item {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            position: relative;
            padding-top: 40px;
        }

        .filho-item .btn-danger {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        #add-filho-btn {
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 16px;
            cursor: pointer;
            font-size: 15px;
            width: 100%;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .row {
                flex-direction: column;
                gap: 0;
            }

            .col {
                min-width: 100%;
            }

            .btn {
                width: 100%;
                text-align: center;
                margin: 4px 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
            <h1>✏️ Editar Membro</h1>
            <a href="listar_membros.php" class="btn btn-secondary">Voltar</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success'];
                                    unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error'];
                                unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="editar_membro.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $membro['id']; ?>">

            <!-- Dados Pessoais -->
            <div style="margin-bottom:20px;">
                <h4>👤 Dados Pessoais</h4>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Nome Completo *</label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($membro['nome']); ?>" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($membro['email']); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($membro['telefone']); ?>" maxlength="15">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($membro['data_nascimento']); ?>">
                            <label style="margin-top:5px; color:#2980b9; font-size:13px;">Idade</label>
                            <input type="text" id="idade_display" readonly placeholder="Calculado automaticamente" style="background:#eaf4fb; color:#2980b9; font-weight:bold; border:1px solid #aed6f1; padding:10px; width:100%;">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Sexo</label>
                            <select name="sexo">
                                <option value="">Selecione</option>
                                <option value="Masculino" <?php echo $membro['sexo'] == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                <option value="Feminino" <?php echo $membro['sexo'] == 'Feminino' ? 'selected' : ''; ?>>Feminino</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Naturalidade</label>
                            <input type="text" name="naturalidade" value="<?php echo htmlspecialchars($membro['naturalidade']); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div style="margin-bottom:20px;">
                <h4>🏠 Endereço</h4>
                <div class="form-group">
                    <label>Endereço Completo</label>
                    <input type="text" name="endereco" value="<?php echo htmlspecialchars($membro['endereco']); ?>">
                </div>
            </div>

            <!-- Dados Familiares -->
            <div style="margin-bottom:20px;">
                <h4>👨‍👩‍👧‍👦 Dados Familiares</h4>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Estado Civil</label>
                            <select id="estado_civil" name="estado_civil">
                                <option value="">Selecione</option>
                                <?php foreach (['Solteiro(a)', 'Casado(a)', 'Viúvo(a)', 'Divorciado(a)', 'União Estável'] as $ec): ?>
                                    <option value="<?php echo $ec; ?>" <?php echo $membro['estado_civil'] == $ec ? 'selected' : ''; ?>><?php echo $ec; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Nome do Cônjuge/Parceiro(a)</label>
                            <input type="text" id="nome_conjuge" name="nome_conjuge" value="<?php echo htmlspecialchars($membro['nome_conjuge']); ?>"
                                <?php echo in_array($membro['estado_civil'], ['Casado(a)', 'União Estável']) ? '' : 'disabled'; ?>>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Telefone do Cônjuge/Parceiro(a)</label>
                            <input type="tel" id="telefone_conjuge" name="telefone_conjuge" value="<?php echo htmlspecialchars($membro['telefone_conjuge']); ?>" maxlength="15"
                                <?php echo in_array($membro['estado_civil'], ['Casado(a)', 'União Estável']) ? '' : 'disabled'; ?>>
                        </div>
                    </div>
                </div>

                <!-- Filhos -->
                <div style="margin-top:20px;">
                    <h4>👶 Filhos</h4>
                    <div id="filhos-container">
                        <?php foreach ($filhos as $filho): ?>
                            <div class="filho-item" id="filho_ex_<?php echo $filho['id']; ?>">
                                <button type="button" class="btn-danger" onclick="marcarRemover(<?php echo $filho['id']; ?>)">✕ Remover</button>
                                <strong>Filho cadastrado</strong>
                                <input type="hidden" name="filhos_remover[]" id="remover_<?php echo $filho['id']; ?>" value="" disabled>
                                <div class="row" style="margin-top:10px;">
                                    <div class="col">
                                        <div class="form-group">
                                            <label>Nome</label>
                                            <input type="text" name="filhos_existentes[<?php echo $filho['id']; ?>][nome]" value="<?php echo htmlspecialchars($filho['nome']); ?>">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label>Data de Nascimento</label>
                                            <input type="date" name="filhos_existentes[<?php echo $filho['id']; ?>][data_nascimento]" value="<?php echo htmlspecialchars($filho['data_nascimento']); ?>" onchange="calcularIdadeFilho(this)">
                                            <label style="margin-top:5px; color:#2980b9; font-size:13px;">Idade</label>
                                            <input type="text" readonly placeholder="Calculado automaticamente" style="background:#eaf4fb; color:#2980b9; font-weight:bold; border:1px solid #aed6f1; padding:10px; width:100%;">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label>Sexo</label>
                                            <select name="filhos_existentes[<?php echo $filho['id']; ?>][sexo]">
                                                <option value="">Selecione</option>
                                                <option value="Masculino" <?php echo $filho['sexo'] == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                                <option value="Feminino" <?php echo $filho['sexo'] == 'Feminino' ? 'selected' : ''; ?>>Feminino</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-filho-btn">+ Adicionar Filho</button>
                </div>
            </div>

            <!-- Dados Espirituais -->
            <div style="margin-bottom:20px;">
                <h4>🙏 Dados Espirituais</h4>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Data do Batismo nas Águas</label>
                            <input type="date" name="batismo_aguas" value="<?php echo htmlspecialchars($membro['batismo_aguas']); ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Data de Filiação</label>
                            <input type="date" id="data_filiacao" name="data_filiacao" value="<?php echo htmlspecialchars($membro['data_filiacao']); ?>">
                            <label style="margin-top:5px; color:#1e8449; font-size:13px;">Tempo Filiado</label>
                            <input type="text" id="filiacao_display" readonly placeholder="Calculado automaticamente" style="background:#eafaf1; color:#1e8449; font-weight:bold; border:1px solid #a9dfbf; padding:10px; width:100%;">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Ministério</label>
                            <select name="ministrio">
                                <option value="">Nenhum</option>
                                <?php
                                $mins = ['Ação Social', 'Casais', 'Eventos', 'Homens', 'Infantil', 'Jovens e Adolescentes', 'Louvor', 'Mídia', 'Mulheres', 'Oração e Intercessão', 'Patrimonial'];
                                foreach ($mins as $m): ?>
                                    <option value="<?php echo $m; ?>" <?php echo $membro['ministrio'] == $m ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Função na Igreja</label>
                            <select name="funcao">
                                <option value="">Selecione</option>
                                <?php foreach (['Membro', 'Diácono', 'Diaconisa', 'Pastor', 'Pastora', 'Auxiliar', 'Ajudante', 'Presbítero', 'Presbítera', 'Levita (Músico)', 'Evangelista'] as $f): ?>
                                    <option value="<?php echo $f; ?>" <?php echo $membro['funcao'] == $f ? 'selected' : ''; ?>><?php echo $f; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Situação do Membro</label>
                            <select id="situacao" name="situacao">
                                <option value="Ativo" <?php echo $membro['situacao'] == 'Ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="Transferido" <?php echo $membro['situacao'] == 'Transferido' ? 'selected' : ''; ?>>Transferido</option>
                                <option value="Inativo" <?php echo $membro['situacao'] == 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Data de Saída (Transferência)</label>
                            <input type="date" id="data_saida" name="data_saida" value="<?php echo htmlspecialchars($membro['data_saida']); ?>" <?php echo $membro['situacao'] == 'Transferido' ? '' : 'disabled'; ?>>
                            <label style="margin-top:5px; color:#8e44ad; font-size:13px;">Tempo desde a Saída</label>
                            <input type="text" id="saida_display" readonly placeholder="Calculado automaticamente" style="background:#fdf2f8; color:#8e44ad; font-weight:bold; border:1px solid #d7bde2; padding:10px; width:100%;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div style="margin-bottom:20px;">
                <h4>📝 Observações</h4>
                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacao" rows="3"><?php echo htmlspecialchars($membro['observacao']); ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                <a href="listar_membros.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        // Máscara telefone
        function mascaraTelefone(input) {
            input.addEventListener('input', function() {
                let v = this.value.replace(/\D/g, '').substring(0, 11);
                if (v.length <= 10) v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                else v = v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                this.value = v;
            });
        }
        mascaraTelefone(document.getElementById('telefone'));
        mascaraTelefone(document.getElementById('telefone_conjuge'));

        // Calcular idade
        function calcularIdade(inputDate, displayId) {
            const nasc = new Date(inputDate.value);
            const hoje = new Date();
            let idade = hoje.getFullYear() - nasc.getFullYear();
            const m = hoje.getMonth() - nasc.getMonth();
            if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
            document.getElementById(displayId).value = inputDate.value ? `Idade: ${idade} anos` : '';
        }

        const dnInput = document.getElementById('data_nascimento');
        dnInput.addEventListener('change', () => calcularIdade(dnInput, 'idade_display'));
        if (dnInput.value) calcularIdade(dnInput, 'idade_display');

        // Calcular idade filho
        function calcularIdadeFilho(input) {
            const nasc = new Date(input.value);
            const hoje = new Date();
            let idade = hoje.getFullYear() - nasc.getFullYear();
            const m = hoje.getMonth() - nasc.getMonth();
            if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
            const wrapper = input.parentElement;
            const idadeInput = wrapper.querySelector('input[readonly]');
            if (idadeInput) idadeInput.value = input.value ? `${idade} anos` : '';
        }

        // Calcular idade filhos existentes ao carregar
        document.querySelectorAll('.filho-item input[type="date"]').forEach(input => {
            if (input.value) calcularIdadeFilho(input);
        });

        // Tempo filiado
        function calcularTempoFiliado() {
            const filiacaoVal = document.getElementById('data_filiacao').value;
            const saidaVal = document.getElementById('data_saida').value;
            const situacao = document.getElementById('situacao').value;
            if (!filiacaoVal) {
                document.getElementById('filiacao_display').value = '';
                return;
            }
            const fim = (situacao === 'Transferido' || situacao === 'Inativo') && saidaVal ? new Date(saidaVal) : new Date();
            const filiacao = new Date(filiacaoVal);
            let anos = fim.getFullYear() - filiacao.getFullYear();
            let meses = fim.getMonth() - filiacao.getMonth();
            if (meses < 0) {
                anos--;
                meses += 12;
            }
            let texto = '';
            if (anos > 0) texto += `${anos} ano${anos > 1 ? 's' : ''}`;
            if (meses > 0) texto += (texto ? ' e ' : '') + `${meses} mês${meses > 1 ? 'es' : ''}`;
            const prefixo = (situacao === 'Transferido' || situacao === 'Inativo') && saidaVal ? 'Filiado por: ' : 'Filiado há: ';
            document.getElementById('filiacao_display').value = texto ? prefixo + texto : 'Filiado este mês';
        }
        document.getElementById('data_filiacao').addEventListener('change', calcularTempoFiliado);
        document.getElementById('situacao').addEventListener('change', function() {
            const dataSaida = document.getElementById('data_saida');
            dataSaida.disabled = this.value !== 'Transferido';
            if (this.value !== 'Transferido') {
                dataSaida.value = '';
                document.getElementById('saida_display').value = '';
            }
            calcularTempoFiliado();
        });
        document.getElementById('data_saida').addEventListener('change', function() {
            if (!this.value) {
                document.getElementById('saida_display').value = '';
                calcularTempoFiliado();
                return;
            }
            const saida = new Date(this.value);
            const hoje = new Date();
            let anos = hoje.getFullYear() - saida.getFullYear();
            let meses = hoje.getMonth() - saida.getMonth();
            if (meses < 0) {
                anos--;
                meses += 12;
            }
            let texto = '';
            if (anos > 0) texto += `${anos} ano${anos > 1 ? 's' : ''}`;
            if (meses > 0) texto += (texto ? ' e ' : '') + `${meses} mês${meses > 1 ? 'es' : ''}`;
            document.getElementById('saida_display').value = texto ? `Há ${texto}` : 'Este mês';
            calcularTempoFiliado();
        });
        if (document.getElementById('data_filiacao').value) calcularTempoFiliado();

        // Estado civil - habilitar cônjuge
        document.getElementById('estado_civil').addEventListener('change', function() {
            const comConjuge = ['Casado(a)', 'União Estável'];
            const tem = comConjuge.includes(this.value);
            document.getElementById('nome_conjuge').disabled = !tem;
            document.getElementById('telefone_conjuge').disabled = !tem;
            if (!tem) {
                document.getElementById('nome_conjuge').value = '';
                document.getElementById('telefone_conjuge').value = '';
            }
        });

        // Limitar ano a 4 dígitos em todos os campos de data
        document.querySelectorAll('input[type="date"]').forEach(function(input) {
            input.addEventListener('input', function() {
                const parts = this.value.split('-');
                if (parts[0] && parts[0].length > 4) {
                    parts[0] = parts[0].substring(0, 4);
                    this.value = parts.join('-');
                }
            });
        });

        // Marcar filho para remoção
        function marcarRemover(id) {
            if (confirm('Remover este filho?')) {
                document.getElementById('remover_' + id).disabled = false;
                document.getElementById('remover_' + id).value = id;
                document.getElementById('filho_ex_' + id).style.opacity = '0.4';
                document.getElementById('filho_ex_' + id).style.pointerEvents = 'none';
            }
        }

        // Adicionar novo filho
        let novoCount = 0;
        document.getElementById('add-filho-btn').addEventListener('click', function() {
            novoCount++;
            const div = document.createElement('div');
            div.className = 'filho-item';
            div.id = `filho_novo_${novoCount}`;
            div.innerHTML = `
            <button type="button" class="btn-danger" onclick="document.getElementById('filho_novo_${novoCount}').remove()">✕ Remover</button>
            <strong>Novo Filho</strong>
            <div class="row" style="margin-top:10px;">
                <div class="col">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" name="filhos_novos[${novoCount}][nome]" placeholder="Nome completo">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>Data de Nascimento</label>
                        <input type="date" name="filhos_novos[${novoCount}][data_nascimento]" onchange="calcularIdadeFilho(this)" style="width:100%;">
                        <label style="margin-top:5px; color:#2980b9; font-size:13px;">Idade</label>
                        <input type="text" readonly placeholder="Calculado automaticamente" style="background:#eaf4fb; color:#2980b9; font-weight:bold; border:1px solid #aed6f1; padding:10px; width:100%;">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>Sexo</label>
                        <select name="filhos_novos[${novoCount}][sexo]">
                            <option value="">Selecione</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                        </select>
                    </div>
                </div>
            </div>`;
            document.getElementById('filhos-container').appendChild(div);
        });
    </script>

    <script>
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("sw.js");
        }
    </script>

</body>

</html>