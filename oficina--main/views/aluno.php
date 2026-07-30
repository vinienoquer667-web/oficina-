<?php
// =====================================================================
// alunos.STG.php - Painel do Aluno
// =====================================================================

require_once '../config.php';

// Verificar autenticação e permissão
$session = Session::getInstance();
$session->requireAuth();
$session->requireProfile(['estagiario']);

$userName = $session->getUserName();
$userEmail = $session->getUserEmail();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGE - Área do Aluno</title>
    <link rel="stylesheet" href="../alunos.STG.css">
</head>
<body>

    <button id="btn-toggle-menu" class="btn-menu-trigger">☰</button>

    <nav class="sidebar" id="sidebar">
        <div class="logo"><h2>SGE Aluno</h2></div>
        <ul class="nav-links">
            <li class="section-title">MEU PAINEL</li>
            <li onclick="navegar('aba-status')" style="cursor:pointer">Início / Status</li>
            <li onclick="navegar('aba-vagas')" style="cursor:pointer">Vagas de Estágio</li>
            <li onclick="navegar('aba-candidaturas')" style="cursor:pointer">Minhas Candidaturas</li>
            <li onclick="navegar('aba-documentos')" style="cursor:pointer">Meus Documentos</li>
            
            <li class="section-title">CONTA</li>
            <li>Meu Perfil</li>
            <li onclick="window.location.href='../api/logout.php'" style="cursor:pointer; color: #e74c3c;">Sair</li>
        </ul>
    </nav>

    <main class="content" id="main-content">
        <header class="top-bar">
            <div style="display: flex; align-items: center;">
                <img src="../img/if.png" alt="logo" style="margin-right: 15px;" onerror="this.style.display='none';">
                <div>
                    <h1>Portal do Estudante</h1>
                    <small style="color: #666;">Logado como: <strong><?php echo htmlspecialchars($userName); ?></strong> (<?php echo htmlspecialchars($userEmail); ?>)</small>
                </div>
            </div>
        </header>

        <section id="aba-status" class="view-section active">
            <div class="table-section" style="margin-bottom: 25px;">
                <h2>Meu Estágio Atual</h2>
                <div id="status-container" style="margin-top: 20px;"></div>
            </div>

            <div class="table-section" style="margin-bottom: 25px; border-top: 4px solid #f1c40f;">
                <h2>Mural de Atividades (Professor)</h2>
                <div id="atividades-professor-container" style="margin-top: 15px;">
                    <p style="color: gray;">Nenhuma atividade pendente.</p>
                </div>
            </div>

            <div class="stats-container">
                <div class="card border-blue">
                    <h3>Horas Totais</h3>
                    <p>400 Horas</p>
                </div>
                <div class="card border-green">
                    <h3>Horas Cumpridas</h3>
                    <p>120 Horas</p>
                </div>
                <div class="card border-yellow">
                    <h3>Próximo Relatório</h3>
                    <p>Em 15 dias</p>
                </div>
            </div>
        </section>

        <section id="aba-vagas" class="view-section">
            <div class="stats-container">
                <div class="card"><h3>Vagas Informática</h3><p id="count-vagas-info">0</p></div>
                <div class="card"><h3>Vagas Agro</h3><p id="count-vagas-agro">0</p></div>
                <div class="card"><h3>Total Vagas</h3><p id="count-vagas-total">0</p></div>
                <div class="card"><h3>Minhas Candidaturas</h3><p id="count-minhas-candidaturas">0</p></div>
            </div>

            <div class="table-section">
                <h2>Vagas de Estágio Disponíveis</h2>
                <div style="margin-bottom: 15px;">
                    <select id="filtro-area" onchange="filtrarVagas()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Todas as Áreas</option>
                        <option value="informatica">Informática</option>
                        <option value="agro">Agro</option>
                    </select>
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
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-vagas-aluno"></tbody>
                </table>
            </div>
        </section>

        <section id="aba-candidaturas" class="view-section">
            <div class="table-section">
                <h2>Minhas Candidaturas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Vaga</th>
                            <th>Área</th>
                            <th>Empresa</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-candidaturas-aluno"></tbody>
                </table>
            </div>
        </section>

        <section id="aba-documentos" class="view-section">
            <div class="table-section">
                <h2>Documentação do Estágio</h2>
                <p id="doc-alerta" style="margin-bottom: 15px; color: #666;"></p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Tipo</th>
                            <th>Status no Sistema</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-documentos-aluno"></tbody>
                </table>

                <div style="margin-top: 30px; padding: 20px; border: 2px dashed #05c46b; border-radius: 10px; text-align: center;">
                    <h3>Enviar Novo Documento</h3>
                    <input type="file" id="fileInput" style="margin-top: 10px;">
                    <br><br>
                    <button class="btn-primary" onclick="alert('Documento enviado para análise do Orientador!')">Fazer Upload</button>
                </div>
            </div>
        </section>

        <section id="aba-vagas" class="view-section">
            <div class="table-section">
                <h2>Vagas Disponíveis</h2>
                <p>Nenhuma vaga disponível para o seu curso no momento.</p>
            </div>
        </section>
    </main>

    <script>
        const sb = document.getElementById('sidebar');
        const ct = document.getElementById('main-content');

        // --- LÓGICA DO MENU ---
        sb.style.transition = "all 0.3s ease";
        ct.style.transition = "all 0.3s ease";

        document.getElementById('btn-toggle-menu').addEventListener('mouseenter', () => {
            sb.style.left = "0";
            ct.style.marginLeft = "250px";
        });

        sb.addEventListener('mouseleave', () => {
            sb.style.left = "-250px";
            ct.style.marginLeft = "0";
        });

        function navegar(id) {
            document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
            const aba = document.getElementById(id);
            if(aba) aba.classList.add('active');
            
            if (id === 'aba-vagas') {
                carregarVagasAluno();
            } else if (id === 'aba-candidaturas') {
                carregarCandidaturasAluno();
            }
        }

        // --- FUNÇÕES PARA VAGAS ---
        async function carregarVagasAluno() {
            try {
                const area = document.getElementById('filtro-area').value;
                const url = area ? `../api/vagas.php?area=${area}` : '../api/vagas.php';
                
                const response = await fetch(url);
                const dados = await response.json();
                
                if (dados.sucesso) {
                    const tabela = document.getElementById('tabela-vagas-aluno');
                    tabela.innerHTML = '';
                    
                    let countInfo = 0;
                    let countAgro = 0;
                    
                    dados.vagas.forEach(vaga => {
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
                            <td>
                                <button onclick="candidatarVaga(${vaga.id})" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">Candidatar-se</button>
                            </td>
                        `;
                        tabela.appendChild(row);
                    });
                    
                    document.getElementById('count-vagas-info').textContent = countInfo;
                    document.getElementById('count-vagas-agro').textContent = countAgro;
                    document.getElementById('count-vagas-total').textContent = dados.vagas.length;
                }
            } catch (error) {
                console.error('Erro ao carregar vagas:', error);
            }
        }

        function filtrarVagas() {
            carregarVagasAluno();
        }

        async function candidatarVaga(vagaId) {
            if (!confirm('Deseja se candidatar a esta vaga?')) return;
            
            try {
                const response = await fetch('../api/candidaturas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ vaga_id: vagaId })
                });
                
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    alert('Candidatura realizada com sucesso!');
                    carregarVagasAluno();
                    carregarCandidaturasAluno();
                } else {
                    alert('Erro: ' + resultado.mensagem);
                }
            } catch (error) {
                alert('Erro ao realizar candidatura: ' + error);
            }
        }

        // --- FUNÇÕES PARA CANDIDATURAS ---
        async function carregarCandidaturasAluno() {
            try {
                const response = await fetch('../api/candidaturas.php');
                const dados = await response.json();
                
                if (dados.sucesso) {
                    const tabela = document.getElementById('tabela-candidaturas-aluno');
                    tabela.innerHTML = '';
                    
                    dados.candidaturas.forEach(cand => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${cand.vaga_titulo}</strong></td>
                            <td><span class="badge badge-${cand.vaga_area === 'informatica' ? 'success' : 'warning'}">${cand.vaga_area}</span></td>
                            <td>${cand.empresa_nome}</td>
                            <td>${new Date(cand.data_candidatura).toLocaleDateString('pt-BR')}</td>
                            <td><span class="badge badge-${cand.status === 'aprovada' ? 'success' : cand.status === 'rejeitada' ? 'danger' : 'warning'}">${cand.status}</span></td>
                            <td>
                                ${cand.status === 'pendente' ? `<button onclick="cancelarCandidatura(${cand.id})" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Cancelar</button>` : '-'}
                            </td>
                        `;
                        tabela.appendChild(row);
                    });
                    
                    document.getElementById('count-minhas-candidaturas').textContent = dados.candidaturas.length;
                }
            } catch (error) {
                console.error('Erro ao carregar candidaturas:', error);
            }
        }

        async function cancelarCandidatura(id) {
            if (!confirm('Tem certeza que deseja cancelar esta candidatura?')) return;
            
            try {
                const response = await fetch(`../api/candidaturas.php?id=${id}`, { method: 'DELETE' });
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    alert('Candidatura cancelada com sucesso!');
                    carregarCandidaturasAluno();
                    carregarVagasAluno();
                } else {
                    alert('Erro: ' + resultado.mensagem);
                }
            } catch (error) {
                alert('Erro ao cancelar candidatura: ' + error);
            }
        }

        // Carregar vagas ao iniciar
        carregarVagasAluno();

        // --- FUNÇÃO PARA RECEBER ATIVIDADES DO PROFESSOR ---
        function carregarAtividadesDoProfessor() {
            const atividades = JSON.parse(localStorage.getItem('sge_atividades') || '[]');
            const container = document.getElementById('atividades-professor-container');
            
            if (atividades.length > 0) {
                container.innerHTML = ''; 
                
                atividades.reverse().forEach(at => {
                    const item = document.createElement('div');
                    item.style.cssText = "background: #fffbe6; border: 1px solid #ffe58f; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 5px solid #f1c40f;";
                    item.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong style="color: #856404; font-size: 16px;">${at.titulo}</strong>
                            <small style="color: #999;">${at.data}</small>
                        </div>
                        <p style="margin-top: 8px; color: #555; line-height: 1.4;">${at.desc}</p>
                        <button onclick="alert('Funcionalidade de resposta em breve!')" style="margin-top: 10px; font-size: 11px; cursor: pointer; padding: 4px 8px;">Responder Atividade</button>
                    `;
                    container.appendChild(item);
                });
            } else {
                container.innerHTML = "<p style='color: gray;'>Nenhuma atividade pendente enviada pelo seu orientador.</p>";
            }
        }

        // --- SINCRONIZAÇÃO COM DADOS DO ESTÁGIO ---
        function carregarDadosSincronizados() {
            const dados = JSON.parse(localStorage.getItem('sge_data') || '[]');
            const container = document.getElementById('status-container');
            const tabelaDocs = document.getElementById('tabela-documentos-aluno');
            
            if (dados.length > 0) {
                const meuEstagio = dados[0]; // Pega o primeiro cadastro fictício para testes
                const professorOrientador = meuEstagio.professor || "Não definido"; // <-- Lê o professor salvo

                container.innerHTML = `
                    <div style="background: #f4f4f4; padding: 20px; border-radius: 8px; border-left: 8px solid #007bff;">
                        <h3 style="color: #2c3e50;">Bem-vindo(a), ${meuEstagio.nome}</h3>
                        <p><strong>Empresa:</strong> ${meuEstagio.empresa}</p>
                        <p><strong>Tipo de Estágio:</strong> ${meuEstagio.tipo || 'Obrigatório'}</p>
                        <p><strong>Professor Orientador:</strong> <span style="color:#007bff; font-weight:bold;">${professorOrientador}</span></p>
                        <p><strong>Status Atual:</strong> <span class="badge ${meuEstagio.badgeCls || meuEstagio.badgeClass}">${meuEstagio.status}</span></p>
                        <p style="margin-top:10px; font-size: 14px; color: #555;">
                            Responsável no Sistema: <strong>${meuEstagio.resp || meuEstagio.responsavel}</strong>
                        </p>
                    </div>
                `;

                tabelaDocs.innerHTML = `
                    <tr>
                        <td>Plano de Trabalho</td>
                        <td>PDF</td>
                        <td><span class="badge ${meuEstagio.badgeCls || meuEstagio.badgeClass}">${meuEstagio.status}</span></td>
                        <td><button class="btn-primary" onclick="alert('Baixando Modelo...')">Baixar</button></td>
                    </tr>
                `;
            } else {
                container.innerHTML = "<p>Nenhum estágio ativo encontrado no sistema.</p>";
                tabelaDocs.innerHTML = "<tr><td colspan='4'>Nenhum documento encontrado.</td></tr>";
            }
        }

        // Inicialização
        window.onload = () => {
            sb.style.left = "-250px";
            ct.style.marginLeft = "0";
            carregarDadosSincronizados();
            carregarAtividadesDoProfessor();
        };

        window.addEventListener('storage', () => {
            carregarDadosSincronizados();
            carregarAtividadesDoProfessor();
        });
    </script>
</body>
</html>
