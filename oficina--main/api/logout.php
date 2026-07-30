<?php
// =====================================================================
// logout.php - Fazer Logout
// =====================================================================

require_once '../config.php';

// Usar classe Auth para logout
$auth = new Auth();
$resultado = $auth->logout();

// Redirecionar para página de login
header('Location: ../views/login.php');
exit;

?>
