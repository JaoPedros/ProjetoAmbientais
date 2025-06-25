<?php
session_start();
require_once __DIR__ . '/../ConexaoEFuncoes/conexao.php';
require_once __DIR__ . '/../ConexaoEFuncoes/funcoes.php';

protegerPagina(); // Garante que está logado

$usuarioId = $_SESSION['usuario_id'] ?? null;
$noticiaId = $_GET['id'] ?? null;

if (!$usuarioId || !$noticiaId) {
    // Redireciona para dashboard se faltar dados
    header("Location: dashboard.php");
    exit;
}

// Primeiro, verificar se a notícia pertence a esse usuário
$stmt = $conexao->prepare("SELECT imagem FROM noticias WHERE id = ? AND autor = ?");
$stmt->bind_param("ii", $noticiaId, $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Notícia não encontrada ou não pertence ao usuário
    header("Location: dashboard.php?msg=noticia_nao_encontrada");
    exit;
}

$noticia = $result->fetch_assoc();

// Se existir imagem, apagar o arquivo físico
if (!empty($noticia['imagem'])) {
    $caminhoImagem = __DIR__ . '/../' . $noticia['imagem'];
    if (file_exists($caminhoImagem)) {
        unlink($caminhoImagem);
    }
}

// Agora, excluir a notícia do banco
$stmtDel = $conexao->prepare("DELETE FROM noticias WHERE id = ? AND autor = ?");
$stmtDel->bind_param("ii", $noticiaId, $usuarioId);
$stmtDel->execute();

// Redireciona para dashboard com mensagem de sucesso
header("Location: dashboard.php?msg=noticia_excluida");
exit;
?>
