<?php
// Inicia a sessão
session_start();

// Destrói todas as variáveis da sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Redireciona o usuário para a página de login
header("location: login.php");
exit;
?>