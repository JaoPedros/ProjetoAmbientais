<?php
session_start();
require_once '../ConexaoEFuncoes/conexao.php'; // ajuste o caminho conforme seu projeto

$usuario_logado = isset($_SESSION['usuario_id']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Login - Ecologica Verde</title>
    <link rel="stylesheet" href="../Extras/styleslogin.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>

    <header class="main-header">
        <div class="logo-container" style="display:flex; align-items:center;">
            <img src="../Extras/img/arvore.png" alt="Ícone Árvore" class="logo-icon" />
            <img src="../Extras/img/ecologicaverde.png" alt="Logo Ecologica Verde" class="logo-text" />
        </div>
        <nav class="header-buttons">
            <a href="../index.php" class="btn-outline">Início</a>
            <?php if (!$usuario_logado): ?>
                <a href="login.php" class="btn-outline">Login</a>
            <?php else: ?>
                <a href="/ProjetoAmbientais/AreaRestrita/dashboard.php" class="btn-outline">Perfil</a>
                <a href="nova_noticia.php" class="btn-outline">Publicar Nova Notícia</a>
                <a href="/ProjetoAmbientais/PaginasPublicas/logout.php" onclick="confirmarLogout(event)" class="btn-outline">Sair</a>
            <?php endif; ?>
        </nav>
    </header>

    <main id="top">
        <img src="../Extras/img/iconlogin.png" style="margin-top: -100px;" alt="Ícone de Login" class="icon-login" />
        <h2>Login</h2>

        <?php if (isset($_GET['deslogado']) && $_GET['deslogado'] == 1): ?>
            <p style="color: green; font-weight: bold;">Você foi deslogado com sucesso.</p>
        <?php endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] == 1): ?>
            <p style="color: red; font-weight: bold;">Conta não existe ou não foi encontrada.</p>
        <?php endif; ?>

        <form action="../ConexaoEFuncoes/verifica_login.php" method="POST">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required placeholder="Digite seu e-mail" />

            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required placeholder="Digite sua senha" />

            <button type="submit" class="btn-outline">Entrar</button>
        </form>

        <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a>.</p>
    </main>

    <footer class="main-footer">
        &copy; 2025 Ecologica Verde. Todos os direitos reservados. <br />
        <span class="creators">João Pedro, Vitor Machado, Jeremias Fagundes</span>
    </footer>

</body>

</html>
