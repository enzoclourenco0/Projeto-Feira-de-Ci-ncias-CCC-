<?php
// Inicia a sessão.
session_start();

// Inclui o arquivo de conexão.
require_once '../database/conexao.php';

// Variáveis para armazenar mensagens de erro e de sucesso
$erro = '';
$sucesso = '';

// Variáveis para manter o preenchimento dos campos do formulário
// Inicializa as variáveis com valores padrão vazios.
$telefoneValue = '';
$senhaValue = '';
$nomeValue = '';
$confirmarSenhaValue = '';

// Se o formulário já foi submetido e redirecionado, preenche os campos com os dados da sessão.
if (isset($_SESSION['post_data'])) {
    $telefoneValue = $_SESSION['post_data']['telefone'] ?? '';
    $senhaValue = $_SESSION['post_data']['senha'] ?? '';
    $nomeValue = $_SESSION['post_data']['nome'] ?? '';
    $confirmarSenhaValue = $_SESSION['post_data']['confirmar_senha'] ?? '';
    
    // Limpa os dados do POST da sessão após o uso
    unset($_SESSION['post_data']);
}

// Se houver mensagens de erro ou sucesso na sessão, armazena nas variáveis
if (isset($_SESSION['erro'])) {
    $erro = $_SESSION['erro'];
    unset($_SESSION['erro']);
}
if (isset($_SESSION['sucesso'])) {
    $sucesso = $_SESSION['sucesso'];
    unset($_SESSION['sucesso']);
}


// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Armazena os dados do POST na sessão antes de qualquer validação.
    // Isso garante que os campos sejam preenchidos caso haja um erro de validação.
    $_SESSION['post_data'] = $_POST;

    // Cria uma instância da classe de conexão
    $database = new Conexao();
    $db = $database->getConexao();

    // Limpa e armazena os dados do formulário
    $telefone = trim($_POST["telefone"] ?? '');
    $senha = trim($_POST["senha"] ?? '');
    $nome = trim($_POST["nome"] ?? '');
    $confirmar_senha = trim($_POST["confirmar_senha"] ?? '');
    
    // Define se a operação é de cadastro com base nos campos de nome e confirmação de senha
    $isCadastro = !empty($nome) && !empty($confirmar_senha);

    if ($isCadastro) {
        // --- LÓGICA DE CADASTRO ---
        if (empty($telefone) || empty($senha)) {
            $_SESSION['erro'] = "Para se cadastrar, preencha todos os campos.";
        } elseif ($senha !== $confirmar_senha) {
            $_SESSION['erro'] = "As senhas não coincidem.";
        } else {
            // Tenta verificar se o telefone já existe
            $sql = "SELECT id_usuario FROM usuario WHERE telefone = ?";
            if ($stmt = $db->prepare($sql)) {
                $stmt->bind_param("s", $param_telefone);
                $param_telefone = $telefone;
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $_SESSION['erro'] = "Este telefone já está cadastrado. Por favor, use a opção de login.";
                } else {
                    // Prepara para inserir o novo usuário
                    $sql_insert = "INSERT INTO usuario (telefone, nome, senha, is_admin) VALUES (?, ?, ?, ?)";
                    
                    if ($stmt_insert = $db->prepare($sql_insert)) {
                        
                        // HASH da senha para segurança
                        $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

                        // O valor 'is_admin' será sempre 0 para novos cadastros
                        $is_admin_default = 0;

                        $stmt_insert->bind_param("sssi", $param_telefone, $param_nome, $param_hashed_senha, $param_is_admin);
                        $param_nome = $nome;
                        $param_hashed_senha = $hashed_password;
                        $param_is_admin = $is_admin_default;

                        if ($stmt_insert->execute()) {
                            $_SESSION['sucesso'] = "Cadastro realizado com sucesso! Agora você pode fazer login com seu telefone e senha.";
                            
                            // *** CORREÇÃO APLICADA AQUI ***
                            // Limpa os dados de nome e senha para que os campos fiquem vazios
                            // na próxima vez que a página for carregada após o redirecionamento
                            $_SESSION['post_data']['nome'] = '';
                            $_SESSION['post_data']['confirmar_senha'] = '';
                            $_SESSION['post_data']['senha'] = '';

                        } else {
                            $_SESSION['erro'] = "Erro ao cadastrar. Por favor, tente novamente.";
                        }
                        $stmt_insert->close();
                    }
                }
                $stmt->close();
            }
        }
    } else {
        // --- LÓGICA DE LOGIN ---
        if (empty($telefone) || empty($senha)) {
            $_SESSION['erro'] = "Por favor, preencha o telefone e a senha para fazer login.";
        } else {
            // Prepara a declaração SQL para evitar injeção de SQL
            $sql = "SELECT id_usuario, nome, senha, is_admin FROM usuario WHERE telefone = ?";

            if ($stmt = $db->prepare($sql)) {
                $stmt->bind_param("s", $param_telefone);
                $param_telefone = $telefone;

                if ($stmt->execute()) {
                    $stmt->store_result();

                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($id, $nome_db, $hashed_password_db, $is_admin);
                        if ($stmt->fetch()) {
                            
                            // Compara a senha digitada com a senha HASHED do banco
                            if (password_verify($senha, $hashed_password_db)) {
                                // Senha correta, inicia uma nova sessão
                                session_regenerate_id();
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["nome"] = $nome_db;
                                // Salva o status de administrador na sessão
                                $_SESSION["is_admin"] = $is_admin;

                                // Redireciona para a página principal
                                header("location: homepage.php");
                                exit;
                            } else {
                                $_SESSION['erro'] = "A senha que você digitou não é válida.";
                            }
                        }
                    } else {
                        $_SESSION['erro'] = "Nenhuma conta encontrada com esse telefone. Preencha os campos abaixo para se cadastrar.";
                    }
                } else {
                    $_SESSION['erro'] = "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                }
                $stmt->close();
            }
        }
    }

    // Fecha a conexão e redireciona para a página atual para exibir mensagens
    $db->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Feira de Ciências</title>
    <link rel="stylesheet" href="/ProjetoFeiraDeCiencias/style/styleLogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">     
</head>
<body>
    <div class="login-container">
        <h1>Login de Avaliador</h1>
        <p>Use seu telefone e senha para entrar ou preencha todos os campos para se cadastrar.</p>

        <?php 
        if(!empty($erro)){
            echo '<div class="error-message">' . htmlspecialchars($erro) . '</div>';
        }
        if(!empty($sucesso)){
            echo '<div class="success-message">' . htmlspecialchars($sucesso) . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" placeholder="Ex: (31) 99999-9999" value="<?php echo htmlspecialchars($telefoneValue); ?>">
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" value="<?php echo htmlspecialchars($senhaValue); ?>">
            </div>
            
            <div class="separator"><span class="separator-text">Ou</span></div>

            <p class="text-muted">Preencha os campos abaixo para **CADASTRO**.</p>
            
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" placeholder="Seu nome" value="<?php echo htmlspecialchars($nomeValue); ?>">
            </div>
            
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua senha" value="<?php echo htmlspecialchars($confirmarSenhaValue); ?>">
            </div>
            
            <button type="submit" class="btn-submit">Entrar / Cadastrar</button>
        </form>
    </div>
</body>
</html>