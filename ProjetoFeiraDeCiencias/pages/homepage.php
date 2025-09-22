<?php
// Inicia a sessão para ter acesso às variáveis de sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação da Feira de Ciências</title>
    <link rel="stylesheet" href="/ProjetoFeiraDeCiencias/style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #3b5998;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            text-decoration: none;
            margin-top: 15px;
        }
        .admin-btn:hover {
            background-color: #2e4a86;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <h1>Feira de Ciências 2025</h1>
            <div class="user-info">
                <span>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION["nome"]); ?>!</span>
                <a href="logout.php" class="logout-btn">Sair</a>
                
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="welcome-section">
                <h2>Seja Bem-Vindo(a) ao Sistema de Avaliação!</h2>
                <p>Selecione um projeto abaixo para iniciar a avaliação.</p>
            </section>

            <?php if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === 1): ?>
            <section class="sorteio-section">
                <a href="/ProjetoFeiraDeCiencias/pages/sorteio.php" class="discreet-btn">
                    Fazer Sorteio
                </a>
                <a href="/ProjetoFeiraDeCiencias/pages/painel_admin.php" class="admin-btn">
                    Painel Administrativo
                </a>
            </section>
            <?php endif; ?>

            <section class="projects-section">
                <h3>Projetos em Exposição:</h3>
                <ul class="project-list">
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande1.php">
                            <h4>Robótica Sustentável</h4>
                            <p>Estande 1</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande2.php">
                            <h4>O Impacto dos Microplásticos no Ecossistema Marinho</h4>
                            <p>Estande 2</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande3.php">
                            <h4>Energia Renovável a partir de Fontes Alternativas</h4>
                            <p>Estande 3</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande4.php">
                            <h4>Inteligência Artificial na Análise de Dados Médicos</h4>
                            <p>Estande 4</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande5.php">
                            <h4>Química dos Alimentos: Fermentação e Conservação</h4>
                            <p>Estande 5</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande6.php">
                            <h4>Fisica e Todas suas Leis</h4>
                            <p>Estande 6</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande7.php">
                            <h4>O poder da Matematica</h4>
                            <p>Estande 7</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande8.php">
                            <h4>Logica da Programação</h4>
                            <p>Estande 8</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande9.php">
                            <h4>Redes e Arquitetura de Computadores</h4>
                            <p>Estande 9</p>
                        </a>
                    </li>
                    <li>
                        <a href="/ProjetoFeiraDeCiencias/pages/perguntas/perguntaEstande10.php">
                            <h4>Banco de dados, Como funciona</h4>
                            <p>Estande 10</p>
                        </a>
                    </li>
                </ul>
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