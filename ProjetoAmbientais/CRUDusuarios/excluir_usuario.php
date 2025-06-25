<?php
// excluir_usuario.php
session_start();
require_once '../ConexaoEFuncoes/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../PaginasPublicas/login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo "ID inválido.";
    exit;
}

// Excluir usuário
$sql = "DELETE FROM usuarios WHERE id = $id";

if (mysqli_query($conexao, $sql)) {
    header("Location: usuario.php?msg=excluido");
    exit;
} else {
    echo "Erro ao excluir usuário: " . mysqli_error($conexao);
}
?>
