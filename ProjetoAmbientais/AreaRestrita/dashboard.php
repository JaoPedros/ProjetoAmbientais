<?php
session_start();
require_once __DIR__ . '/../ConexaoEFuncoes/conexao.php';
require_once __DIR__ . '/../ConexaoEFuncoes/funcoes.php';

protegerPagina();

$usuarioId = $_SESSION['usuario_id'];

// Buscar dados do usuário
$sqlUsuario = "SELECT nome, bio, foto, banner FROM usuarios WHERE id = ?";
$stmt = $conexao->prepare($sqlUsuario);
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$resultUsuario = $stmt->get_result();
$usuario = $resultUsuario->fetch_assoc();

if (!$usuario) {
    die("Usuário não encontrado.");
}

// Caminhos padrão
$defaultFoto = '/ProjetoAmbientais/Extras/img/usuario.png';
$defaultBanner = '/ProjetoAmbientais/Extras/img/banner_default.jpg';

// Usa caminho salvo no banco ou o padrão
$foto_usuario = !empty($usuario['foto']) ? '.' . ltrim($usuario['foto'], '/') : $defaultFoto;
$banner_usuario = !empty($usuario['banner']) ? '.' . ltrim($usuario['banner'], '/') : $defaultBanner;

// Buscar notícias do usuário
$sqlNoticias = "SELECT * FROM noticias WHERE autor = ? ORDER BY data DESC";
$stmtNoticias = $conexao->prepare($sqlNoticias);
$stmtNoticias->bind_param('i', $usuarioId);
$stmtNoticias->execute();
$resultNoticias = $stmtNoticias->get_result();
$noticias = $resultNoticias->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - Ecológica Verde</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../Extras/stylesDashboard.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
</head>
<body>

<header class="main-header">
  <div class="logo-container">
    <img src="../Extras/img/arvore.png" alt="Ícone Árvore" class="logo-icon" />
    <img src="../Extras/img/ecologicaverde.png" alt="Logo Ecológica Verde" class="logo-text" />
  </div>
  <nav class="header-buttons">
    <a href="../index.php" class="btn-outline">Início</a>
    <a href="dashboard.php" class="btn-outline">Perfil</a>
    <a href="nova_noticia.php" class="btn-outline">Publicar Nova Notícia</a>
    <a href="/ProjetoAmbientais/logout.php" onclick="confirmarLogout(event)" class="btn-outline">Sair</a>
  </nav>
</header>

<!-- Banner e foto de perfil -->
<section class="perfil-banner">
  <div class="espaco-banner" style="background-image: url('<?= htmlspecialchars($banner_usuario) ?>');">
    <div class="foto-perfil" style="background-image: url('<?= htmlspecialchars($foto_usuario) ?>');"></div>
  </div>
</section>

<!-- Bio -->
<section class="bio-usuario">
  <div class="conteudo-bio">
    <h3>Sobre Mim</h3>
    <p><?= nl2br(htmlspecialchars($usuario['bio'] ?: 'Este é um espaço reservado para a biografia do usuário. Em breve será possível editar esta descrição no perfil.')) ?></p>
  </div>
</section>

<main class="container py-4">
  <h2 class="mb-4">Minha Área</h2>
  <a href="nova_noticia.php" class="btn btn-success mb-3 me-2">➕ Nova Notícia</a>
  <a href="editar_perfil.php" class="btn btn-primary mb-3">✏️ Editar Perfil</a>

  <?php if (count($noticias) === 0): ?>
    <p>Você ainda não publicou nenhuma notícia.</p>
  <?php else: ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Título</th>
          <th>Data</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($noticias as $noticia): ?>
          <tr>
            <td><?= htmlspecialchars($noticia['titulo']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></td>
            <td>
              <a href="../PaginasPublicas/noticia.php?id=<?= $noticia['id'] ?>" class="btn btn-info btn-sm">Ver</a>
              <a href="editar_noticia.php?id=<?= $noticia['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
              <a href="excluir_noticia.php?id=<?= $noticia['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir esta notícia?')">Excluir</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</main>

<footer class="main-footer text-center">
  &copy; 2025 Ecológica Verde. Todos os direitos reservados. <br />
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
