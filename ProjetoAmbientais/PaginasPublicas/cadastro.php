<?php
session_start();
require_once '../ConexaoEFuncoes/conexao.php'; // Ajuste o caminho conforme seu projeto

$usuario_logado = isset($_SESSION['usuario_id']);

$erro = '';
$sucesso = '';

// Valores padrão para preencher o formulário em caso de erro
$nome_val = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    $nome_val = htmlspecialchars($nome);
    $email_val = htmlspecialchars($email);

    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // Verificar se email já existe
        $email_esc = mysqli_real_escape_string($conexao, $email);
        $sql_check = "SELECT id FROM usuarios WHERE email = '$email_esc' LIMIT 1";
        $res_check = mysqli_query($conexao, $sql_check);

        if ($res_check && mysqli_num_rows($res_check) > 0) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            // Cadastrar usuário
            $nome_esc = mysqli_real_escape_string($conexao, $nome);
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $foto_padrao = './Extras/img/usuario.png';
            $banner_padrao = './Extras/img/banner_default.png';
            $bio_padrao = '';

            $sql_insert = "INSERT INTO usuarios (nome, email, senha, foto, banner, bio) 
               VALUES ('$nome_esc', '$email_esc', '$senha_hash', '$foto_padrao', '$banner_padrao', '$bio_padrao')";


            if (mysqli_query($conexao, $sql_insert)) {
                $sucesso = "Cadastro realizado com sucesso! Você já pode fazer login.";
                // Limpar valores do formulário após sucesso
                $nome_val = '';
                $email_val = '';
            } else {
                $erro = "Erro ao cadastrar usuário. Tente novamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Cadastro - Ecologica Verde</title>
    <link rel="stylesheet" href="../Extras/stylesCadastro.css" />
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
                <a href="dashboard.php" class="btn-outline">Perfil</a>
                <a href="nova_noticia.php" class="btn-outline">Publicar Nova Notícia</a>
                <a href="logout.php" class="btn-outline" onclick="confirmarLogout(event)">Sair</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <img src="../Extras/img/iconlogin.png" style="margin-top: -100px;" alt="Ícone de Cadastro" class="icon-login" />
        <h2>Cadastro de Usuário</h2>

        <?php if ($erro): ?>
            <div style="color: red; margin-bottom: 15px;"><?php echo $erro; ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div style="color: green; margin-bottom: 15px;"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <form action="cadastro.php" method="POST" class="form-cadastro" autocomplete="off">
            <label for="nome">Nome:</label>
            <input
                type="text"
                id="nome"
                name="nome"
                required
                placeholder="Digite seu nome completo"
                value="<?php echo $nome_val; ?>" />

            <label for="email">E-mail:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                placeholder="Digite seu e-mail"
                value="<?php echo $email_val; ?>" />

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required placeholder="Crie uma senha" />

            <label for="confirmar_senha">Confirmar Senha:</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Repita a senha" />

            <button type="submit" class="btn-outline">Cadastrar</button>
        </form>

        <p>Já tem uma conta? <a href="login.php">Faça login aqui</a>.</p>
    </main>

    <footer class="main-footer">
        &copy; 2025 Ecologica Verde. Todos os direitos reservados. <br />
        <span class="creators">João Pedro, Vitor Machado, Jeremias Fagundes</span>
    </footer>

    <script>
        function confirmarLogout(event) {
            if (!confirm("Deseja realmente sair?")) {
                event.preventDefault();
            }
        }
    </script>

</body>

</html>