<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Membros - Igreja</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        input[type="text"], input[type="email"], input[type="tel"], textarea, select { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; 
        }
        .btn { background-color: #3498db; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background-color: #2980b9; }
        .btn-secondary { background-color: #95a5a6; }
        .btn-secondary:hover { background-color: #7f8c8d; }
        .row { display: flex; gap: 20px; }
        .col { flex: 1; }
        .success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🙏 Cadastro de Membros da Igreja</h1>
        
        <?php 
// Página de cadastro - sem restrição
?>

<?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="salvar_membro.php" method="POST" id="cadastroForm">
            <!-- Dados Pessoais -->
            <div style="margin-bottom: 30px;">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">👤 Dados Pessoais</h3>
                
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" id="nome" name="nome" required placeholder="Digite seu nome completo">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="seu@email.com">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(11) 99999-9999">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="sexo">Sexo</label>
                            <select id="sexo" name="sexo">
                                <option value="">Selecione</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="naturalidade">Naturalidade</label>
                            <input type="text" id="naturalidade" name="naturalidade" placeholder="Cidade/Estado">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div style="margin-bottom: 30px;">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">🏠 Endereço</h3>
                
                <div class="form-group">
                    <label for="endereco">Endereço Completo</label>
                    <input type="text" id="endereco" name="endereco" placeholder="Rua, número, bairro, cidade, estado">
                </div>
            </div>

            <!-- Dados Familiares -->
            <div style="margin-bottom: 30px;">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">👨‍👩‍👧‍👦 Dados Familiares</h3>
                
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="estado_civil">Estado Civil</label>
                            <select id="estado_civil" name="estado_civil">
                                <option value="">Selecione</option>
                                <option value="Solteiro(a)">Solteiro(a)</option>
                                <option value="Casado(a)">Casado(a)</option>
                                <option value="Viúvo(a)">Viúvo(a)</option>
                                <option value="Divorciado(a)">Divorciado(a)</option>
                                <option value="União Estável">União Estável</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="nome_conjuge">Nome do Cônjuge/Parceiro(a)</label>
                            <input type="text" id="nome_conjuge" name="nome_conjuge" placeholder="Nome completo">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="telefone_conjuge">Telefone do Cônjuge/Parceiro(a)</label>
                            <input type="tel" id="telefone_conjuge" name="telefone_conjuge" placeholder="(11) 99999-9999">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados Espirituais -->
            <div style="margin-bottom: 30px;">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">🙏 Dados Espirituais</h3>
                
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="batismo_aguas">Batismo nas Águas</label>
                            <select id="batismo_aguas" name="batismo_aguas">
                                <option value="">Selecione</option>
                                <option value="Sim">Sim</option>
                                <option value="Não">Não</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="data_filiacao">Data de Filiação</label>
                            <input type="date" id="data_filiacao" name="data_filiacao">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="ministrio">Ministério</label>
                            <select id="ministrio" name="ministrio">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="funcao">Função na Igreja</label>
                            <select id="funcao" name="funcao">
                                <option value="">Selecione</option>
                                <option value="Membro">Membro</option>
                                <option value="Diácono">Diácono</option>
                                <option value="Diaconisa">Diaconisa</option>
                                <option value="Pastor">Pastor</option>
                                <option value="Pastora">Pastora</option>
                                <option value="Auxiliar">Auxiliar</option>
                                <option value="Ajudante">Ajudante</option>
                                <option value="Presbítero">Presbítero</option>
                                <option value="Presbítera">Presbítera</option>
                                <option value="Levita">Levista (Músico)</option>
                                <option value="Evangelista">Evangelista</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="situacao">Situação do Membro</label>
                            <select id="situacao" name="situacao">
                                <option value="Ativo">Ativo</option>
                                <option value="Transferido">Transferido</option>
                                <option value="Inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="data_saida">Data de Saída (Transferência)</label>
                            <input type="date" id="data_saida" name="data_saida" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div style="margin-bottom: 30px;">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">📝 Observações</h3>
                
                <div class="form-group">
                    <label for="observacao">Observações</label>
                    <textarea id="observacao" name="observacao" rows="3" placeholder="Informações adicionais..."></textarea>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Cadastrar Membro</button>
                <a href="listar_membros.php" class="btn btn-secondary">Listar Membros</a>
                <a href="logout.php" class="btn btn-danger">Sair</a>
            </div>
        </form>

        <script>
            // Carregar ministérios do banco de dados
            fetch('get_ministerios.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('ministrio');
                    data.forEach(ministerio => {
                        const option = document.createElement('option');
                        option.value = ministerio.nome;
                        option.textContent = ministerio.nome;
                        select.appendChild(option);
                    });
                });

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

            document.getElementById('cadastroForm').addEventListener('submit', function() {
                const nome = document.getElementById('nome').value.trim();
                if (nome.length < 3) {
                    alert('O nome deve ter pelo menos 3 caracteres');
                    return false;
                }
                return true;
            });
        </script>
</body>
</html>