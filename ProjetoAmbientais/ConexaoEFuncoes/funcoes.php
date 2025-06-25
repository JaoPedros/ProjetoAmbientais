<?php
// Inicia a sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para verificar se o usuário está logado
function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

// Função para proteger páginas que exigem login
function protegerPagina() {
    if (!estaLogado()) {
        // Redireciona para login mantendo o caminho relativo
        header("Location: ../PaginasPublicas/login.php");
        exit();
    }
}

// Função para fazer login (retorna true se sucesso, false caso contrário)
function login($email, $senha, $conexao) {
    $email = mysqli_real_escape_string($conexao, $email);

    $sql = "SELECT * FROM usuarios WHERE email = '$email' LIMIT 1";
    $resultado = mysqli_query($conexao, $sql);

    if ($resultado && mysqli_num_rows($resultado) === 1) {
        $usuario = mysqli_fetch_assoc($resultado);

        // Verifica a senha usando password_hash/password_verify
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            return true;
        }
    }

    return false;
}

// Função para logout
function logout() {
    session_unset();
    session_destroy();
}
