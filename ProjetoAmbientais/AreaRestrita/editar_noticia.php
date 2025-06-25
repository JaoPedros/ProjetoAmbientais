<?php
session_start();
require_once __DIR__ . '/../ConexaoEFuncoes/conexao.php';
require_once __DIR__ . '/../ConexaoEFuncoes/funcoes.php';

protegerPagina();

$usuarioId = $_SESSION['usuario_id'];

// Define charset utf8mb4 para acentuação correta
mysqli_set_charset($conexao, 'utf8mb4');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID da notícia inválido.");
}

$noticiaId = (int)$_GET['id'];

// Busca dados da notícia e valida autor
$sql = "SELECT * FROM noticias WHERE id = ? AND autor = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param('ii', $noticiaId, $usuarioId);
$stmt->execute();
$result = $stmt->get_result();
$noticia = $result->fetch_assoc();

if (!$noticia) {
    die("Notícia não encontrada ou você não tem permissão para editar esta notícia.");
}

$erro = '';
$sucesso = '';

$titulo_val = $noticia['titulo'];
$noticia_val = $noticia['noticia'];
$imagem_atual = $noticia['imagem']; // caminho relativo salvo no banco, pode estar vazio

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['noticia'] ?? '');

    $titulo_val = htmlspecialchars($titulo);
    $noticia_val = $conteudo; // mantém HTML do TinyMCE

    if (empty($titulo)) {
        $erro = 'Informe o título da notícia.';
    } elseif (empty($conteudo)) {
        $erro = 'Informe o conteúdo da notícia.';
    } else {
        // Processa imagem (se nova enviada)
        $imagem_nome = $imagem_atual; // padrão: mantém a atual

        if (!empty($_FILES['imagem']['name'])) {
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            $nomeArquivo = $_FILES['imagem']['name'];
            $ext = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

            if (!in_array($ext, $extensoes_permitidas)) {
                $erro = 'Formato de imagem inválido. Use JPG, PNG ou GIF.';
            } else {
                $nomeFinal = uniqid('img_') . "." . $ext;
                $caminhoDestino = __DIR__ . "/../Uploads/" . $nomeFinal;

                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoDestino)) {
                    // Apaga imagem antiga se existir e não for padrão
                    if (!empty($imagem_atual) && file_exists(__DIR__ . '/../' . $imagem_atual)) {
                        unlink(__DIR__ . '/../' . $imagem_atual);
                    }
                    // Atualiza o caminho da imagem no banco
                    $imagem_nome = "Uploads/" . $nomeFinal;
                } else {
                    $erro = "Erro ao salvar a imagem.";
                }
            }
        }

        if (!$erro) {
            // Atualiza notícia no banco
            $sqlUpdate = "UPDATE noticias SET titulo = ?, noticia = ?, imagem = ?, data = NOW() WHERE id = ? AND autor = ?";
            $stmtUpdate = $conexao->prepare($sqlUpdate);
            $stmtUpdate->bind_param('sssii', $titulo, $conteudo, $imagem_nome, $noticiaId, $usuarioId);
            if ($stmtUpdate->execute()) {
                $sucesso = "Notícia atualizada com sucesso!";
                $titulo_val = htmlspecialchars($titulo);
                $noticia_val = $conteudo;
                $imagem_atual = $imagem_nome;
            } else {
                $erro = "Erro ao atualizar a notícia.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Editar Notícia - Ecológica Verde</title>

  <link rel="stylesheet" href="../Extras/stylesNovaNtc.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />

  <!-- TinyMCE -->
  <script src="https://cdn.tiny.cloud/1/pgcpkaetffwqynak9aqoe2iu5q9feyv8ud0921j1ks230qcg/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    tinymce.init({
      selector: '#noticia',
      menubar: false,
      toolbar: 'undo redo | bold italic underline | bullist numlist | removeformat',
      height: 300,
      branding: false,
      content_style: "body { font-family:Inter,sans-serif; font-size:16px; }"
    });
  </script>
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
    <a href="nova_noticia.php" class="btn-outline">Nova Notícia</a>
    <a href="../logout.php" onclick="return confirmarLogout(event)" class="btn-outline">Sair</a>
  </nav>
</header>

<main class="container">
  <h2>Editar Notícia</h2>

  <?php if ($erro): ?>
    <div class="mensagem-erro"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <?php if ($sucesso): ?>
    <div class="mensagem-sucesso"><?= htmlspecialchars($sucesso) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="form-noticia">
    <label for="titulo">Título:</label>
    <input type="text" name="titulo" id="titulo" required value="<?= $titulo_val ?>" />

    <label for="noticia">Texto da Notícia:</label>
    <textarea name="noticia" id="noticia"><?= $noticia_val ?></textarea>

    <label for="imagem">Imagem da Notícia (opcional):</label>
    <?php if (!empty($imagem_atual) && file_exists(__DIR__ . '/../' . $imagem_atual)): ?>
      <div style="margin-bottom:10px;">
        <img src="../<?= htmlspecialchars($imagem_atual) ?>" alt="Imagem atual" style="max-width: 300px; max-height: 150px; display: block; margin-bottom: 5px; border-radius: 5px;" />
        <small>Imagem atual</small>
      </div>
    <?php endif; ?>
    <input type="file" name="imagem" id="imagem" accept=".jpg,.jpeg,.png,.gif" />

    <div class="botoes">
      <button type="submit" class="btn-salvar">Salvar Alterações</button>
      <a href="dashboard.php" class="btn-cancelar">Cancelar</a>
      <button type="button" onclick="limparFormulario()" class="btn-limpar">Limpar Tudo</button>
    </div>
  </form>
</main>

<footer class="main-footer">
  &copy; 2025 Ecológica Verde. Todos os direitos reservados. <br />
  <span class="creators">João Pedro, Vitor Machado, Jeremias Fagundes</span>
</footer>

<script>
function confirmarLogout(event) {
  if (!confirm("Deseja realmente sair?")) {
    event.preventDefault();
    return false;
  }
  return true;
}

function limparFormulario() {
  document.getElementById('titulo').value = '';
  tinymce.get('noticia').setContent('');
  document.getElementById('imagem').value = '';
}
</script>

</body>
</html>
