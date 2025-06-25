<?php
// editar_usuario.php
session_start();
require_once '../ConexaoEFuncoes/conexao.php';

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../PaginasPublicas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Exibir formulário de edição

    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        echo "ID inválido.";
        exit;
    }

    // Busca usuário pelo id
    $sql = "SELECT id, nome, email FROM usuarios WHERE id = $id LIMIT 1";
    $resultado = mysqli_query($conexao, $sql);

    if (!$resultado || mysqli_num_rows($resultado) === 0) {
        echo "Usuário não encontrado.";
        exit;
    }

    $usuario = mysqli_fetch_assoc($resultado);
    ?>

    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8" />
        <title>Editar Usuário</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; }
            label { display: block; margin-top: 10px; }
            input { width: 100%; padding: 8px; margin-top: 5px; }
            button { margin-top: 15px; padding: 10px 20px; background-color: #a0cfa0; border: none; color: white; cursor: pointer; }
            button:hover { background-color: #85b885; }
        </style>
    </head>
    <body>
        <h2>Editar Usuário</h2>
        <form action="editar_usuario.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>" />
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($usuario['nome']); ?>" />

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($usuario['email']); ?>" />

            <button type="submit">Salvar Alterações</button>
        </form>
        <p><a href="usuario.php">Voltar à lista</a></p>
    </body>
    </html>

    <?php
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Processa atualização

    $id = intval($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($id <= 0 || empty($nome) || empty($email)) {
        echo "Dados inválidos.";
        exit;
    }

    // Escapa valores
    $nome_esc = mysqli_real_escape_string($conexao, $nome);
    $email_esc = mysqli_real_escape_string($conexao, $email);

    $sql = "UPDATE usuarios SET nome = '$nome_esc', email = '$email_esc' WHERE id = $id";

    if (mysqli_query($conexao, $sql)) {
        header("Location: usuario.php?msg=editado");
        exit;
    } else {
        echo "Erro ao atualizar usuário: " . mysqli_error($conexao);
    }
}
?>
