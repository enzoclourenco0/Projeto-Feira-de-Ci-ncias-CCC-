<?php
session_start();
require_once '../../database/conexao.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

$erro = $_SESSION['erro'] ?? '';
$sucesso = $_SESSION['sucesso'] ?? '';
unset($_SESSION['erro']);
unset($_SESSION['sucesso']);

$id_usuario = $_SESSION['id'];
$id_materia = $_GET['id_materia'] ?? null;

if (empty($id_materia) || !is_numeric($id_materia)) {
    $_SESSION['erro'] = "Estande não especificado.";
    header("location: ../homepage.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $avaliacao = $_POST['avaliacao'] ?? null;

    if (empty($avaliacao) || !is_numeric($avaliacao) || $avaliacao < 1 || $avaliacao > 5) {
        $_SESSION['erro'] = "Por favor, selecione uma avaliação válida.";
        header("location: avaliacao.php?id_materia=" . $id_materia);
        exit;
    }

    try {
        $database = new Conexao();
        $db = $database->getConexao();
        $db->begin_transaction();

        $sql_estrelas = "INSERT INTO estrelas (quantidade, fk_id_usuario) VALUES (?, ?)";
        $stmt_estrelas = $db->prepare($sql_estrelas);
        $stmt_estrelas->bind_param("di", $avaliacao, $id_usuario);
        $stmt_estrelas->execute();
        $id_estrela = $db->insert_id;
        $stmt_estrelas->close();

        $pontos = $avaliacao;
        $sql_pontuacao = "INSERT INTO pontuacao (fk_id_materia, fk_id_estrela, pontos) VALUES (?, ?, ?)";
        $stmt_pontuacao = $db->prepare($sql_pontuacao);
        $stmt_pontuacao->bind_param("iid", $id_materia, $id_estrela, $pontos);
        $stmt_pontuacao->execute();
        $stmt_pontuacao->close();

        $db->commit();

        $_SESSION['sucesso'] = "Avaliação salva com sucesso! Obrigado por sua colaboração.";
        header("location: ../homepage.php");
        exit;

    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['erro'] = "Erro ao salvar a avaliação: " . $e->getMessage();
        header("location: avaliacao.php?id_materia=" . $id_materia);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação do Estande</title>
    <link rel="stylesheet" href="/ProjetoFeiraDeCiencias/style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info span { font-weight: 600; color: white; }
        .logout-btn { background-color: #d9534f; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; transition: background-color 0.3s; }
        .logout-btn:hover { background-color: #c9302c; }
        .review-section { background-color: #f9f9f9; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); max-width: 600px; margin: 40px auto; text-align: center; }
        .review-section h2 { margin-bottom: 20px; color: #333; }
        
        .star-rating {
            direction: rtl; /* Inverte a ordem das estrelas */
            display: inline-block;
            font-size: 2rem;
            unicode-bidi: bidi-override; /* Força a renderização da direita para a esquerda */
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
            display: inline-block;
        }
        /* Mudar a cor ao passar o mouse */
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #fdd835;
        }
        /* Mudar a cor ao selecionar */
        .star-rating input[type="radio"]:checked ~ label {
            color: #fdd835;
        }
        
        .message-container { text-align: center; margin-bottom: 20px; }
        .error-message, .success-message { padding: 10px; border-radius: 5px; font-weight: bold; }
        .error-message { background-color: #ffe0e0; color: #d8000c; }
        .success-message { background-color: #dff2bf; color: #4f8a10; }
        .btn-submit { display: block; width: 100%; padding: 15px; background-color: #2E8B57; color: white; border: none; border-radius: 5px; font-size: 1.1em; cursor: pointer; transition: background-color 0.3s; margin-top: 25px; }
        .btn-submit:hover { background-color: #3CB371; }
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
            <section class="review-section">
                <h2>Avalie o Estande</h2>
                <div class="message-container">
                    <?php 
                    if (!empty($erro)) { echo '<div class="error-message">' . htmlspecialchars($erro) . '</div>'; }
                    if (!empty($sucesso)) { echo '<div class="success-message">' . htmlspecialchars($sucesso) . '</div>'; }
                    ?>
                </div>
                <p>Por favor, dê uma nota de 1 a 5 estrelas para este estande.</p>
                
                <form action="avaliacao.php?id_materia=<?php echo htmlspecialchars($id_materia); ?>" method="post">
                    <div class="star-rating">
                        <input type="radio" id="star5" name="avaliacao" value="5" required>
                        <label for="star5" title="5 estrelas">&#9733;</label>
                        <input type="radio" id="star4" name="avaliacao" value="4">
                        <label for="star4" title="4 estrelas">&#9733;</label>
                        <input type="radio" id="star3" name="avaliacao" value="3">
                        <label for="star3" title="3 estrelas">&#9733;</label>
                        <input type="radio" id="star2" name="avaliacao" value="2">
                        <label for="star2" title="2 estrelas">&#9733;</label>
                        <input type="radio" id="star1" name="avaliacao" value="1">
                        <label for="star1" title="1 estrela">&#9733;</label>
                    </div>
                    <button type="submit" class="btn-submit">Enviar Avaliação</button>
                </form>
            </section>
        </div>
    </main>

    <footer><div class="container"><p>&copy; 2025 - Colégio Comercial Caçapava.</p></div></footer>
</body>
</html>