<?php
session_start();
require_once '../ConexaoEFuncoes/conexao.php';

$usuario_logado = isset($_SESSION['usuario_id']);
$id_usuario_logado = $usuario_logado ? $_SESSION['usuario_id'] : null;

// Verifica se foi passado um ID válido via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID da notícia inválido.");
}

$id = intval($_GET['id']);

// Consulta a notícia com o nome do autor
$sql = "SELECT noticias.*, usuarios.nome AS autor_nome 
        FROM noticias 
        INNER JOIN usuarios ON noticias.autor = usuarios.id 
        WHERE noticias.id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Notícia não encontrada.");
}

$noticia = $resultado->fetch_assoc();

// Consulta outras notícias para a sidebar
$sql_outras = "SELECT id, titulo FROM noticias WHERE id != ? ORDER BY data DESC LIMIT 5";
$stmt2 = $conexao->prepare($sql_outras);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$result_outras = $stmt2->get_result();

// Excluir comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_comentario_id']) && $usuario_logado) {
    $comentario_id = intval($_POST['excluir_comentario_id']);

    // Verifica se o comentário pertence ao usuário
    $verifica = $conexao->prepare("SELECT * FROM comentarios WHERE id = ? AND id_usuario = ?");
    $verifica->bind_param("ii", $comentario_id, $id_usuario_logado);
    $verifica->execute();
    $res = $verifica->get_result();

    if ($res->num_rows > 0) {
        $delete = $conexao->prepare("DELETE FROM comentarios WHERE id = ?");
        $delete->bind_param("i", $comentario_id);
        $delete->execute();
    }

    header("Location: noticia.php?id=$id");
    exit;
}

// Enviar novo comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario_logado && isset($_POST['comentario']) && trim($_POST['comentario']) !== '') {
    $comentario = trim($_POST['comentario']);

    $stmtInserir = $conexao->prepare("INSERT INTO comentarios (id_noticia, id_usuario, comentario, data) VALUES (?, ?, ?, NOW())");
    $stmtInserir->bind_param("iis", $id, $id_usuario_logado, $comentario);
    $stmtInserir->execute();

    header("Location: noticia.php?id=$id");
    exit;
}

// Comentários
$queryComentarios = "SELECT c.*, u.nome, u.foto FROM comentarios c INNER JOIN usuarios u ON c.id_usuario = u.id WHERE c.id_noticia = ? ORDER BY c.data DESC";
$stmtComentarios = $conexao->prepare($queryComentarios);
$stmtComentarios->bind_param("i", $id);
$stmtComentarios->execute();
$resultComentarios = $stmtComentarios->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($noticia['titulo']) ?> - Ecologica Verde</title>
    <link rel="stylesheet" href="../Extras/stylesNoticia.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <style>
        .sidebar-noticias a {
            text-decoration: none !important;
        }
        .texto-noticia p {
            margin: 10px 0;
            line-height: 1.6;
        }
        .conteudo-principal img {
            max-width: 100%;
            height: auto;
            margin: 20px 0;
        }
        .comentarios {
            margin-top: 40px;
        }
        .comentario {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .comentario img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        .comentario-conteudo {
            background: #f4f4f4;
            padding: 10px 15px;
            border-radius: 8px;
            width: 100%;
            position: relative;
        }
        .comentario-conteudo strong {
            color: #2e7d32;
        }
        .form-comentario {
            margin-top: 30px;
        }
        .form-comentario textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            resize: vertical;
        }
        .form-comentario button {
            margin-top: 10px;
            background: #2e7d32;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        .excluir-btn {
            position: absolute;
            top: 8px;
            right: 10px;
            background: none;
            border: none;
            color: red;
            font-size: 0.9rem;
            cursor: pointer;
        }
    </style>
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
            <a href="login.php" class="btn-outline">Login / Cadastro</a>
        <?php else: ?>
            <a href="../AreaRestrita/dashboard.php" class="btn-outline">Perfil</a>
            <a href="../AreaRestrita/nova_noticia.php" class="btn-outline">Publicar Nova Notícia</a>
            <a href="../PaginasPublicas/logout.php" onclick="confirmarLogout(event)" class="btn-outline">Sair</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <section class="conteudo-principal">
        <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
        <p class="autor-data">
            Publicado por <strong><?= htmlspecialchars($noticia['autor_nome']) ?></strong> 
            em <?= date("d/m/Y H:i", strtotime($noticia['data'])) ?>
        </p>

        <?php if (!empty($noticia['imagem'])): ?>
            <img src="../<?= htmlspecialchars($noticia['imagem']) ?>" alt="Imagem da Notícia" />
        <?php endif; ?>

        <div class="texto-noticia">
            <?= $noticia['noticia'] ?>
        </div>

        <div class="comentarios">
            <h3>Comentários</h3>
            <?php while ($c = $resultComentarios->fetch_assoc()): ?>
                <div class="comentario">
                    <img src="../<?= htmlspecialchars($c['foto']) ?>" alt="Usuário" />
                    <div class="comentario-conteudo">
                        <strong><?= htmlspecialchars($c['nome']) ?></strong><br>
                        <small><?= date("d/m/Y H:i", strtotime($c['data'])) ?></small>
                        <p><?= nl2br(htmlspecialchars($c['comentario'])) ?></p>

                        <?php if ($usuario_logado && $id_usuario_logado == $c['id_usuario']): ?>
                            <form method="POST" onsubmit="return confirm('Deseja excluir este comentário?')">
                                <input type="hidden" name="excluir_comentario_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="excluir-btn">Excluir</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if ($usuario_logado): ?>
                <form class="form-comentario" method="POST">
                    <textarea name="comentario" rows="3" placeholder="Deixe seu comentário..."></textarea>
                    <button type="submit">Comentar</button>
                </form>
            <?php else: ?>
                <p><a href="login.php">Faça login</a> para comentar.</p>
            <?php endif; ?>
        </div>

        <a href="../index.php" class="btn-outline">← Voltar para notícias</a>
    </section>

    <aside class="sidebar-noticias">
        <h3>Outras notícias</h3>
        <ul>
            <?php while ($linha = $result_outras->fetch_assoc()): ?>
                <li>
                    <a href="noticia.php?id=<?= $linha['id'] ?>">
                        <?= htmlspecialchars($linha['titulo']) ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </aside>
</main>

<footer class="main-footer">
    &copy; 2025 Ecologica Verde. Todos os direitos reservados. <br>
    <span class="creators">João Pedro, Vitor Machado, Jeremias Fagundes</span>
</footer>

<script>
function confirmarLogout(event) {
    if (!confirm("Tem certeza que deseja sair?")) {
        event.preventDefault();
    }
}
</script>

</body>
</html>
