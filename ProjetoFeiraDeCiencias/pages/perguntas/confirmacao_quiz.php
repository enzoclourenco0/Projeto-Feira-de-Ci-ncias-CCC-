<?php
session_start();

// Redireciona para a página de login se o usuário não estiver logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Verifica e limpa as mensagens de sessão
$sucesso = $_SESSION['sucesso'] ?? '';
unset($_SESSION['sucesso']);

// Pega o resultado da resposta da sessão e o remove imediatamente
$resultado_quiz = $_SESSION['resultado_quiz'] ?? null;
unset($_SESSION['resultado_quiz']);

// Prepara a mensagem de feedback
$feedback_message = '';
if ($resultado_quiz !== null) {
    if ($resultado_quiz['acertou']) {
        $feedback_message = 'Parabéns! Você acertou a pergunta!';
    } else {
        $feedback_message = 'Ops, você errou. A resposta correta era: ' . htmlspecialchars($resultado_quiz['resposta_correta']);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação</title>
    <link rel="stylesheet" href="/ProjetoFeiraDeCiencias/style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info span {
            font-weight: 600;
            color: white;
        }
        .logout-btn {
            background-color: #d9534f;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: #c9302c;
        }
        .confirmation-box {
            background-color: #f9f9f9;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 80px auto;
            text-align: center;
        }
        .confirmation-box h2 {
            color: #2E8B57;
            margin-bottom: 10px;
            font-size: 2em;
        }
        .confirmation-box p {
            color: #333;
            font-size: 1.1em;
            margin-bottom: 25px;
        }
        .continue-link {
            display: inline-block;
            padding: 15px 30px;
            background-color: #2E8B57;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .continue-link:hover {
            background-color: #3CB371;
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
        <div class="container">
            <section class="confirmation-box">
                <h2>Obrigado!</h2>
                <?php if (!empty($feedback_message)): ?>
                    <p><?php echo $feedback_message; ?></p>
                <?php elseif (!empty($sucesso)): ?>
                    <p><?php echo htmlspecialchars($sucesso); ?></p>
                <?php else: ?>
                    <p>Sua ação foi concluída com sucesso!</p>
                <?php endif; ?>
                <a href="../homepage.php" class="continue-link">Voltar para a Página Inicial</a>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 - Colégio Comercial Caçapava.</p>
        </div>
    </footer>
</body>
</html>