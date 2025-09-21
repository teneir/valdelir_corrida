<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscrição para Corrida</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>&#127939; Inscrição da Corrida</h2>
            <p>Preencha os dados abaixo para garantir sua vaga.</p>
        </div>
        <div class="portal-link">
            <a href="portal.php">Já se inscreveu? Acesse seu portal aqui</a>
        </div>
        
        <form action="cadastrar.php" method="post" id="form-cadastro">
            <label for="nome">Nome Completo:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="nascimento">Data de Nascimento:</label>
            <input type="text" id="nascimento" name="nascimento" required placeholder="DD/MM/AAAA" maxlength="10">
            <p id="erro-data" style="color: var(--danger-color); font-size: 0.9rem; display: none; margin: -5px 0 0 0;"></p>

            <label for="sexo">Sexo:</label>
            <select id="sexo" name="sexo" required>
                <option value="" disabled selected>Selecione...</option>
                <option value="Masculino">Masculino</option>
                <option value="Feminino">Feminino</option>
            </select>

            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required placeholder="Digite apenas os números" maxlength="14">

            <label for="telefone">Telefone:</label>
            <input type="tel" id="telefone" name="telefone" required placeholder="(00) 00000-0000">
            
            <hr>
            
            <label for="pin">Crie um PIN de 4 dígitos:</label>
            <input type="text" id="pin" name="pin" required pattern="\d{4}" maxlength="4" inputmode="numeric">

            <button type="submit">Finalizar Inscrição</button>
        </form>
    </div>
    
    <script>
        const form = document.getElementById('form-cadastro');
        const inputCPF = document.getElementById('cpf');
        const inputNascimento = document.getElementById('nascimento');
        const erroDataElemento = document.getElementById('erro-data');

        // Adiciona os 'ouvintes' de evento para as máscaras e para a validação
        inputCPF.addEventListener('input', mascaraCPF);
        inputNascimento.addEventListener('input', mascaraData);
        form.addEventListener('submit', validaFormulario);

        function mascaraCPF(event) {
            let valor = event.target.value.replace(/\D/g, '');
            valor = valor.substring(0, 11); // Limita a 11 dígitos
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d{2})$/, '$1-$2');
            event.target.value = valor;
        }

        /**
         * Aplica a máscara de data DD/MM/AAAA enquanto o usuário digita.
         */
        function mascaraData(event) {
            erroDataElemento.style.display = 'none'; // Esconde a mensagem de erro ao começar a corrigir

            let valor = event.target.value.replace(/\D/g, '');
            valor = valor.substring(0, 8); // Limita a 8 dígitos (DDMMYYYY)

            if (valor.length > 4) {
                valor = valor.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
            } else if (valor.length > 2) {
                valor = valor.replace(/(\d{2})(\d{1,2})/, '$1/$2');
            }
            event.target.value = valor;
        }

        /**
         * Valida o formulário ANTES de enviar.
         */
        function validaFormulario(event) {
            const dataNascimento = inputNascimento.value;
            if (!validaData(dataNascimento)) {
                event.preventDefault(); // Impede o envio do formulário
                erroDataElemento.textContent = 'Data inválida. Use o formato DD/MM/AAAA com uma data real.';
                erroDataElemento.style.display = 'block';
                inputNascimento.focus();
            } else {
                erroDataElemento.style.display = 'none';
            }
        }

        /**
         * Verifica se uma data no formato DD/MM/AAAA é real e válida.
         */
        function validaData(dataString) {
            if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dataString)) {
                return false;
            }
            const [dia, mes, ano] = dataString.split('/').map(Number);
            const data = new Date(ano, mes - 1, dia);

            // A data é válida se o ano, mês e dia no objeto Date forem os mesmos que o usuário digitou
            // E se o ano estiver num intervalo razoável (ex: maior que 1920 e menor ou igual ao ano atual)
            return (
                data.getFullYear() === ano &&
                data.getMonth() === mes - 1 &&
                data.getDate() === dia &&
                ano > 1920 &&
                ano <= new Date().getFullYear()
            );
        }
    </script>

</body>
</html>