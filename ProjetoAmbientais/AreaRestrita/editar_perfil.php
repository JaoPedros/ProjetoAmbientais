<?php
session_start();
require_once __DIR__ . '/../ConexaoEFuncoes/conexao.php';
require_once __DIR__ . '/../ConexaoEFuncoes/funcoes.php';

protegerPagina();

$usuarioId = $_SESSION['usuario_id'];

$sql = "SELECT nome, bio, foto, banner FROM usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
  die("Usuário não encontrado.");
}

$usuarioId = $_SESSION['usuario_id'];

$defaultFoto = '/ProjetoAmbientais/Extras/img/usuario.png';
$defaultBanner = '/ProjetoAmbientais/Extras/img/banner_default.png';

$fotoPathRelativo = 'Extras/uploads/fotos/usuario_' . $usuarioId . '.jpg';
$bannerPathRelativo = 'Extras/uploads/banners/banner_' . $usuarioId . '.jpg';

$caminhoFotoAbsoluto = __DIR__ . '/../' . $fotoPathRelativo;
$caminhoBannerAbsoluto = __DIR__ . '/../' . $bannerPathRelativo;

$foto_usuario = file_exists($caminhoFotoAbsoluto) ? '/ProjetoAmbientais/' . $fotoPathRelativo : $defaultFoto;
$banner_usuario = file_exists($caminhoBannerAbsoluto) ? '/ProjetoAmbientais/' . $bannerPathRelativo : $defaultBanner;


//var_dump($foto_usuario, $banner_usuario);
//exit();

$erro = '';
$sucesso = '';

function uploadImagem($campo, $pastaDestinoRelativa, $nomeAntigoRelativo = null)
{
  if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
    // Nenhum arquivo enviado - sem erro, retorna sucesso falso
    return ['sucesso' => false];
  }

  if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
    return ['erro' => "Erro no upload do arquivo '$campo': código " . $_FILES[$campo]['error']];
  }

  $nomeTmp = $_FILES[$campo]['tmp_name'];
  if (!file_exists($nomeTmp)) {
    return ['erro' => "Arquivo temporário '$nomeTmp' do campo '$campo' não existe."];
  }

  $nomeOriginal = basename($_FILES[$campo]['name']);
  $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
  $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];

  if (!in_array($extensao, $extensoesPermitidas)) {
    return ['erro' => "Formato da imagem não permitido para $campo. Apenas JPG, PNG, GIF."];
  }

  $pastaDestinoAbsoluta = __DIR__ . '/../' . trim($pastaDestinoRelativa, '/');

  if (!is_dir($pastaDestinoAbsoluta)) {
    if (!mkdir($pastaDestinoAbsoluta, 0755, true)) {
      return ['erro' => "Não foi possível criar a pasta: $pastaDestinoAbsoluta"];
    }
  }

  if (!is_writable($pastaDestinoAbsoluta)) {
    return ['erro' => "A pasta '$pastaDestinoAbsoluta' não tem permissão de escrita para o PHP."];
  }

  $novoNome = uniqid() . '.' . $extensao;
  $destinoAbsoluto = $pastaDestinoAbsoluta . '/' . $novoNome;
  $destinoRelativo = $pastaDestinoRelativa . '/' . $novoNome;

  if (!move_uploaded_file($nomeTmp, $destinoAbsoluto)) {
    return ['erro' => "Erro ao mover o arquivo para '$destinoAbsoluto'."];
  }

  // Apaga imagem antiga, exceto imagens padrão
  if (
    $nomeAntigoRelativo
    && file_exists(__DIR__ . '/../' . $nomeAntigoRelativo)
    && !str_ends_with($nomeAntigoRelativo, 'usuario.png')
    && !str_ends_with($nomeAntigoRelativo, 'banner_default.jpg')
  ) {
    unlink(__DIR__ . '/../' . $nomeAntigoRelativo);
  }

  return ['sucesso' => true, 'caminho' => $destinoRelativo];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = trim($_POST['nome'] ?? '');
  $bio = trim($_POST['bio'] ?? '');

  if (strlen($bio) > 255) {
    $erro = "A bio deve ter no máximo 255 caracteres.";
  } elseif (empty($nome)) {
    $erro = "O nome não pode ficar vazio.";
  } else {
    $uploadFoto = uploadImagem('foto', './Extras/uploads/fotos', $usuario['foto']);
    $uploadBanner = uploadImagem('banner', './Extras/uploads/banners', $usuario['banner']);

    if (isset($uploadFoto['erro'])) {
      $erro = $uploadFoto['erro'];
    } elseif (isset($uploadBanner['erro'])) {
      $erro = $uploadBanner['erro'];
    }

    if (!$erro) {
      $fotoAtualizada = $uploadFoto['sucesso'] ? $uploadFoto['caminho'] : $usuario['foto'];
      $bannerAtualizado = $uploadBanner['sucesso'] ? $uploadBanner['caminho'] : $usuario['banner'];
      $sql_update = "UPDATE usuarios SET nome = ?, bio = ?, foto = ?, banner = ? WHERE id = ?";
      $stmt_update = $conexao->prepare($sql_update);
      $stmt_update->bind_param('ssssi', $nome, $bio, $fotoAtualizada, $bannerAtualizado, $usuarioId);

      if ($stmt_update->execute()) {
        $sucesso = "Perfil atualizado com sucesso!";
        // Atualiza as variáveis para refletir a mudança na tela
        $usuario['nome'] = $nome;
        $usuario['bio'] = $bio;
        $usuario['foto'] = $fotoAtualizada;
        $usuario['banner'] = $bannerAtualizado;

        $foto_usuario = '.' . ltrim($fotoAtualizada, '/');
        $banner_usuario = '.' . ltrim($bannerAtualizado, '/');
      } else {
        $erro = "Erro ao atualizar o perfil.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <title>Editar Perfil - Ecológica Verde</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../Extras/stylesDashboard.css" />
  <link rel="stylesheet" href="../Extras/stylesEditarP.css" />
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
      <a href="../PaginasPublicas/logout.php" onclick="return confirm('Tem certeza que deseja sair?')" class="btn-outline">Sair</a>
    </nav>
  </header>

  <section class="perfil-banner">
    <div class="espaco-banner" style="background-image: url('<?= htmlspecialchars($banner_usuario) ?>');"></div>
    <div class="foto-perfil" style="background-image: url('<?= htmlspecialchars($foto_usuario) ?>');"></div>
  </section>


  <main class="container form-container">
    <h2>Editar Perfil</h2>

    <?php if ($erro): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
      <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <form action="editar_perfil.php" method="POST" enctype="multipart/form-data" autocomplete="off">
      <div class="mb-3">
        <label for="nome" class="form-label">Nome:</label>
        <input type="text" id="nome" name="nome" class="form-control" required value="<?= htmlspecialchars($usuario['nome']) ?>" />
      </div>

      <div class="mb-3">
        <label for="bio" class="form-label">Bio (máximo 255 caracteres):</label>
        <textarea id="bio" name="bio" class="form-control" maxlength="255"><?= htmlspecialchars($usuario['bio']) ?></textarea>
      </div>

      <div class="mb-3">
        <label for="foto" class="form-label">Nova Foto do Perfil (jpg, png, gif):</label>
        <input type="file" id="foto" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.gif" />
      </div>

      <div class="mb-3">
        <label for="banner" class="form-label">Novo Banner do Perfil (jpg, png, gif):</label>
        <input type="file" id="banner" name="banner" class="form-control" accept=".jpg,.jpeg,.png,.gif" />
      </div>

      <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
  </main>

  <footer class="main-footer text-center mt-5">
    &copy; 2025 Ecológica Verde. Todos os direitos reservados. <br />
    <span class="creators">João Pedro, Vitor Machado, Jeremias Fagundes</span>
  </footer>

</body>

</html>