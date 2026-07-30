<?php
// =====================================================================
// adm.STG.php - Painel Administrativo
// =====================================================================

require_once '../config.php';

// Verificar autenticação e permissão
$session = Session::getInstance();
$session->requireAuth();
$session->requireProfile(['admin']);

$userName = $session->getUserName();
$userEmail = $session->getUserEmail();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFSertãoPE - SGE (Administração)</title>
    <link rel="stylesheet" href="../adm.STG.css">
    <style>
        /* Força o menu a iniciar escondido e com transição suave */
        .sidebar {
            left: -250px;
            transition: all 0.3s ease;
        }
        .content {
            margin-left: 0;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>

    <button id="btn-toggle-menu" class="btn-menu-trigger">☰</button>

    <nav class="sidebar" id="sidebar">
        <div class="logo"><h2>SGE Estágios</h2></div>
        <ul class="nav-links">
            <li class="section-title">ADMINISTRAÇÃO</li>
            <li onclick="navegar('vagas')" style="cursor:pointer">Gerenciar Vagas de Estágio</li>
            <li onclick="navegar('candidaturas')" style="cursor:pointer">Aprovar Candidaturas</li>
            <li onclick="navegar('validacao')" style="cursor:pointer">Validar Novos Estágios</li>
            <li class="section-title">CONTA</li>
            <li onclick="window.location.href='../api/logout.php'" style="cursor:pointer; color: #e74c3c;">Sair</li>
        </ul>
    </nav>

    <main class="content" id="main-content">
        <header class="top-bar" style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
            <div style="display: flex; align-items: center;">
                <img src="../img/if.png" alt="logo" style="margin-right: 15px;" onerror="this.style.display='none';">
                <div>
                    <h1>IFSertãoPE - SGE (Painel Admin)</h1>
                    <small style="color: #666;">Logado como: <strong><?php echo htmlspecialchars($userName); ?></strong> (<?php echo htmlspecialchars($userEmail); ?>)</small>
                </div>
            </div>
        </header>
        
        <!-- Seção: Gerenciar Vagas -->
        <section id="view-vagas" class="view-section active">
            <section class="stats-container">
                <div class="card"><h3>Vagas Abertas</h3><p id="count-vagas-abertas">0</p></div>
                <div class="card"><h3>Vagas Informática</h3><p id="count-vagas-info">0</p></div>
                <div class="card"><h3>Vagas Agro</h3><p id="count-vagas-agro">0</p></div>
                <div class="card"><h3>Total Vagas</h3><p id="count-vagas-total">0</p></div>
            </section>

            <section class="table-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2>Vagas de Estágio Disponíveis</h2>
                    <button onclick="mostrarFormularioVaga()" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">+ Nova Vaga</button>
                </div>

                <!-- Formulário para criar/editar vaga -->
                <div id="form-vaga" style="display: none; background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 id="form-vaga-titulo">Nova Vaga de Estágio</h3>
                    <form id="vaga-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Título da Vaga *</label>
                            <input type="text" id="vaga-titulo" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Área *</label>
                            <select id="vaga-area" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">Selecione...</option>
                                <option value="informatica">Informática</option>
                                <option value="agro">Agro</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Empresa *</label>
                            <select id="vaga-empresa" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Carga Horária (horas)</label>
                            <input type="number" id="vaga-carga" value="400" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Remuneração (R$)</label>
                            <input type="number" id="vaga-remuneracao" step="0.01" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Vagas Disponíveis</label>
                            <input type="number" id="vaga-quantidade" value="1" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Descrição *</label>
                            <textarea id="vaga-descricao" required rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Requisitos</label>
                            <textarea id="vaga-requisitos" rows="2" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Benefícios</label>
                            <textarea id="vaga-beneficios" rows="2" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Data de Encerramento</label>
                            <input type="date" id="vaga-encerramento" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div style="grid-column: 1 / -1; display: flex; gap: 10px; margin-top: 10px;">
                            <button type="submit" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">Salvar Vaga</button>
                            <button type="button" onclick="ocultarFormularioVaga()" style="background: #666; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Cancelar</button>
                        </div>
                    </form>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Área</th>
                            <th>Empresa</th>
                            <th>Carga Horária</th>
                            <th>Remuneração</th>
                            <th>Vagas</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-vagas"></tbody>
                </table>
            </section>
        </section>

        <!-- Seção: Aprovar Candidaturas -->
        <section id="view-candidaturas" class="view-section">
            <section class="stats-container">
                <div class="card"><h3>Candidaturas Pendentes</h3><p id="count-candidaturas-pendentes">0</p></div>
                <div class="card"><h3>Aprovadas Hoje</h3><p id="count-candidaturas-aprovadas">0</p></div>
                <div class="card"><h3>Rejeitadas Hoje</h3><p id="count-candidaturas-rejeitadas">0</p></div>
                <div class="card"><h3>Total Candidaturas</h3><p id="count-candidaturas-total">0</p></div>
            </section>

            <section class="table-section">
                <h2>Candidaturas Pendentes de Aprovação</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Vaga</th>
                            <th>Área</th>
                            <th>Empresa</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-candidaturas"></tbody>
                </table>
            </section>
        </section>

        <!-- Seção: Validação de Estágios -->
        <section id="view-validacao" class="view-section">
            <section class="stats-container">
                <div class="card border-blue"><h3>Abertura</h3><p id="count-abertura">0 Pendentes</p></div>
                <div class="card border-yellow"><h3>Em Andamento</h3><p id="count-andamento">0 Aguardando</p></div>
                <div class="card border-green"><h3>Concluídos</h3><p id="count-concluido">0 Finalizados</p></div>
                <div class="card border-red"><h3>Total</h3><p id="count-total">0 Registros</p></div>
            </section>

            <section class="table-section">
                <h2>Validação de Novos Estágios</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Curso</th>
                            <th>Empresa</th>
                            <th>Data Solicitação</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-validacao"></tbody>
                </table>
            </section>

            <section class="table-section">
                <h2>Fluxo de Estágios Global</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Estagiário / Tipo / Orientador</th>
                            <th>Empresa</th>
                            <th>Status do Fluxo</th>
                            <th>Responsável Atual</th>
                            <th>Ação Próxima</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-estagios"></tbody>
                </table>
            </section>
        </section>
    </main>

    <script>
        const estiloBotoes = "width: 100px; height: 35px; border-radius: 4px; border: none; cursor: pointer; font-weight: bold; transition: 0.3s; margin-right: 5px;";
        const tabela = document.getElementById('tabela-estagios');
        const sb = document.getElementById('sidebar');
        const ct = document.getElementById('main-content');

        // Lógica do Menu Hover (Passar o mouse)
        document.getElementById('btn-toggle-menu').addEventListener('mouseenter', () => { sb.style.left = "0"; ct.style.marginLeft = "250px"; });
        sb.addEventListener('mouseleave', () => { sb.style.left = "-250px"; ct.style.marginLeft = "0"; });

        // --- NAVEGAÇÃO ENTRE SEÇÕES ---
        function navegar(secao) {
            document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
            document.getElementById('view-' + secao).classList.add('active');
            
            if (secao === 'vagas') {
                carregarVagas();
                carregarEmpresas();
            } else if (secao === 'candidaturas') {
                carregarCandidaturas();
            } else if (secao === 'validacao') {
                carregarEstagiosValidacao();
            }
        }

        // --- FUNÇÕES PARA VAGAS ---
        async function carregarVagas() {
            try {
                const response = await fetch('../api/vagas.php');
                const dados = await response.json();
                
                if (dados.sucesso) {
                    const tabela = document.getElementById('tabela-vagas');
                    tabela.innerHTML = '';
                    
                    let countAbertas = 0;
                    let countInfo = 0;
                    let countAgro = 0;
                    
                    dados.vagas.forEach(vaga => {
                        if (vaga.status === 'aberta') countAbertas++;
                        if (vaga.area === 'informatica') countInfo++;
                        if (vaga.area === 'agro') countAgro++;
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${vaga.titulo}</strong></td>
                            <td><span class="badge badge-${vaga.area === 'informatica' ? 'success' : 'warning'}">${vaga.area}</span></td>
                            <td>${vaga.empresa_nome}</td>
                            <td>${vaga.carga_horaria}h</td>
                            <td>R$ ${vaga.remuneracao ? parseFloat(vaga.remuneracao).toFixed(2) : '0,00'}</td>
                            <td>${vaga.vagas_disponiveis}</td>
                            <td><span class="badge badge-${vaga.status === 'aberta' ? 'success' : 'danger'}">${vaga.status}</span></td>
                            <td>
                                <button onclick="deletarVaga(${vaga.id})" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Excluir</button>
                            </td>
                        `;
                        tabela.appendChild(row);
                    });
                    
                    document.getElementById('count-vagas-abertas').textContent = countAbertas;
                    document.getElementById('count-vagas-info').textContent = countInfo;
                    document.getElementById('count-vagas-agro').textContent = countAgro;
                    document.getElementById('count-vagas-total').textContent = dados.vagas.length;
                }
            } catch (error) {
                console.error('Erro ao carregar vagas:', error);
            }
        }

        async function carregarEmpresas() {
            try {
                const response = await fetch('../api/empresas.php');
                const dados = await response.json();
                
                if (dados.sucesso) {
                    const select = document.getElementById('vaga-empresa');
                    select.innerHTML = '<option value="">Selecione...</option>';
                    dados.empresas.forEach(empresa => {
                        select.innerHTML += `<option value="${empresa.id}">${empresa.nome}</option>`;
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar empresas:', error);
            }
        }

        function mostrarFormularioVaga() {
            document.getElementById('form-vaga').style.display = 'block';
            document.getElementById('vaga-form').reset();
            document.getElementById('form-vaga-titulo').textContent = 'Nova Vaga de Estágio';
        }

        function ocultarFormularioVaga() {
            document.getElementById('form-vaga').style.display = 'none';
        }

        document.getElementById('vaga-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const dados = {
                titulo: document.getElementById('vaga-titulo').value,
                descricao: document.getElementById('vaga-descricao').value,
                area: document.getElementById('vaga-area').value,
                empresa_id: document.getElementById('vaga-empresa').value,
                carga_horaria: document.getElementById('vaga-carga').value,
                remuneracao: document.getElementById('vaga-remuneracao').value,
                vagas_disponiveis: document.getElementById('vaga-quantidade').value,
                requisitos: document.getElementById('vaga-requisitos').value,
                beneficios: document.getElementById('vaga-beneficios').value,
                data_encerramento: document.getElementById('vaga-encerramento').value
            };
            
            try {
                const response = await fetch('../api/vagas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });
                
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    alert('Vaga criada com sucesso!');
                    ocultarFormularioVaga();
                    carregarVagas();
                } else {
                    alert('Erro: ' + resultado.mensagem);
                }
            } catch (error) {
                alert('Erro ao criar vaga: ' + error);
            }
        });

        async function deletarVaga(id) {
            if (!confirm('Tem certeza que deseja excluir esta vaga?')) return;
            
            try {
                const response = await fetch(`../api/vagas.php?id=${id}`, { method: 'DELETE' });
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    alert('Vaga excluída com sucesso!');
                    carregarVagas();
                } else {
                    alert('Erro: ' + resultado.mensagem);
                }
            } catch (error) {
                alert('Erro ao excluir vaga: ' + error);
            }
        }

        // --- FUNÇÕES PARA CANDIDATURAS ---
        async function carregarCandidaturas() {
            try {
                const response = await fetch('../api/candidaturas.php?status=pendente');
                const dados = await response.json();
                
                if (dados.sucesso) {
                    const tabela = document.getElementById('tabela-candidaturas');
                    tabela.innerHTML = '';
                    
                    dados.candidaturas.forEach(cand => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${cand.usuario_nome}</strong><br><small>${cand.usuario_email}</small></td>
                            <td>${cand.vaga_titulo}</td>
                            <td><span class="badge badge-${cand.vaga_area === 'informatica' ? 'success' : 'warning'}">${cand.vaga_area}</span></td>
                            <td>${cand.empresa_nome}</td>
                            <td>${new Date(cand.data_candidatura).toLocaleDateString('pt-BR')}</td>
                            <td>
                                <button onclick="aprovarCandidatura(${cand.id})" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px;">Aprovar</button>
                                <button onclick="rejeitarCandidatura(${cand.id})" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Rejeitar</button>
                            </td>
                        `;
                        tabela.appendChild(row);
                    });
                    
                    document.getElementById('count-candidaturas-pendentes').textContent = dados.candidaturas.length;
                    document.getElementById('count-candidaturas-total').textContent = dados.candidaturas.length;
                }
            } catch (error) {
                console.error('Erro ao carregar candidaturas:', error);
            }
        }

        async function aprovarCandidatura(id) {
            const orientador = prompt('ID do Orientador:');
            const supervisor = prompt('ID do Supervisor:');
            
            if (!orientador || !supervisor) {
                alert('É necessário informar o ID do orientador e supervisor.');
                return;
            }
            
            try {
                const response = await fetch('../api/candidaturas.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        status: 'aprovada',
                        orientador_id: orientador,
                        supervisor_id: supervisor
                    })
                });
                
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    alert('Candidatura aprovada com sucesso! Estágio criado automaticamente.');
                    carregarCandidaturas();
                } else {
                    alert('Erro: ' + resultado.mensagem);
                }
            } catch (error) {
                alert('Erro ao aprovar candidatura: ' + error);
            }
        }

        async function rejeitarCandidatura(id) {
            const observacao = prompt('Motivo da rejeição:');
            
            try {
                const response = await fetch('../api/candidaturas.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        status: 'rejeitada',
                        observacoes: observacao
                    })
                });
                
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    alert('Candidatura rejeitada com sucesso!');
                    carregarCandidaturas();
                } else {
                    alert('Erro: ' + resultado.mensagem);
                }
            } catch (error) {
                alert('Erro ao rejeitar candidatura: ' + error);
            }
        }

        // --- FUNÇÕES PARA VALIDAÇÃO DE ESTÁGIOS ---
        async function carregarEstagiosValidacao() {
            try {
                const response = await fetch('../api/estagios.php?status=abertura');
                const dados = await response.json();
                
                if (dados.sucesso) {
                    const tabela = document.getElementById('tabela-validacao');
                    tabela.innerHTML = '';
                    
                    dados.dados.forEach(estagio => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${estagio.aluno_nome}</strong></td>
                            <td>${estagio.curso_nome}</td>
                            <td>${estagio.empresa_nome}</td>
                            <td>${new Date(estagio.data_criacao).toLocaleDateString('pt-BR')}</td>
                            <td><span class="badge badge-warning">Abertura</span></td>
                            <td>
                                <button onclick="aprovarEstagio(${estagio.id})" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px;">Aprovar</button>
                                <button onclick="rejeitarEstagio(${estagio.id})" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Rejeitar</button>
                            </td>
                        `;
                        tabela.appendChild(row);
                    });
                    
                    document.getElementById('count-abertura').textContent = dados.dados.length + ' Pendentes';
                }
            } catch (error) {
                console.error('Erro ao carregar estágios:', error);
            }
        }

        async function aprovarEstagio(id) {
            const orientador = prompt('ID do Orientador (opcional):');
            const supervisor = prompt('ID do Supervisor (opcional):');
            
            try {
                const response = await fetch('../api/estagios.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        status: 'em_andamento',
                        orientador_id: orientador || null,
                        supervisor_id: supervisor || null
                    })
                });
                
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    alert('Estágio aprovado com sucesso!');
                    carregarEstagiosValidacao();
                } else {
                    alert('Erro: ' + resultado.mensagem);
                }
            } catch (error) {
                alert('Erro ao aprovar estágio: ' + error);
            }
        }

        async function rejeitarEstagio(id) {
            const observacao = prompt('Motivo da rejeição:');
            
            try {
                const response = await fetch('../api/estagios.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        status: 'cancelado',
                        observacoes: observacao
                    })
                });
                
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    alert('Estágio rejeitado com sucesso!');
                    carregarEstagiosValidacao();
                } else {
                    alert('Erro: ' + resultado.mensagem);
                }
            } catch (error) {
                alert('Erro ao rejeitar estágio: ' + error);
            }
        }

        // Carregar vagas ao iniciar
        carregarVagas();
        carregarEmpresas();

        function salvarDados() {
            const linhas = [];
            tabela.querySelectorAll('tr').forEach(linha => {
                linhas.push({
                    nome: linha.dataset.nome,
                    empresa: linha.cells[1].innerText,
                    status: linha.querySelector('.badge').innerText,
                    badgeClass: linha.querySelector('.badge').className,
                    responsavel: linha.cells[3].innerText,
                    tipo: linha.dataset.tipo,
                    professor: linha.dataset.professor
                });
            });
            localStorage.setItem('sge_data', JSON.stringify(linhas));
            atualizarCards();
        }

        function carregarDados() {
            const dados = JSON.parse(localStorage.getItem('sge_data') || "[]");
            tabela.innerHTML = "";
            dados.forEach(item => {
                adicionarLinhaNaTabela(item.nome, item.empresa, item.status, item.badgeClass, item.responsavel, item.tipo, item.professor);
            });
            atualizarCards();
        }

        function atualizarCards() {
            const linhas = tabela.querySelectorAll('tr');
            let abertura = 0, andamento = 0, concluido = 0;
            linhas.forEach(linha => {
                const badge = linha.querySelector('.badge');
                if (badge) {
                    const status = badge.innerText.toLowerCase();
                    if (status === "em andamento") andamento++;
                    else if (status === "concluído") concluido++;
                    else abertura++;
                }
            });
            document.getElementById('count-abertura').innerText = `${abertura} Pendentes`;
            document.getElementById('count-andamento').innerText = `${andamento} Aguardando`;
            document.getElementById('count-concluido').innerText = `${concluido} Finalizados`;
            document.getElementById('count-total').innerText = `${linhas.length} Registros`;
        }

        function adicionarLinhaNaTabela(nome, empresa, statusTxt = "Abertura", badgeClass = "badge status-abertura", responsavel = "Admin", tipo = "Obrigatório", professor = "") {
            const tr = document.createElement('tr');
            tr.dataset.nome = nome;
            tr.dataset.tipo = tipo;
            tr.dataset.professor = professor || ""; 
            
            const txtProf = professor ? professor : `<span style="color:#e74c3c; font-weight:bold;">Aguardando Orientador...</span>`;

            tr.innerHTML = `
                <td>
                    <strong>${nome}</strong><br>
                    <small style="color:gray">Tipo: ${tipo}</small><br>
                    <small style="color:#007bff">Orientador: ${txtProf}</small>
                </td>
                <td>${empresa}</td>
                <td><span class="${badgeClass}">${statusTxt}</span></td>
                <td>${responsavel}</td>
                <td class="acoes"></td>
            `;
            renderizarBotoes(tr, statusTxt);
            tabela.appendChild(tr);
        }

        function renderizarBotoes(linha, status) {
            const celulaAcao = linha.querySelector('.acoes');
            const badge = linha.querySelector('.badge');
            celulaAcao.innerHTML = "";

            const btnExcluir = document.createElement('button');
            btnExcluir.innerText = "Excluir";
            btnExcluir.style.cssText = estiloBotoes + "background: #e74c3c; color: white;";
            btnExcluir.onclick = () => { if(confirm("Excluir?")) { linha.remove(); salvarDados(); } };

            if (status === "Abertura") {
                const btnAprovar = document.createElement('button');
                btnAprovar.innerText = "Aprovar";
                btnAprovar.style.cssText = estiloBotoes + "background: #007bff; color: white;";
                btnAprovar.onclick = () => {
                    badge.innerText = "Em andamento";
                    badge.className = "badge status-plano";
                    renderizarBotoes(linha, "Em andamento");
                    salvarDados();
                };
                celulaAcao.appendChild(btnAprovar);
            } else if (status === "Em andamento") {
                const btnFeito = document.createElement('button');
                btnFeito.innerText = "Concluir";
                btnFeito.style.cssText = estiloBotoes + "background: #27ae60; color: white;";
                btnFeito.onclick = () => {
                    badge.innerText = "Concluído";
                    badge.className = "badge status-manutencao";
                    renderizarBotoes(linha, "Concluído");
                    salvarDados();
                };
                celulaAcao.appendChild(btnFeito);
            }
            celulaAcao.appendChild(btnExcluir);
        }

        document.getElementById('btn-adicionar').addEventListener('click', () => {
            const nome = prompt("Nome do Estagiário:");
            const empresa = prompt("Nome da Empresa:");
            const tipo = prompt("Tipo de Estágio (Obrigatório):", "Obrigatório");
            
            if (nome && empresa) {
                adicionarLinhaNaTabela(nome, empresa, "Abertura", "badge status-abertura", "Admin", tipo, "");
                salvarDados();
            }
        });

        window.onload = () => {
            // Garante o estado inicial fechado via script por segurança
            sb.style.left = "-250px";
            ct.style.marginLeft = "0";
            inicializarControleProfessor();
            carregarDados();
        };

        window.addEventListener('storage', () => carregarDados());
    </script>
</body>
</html>
