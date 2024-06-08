<?php
session_start(); // Inicia a sessão

require 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Filtra e sanitiza os dados do formulário
    $inputUsername = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $inputPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if ($inputUsername && $inputPassword) {
        // Prepara a consulta para selecionar o usuário com base no nome de usuário e senha
        $stmt = $conn->prepare('SELECT * FROM usuarios WHERE username = :username AND password = SHA2(:password, 256)');
        $stmt->bindParam(':username', $inputUsername);
        $stmt->bindParam(':password', $inputPassword);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            // Se o usuário for encontrado, define a sessão e redireciona para a página inicial
            $_SESSION['username'] = $inputUsername;
            header('Location: index.php');
            exit;
        } else {
            // Se as credenciais forem inválidas, redireciona para a página de login com uma mensagem de erro
            header('Location: login.php?error=Usuário ou senha inválidos.');
            exit;
        }
    } else {
        // Se os campos estiverem vazios, redireciona para a página de login com uma mensagem de erro
        header('Location: login.php?error=Por favor, preencha todos os campos.');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" placeholder="Digite seu usuário">
            </div>
            <div class="input-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Digite sua senha">
            </div>
            <button type="submit">Entrar</button>
        </form>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
