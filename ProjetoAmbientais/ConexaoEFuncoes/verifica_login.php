<?php
session_start();
require_once 'conexao.php'; // Caminho da sua conexão

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        header("Location: ../PaginasPublicas/login.php?erro=1");
        exit;
    }

    // Proteção contra SQL Injection
    $email = mysqli_real_escape_string($conexao, $email);

    // Buscar o usuário pelo e-mail
    $sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = '$email' LIMIT 1";
    $resultado = mysqli_query($conexao, $sql);

    if ($resultado && $resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // ✅ Verifica a senha com password_verify (compatível com password_hash)
        if (password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido — cria sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];

            header("Location: ../index.php");
            exit;
        }
    }

    // Login inválido
    header("Location: ../PaginasPublicas/login.php?erro=1");
    exit;
} else {
    // Se não for POST, redireciona para login
    header("Location: ../PaginasPublicas/login.php");
    exit;
}
