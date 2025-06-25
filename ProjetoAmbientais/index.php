<?php
session_start();
require_once './ConexaoEFuncoes/conexao.php';

$usuario_logado = isset($_SESSION['usuario_id']);

// Define a ordenação padrão para "mais vistas" (que agora vai ser todas as notícias)
$order = $_GET['order'] ?? 'recentes_desc';

// Define a cláusula ORDER BY conforme filtro escolhido
switch ($order) {
    case 'recentes_asc':
        $order_by = "n.data ASC";
        break;
    case 'titulo_asc':
        $order_by = "n.titulo ASC";
        break;
    case 'titulo_desc':
        $order_by = "n.titulo DESC";
        break;
    case 'recentes_desc':
    default:
        $order_by = "n.data DESC";
        break;
}

// Notícias mais recentes de todo o tempo (sem filtro de data)
$sql_recentes = "SELECT n.id, n.titulo, n.noticia, n.data, n.imagem, u.nome AS autor
                 FROM noticias n
                 JOIN usuarios u ON n.autor = u.id
                 ORDER BY n.data DESC";
$recentes = mysqli_query($conexao, $sql_recentes);

// Notícias "mais vistas" agora serão todas as notícias, com filtro de ordenação
$sql_mais_vistas = "SELECT n.id, n.titulo, n.noticia, n.data, n.imagem, u.nome AS autor
                    FROM noticias n
                    JOIN usuarios u ON n.autor = u.id
                    ORDER BY $order_by";
$mais_vistas = mysqli_query($conexao, $sql_mais_vistas);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Ecologica Verde - Portal de Notícias Ambientais</title>
    <link rel="stylesheet" href="./Extras/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* Remove underline dos links e títulos no carrossel e notícias mais vistas */
        .carrossel-item,
        .carrossel-item a,
        .carrossel-item h3,
        .noticias-mais-vistas,
        .noticias-mais-vistas a,
        .noticias-mais-vistas h4 {
            text-decoration: none !important;
        }

        /* Garantir que títulos dentro de links não fiquem sublinhados */
        .carrossel-item h3,
        .noticias-mais-vistas h4 {
            text-decoration: none;
        }

        /* Layout do filtro */
        .filtro-container {
            margin-bottom: 15px;
            text-align: right;
        }

        .filtro-container label {
            font-weight: bold;
            margin-right: 8px;
        }

        .filtro-select {
            padding: 5px 8px;
            font-size: 1rem;
        }
    </style>
</head>

<body>

    <header class="main-header">
        <div class="logo-container">
            <img src="./Extras/img/arvore.png" alt="Ícone Árvore" class="logo-icon">
            <img src="./Extras/img/ecologicaverde.png" alt="Logo Ecologica Verde" class="logo-text">
        </div>
        <nav class="header-buttons">
            <a href="#top" class="btn-outline">Início</a>
            <?php if (!$usuario_logado): ?>
                <a href="http://localhost/ProjetoAmbientais/PaginasPublicas/login.php" class="btn-outline">Login</a>
            <?php else: ?>
                <a href="/ProjetoAmbientais/AreaRestrita/dashboard.php" class="btn-outline">Perfil</a>
                <a href="AreaRestrita/nova_noticia.php" class="btn-outline">Publicar Nova Notícia</a>
                <a href="/ProjetoAmbientais/PaginasPublicas/logout.php" onclick="confirmarLogout(event)" class="btn-outline">Sair</a>
            <?php endif; ?>
        </nav>
    </header>

    <main id="top">
        <h1>Bem-vindo ao Portal Ecologica Verde</h1>

        <div class="container">
            <h2 class="secao-titulo">📰 Notícias mais recentes</h2>
            <div class="carrossel-container">
                <?php
                $i = 0;
                while ($noticia = mysqli_fetch_assoc($recentes)): ?>
                    <a href="http://localhost/ProjetoAmbientais/PaginasPublicas/noticia.php?id=<?= $noticia['id'] ?>" class="carrossel-item<?= $i === 0 ? ' ativo' : '' ?>">
                        <?php if (!empty($noticia['imagem'])): ?>
                            <img src="<?= htmlspecialchars($noticia['imagem']) ?>" alt="Imagem da notícia">
                        <?php endif; ?>
                        <div class="texto-noticia">
                            <h3><?= htmlspecialchars($noticia['titulo']) ?></h3>
                           <p><?= nl2br(html_entity_decode(strip_tags($noticia['noticia']))) ?></p>
                            <small><em>Por <?= htmlspecialchars($noticia['autor']) ?> em <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></em></small>
                        </div>
                    </a>
                <?php
                $i++;
                endwhile; ?>
            </div>

            <h2 class="secao-titulo" style="margin-top: 280px;">🔥 Notícias</h2>

            <div class="filtro-container">
                <form method="GET" id="formFiltro">
                    <label for="order">Ordenar por:</label>
                    <select name="order" id="order" class="filtro-select" onchange="document.getElementById('formFiltro').submit()">
                        <option value="recentes_desc" <?= $order === 'recentes_desc' ? 'selected' : '' ?>>Mais recentes → Mais antigas</option>
                        <option value="recentes_asc" <?= $order === 'recentes_asc' ? 'selected' : '' ?>>Mais antigas → Mais recentes</option>
                        <option value="titulo_asc" <?= $order === 'titulo_asc' ? 'selected' : '' ?>>Título A → Z</option>
                        <option value="titulo_desc" <?= $order === 'titulo_desc' ? 'selected' : '' ?>>Título Z → A</option>
                    </select>
                </form>
            </div>

            <div class="noticias-mais-vistas" style="margin-top: 20px;">
                <?php while ($noticia = mysqli_fetch_assoc($mais_vistas)): ?>
                    <a href="http://localhost/ProjetoAmbientais/PaginasPublicas/noticia.php?id=<?= $noticia['id'] ?>" class="card-noticia" style="text-decoration: none; color: inherit;">
                        <h4><?= htmlspecialchars($noticia['titulo']) ?></h4>
                        <?php if (!empty($noticia['imagem'])): ?>
                            <img src="<?= htmlspecialchars($noticia['imagem']) ?>" alt="Imagem" style="max-width: 100%; height: auto;">
                        <?php endif; ?>
                        <p><?= substr(html_entity_decode(strip_tags($noticia['noticia'])), 0, 100) ?>...</p>
                        <small><em>Por <?= htmlspecialchars($noticia['autor']) ?> em <?= date('d/m/Y', strtotime($noticia['data'])) ?></em></small>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <script>
        const items = document.querySelectorAll(".carrossel-item");
        let indexAtual = 0;
        let indexProximo = 1;
        let animando = false;

        function mostrarProximo() {
            if (animando || items.length < 2) return;
            animando = true;

            const atual = items[indexAtual];
            const proximo = items[indexProximo];

            proximo.classList.add('entrando');
            proximo.style.display = 'flex';

            atual.classList.add('saiendo');

            setTimeout(() => {
                atual.classList.remove('ativo', 'saiendo');
                atual.style.display = 'none';

                proximo.classList.remove('entrando');
                proximo.classList.add('ativo');

                indexAtual = indexProximo;
                indexProximo = (indexProximo + 1) % items.length;

                animando = false;
            }, 600);
        }

        if (items.length > 0) {
            items[0].classList.add('ativo');
            items[0].style.display = 'flex';
        }

        setInterval(mostrarProximo, 10000);

        function confirmarLogout(event) {
            event.preventDefault();
            if (confirm("Tem certeza que deseja sair?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
    <footer class="main-footer">
        &copy; 2025 Ecologica Verde. Todos os direitos reservados. <br>
        <span class="creators">João Pedro, Vitor Machado, Jeremias Fagundes</span>
    </footer>

</body>

</html>
