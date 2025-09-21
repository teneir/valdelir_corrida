<?php
session_start(); // Inicia a sessão no topo do arquivo

$erro_login = '';

// --- LÓGICA DE LOGIN ATUALIZADA (CPF + PIN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cpf'])) {
    $cpf_post = $_POST['cpf'];
    $pin_post = $_POST['pin'];
    $numero_atleta_encontrado = null;

    // 1. Encontrar o número do atleta a partir do CPF em atletas.csv
    if (file_exists('atletas.csv')) {
        if (($handle = fopen('atletas.csv', 'r')) !== FALSE) {
            fgetcsv($handle, 1000, ";"); // Pular cabeçalho
            while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
                // A coluna do CPF é a de índice 4
                if (isset($dados[4]) && $dados[4] == $cpf_post) {
                    $numero_atleta_encontrado = $dados[0]; // A coluna do número é a de índice 0
                    break;
                }
            }
            fclose($handle);
        }
    }

    // 2. Se o atleta foi encontrado, validar o PIN em acessos.csv
    $acesso_validado = false;
    if ($numero_atleta_encontrado !== null) {
        if (file_exists('acessos.csv')) {
            if (($handle = fopen('acessos.csv', 'r')) !== FALSE) {
                fgetcsv($handle, 1000, ";"); // Pular cabeçalho
                while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    if ($dados[0] == $numero_atleta_encontrado && $dados[1] == $pin_post) {
                        $acesso_validado = true;
                        break;
                    }
                }
                fclose($handle);
            }
        }
    }

    if ($acesso_validado) {
        $_SESSION['atleta_numero'] = $numero_atleta_encontrado;
        header("Location: portal.php"); // Redireciona para a mesma página para limpar o POST
        exit;
    } else {
        $erro_login = "CPF ou PIN inválido. Por favor, tente novamente.";
    }
}

// --- O RESTANTE DO ARQUIVO PERMANECE IGUAL ---

// Se o usuário está logado, busca seus dados
$dados_atleta = null;
$dados_resultado = null;
if (isset($_SESSION['atleta_numero'])) {
    $numero_logado = $_SESSION['atleta_numero'];
    // ... (lógica para buscar dados do atleta e resultados continua a mesma) ...
    // Busca dados cadastrais
    if (($handle = fopen('atletas.csv', 'r')) !== FALSE) {
        while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if ($dados[0] == $numero_logado) {
                $dados_atleta = $dados;
                break;
            }
        }
        fclose($handle);
    }
    // Busca resultados (se o arquivo existir)
    if (file_exists('resultados.csv')) {
        if (($handle = fopen('resultados.csv', 'r')) !== FALSE) {
            while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($dados[0] == $numero_logado) {
                    $dados_resultado = $dados;
                    break;
                }
            }
            fclose($handle);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Atleta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>

<body>
<div class="container">
    <?php if ($dados_atleta): // Usuário está LOGADO ?>
        <div class="header">
            <h2>&#128373; Bem-vindo, <?php echo explode(' ', htmlspecialchars($dados_atleta[1]))[0]; ?>!</h2>
            <p>Seu número de inscrição é <strong><?php echo htmlspecialchars($dados_atleta[0]); ?></strong></p>
        </div>
        <h3>Seus Dados</h3>
        <ul>
            <li><strong>Nome:</strong> <?php echo htmlspecialchars($dados_atleta[1]); ?></li>
            <li><strong>Nascimento:</strong> <?php echo htmlspecialchars($dados_atleta[2]); ?></li>
            <li><strong>CPF:</strong> <?php echo htmlspecialchars($dados_atleta[4]); ?></li>
            <li><strong>Telefone:</strong> <?php echo htmlspecialchars($dados_atleta[5]); ?></li>
        </ul>
        <a href="editar.php">Editar Meus Dados</a>
        <hr style="margin: 2rem 0;">
        <h3>Resultado da Corrida</h3>
        <?php if ($dados_resultado): ?>
            <p><strong>Posição Geral:</strong> <?php echo htmlspecialchars($dados_resultado[1]); ?>º</p>
            <p><strong>Tempo Final:</strong> <?php echo htmlspecialchars($dados_resultado[2]); ?></p>
        <?php else: ?>
            <p>Os resultados da corrida ainda não foram divulgados. Volte mais tarde!</p>
        <?php endif; ?>
        <div class="footer-link">
            <a href="logout.php">Sair</a>
        </div>
    <?php else: // Usuário NÃO está logado, mostra formulário de login ATUALIZADO ?>
        <div class="header">
            <h2>&#128273; Portal do Atleta</h2>
            <p>Acesse seus dados e resultados da corrida.</p>
        </div>
        <form action="portal.php" method="post">
            <label for="cpf">Seu CPF:</label>
            <input type="text" id="cpf" name="cpf" required placeholder="Digite apenas os números">
            
            <label for="pin">Seu PIN de 4 dígitos:</label>
            <input type="text" name="pin" required maxlength="4" inputmode="numeric">
            
            <?php if ($erro_login): ?>
                <p style="color: red; text-align: center;"><?php echo $erro_login; ?></p>
            <?php endif; ?>

            <button type="submit">Entrar</button>
        </form>
        <script>
            // Adicionando a máscara de CPF também no formulário de login
            const inputCPF = document.getElementById('cpf');
            if(inputCPF) {
                inputCPF.addEventListener('input', (event) => {
                    let valor = event.target.value.replace(/\D/g, '');
                    valor = valor.substring(0, 11);
                    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                    valor = valor.replace(/(\d{3})(\d{2})$/, '$1-$2');
                    event.target.value = valor;
                });
            }
        </script>
    <?php endif; ?>
</div>
</body>
</html>