<?php
session_start();
require_once __DIR__ . '/../ConexaoEFuncoes/conexao.php';
require_once __DIR__ . '/../ConexaoEFuncoes/funcoes.php';

protegerPagina(); // Garante que só usuários logados acessem

// Define charset utf8mb4 para acentuação correta
mysqli_set_charset($conexao, 'utf8mb4');

$erro = '';
$titulo_val = '';
$noticia_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $noticia = trim($_POST['noticia'] ?? '');

    $titulo_val = htmlspecialchars($titulo); // para reexibir no input
    $noticia_val = $noticia; // conteúdo do TinyMCE, manter HTML

    if (empty($titulo)) {
        $erro = 'Informe o título da notícia.';
    } elseif (empty($noticia)) {
        $erro = 'Informe o conteúdo da notícia.';
    } else {
        $imagem_nome = '';

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
                    // Salva caminho relativo para o banco
                    $imagem_nome = "Uploads/" . $nomeFinal;
                } else {
                    $erro = "Erro ao salvar a imagem.";
                }
            }
        }

        if (!$erro) {
            // Prepare statement para inserir notícia com autor sendo o ID do usuário logado
            $stmt = $conexao->prepare("INSERT INTO noticias (titulo, noticia, data, autor, imagem) VALUES (?, ?, NOW(), ?, ?)");
            if (!$stmt) {
                die("Erro no prepare: " . $conexao->error);
            }

            // autor = id do usuário da sessão
            $stmt->bind_param("ssis", $titulo, $noticia, $_SESSION['usuario_id'], $imagem_nome);

            if (!$stmt->execute()) {
                die("Erro na execução: " . $stmt->error);
            }

            // Redireciona para dashboard após sucesso
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Nova Notícia - Ecológica Verde</title>

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
    <!-- Corrigido: link para logout com confirmação e redirecionamento correto -->
    <a href="../logout.php" onclick="return confirmarLogout(event)" class="btn-outline">Sair</a>
  </nav>
</header>

<main class="container">
  <h2>Nova Notícia</h2>

  <?php if ($erro): ?>
    <div class="mensagem-erro"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="form-noticia">
    <label for="titulo">Título:</label>
    <input type="text" name="titulo" id="titulo" required value="<?= $titulo_val ?>"/>

    <label for="noticia">Texto da Notícia:</label>
    <textarea name="noticia" id="noticia"><?= $noticia_val ?></textarea>

    <label for="imagem">Imagem da Notícia (opcional):</label>
    <input type="file" name="imagem" id="imagem" accept=".jpg,.jpeg,.png,.gif"/>

    <div class="botoes">
      <button type="submit" class="btn-salvar">Publicar</button>
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
  // Se confirmado, o link funcionará normalmente (logout.php será chamado)
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
