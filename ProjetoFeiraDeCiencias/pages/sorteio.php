<?php
session_start();
require_once '../database/conexao.php';

// Verifica se o usuário tem permissão de administrador
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] !== 1)) {
    header("location: ../login.php");
    exit;
}

$vencedor = null;
$mensagem = "Clique no botão para sortear!";
$participantes_nomes = [];
$show_result = false;

try {
    $database = new Conexao();
    $db = $database->getConexao();

    // Lógica para obter a lista de participantes para a animação da roleta
    $sql_participantes = "SELECT DISTINCT U.nome FROM tentativa AS T INNER JOIN usuario AS U ON T.fk_id_usuario = U.id_usuario WHERE T.validada_para_sorteio = 1";
    $stmt_participantes = $db->prepare($sql_participantes);
    $stmt_participantes->execute();
    $result_participantes = $stmt_participantes->get_result();
    while ($row = $result_participantes->fetch_assoc()) {
        $participantes_nomes[] = htmlspecialchars($row['nome']);
    }
    $stmt_participantes->close();

    // Se o parâmetro 'sortear' estiver presente na URL, executa o sorteio
    if (isset($_GET['sortear'])) {
        $show_result = true;
        
        $sql_sorteio = "SELECT DISTINCT T.fk_id_usuario, U.nome
                        FROM tentativa AS T
                        INNER JOIN usuario AS U ON T.fk_id_usuario = U.id_usuario
                        WHERE T.validada_para_sorteio = 1
                        ORDER BY RAND()
                        LIMIT 1";

        $stmt_sorteio = $db->prepare($sql_sorteio);
        $stmt_sorteio->execute();
        $result = $stmt_sorteio->get_result();
        
        if ($result->num_rows > 0) {
            $vencedor = $result->fetch_assoc();
            $mensagem = "O ganhador do sorteio é: " . htmlspecialchars($vencedor['nome']) . "!";
        } else {
            $mensagem = "Nenhum participante elegível encontrado para o sorteio.";
        }
        $stmt_sorteio->close();
    }

    $db->close();

} catch (Exception $e) {
    $mensagem = "Erro ao realizar o sorteio: " . $e->getMessage();
}

// Converte a lista de nomes para um formato JSON para ser usado no JavaScript
$participantes_json = json_encode($participantes_nomes);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteio - Feira de Ciências</title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos específicos da página de sorteio */
        body {
            background: linear-gradient(135deg, #f0fff0, #e6f7ff);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sorteio-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 3rem 2rem;
            margin: auto;
            max-width: 600px;
            width: 90%;
            text-align: center;
        }

        .sorteio-section h2 {
            font-size: 2.5rem;
            color: #2E8B57;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        #ganhador-container {
            font-size: 2.2em;
            font-weight: bold;
            color: #4CAF50;
            min-height: 2em;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2rem 0;
            padding: 1.5rem;
            border-radius: 12px;
            background: #f8fff8;
            border: 2px dashed #d4edda;
            transition: all 0.6s ease-in-out;
            word-wrap: break-word; /* Garante que nomes longos se quebrem */
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        #ganhador-container.final {
            border: 3px solid #4CAF50;
            background: #e6ffeb;
            box-shadow: 0 0 15px rgba(76, 175, 80, 0.4);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        #sorteio-btn {
            padding: 1rem 2.5rem;
            font-size: 1.3rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(45deg, #2E8B57, #4CAF50);
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        #sorteio-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        #sorteio-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: translateY(0);
            box-shadow: none;
        }
        
        .discreet-btn {
            background-color: #bdc3c7;
            color: #2c3e50;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-top: 1.5rem;
        }
        
        .discreet-btn:hover {
            background-color: #95a5a6;
            transform: translateY(-2px);
        }

        .back-btn {
            background-color: #3498db;
            color: #fff;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-top: 1.5rem;
        }

        .back-btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <h1>Feira de Ciências 2025</h1>
            <div class="user-info">
                <span>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION["nome"]); ?>!</span>
                <a href="../logout.php" class="logout-btn">Sair</a>
            </div>
        </div>
    </header>

    <main>
        <div class="sorteio-section">
            <h2>Sorteio do Ganhador</h2>
            <div id="ganhador-container" class="<?php echo $show_result ? 'final' : ''; ?>">
                <p><?php echo $mensagem; ?></p>
            </div>
            
            <?php if (!$show_result): ?>
                <button id="sorteio-btn">Sortear Ganhador</button>
            <?php else: ?>
                <a href="sorteio.php" class="discreet-btn">Fazer outro sorteio</a>
            <?php endif; ?>
            
            <a href="homepage.php" class="back-btn">Voltar para o Início</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 - Colégio Comercial Caçapava.</p>
        </div>
    </footer>

    <script>
        const participantes = <?php echo $participantes_json; ?>;
        const ganhadorContainer = document.getElementById('ganhador-container');
        const sorteioBtn = document.getElementById('sorteio-btn');

        if (sorteioBtn) {
            sorteioBtn.addEventListener('click', () => {
                if (participantes.length === 0) {
                    ganhadorContainer.innerHTML = '<p>Nenhum participante elegível.</p>';
                    return;
                }
                
                ganhadorContainer.innerHTML = '<p>Sorteando...</p>';
                sorteioBtn.disabled = true;

                // Efeito de "roleta"
                let interval;
                let count = 0;
                let speed = 100;

                function startRoulette() {
                    interval = setInterval(() => {
                        const nomeAleatorio = participantes[Math.floor(Math.random() * participantes.length)];
                        ganhadorContainer.innerHTML = `<p>${nomeAleatorio}</p>`;
                        count++;
                        // Acelera a roleta no início e desacelera no final
                        if (count < 20) {
                            speed = Math.max(20, speed - 5);
                        } else if (count > 40) {
                            speed = Math.min(200, speed + 10);
                        }
                        clearInterval(interval);
                        interval = setInterval(startRoulette, speed);
                    }, speed);
                }

                startRoulette();

                // Para o efeito da roleta e recarrega a página para mostrar o resultado
                setTimeout(() => {
                    clearInterval(interval);
                    window.location.href = 'sorteio.php?sortear=1';
                }, 4000); // 4 segundos para a roleta
            });
        }
    </script>
</body>
</html>