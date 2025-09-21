<?php
session_start();
if (!isset($_SESSION['atleta_numero']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: portal.php");
    exit;
}

$numero_logado = $_SESSION['atleta_numero'];
$novo_nome = $_POST['nome'];
$novo_telefone = $_POST['telefone'];

$linhas = file('atletas.csv');
$arquivo_atualizado = fopen('atletas.csv', 'w');

foreach ($linhas as $i => $linha) {
    $dados = str_getcsv($linha, ";");
    if ($dados[0] == $numero_logado) {
        // Modifica os dados desta linha
        $dados[1] = $novo_nome;
        $dados[5] = $novo_telefone;
        // Reescreve a linha no formato CSV
        fputcsv($arquivo_atualizado, $dados, ";");
    } else {
        // Escreve a linha original
        fwrite($arquivo_atualizado, $linha);
    }
}
fclose($arquivo_atualizado);

header("Location: portal.php?sucesso=1"); // Volta ao portal com mensagem de sucesso
exit;
?>