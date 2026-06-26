# SGE - Sistema de Gestão de Estágios

## 📋 Descrição

O **SGE (Sistema de Gestão de Estágios)** é uma plataforma web desenvolvida para gerenciar todo o processo de estágio em instituições de educação profissional. O sistema permite controlar alunos, empresas, orientadores, documentos, horas trabalhadas e atividades relacionadas aos estágios.

## 🎯 Funcionalidades Principais

### Para Administradores
- ✅ Cadastro e gerenciamento de cursos
- ✅ Cadastro e gerenciamento de empresas
- ✅ Criação e atribuição de estágios
- ✅ Atribuição de orientadores e supervisores
- ✅ Gerenciamento de usuários
- ✅ Visualização de relatórios e logs

### Para Estagiários
- ✅ Visualização de seus estágios
- ✅ Envio de documentos (plano de trabalho, relatórios)
- ✅ Registro de horas trabalhadas
- ✅ Visualização de atividades do professor
- ✅ Acesso ao painel pessoal

### Para Orientadores/Supervisores
- ✅ Visualização dos estágios sob sua responsabilidade
- ✅ Aprovação de documentos
- ✅ Registro de atividades para alunos
- ✅ Confirmação de horas trabalhadas
- ✅ Geração de relatórios

## 🛠️ Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Servidor**: Apache/Nginx com suporte a PHP

## 📦 Instalação

### Pré-requisitos

1. **Servidor Web** (Apache com mod_rewrite habilitado ou Nginx)
2. **PHP 7.4 ou superior** com extensões:
   - mysqli
   - json
   - session
3. **MySQL 5.7 ou superior**
4. **Navegador moderno** (Chrome, Firefox, Safari, Edge)

### Passos de Instalação

#### 1. Clonar o Repositório
```bash
git clone https://github.com/vinienoquer667-web/oficina-.git
cd oficina-
```

#### 2. Criar Banco de Dados
```bash
# Acessar o MySQL
mysql -u root -p

# Executar o schema
mysql -u root -p < schema.sql
```

Ou copie e cole o conteúdo de `schema.sql` no phpMyAdmin.

#### 3. Configurar Conexão
Abra `config.php` e ajuste as credenciais:

```php
$db_host = 'localhost';      // Host do MySQL
$db_user = 'root';           // Usuário MySQL
$db_password = '';           // Senha MySQL
$db_name = 'sge_db';         // Nome do banco
$db_port = 3306;             // Porta MySQL
```

#### 4. Colocar Arquivos no Servidor Web

**Para Apache:**
```bash
# Linux/Mac
sudo cp -r ./* /var/www/html/sge/

# Ou configure o DocumentRoot no VirtualHost
```

**Para Nginx:**
Configure o bloco server para apontar para o diretório do projeto.

#### 5. Definir Permissões (Linux/Mac)
```bash
chmod -R 755 /var/www/html/sge
chmod -R 775 /var/www/html/sge/uploads
chown -R www-data:www-data /var/www/html/sge
```

#### 6. Acessar o Sistema
```
http://localhost/sge/login.html
```

## 👥 Usuários de Teste

O sistema vem com usuários de teste já cadastrados. Use as credenciais abaixo:

| Perfil | CPF | Senha |
|--------|-----|-------|
| Admin | 123.456.789-01 | senha123 |
| Orientador | 987.654.321-01 | senha123 |
| Supervisor | 111.222.333-44 | senha123 |
| Estagiário | 555.666.777-88 | senha123 |

**Importante:** Altere as senhas após o primeiro acesso em ambiente de produção!

## 📁 Estrutura de Arquivos

```
oficina-/
├── config.php              # Configuração do banco de dados
├── login.php               # Autenticação
├── logout.php              # Logout
├── index.php               # Listar estágios
├── cadastro.php            # Criar novo estágio
├── editar.php              # Atualizar estágio
├── excluir.php             # Deletar estágio
├── schema.sql              # Script de criação do banco
├── login.html              # Página de login
├── login.css               # Estilos do login
├── alunos.STG.html         # Painel do aluno
├── alunos.STG.css          # Estilos do painel do aluno
├── adm.STG.html            # Painel admin
├── professor.orin.STG.html # Painel do orientador
├── professor.super.STG.html# Painel do supervisor
├── README.md               # Este arquivo
└── img/                    # Pasta de imagens
    └── if.png              # Logo do IF Sertão-PE
```

## 🔌 API REST

O sistema utiliza uma API REST para comunicação entre frontend e backend. Todas as respostas são em JSON.

### Endpoints Disponíveis

#### 1. **Login**
```
POST /login.php
Content-Type: application/json

{
  "username": "12345678901",
  "password": "senha123"
}

Response:
{
  "sucesso": true,
  "usuario": {
    "id": 1,
    "nome": "Admin Sistema",
    "email": "admin@ifsertaope.edu.br",
    "perfil": "admin"
  }
}
```

#### 2. **Listar Estágios**
```
GET /index.php
GET /index.php?status=em_andamento
GET /index.php?busca=Pedro

Response:
{
  "sucesso": true,
  "total": 5,
  "dados": [...]
}
```

#### 3. **Criar Estágio**
```
POST /cadastro.php
Content-Type: application/json

{
  "usuario_id": 4,
  "curso_id": 1,
  "empresa_id": 1,
  "tipo": "obrigatorio",
  "data_inicio": "2024-01-15",
  "data_fim": "2024-06-30",
  "carga_horaria_total": 400
}

Response:
{
  "sucesso": true,
  "estagio_id": 1
}
```

#### 4. **Atualizar Estágio**
```
PUT /editar.php
Content-Type: application/json

{
  "id": 1,
  "status": "em_andamento",
  "carga_horaria_cumprida": 150
}

Response:
{
  "sucesso": true,
  "mensagem": "Estágio atualizado com sucesso"
}
```

#### 5. **Deletar Estágio**
```
DELETE /excluir.php
Content-Type: application/json

{
  "id": 1
}

Response:
{
  "sucesso": true,
  "mensagem": "Estágio deletado com sucesso"
}
```

#### 6. **Logout**
```
POST /logout.php

Response:
{
  "sucesso": true,
  "mensagem": "Logout realizado com sucesso"
}
```

## 🔒 Segurança

### Implementações de Segurança

1. **SQL Injection Prevention**: Prepared Statements com Bind Parameters
2. **Session Management**: Validação de sessão em todas as operações
3. **Permission Control**: Verificação de permissões por perfil
4. **Password Hashing**: Senhas criptografadas com MD5
5. **Logging**: Registro de todas as operações do sistema
6. **HTTPS**: Recomenda-se usar em produção

## 📊 Estrutura do Banco de Dados

### Tabelas Principais

- **usuarios**: Dados de usuários e autenticação
- **estagios**: Informações dos estágios
- **documentos**: Documentos relacionados aos estágios
- **relatorio_horas**: Registro de horas trabalhadas
- **logs_sistema**: Auditoria de operações

## 🐛 Troubleshooting

### Erro: "Erro de conexão com o banco de dados"
- Verifique se o MySQL está rodando
- Confirme as credenciais em `config.php`
- Verifique se o banco `sge_db` existe

### Erro: "Não autenticado"
- Limpe os cookies/localStorage
- Faça login novamente
- Verifique se as sessões PHP estão habilitadas

### Erro: "Permissão negada"
- Verifique o perfil do usuário logado
- Confirme se o usuário tem permissão para a ação

## 📞 Suporte

Para dúvidas ou problemas, entre em contato com:
- **Instituição**: Instituto Federal de Educação, Ciência e Tecnologia do Sertão Pernambucano
- **Desenvolvedor**: vinienoquer667-web

## 📄 Licença

Este projeto é de código aberto e está disponível sob a licença MIT.

---

**Última atualização**: 26 de Junho de 2024
**Status**: ✅ Em Produção
