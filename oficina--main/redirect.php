<?php
// =====================================================================
// redirect.php - Redirecionamento baseado no perfil do usuário
// =====================================================================

require_once 'config.php';

// Verificar se usuário está autenticado
$session = Session::getInstance();
$session->requireAuth();

$perfil = $session->getUserProfile();

// Redirecionar baseado no perfil
switch ($perfil) {
    case 'admin':
        header('Location: views/admin.php');
        break;
    case 'orientador':
        header('Location: views/orientador.php');
        break;
    case 'supervisor':
        header('Location: views/supervisor.php');
        break;
    case 'estagiario':
        header('Location: views/aluno.php');
        break;
    default:
        // Perfil não reconhecido, volta para login
        header('Location: views/login.php');
        break;
}

exit;
?>
