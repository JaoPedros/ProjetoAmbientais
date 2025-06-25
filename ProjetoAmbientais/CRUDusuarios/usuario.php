<?php
// usuario.php
session_start();
require_once '../ConexaoEFuncoes/conexao.php'; // Ajuste o caminho

// Verifica se está logado (pode usar função se tiver)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../PaginasPublicas/login.php");
    exit;
}

// Consulta todos os usuários
$sql = "SELECT id, nome, email FROM usuarios ORDER BY nome ASC";
$resultado = mysqli_query($conexao, $sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Lista de Usuários</title>
    <style>
        table { border-collapse: collapse; width: 80%; margin: 20px auto; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        th { background-color: #a0cfa0; color: white; }
        a { color: #1b5e20; text-decoration: none; margin-right: 10px; }
        a:hover { text-decoration: underline; }
        body { font-family: Arial, sans-serif; }
        .container { width: 90%; margin: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Lista de Usuários</h2>
        <a href="../PaginasPublicas/dashboard.php">Voltar ao Dashboard</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($usuario = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>">Editar</a>
                                <a href="excluir_usuario.php?id=<?php echo $usuario['id']; ?>" onclick="return confirm('Deseja realmente excluir este usuário?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Nenhum usuário cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
