<?php
session_start();
if (!isset($_SESSION['atleta_numero'])) {
    header("Location: portal.php"); // Se não estiver logado, volta para o portal
    exit;
}

// Busca dados atuais para preencher o formulário
$dados_atleta = null;
if (($handle = fopen('atletas.csv', 'r')) !== FALSE) {
    while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if ($dados[0] == $_SESSION['atleta_numero']) {
            $dados_atleta = $dados;
            break;
        }
    }
    fclose($handle);
}

if ($dados_atleta === null) die("Erro: Atleta não encontrado.");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Dados</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>

<body>
<div class="container">
    <h2>Editar Meus Dados</h2>
    <form action="salvar_edicao.php" method="post">
        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($dados_atleta[1]); ?>" required>
        
        <label>Telefone:</label>
        <input type="tel" name="telefone" value="<?php echo htmlspecialchars($dados_atleta[5]); ?>" required>

        <button type="submit">Salvar Alterações</button>
        <a href="portal.php" style="display: block; text-align: center; margin-top: 1rem;">Cancelar</a>
    </form>
</div>
</body>
</html>