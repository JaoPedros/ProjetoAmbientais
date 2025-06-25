<?php
session_start();
session_unset();
session_destroy();

header("Location: /ProjetoAmbientais/PaginasPublicas/login.php?deslogado=1");
exit;
?>
