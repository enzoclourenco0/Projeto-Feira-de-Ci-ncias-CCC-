<?php
// Inicia a sessão.
session_start();

// Inclui o arquivo de conexão.
require_once '../../database/conexao.php';

// Redireciona para a página de login se o usuário não estiver logado.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Variáveis para armazenar mensagens de erro e de sucesso
$erro = $_SESSION['erro'] ?? '';
$sucesso = $_SESSION['sucesso'] ?? '';
unset($_SESSION['erro']);
unset($_SESSION['sucesso']);

// Define o ID do usuário e do estande (materia)
$id_usuario = $_SESSION['id'];
$id_materia_estande1 = 1;

// Lógica para verificar se o quiz já foi respondido e avaliado
$quiz_ja_respondido = false;
$estande_ja_avaliado = false;

try {
    $database = new Conexao();
    $db = $database->getConexao();

    // Verifica se o quiz já foi respondido
    $sql_check_tentativa = "SELECT 1 FROM tentativa WHERE fk_id_usuario = ? AND fk_id_materia = ? AND finalizada = TRUE LIMIT 1";
    $stmt_check = $db->prepare($sql_check_tentativa);
    $stmt_check->bind_param("ii", $id_usuario, $id_materia_estande1);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        $quiz_ja_respondido = true;
    }
    $stmt_check->close();

    // Verifica se o estande já foi avaliado
    $sql_check_avaliacao = "SELECT 1 FROM pontuacao WHERE fk_id_materia = ? AND fk_id_estrela IN (SELECT id_estrela FROM estrelas WHERE fk_id_usuario = ?)";
    $stmt_check_avaliacao = $db->prepare($sql_check_avaliacao);
    $stmt_check_avaliacao->bind_param("ii", $id_materia_estande1, $id_usuario);
    $stmt_check_avaliacao->execute();
    $stmt_check_avaliacao->store_result();

    if ($stmt_check_avaliacao->num_rows > 0) {
        $estande_ja_avaliado = true;
    }
    $stmt_check_avaliacao->close();

    $db->close();
    
} catch (Exception $e) {
    $erro = "Erro ao verificar o status do quiz ou da avaliação: " . $e->getMessage();
}

// Lógica para carregar a pergunta se o quiz ainda não foi respondido
$pergunta = null;
if (!$quiz_ja_respondido && empty($erro)) {
    try {
        $database = new Conexao();
        $db = $database->getConexao();
        
        // Seleciona a primeira (e única) pergunta do estande
        $sql_question = "SELECT id_pergunta, enunciado, alternativa_A, alternativa_B, alternativa_C, alternativa_D FROM perguntaResposta WHERE fk_id_materia = ? LIMIT 1";
        $stmt_question = $db->prepare($sql_question);
        $stmt_question->bind_param("i", $id_materia_estande1);
        $stmt_question->execute();
        $result = $stmt_question->get_result();

        if ($result->num_rows > 0) {
            $pergunta = $result->fetch_assoc();
        } else {
            $erro = "Nenhuma pergunta encontrada para o Estande 1.";
        }
        $db->close();
    } catch (Exception $e) {
        $erro = "Erro de conexão com o banco de dados: " . $e->getMessage();
    }
}

// Processa o formulário apenas se ele foi enviado e o quiz ainda não foi respondido
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$quiz_ja_respondido) {
    
    $resposta_usuario = trim($_POST["resposta_usuario"] ?? '');
    $id_pergunta = $_POST["id_pergunta"] ?? null;

    if (empty($resposta_usuario) || empty($id_pergunta)) {
        $_SESSION['erro'] = "Por favor, selecione uma alternativa antes de continuar.";
        header("location: perguntaEstande1.php");
        exit;
    }

    try {
        $database = new Conexao();
        $db = $database->getConexao();

        $sql_check_correct = "SELECT alternativa_correta FROM perguntaResposta WHERE id_pergunta = ?";
        $stmt_check = $db->prepare($sql_check_correct);
        $stmt_check->bind_param("i", $id_pergunta);
        $stmt_check->execute();
        $stmt_check->bind_result($alternativa_correta);
        $stmt_check->fetch();
        $stmt_check->close();

        $acertou = ($resposta_usuario == $alternativa_correta) ? 1 : 0;

        $sql_tentativa = "INSERT INTO tentativa (fk_id_usuario, fk_id_materia, acertos_totais, total_perguntas, finalizada, validada_para_sorteio) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_tentativa = $db->prepare($sql_tentativa);
        $acertos_iniciais = $acertou;
        $total_perguntas = 1;
        $finalizada = 1;
        $validada = $acertou;
        $stmt_tentativa->bind_param("iiiiii", $id_usuario, $id_materia_estande1, $acertos_iniciais, $total_perguntas, $finalizada, $validada);
        $stmt_tentativa->execute();
        
        $id_tentativa = $db->insert_id;
        $stmt_tentativa->close();

        $sql_resposta = "INSERT INTO resposta (fk_id_tentativa, fk_id_pergunta, resposta_dada, acertou) VALUES (?, ?, ?, ?)";
        $stmt_resposta = $db->prepare($sql_resposta);
        $stmt_resposta->bind_param("iisi", $id_tentativa, $id_pergunta, $resposta_usuario, $acertou);
        $stmt_resposta->execute();
        $stmt_resposta->close();

        $_SESSION['sucesso'] = "Sua resposta foi salva com sucesso!";
        
        // Redireciona para a mesma página para atualizar o status e exibir o botão de avaliação
        header("location: perguntaEstande1.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['erro'] = "Erro ao salvar a resposta: " . $e->getMessage();
        header("location: perguntaEstande1.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Estande 1</title>
    <link rel="stylesheet" href="/ProjetoFeiraDeCiencias/style/style.css">
    <link rel="stylesheet" href="/ProjetoFeiraDeCiencias/style/stylePerguntas.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
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
            <section class="question-form">
                <h2>Quiz Estande 1</h2>
                <div class="message-container">
                    <?php 
                    if (!empty($erro)) { echo '<div class="error-message">' . htmlspecialchars($erro) . '</div>'; }
                    if (!empty($sucesso)) { echo '<div class="success-message">' . htmlspecialchars($sucesso) . '</div>'; }
                    ?>
                </div>
                <?php if ($estande_ja_avaliado): ?>
                    <div class="aviso-message">Você já respondeu e avaliou este estande. Obrigado!</div>
                    <a href="../homepage.php" class="btn-action btn-voltar">Voltar para a Página Inicial</a>
                <?php elseif ($quiz_ja_respondido): ?>
                    <div class="aviso-message">Você já respondeu ao questionário. Agora, ajude-nos a avaliar este estande!</div>
                    <a href="avaliacao.php?id_materia=<?php echo htmlspecialchars($id_materia_estande1); ?>" class="btn-voltar">Avaliar Estande</a>
                    <a href="../homepage.php" class="btn-action btn-voltar" style="background-color: #ccc; margin-top: 10px;">Voltar para a Página Inicial</a>
                <?php elseif ($pergunta): ?>
                    <form action="perguntaEstande1.php" method="post">
                        <input type="hidden" name="id_pergunta" value="<?php echo htmlspecialchars($pergunta['id_pergunta']); ?>">
                        <h3><?php echo htmlspecialchars($pergunta['enunciado']); ?></h3>
                        <div class="alternatives-group">
                            <label class="alternative"><input type="radio" name="resposta_usuario" value="A" required> A) <?php echo htmlspecialchars($pergunta['alternativa_A']); ?></label>
                            <label class="alternative"><input type="radio" name="resposta_usuario" value="B"> B) <?php echo htmlspecialchars($pergunta['alternativa_B']); ?></label>
                            <label class="alternative"><input type="radio" name="resposta_usuario" value="C"> C) <?php echo htmlspecialchars($pergunta['alternativa_C']); ?></label>
                            <label class="alternative"><input type="radio" name="resposta_usuario" value="D"> D) <?php echo htmlspecialchars($pergunta['alternativa_D']); ?></label>
                        </div>
                        <button type="submit" class="btn-submit">Enviar Resposta</button>
                    </form>
                    <a href="../homepage.php" class="btn-action btn-voltar">Voltar para a Página Inicial</a>
                <?php else: ?>
                    <div class="message-container error-message">Nenhuma pergunta para exibir.</div>
                    <a href="../homepage.php" class="btn-action btn-voltar">Voltar para a Página Inicial</a>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <footer><div class="container"><p>&copy; 2025 - Colégio Comercial Caçapava.</p></div></footer>
</body>
</html>