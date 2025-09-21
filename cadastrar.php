<?php

/**
 * Função para exibir uma página HTML com estilo consistente.
 * @param string $titulo O título da página.
 * @param string $conteudo_html O conteúdo HTML a ser exibido dentro do container.
 * @param string $tipo_classe Classe CSS adicional para o container (ex: 'erro').
 */
function exibirPagina($titulo, $conteudo_html, $tipo_classe = '') {
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$titulo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #0d6efd; --bg-color: #f8f9fa; --card-bg-color: #ffffff; --text-color: #212529; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--bg-color); color: var(--text-color); margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; text-align: center; }
        .container { background-color: var(--card-bg-color); padding: 2.5rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); max-width: 500px; width: 100%; }
        h1 { color: var(--primary-color); margin-bottom: 1rem; }
        p { margin-bottom: 1.5rem; line-height: 1.6; }
        .numero-atleta { font-size: 3.5rem; font-weight: 700; color: #198754; margin: 1rem 0; }
        a { background-color: var(--primary-color); color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 4px; transition: background-color 0.2s; }
        a:hover { background-color: #0b5ed7; }
        .erro h1 { color: #dc3545; }
        .erro p strong { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container $tipo_classe">
        $conteudo_html
    </div>
</body>
</html>
HTML;
}

$arquivo_csv = 'atletas.csv';

/**
 * Verifica se um CPF já está cadastrado no arquivo.
 * @param string $cpf O CPF a ser verificado.
 * @param string $arquivo O caminho para o arquivo CSV.
 * @return bool Retorna true se o CPF já existir, false caso contrário.
 */
function cpfJaCadastrado($cpf, $arquivo) {
    if (!file_exists($arquivo)) {
        return false;
    }

    if (($handle = fopen($arquivo, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ";"); // Pula o cabeçalho
        while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
            // A coluna do CPF é a de índice 4 (Numero;Nome;Nascimento;Sexo;CPF;...)
            if (isset($dados[4]) && $dados[4] == $cpf) {
                fclose($handle);
                return true; // CPF encontrado
            }
        }
        fclose($handle);
    }
    return false; // CPF não encontrado
}

/**
 * Função para encontrar o próximo número de atleta disponível.
 * (Esta função permanece a mesma)
 */
function getProximoNumero($arquivo) {
    if (!file_exists($arquivo) || filesize($arquivo) === 0) { return 1; }
    $maior_numero = 0;
    if (($handle = fopen($arquivo, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ";");
        while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if (isset($dados[0]) && is_numeric($dados[0])) {
                if ((int)$dados[0] > $maior_numero) { $maior_numero = (int)$dados[0]; }
            }
        }
        fclose($handle);
    }
    return $maior_numero + 1;
}

// --- FLUXO PRINCIPAL DO SCRIPT ---

// 1. Recebe os dados do formulário
$nome = htmlspecialchars($_POST['nome']);
$nascimento = htmlspecialchars($_POST['nascimento']);
$sexo = htmlspecialchars($_POST['sexo']);
$cpf = htmlspecialchars($_POST['cpf']);
$telefone = htmlspecialchars($_POST['telefone']);
$pin = htmlspecialchars($_POST['pin']); // Recebe o PIN

// Valida o PIN (mesmo que o HTML já faça isso)
if (!preg_match('/^\d{4}$/', $pin)) {
    // Exibe página de erro se o PIN for inválido
    exibirPagina("Erro no PIN", "<h1>&#10060; PIN Inválido</h1><p>O PIN deve conter exatamente 4 números.</p><a href='index.php'>Voltar</a>", 'erro');
    exit;
}


// 2. VERIFICA SE O CPF JÁ ESTÁ CADASTRADO
if (cpfJaCadastrado($cpf, $arquivo_csv)) {
    $titulo_pagina = "Erro na Inscrição";
    $conteudo = '<div class="erro"><h1>&#9888; CPF já Cadastrado</h1>
                 <p>O CPF <strong>' . $cpf . '</strong> já foi utilizado em uma inscrição anterior.</p>
                 <p>Cada atleta pode se inscrever apenas uma vez.</p>
                 <a href="index.php">Voltar ao formulário</a></div>';
    exibirPagina($titulo_pagina, $conteudo, 'erro');
    exit; // Para a execução do script
}

// 3. Verifica se ainda há vagas
$numero_atleta = getProximoNumero($arquivo_csv);
if ($numero_atleta > 100) {
    $titulo_pagina = "Vagas Esgotadas";
    $conteudo = '<div class="erro"><h1>&#128683; Vagas Esgotadas!</h1>
                 <p>Desculpe, o limite de 100 atletas para esta competição já foi atingido.</p>
                 <a href="index.php">Voltar</a></div>';
    exibirPagina($titulo_pagina, $conteudo, 'erro');
    exit;
}

// 4. Se passou pelas validações, prossegue com o cadastro
$numero_formatado = str_pad($numero_atleta, 3, "0", STR_PAD_LEFT);

$cabecalho = "Numero;Nome;Nascimento;Sexo;CPF;Telefone\n";
if (!file_exists($arquivo_csv)) {
    file_put_contents($arquivo_csv, $cabecalho);
}

$linha = "{$numero_formatado};{$nome};{$nascimento};{$sexo};{$cpf};{$telefone}\n";
file_put_contents($arquivo_csv, $linha, FILE_APPEND);

// --- NOVO: Salva os dados de acesso em acessos.csv ---
$arquivo_acessos = 'acessos.csv';
$linha_acesso = "{$numero_formatado};{$pin}\n";

// Cria o cabeçalho se o arquivo não existir
if (!file_exists($arquivo_acessos)) {
    file_put_contents($arquivo_acessos, "Numero;PIN\n");
}
// Adiciona a nova linha de acesso
file_put_contents($arquivo_acessos, $linha_acesso, FILE_APPEND);

// 5. Exibe a página de sucesso
$titulo_pagina = "Inscrição Realizada!";
$conteudo = '<h1>&#9989; Inscrição Realizada com Sucesso!</h1>
             <p>Parabéns, <strong>' . $nome . '</strong>. Seu número de competição é:</p>
             <div class="numero-atleta">' . $numero_formatado . '</div>
             <a href="index.php">Realizar Nova Inscrição</a>';
exibirPagina($titulo_pagina, $conteudo);

?>