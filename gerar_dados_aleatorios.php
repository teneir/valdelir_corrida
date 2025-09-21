<?php

// --- CONFIGURAÇÃO ---
$quantidade_a_gerar = 50; // Altere este número para gerar mais ou menos atletas.

// --- BANCO DE DADOS PARA GERAÇÃO ---
$nomes_masculinos = ['Lucas', 'Gabriel', 'Pedro', 'Matheus', 'Enzo', 'Guilherme', 'Rafael', 'Felipe', 'Gustavo', 'Leonardo', 'João', 'Daniel', 'Bruno', 'Eduardo', 'Vinicius'];
$nomes_femininos = ['Julia', 'Sophia', 'Isabella', 'Maria', 'Alice', 'Laura', 'Manuela', 'Valentina', 'Helena', 'Luiza', 'Beatriz', 'Mariana', 'Ana', 'Gabriela', 'Yasmin'];
$sobrenomes = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira', 'Lima', 'Gomes', 'Costa', 'Ribeiro', 'Martins', 'Carvalho', 'Almeida'];

// --- FUNÇÕES AUXILIARES ---

/**
 * Gera um CPF no formato XXX.XXX.XXX-XX.
 * (Apenas para teste, não é um CPF matematicamente válido)
 */
function gerarCPF() {
    return sprintf('%03d.%03d.%03d-%02d', mt_rand(1, 999), mt_rand(1, 999), mt_rand(1, 999), mt_rand(1, 99));
}

/**
 * Gera um Telefone no formato (XX) 9XXXX-XXXX.
 */
function gerarTelefone() {
    return sprintf('(%02d) 9%04d-%04d', mt_rand(11, 99), mt_rand(1, 9999), mt_rand(1, 9999));
}

/**
 * Gera uma data de nascimento que resulta em uma idade entre 15 e 79 anos.
 */
function gerarDataNascimento() {
    $idade_alvo = mt_rand(15, 79);
    $ano_atual = date('Y');
    $mes_atual = date('m');
    $dia_atual = date('d');

    $ano_nascimento = $ano_atual - $idade_alvo;

    // Gera uma data aleatória no ano de nascimento alvo
    $timestamp_nascimento = mt_rand(strtotime("$ano_nascimento-01-01"), strtotime("$ano_nascimento-12-31"));
    
    // Garante que o aniversário já passou este ano, se não, subtrai um ano da idade
    if (date('m-d', $timestamp_nascimento) > "$mes_atual-$dia_atual") {
        $timestamp_nascimento = strtotime("-1 year", $timestamp_nascimento);
    }
    
    return date('d/m/Y', $timestamp_nascimento);
}

/**
 * Gera um PIN de 4 dígitos.
 */
function gerarPIN() {
    return sprintf('%04d', mt_rand(0, 9999));
}

/**
 * Encontra o próximo número de atleta disponível.
 */
function getProximoNumero($arquivo) {
    if (!file_exists($arquivo) || filesize($arquivo) === 0) {
        return 1;
    }
    $maior_numero = 0;
    if (($handle = fopen($arquivo, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ";");
        while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if (isset($dados[0]) && is_numeric($dados[0])) {
                if ((int)$dados[0] > $maior_numero) {
                    $maior_numero = (int)$dados[0];
                }
            }
        }
        fclose($handle);
    }
    return $maior_numero + 1;
}

// --- EXECUÇÃO DO SCRIPT ---

header('Content-Type: text/plain; charset=utf-8');
echo "--- Iniciando Geração de Dados de Teste ---\n\n";

$arquivo_atletas = 'atletas.csv';
$arquivo_acessos = 'acessos.csv';

$proximo_numero_disponivel = getProximoNumero($arquivo_atletas);
echo "Próximo número de atleta disponível: $proximo_numero_disponivel\n";

// Abre os arquivos no modo de adição (append)
$handle_atletas = fopen($arquivo_atletas, 'a');
$handle_acessos = fopen($arquivo_acessos, 'a');

// Adiciona cabeçalho se os arquivos estiverem vazios
if (filesize($arquivo_atletas) === 0) {
    fputcsv($handle_atletas, ["Numero", "Nome", "Nascimento", "Sexo", "CPF", "Telefone"], ";");
}
if (filesize($arquivo_acessos) === 0) {
    fputcsv($handle_acessos, ["Numero", "PIN"], ";");
}

for ($i = 0; $i < $quantidade_a_gerar; $i++) {
    $numero_atleta = $proximo_numero_disponivel + $i;
    $numero_formatado = str_pad($numero_atleta, 3, "0", STR_PAD_LEFT);

    // Decide o sexo e nome
    if (mt_rand(0, 1) == 0) {
        $sexo = 'Masculino';
        $nome = $nomes_masculinos[array_rand($nomes_masculinos)];
    } else {
        $sexo = 'Feminino';
        $nome = $nomes_femininos[array_rand($nomes_femininos)];
    }
    $nome_completo = $nome . ' ' . $sobrenomes[array_rand($sobrenomes)] . ' ' . $sobrenomes[array_rand($sobrenomes)];

    $nascimento = gerarDataNascimento();
    $cpf = gerarCPF();
    $telefone = gerarTelefone();
    $pin = gerarPIN();

    // Prepara os dados para os arquivos
    $linha_atleta = [$numero_formatado, $nome_completo, $nascimento, $sexo, $cpf, $telefone];
    $linha_acesso = [$numero_formatado, $pin];

    // Escreve nos arquivos
    fputcsv($handle_atletas, $linha_atleta, ";");
    fputcsv($handle_acessos, $linha_acesso, ";");

    echo "Gerado atleta #$numero_formatado: $nome_completo\n";
}

fclose($handle_atletas);
fclose($handle_acessos);

echo "\n--- Geração Concluída! ---\n";
echo "$quantidade_a_gerar atletas foram adicionados com sucesso.\n";
echo "Acesse 'admin.php' para ver a lista completa e 'portal.php' para testar o login com os novos dados.\n";
echo "IMPORTANTE: Renomeie ou apague o arquivo 'gerar_dados.php' após o uso para não gerar dados duplicados acidentalmente.";

?>