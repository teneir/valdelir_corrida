<?php

// --- FUNÇÕES AUXILIARES ---

/**
 * Converte um intervalo de tempo para segundos com frações.
 * @param DateInterval $interval O intervalo.
 * @return float Total de segundos.
 */
function intervalParaSegundos(DateInterval $interval) {
    return ($interval->days * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s + ($interval->f);
}

/**
 * Formata um objeto DateInterval para o formato H:i:s.v (com milissegundos).
 * @param DateInterval $interval O intervalo.
 * @return string O tempo formatado.
 */
function formatarIntervalo(DateInterval $interval) {
    return sprintf(
        '%02d:%02d:%02d.%03d',
        $interval->h + ($interval->days * 24),
        $interval->i,
        $interval->s,
        floor($interval->f * 1000)
    );
}

/**
 * Converte segundos de volta para um formato de tempo H:i:s.v.
 * @param float $segundos Os segundos.
 * @return string O tempo formatado.
 */
function segundosParaFormato(float $segundos) {
    $horas = floor($segundos / 3600);
    $segundos %= 3600;
    $minutos = floor($segundos / 60);
    $segundos %= 60;
    $milissegundos = round(($segundos - floor($segundos)) * 1000);
    $segundos_int = floor($segundos);

    return sprintf('%02d:%02d:%02d.%03d', $horas, $minutos, $segundos_int, $milissegundos);
}


// --- 1. LER DADOS DOS ATLETAS (NOME E NÚMERO) ---
$atletas_info = [];
if (file_exists('atletas.csv')) {
    $handle = fopen('atletas.csv', 'r');
    fgetcsv($handle, 1000, ";"); // Pular cabeçalho
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $atletas_info[$data[0]] = $data[1]; // Ex: $atletas_info['001'] = 'João Silva';
    }
    fclose($handle);
}

// --- 2. LER E AGRUPAR OS TEMPOS DE PASSAGEM POR ATLETA (BLOCO CORRIGIDO) ---
$passagens_por_atleta = [];
$arquivo_passagens = 'etiquetas_passagens.csv';
if (file_exists($arquivo_passagens)) {
    $handle = fopen($arquivo_passagens, 'r');
    
    // MUDANÇA 1: Pular a linha do cabeçalho (ex: "etiqueta,nome,timestamp")
    // E usar a vírgula como delimitador.
    fgetcsv($handle, 1000, ","); 
    
    // MUDANÇA 2: Usar a vírgula "," como delimitador na leitura das linhas.
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Coluna 0: Etiqueta/Número do Atleta
        $numero_atleta = str_pad(trim($data[0]), 3, "0", STR_PAD_LEFT);
        
        // MUDANÇA 3: O timestamp agora está na coluna 2 (terceiro item).
        // Ignoramos o nome da coluna 1, pois usamos o nome do arquivo 'atletas.csv' que é o oficial.
        $timestamp = trim($data[2]);
        
        // Verifica se o timestamp não está vazio antes de tentar processá-lo
        if (!empty($timestamp)) {
             $passagens_por_atleta[$numero_atleta][] = new DateTime($timestamp);
        }
    }
    fclose($handle);
}


// --- 3. PROCESSAR E CALCULAR OS RESULTADOS ---
$resultados_finais = [];
foreach ($passagens_por_atleta as $numero => $tempos) {
    // Garante que os tempos estão em ordem cronológica
    sort($tempos);

    $qtd_voltas = count($tempos);
    if ($qtd_voltas < 2) continue; // Precisa de pelo menos 2 passagens para ter 1 volta completa

    $tempo_total_interval = $tempos[0]->diff(end($tempos));

    $melhor_volta = PHP_FLOAT_MAX;
    $pior_volta = 0.0;
    $soma_tempo_voltas = 0.0;

    for ($i = 1; $i < $qtd_voltas; $i++) {
        $intervalo_volta = $tempos[$i-1]->diff($tempos[$i]);
        $segundos_volta = intervalParaSegundos($intervalo_volta);

        if ($segundos_volta < $melhor_volta) {
            $melhor_volta = $segundos_volta;
        }
        if ($segundos_volta > $pior_volta) {
            $pior_volta = $segundos_volta;
        }
        $soma_tempo_voltas += $segundos_volta;
    }

    $ritmo_medio = $soma_tempo_voltas / ($qtd_voltas - 1);

    $resultados_finais[$numero] = [
        'nome' => $atletas_info[$numero] ?? 'Atleta Desconhecido',
        'numero' => $numero,
        'voltas' => $qtd_voltas - 1, // 3 passagens = 2 voltas completas
        'tempo_total' => formatarIntervalo($tempo_total_interval),
        'melhor_volta' => segundosParaFormato($melhor_volta),
        'pior_volta' => segundosParaFormato($pior_volta),
        'ritmo_medio' => segundosParaFormato($ritmo_medio)
    ];
}

// Ordenar os resultados finais, por exemplo, por número de voltas (decrescente)
uasort($resultados_finais, function($a, $b) {
    return $b['voltas'] <=> $a['voltas'];
});

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados da Corrida</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container admin-container">
        <div class="header">
            <h2>&#127942; Análise de Resultados da Corrida</h2>
            <p>Resumo de performance por atleta com base nas passagens registradas.</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Pos.</th>
                        <th>Número</th>
                        <th>Nome</th>
                        <th>Nº de Voltas</th>
                        <th>Tempo Total</th>
                        <th>Melhor Volta</th>
                        <th>Pior Volta</th>
                        <th>Ritmo Médio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($resultados_finais)): ?>
                        <?php $posicao = 1; ?>
                        <?php foreach ($resultados_finais as $resultado): ?>
                            <tr>
                                <td><strong><?php echo $posicao++; ?>º</strong></td>
                                <td><?php echo htmlspecialchars($resultado['numero']); ?></td>
                                <td><?php echo htmlspecialchars($resultado['nome']); ?></td>
                                <td><?php echo htmlspecialchars($resultado['voltas']); ?></td>
                                <td><?php echo htmlspecialchars($resultado['tempo_total']); ?></td>
                                <td><?php echo htmlspecialchars($resultado['melhor_volta']); ?></td>
                                <td><?php echo htmlspecialchars($resultado['pior_volta']); ?></td>
                                <td><?php echo htmlspecialchars($resultado['ritmo_medio']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">Nenhum dado de passagem encontrado ou atletas com menos de uma volta completa.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

         <div class="footer-link">
            <a href="admin.php">Voltar ao Painel do Administrador</a>
        </div>
    </div>
</body>
</html>