<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador - Categorias</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>

<body>

<?php
/**
 * Calcula a idade a partir da data de nascimento no formato DD/MM/AAAA.
 * @param string $dataNascimento A data de nascimento.
 * @return int A idade em anos.
 */
function calcularIdade($dataNascimento) {
    // Converte a data de DD/MM/AAAA para um objeto DateTime
    $data_nasc = DateTime::createFromFormat('d/m/Y', $dataNascimento);
    if ($data_nasc === false) {
        // Tenta corrigir o formato se estiver como AAAA-MM-DD
        $data_nasc = DateTime::createFromFormat('Y-m-d', $dataNascimento);
    }
    if ($data_nasc === false) return 0; // Retorna 0 se o formato for inválido

    $data_atual = new DateTime();
    $intervalo = $data_atual->diff($data_nasc);
    return $intervalo->y;
}

/**
 * Define a categoria de idade.
 * @param int $idade A idade do atleta.
 * @return string A categoria de idade.
 */
function definirCategoriaIdade($idade) {
    if ($idade >= 15 && $idade <= 19) return '15 a 19 anos';
    if ($idade >= 20 && $idade <= 24) return '20 a 24 anos';
    if ($idade >= 25 && $idade <= 29) return '25 a 29 anos';
    if ($idade >= 30 && $idade <= 34) return '30 a 34 anos';
    if ($idade >= 35 && $idade <= 39) return '35 a 39 anos';
    if ($idade >= 40 && $idade <= 44) return '40 a 44 anos';
    if ($idade >= 45 && $idade <= 49) return '45 a 49 anos';
    if ($idade >= 50 && $idade <= 54) return '50 a 54 anos';
    if ($idade >= 55 && $idade <= 59) return '55 a 59 anos';
    if ($idade >= 60 && $idade <= 64) return '60 a 64 anos';
    if ($idade >= 65 && $idade <= 69) return '65 a 69 anos';
    if ($idade >= 70 && $idade <= 74) return '70 a 74 anos';
    if ($idade >= 75 && $idade <= 79) return '75 a 79 anos';
    return 'Fora da Faixa Etária';
}

// --- Processamento dos Dados ---
$arquivo_csv = 'atletas.csv';
$categorias = [
    'Masculino' => [],
    'Feminino' => []
];
$total_atletas = 0;

if (file_exists($arquivo_csv)) {
    if (($handle = fopen($arquivo_csv, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ";"); // Pular cabeçalho

        while (($dados = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $total_atletas++;
            $sexo = $dados[3];
            $idade = calcularIdade($dados[2]);
            $categoria_idade = definirCategoriaIdade($idade);
            
            // Adiciona a idade calculada aos dados do atleta
            $dados[] = $idade; 

            if (!isset($categorias[$sexo][$categoria_idade])) {
                $categorias[$sexo][$categoria_idade] = [];
            }
            $categorias[$sexo][$categoria_idade][] = $dados;
        }
        fclose($handle);
    }
}
?>

    <div class="container admin-container">

        <div class="header">
            <h2>&#128202; Painel do Administrador</h2>
            <p>Atletas inscritos, separados por categoria</p>
        </div>

        <div class="summary">
            <p>Total de Atletas Inscritos: <strong><?php echo $total_atletas; ?> / 100</strong></p>
        </div>

        <?php if ($total_atletas > 0): ?>
            <?php foreach ($categorias as $sexo => $grupos_idade): ?>
                <h2 class="categoria-sexo">Categoria <?php echo $sexo; ?></h2>
                
                <?php if (empty($grupos_idade)): ?>
                    <p>Nenhum atleta inscrito nesta categoria.</p>
                <?php else: ?>
                    <?php ksort($grupos_idade); // Ordena as faixas etárias ?>
                    <?php foreach ($grupos_idade as $nome_categoria => $atletas_na_categoria): ?>
                        <h3 class="categoria-idade">Faixa Etária: <?php echo $nome_categoria; ?> (<?php echo count($atletas_na_categoria); ?>)</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Nome</th>
                                        <th>Nascimento</th>
                                        <th>Idade</th>
                                        <th>CPF</th>
                                        <th>Telefone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($atletas_na_categoria as $atleta): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($atleta[0]); ?></td>
                                            <td><?php echo htmlspecialchars($atleta[1]); ?></td>
                                            <td><?php echo htmlspecialchars($atleta[2]); ?></td>
                                            <td><strong><?php echo htmlspecialchars($atleta[6]); // A idade está na nova posição 6 ?></strong></td>
                                            <td><?php echo htmlspecialchars($atleta[4]); ?></td>
                                            <td><?php echo htmlspecialchars($atleta[5]); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">Nenhum atleta cadastrado até o momento.</p>
        <?php endif; ?>

        <div class="footer-link">
            <a href="index.php">Ir para a Página de Inscrição</a>
        </div>
    </div>
</body>
</html>