<?php
// Inicia a sessão.
session_start();

// Inclui o arquivo de conexão.
require_once '../database/conexao.php';

// Redireciona para a página de login se o usuário não estiver logado
// ou se não for um administrador.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== 1) {
    header("location: ../login.php");
    exit;
}

$resultados = [];
$erro = '';

try {
    $database = new Conexao();
    $db = $database->getConexao();

    // Consulta SQL para somar os pontos por estande
    $sql = "SELECT m.nome, SUM(po.pontos) AS total_estrelas
            FROM pontuacao po
            JOIN materia m ON po.fk_id_materia = m.id_materia
            GROUP BY m.nome
            ORDER BY total_estrelas DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
    } else {
        $erro = "Nenhuma pontuação encontrada.";
    }

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    $erro = "Erro ao buscar dados do banco de dados: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - Feira de Ciências</title>
    <link rel="stylesheet" href="/ProjetoFeiraDeCiencias/style/style.css">
    <link rel="stylesheet" href="/ProjetoFeiraDeCiencias/style/stylePainelAdm.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
</head>
<body>
    <header>
        <div class="container header-content">
            <h1>Painel Administrativo</h1>
            <div class="user-info">
                <a href="homepage.php" class="admin-nav-btn">Voltar</a>
                <span>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION["nome"]); ?>!</span>
                <a href="logout.php" class="logout-btn">Sair</a>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="admin-table-container">
                <h2>Pontuação dos Estandes por Avaliação</h2>
                <div class="message-container">
                    <?php if (!empty($erro)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($erro); ?></div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($resultados)): ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Estande</th>
                            <th>Total de Estrelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['total_estrelas']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer><div class="container"><p>&copy; 2025 - Colégio Comercial Caçapava.</p></div></footer>
</body>
</html>