<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <title>Cadastro de Membros - Igreja</title>
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

        h3 {
            font-size: clamp(1rem, 4vw, 1.2rem);
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
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px 5px 5px 0;
            display: inline-block;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
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

        .filhos-item {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            position: relative;
            padding-top: 40px;
        }

        .filhos-item .btn-remove {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 4px 10px;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 13px;
        }

        #add-filho-btn {
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 16px;
            cursor: pointer;
            margin-top: 5px;
            font-size: 15px;
            width: 100%;
        }

        #add-filho-btn:hover {
            background-color: #219a52;
        }

        @media (max-width: 600px) {
            .container {
                padding: 15px;
                border-radius: 5px;
            }

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
                margin: 5px 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🙏 Cadastro de Membros da Igreja</h1>

        <?php
        session_start();
        if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success'];
                                    unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error'];
                                unset($_SESSION['error']); ?></div>
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
                            <input type="tel" id="telefone" name="telefone" placeholder="(11) 99999-9999" maxlength="15">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" style="width:100%;">
                            <label for="idade_display" style="margin-top:8px;">Idade</label>
                            <input type="text" id="idade_display" readonly placeholder="Calculado automaticamente" style="width:100%; background:#eaf4fb; color:#2980b9; font-weight:bold; border:1px solid #aed6f1;">
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
                            <input type="text" id="nome_conjuge" name="nome_conjuge" placeholder="Nome completo" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="telefone_conjuge">Telefone do Cônjuge/Parceiro(a)</label>
                            <input type="tel" id="telefone_conjuge" name="telefone_conjuge" placeholder="(11) 99999-9999" maxlength="15" disabled>
                        </div>
                    </div>
                </div>

                <!-- Filhos dinâmicos -->
                <div style="margin-top: 20px;">
                    <h4 style="color: #2c3e50; margin-bottom: 10px;">👶 Filhos</h4>
                    <div id="filhos-container"></div>
                    <button type="button" id="add-filho-btn">+ Adicionar Filho</button>
                </div>
            </div>

            <!-- Dados Espirituais -->
            <div style="margin-bottom: 30px;">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">🙏 Dados Espirituais</h3>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="batismo_aguas">Data do Batismo nas Águas</label>
                            <input type="date" id="batismo_aguas" name="batismo_aguas" style="width:100%;">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="data_filiacao">Data de Filiação</label>
                            <input type="date" id="data_filiacao" name="data_filiacao" style="width:100%;">
                            <label for="filiacao_display" style="margin-top:8px;">Tempo Filiado</label>
                            <input type="text" id="filiacao_display" readonly placeholder="Calculado automaticamente" style="width:100%; background:#eafaf1; color:#1e8449; font-weight:bold; border:1px solid #a9dfbf;">
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
                                <option value="Levita">Levita (Músico)</option>
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
                            <input type="date" id="data_saida" name="data_saida" disabled style="width:100%;">
                            <label for="saida_display" style="margin-top:8px;">Tempo Transferido</label>
                            <input type="text" id="saida_display" readonly placeholder="Calculado automaticamente" style="width:100%; background:#fdf2f8; color:#8e44ad; font-weight:bold; border:1px solid #d7bde2;">
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
            </div>
        </form>

        <script>
            // Máscara de telefone
            function mascaraTelefone(input) {
                input.addEventListener('input', function() {
                    let v = this.value.replace(/\D/g, '').substring(0, 11);
                    if (v.length <= 10) {
                        v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                    } else {
                        v = v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                    }
                    this.value = v;
                });
            }
            mascaraTelefone(document.getElementById('telefone'));
            mascaraTelefone(document.getElementById('telefone_conjuge'));

            // Calcular idade
            document.getElementById('data_nascimento').addEventListener('change', function() {
                const nascimento = new Date(this.value);
                const hoje = new Date();
                let idade = hoje.getFullYear() - nascimento.getFullYear();
                const m = hoje.getMonth() - nascimento.getMonth();
                if (m < 0 || (m === 0 && hoje.getDate() < nascimento.getDate())) idade--;
                document.getElementById('idade_display').value = this.value ? `${idade} anos` : '';
            });

            // Tempo de filiado
            function calcularTempoFiliado() {
                const filiacaoVal = document.getElementById('data_filiacao').value;
                const saidaVal = document.getElementById('data_saida').value;
                const situacao = document.getElementById('situacao').value;
                if (!filiacaoVal) {
                    document.getElementById('filiacao_display').value = '';
                    return;
                }
                const filiacao = new Date(filiacaoVal);
                const fim = (situacao === 'Transferido' || situacao === 'Inativo') && saidaVal ?
                    new Date(saidaVal) : new Date();
                let anos = fim.getFullYear() - filiacao.getFullYear();
                let meses = fim.getMonth() - filiacao.getMonth();
                if (meses < 0) {
                    anos--;
                    meses += 12;
                }
                let texto = '';
                if (anos > 0) texto += `${anos} ano${anos > 1 ? 's' : ''}`;
                if (meses > 0) texto += (texto ? ' e ' : '') + `${meses} mês${meses > 1 ? 'es' : ''}`;
                const prefixo = (situacao === 'Transferido' || situacao === 'Inativo') && saidaVal ?
                    'Filiado por: ' : 'Filiado há: ';
                document.getElementById('filiacao_display').value = texto ? prefixo + texto : 'Filiado este mês';
            }
            document.getElementById('data_filiacao').addEventListener('change', calcularTempoFiliado);

            // Habilitar/desabilitar campos de cônjuge
            document.getElementById('estado_civil').addEventListener('change', function() {
                const comConjuge = ['Casado(a)', 'União Estável'];
                const temConjuge = comConjuge.includes(this.value);
                const nomeConj = document.getElementById('nome_conjuge');
                const telConj = document.getElementById('telefone_conjuge');
                nomeConj.disabled = !temConjuge;
                telConj.disabled = !temConjuge;
                if (!temConjuge) {
                    nomeConj.value = '';
                    telConj.value = '';
                }
            });

            // Filhos dinâmicos
            let filhoCount = 0;
            document.getElementById('add-filho-btn').addEventListener('click', function() {
                filhoCount++;
                const div = document.createElement('div');
                div.className = 'filhos-item';
                div.id = `filho_${filhoCount}`;
                div.innerHTML = `
                    <button type="button" class="btn-remove" onclick="removerFilho(${filhoCount})">✕ Remover</button>
                    <strong>Filho ${filhoCount}</strong>
                    <div class="row" style="margin-top:10px;">
                        <div class="col">
                            <div class="form-group">
                                <label>Nome do Filho</label>
                                <input type="text" name="filhos[${filhoCount}][nome]" placeholder="Nome completo">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label>Data de Nascimento</label>
                                <input type="date" name="filhos[${filhoCount}][data_nascimento]" onchange="calcularIdadeFilho(this)" style="width:100%;">
                                <label style="margin-top:8px;">Idade</label>
                                <input type="text" readonly placeholder="Calculado automaticamente" style="width:100%; background:#eaf4fb; color:#2980b9; font-weight:bold; border:1px solid #aed6f1;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label>Sexo</label>
                                <select name="filhos[${filhoCount}][sexo]">
                                    <option value="">Selecione</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Feminino">Feminino</option>
                                </select>
                            </div>
                        </div>
                    </div>`;
                document.getElementById('filhos-container').appendChild(div);
            });

            function removerFilho(id) {
                document.getElementById(`filho_${id}`).remove();
            }

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

            // Habilitar data de saída quando Transferido
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

            // Carregar ministérios
            fetch('get_ministerios.php')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('ministrio');
                    data.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.nome;
                        opt.textContent = m.nome;
                        sel.appendChild(opt);
                    });
                });

            // Limitar ano a 4 dígitos em todos os campos de data
            function limitarAno(input) {
                const parts = input.value.split('-');
                if (parts[0] && parts[0].length > 4) {
                    parts[0] = parts[0].substring(0, 4);
                    input.value = parts.join('-');
                }
            }
            document.querySelectorAll('input[type="date"]').forEach(function(input) {
                input.addEventListener('input', function() {
                    limitarAno(this);
                });
                input.addEventListener('change', function() {
                    limitarAno(this);
                });
                input.addEventListener('blur', function() {
                    limitarAno(this);
                });
            });

            // Validação do formulário
            document.getElementById('cadastroForm').addEventListener('submit', function(e) {
                const nome = document.getElementById('nome').value.trim();
                if (nome.length < 3) {
                    alert('O nome deve ter pelo menos 3 caracteres');
                    e.preventDefault();
                }
            });
        </script>
    </div>

    <script>
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("sw.js");
        }
    </script>

</body>

</html>