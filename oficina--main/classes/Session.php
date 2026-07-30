<?php
// =====================================================================
// Session.php - Classe para gerenciar sessões
// =====================================================================

class Session {
    private static $instance = null;

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function has($key) {
        return isset($_SESSION[$key]);
    }

    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy() {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function regenerateId() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function getUserId() {
        return $this->get('usuario_id');
    }

    public function getUserName() {
        return $this->get('usuario_nome');
    }

    public function getUserEmail() {
        return $this->get('usuario_email');
    }

    public function getUserProfile() {
        return $this->get('usuario_perfil');
    }

    public function isAuthenticated() {
        return $this->has('usuario_id');
    }

    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Não autenticado'
            ]);
            exit;
        }
    }

    public function requireProfile($allowedProfiles) {
        $this->requireAuth();
        
        $currentProfile = $this->getUserProfile();
        
        if (!in_array($currentProfile, $allowedProfiles)) {
            http_response_code(403);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Permissão negada'
            ]);
            exit;
        }
    }

    public function __clone() {
        throw new Exception("Clonagem não permitida");
    }

    public function __wakeup() {
        throw new Exception("Deserialização não permitida");
    }
}
