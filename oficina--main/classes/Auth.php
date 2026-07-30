<?php
// =====================================================================
// Auth.php - Classe para gerenciar autenticação
// =====================================================================

class Auth {
    private $db;
    private $session;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = Session::getInstance();
    }

    public function login($campo, $valor, $password) {
        // Se campo for 'cpf', limpar caracteres não numéricos
        if ($campo === 'cpf') {
            $valor = preg_replace('/\D/', '', $valor);
            
            // Validar CPF
            if (strlen($valor) !== 11) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'CPF inválido'
                ];
            }
        }

        // Buscar usuário por CPF ou email
        $sql = "SELECT id, cpf, nome, email, senha, perfil, ativo 
                FROM usuarios 
                WHERE $campo = ?";
        
        $user = $this->db->fetchOne($sql, [$valor]);

        if (!$user) {
            $this->logLoginAttempt(null, false, "$campo: $valor");
            return [
                'sucesso' => false,
                'mensagem' => 'Usuário ou senha incorretos'
            ];
        }

        // Verificar se usuário está ativo
        if (!$user['ativo']) {
            return [
                'sucesso' => false,
                'mensagem' => 'Usuário desativado. Entre em contato com o administrador'
            ];
        }

        // Verificar senha (compatível com MD5 antigo e password_hash novo)
        if (!$this->verifyPassword($password, $user['senha'])) {
            $this->logLoginAttempt($user['id'], false, 'Senha incorreta');
            return [
                'sucesso' => false,
                'mensagem' => 'Usuário ou senha incorretos'
            ];
        }

        // Login bem-sucedido
        $this->session->regenerateId();
        $this->session->set('usuario_id', $user['id']);
        $this->session->set('usuario_nome', $user['nome']);
        $this->session->set('usuario_email', $user['email']);
        $this->session->set('usuario_perfil', $user['perfil']);

        $this->logLoginAttempt($user['id'], true, '');

        return [
            'sucesso' => true,
            'mensagem' => 'Login realizado com sucesso',
            'usuario' => [
                'id' => $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'perfil' => $user['perfil']
            ]
        ];
    }

    public function logout() {
        $userId = $this->session->getUserId();
        
        if ($userId) {
            $this->logLoginAttempt($userId, true, 'Logout');
        }
        
        $this->session->destroy();
        
        return [
            'sucesso' => true,
            'mensagem' => 'Logout realizado com sucesso'
        ];
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password, $hash) {
        // Verificar com password_verify (novo método)
        if (password_verify($password, $hash)) {
            return true;
        }

        // Verificar com MD5 (compatibilidade com dados antigos)
        if (md5($password) === $hash) {
            return true;
        }

        return false;
    }

    public function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    public function rehashPassword($userId, $newPassword) {
        $newHash = $this->hashPassword($newPassword);
        
        $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
        $result = $this->db->query($sql, [$newHash, $userId]);
        
        return $result->rowCount() > 0;
    }

    private function logLoginAttempt($userId, $success, $description) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $action = $success ? 'LOGIN_SUCESSO' : 'LOGIN_FALHA';
        
        $sql = "INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $userId,
                $action,
                'usuarios',
                $userId,
                $description,
                $ipAddress,
                $userAgent
            ]);
        } catch (Exception $e) {
            error_log("Erro ao registrar log de login: " . $e->getMessage());
        }
    }

    public function getCurrentUser() {
        if (!$this->session->isAuthenticated()) {
            return null;
        }

        return [
            'id' => $this->session->getUserId(),
            'nome' => $this->session->getUserName(),
            'email' => $this->session->getUserEmail(),
            'perfil' => $this->session->getUserProfile()
        ];
    }

    public function hasPermission($requiredProfile) {
        if (!$this->session->isAuthenticated()) {
            return false;
        }

        $currentProfile = $this->session->getUserProfile();
        
        if ($currentProfile === 'admin') {
            return true;
        }

        return $currentProfile === $requiredProfile;
    }

    public function hasAnyPermission($profiles) {
        if (!$this->session->isAuthenticated()) {
            return false;
        }

        $currentProfile = $this->session->getUserProfile();
        
        if ($currentProfile === 'admin') {
            return true;
        }

        return in_array($currentProfile, $profiles);
    }
}
