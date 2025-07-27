<?php
include('server/connection.php');

$error_message = '';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: account.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Por favor, preencha todos os campos.";
    } 
    else if ($password !== $confirm_password) {
        $error_message = "As senhas não coincidem.";
    } 
    else if (strlen($password) < 6) {
        $error_message = "A senha deve ter pelo menos 6 caracteres.";
    } 
    else {
        $stmt_check_email = $conn->prepare("SELECT user_id FROM users WHERE user_email = ? LIMIT 1");
        $stmt_check_email->bind_param('s', $email);
        $stmt_check_email->execute();
        $stmt_check_email->store_result();

        if ($stmt_check_email->num_rows > 0) {
            $error_message = "Este e-mail já está cadastrado. Por favor, faça login ou use outro e-mail.";
        } else {
            $hashed_password = md5($password); 

            $stmt_insert = $conn->prepare("INSERT INTO users (user_name, user_email, user_password) VALUES (?, ?, ?)");
            $stmt_insert->bind_param('sss', $name, $email, $hashed_password);

            if ($stmt_insert->execute()) {
                $user_id = $conn->insert_id; 

                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['logged_in'] = true; 

                header('Location: account.php');
                exit;
            } else {
                $error_message = "Erro ao cadastrar usuário: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check_email->close();
    }
}
?>
<?php include('layouts/header.php'); ?>

<section class="my-5 py-5" id="register-form">
    <div class="container d-flex mt-3">
        <div class="w-100 m-auto">
            <div class="">
                <div class="text-center mb-4">
                    <h4 class="font-weight-bold">Registrar</h4>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nameInput" name="name" placeholder="Seu nome" required>
                        <div class="invalid-feedback">Por favor, insira seu nome.</div>
                    </div>
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="emailInput" name="email" placeholder="Seu e-mail" required>
                        <div class="invalid-feedback">Por favor, insira um e-mail válido.</div>
                    </div>
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="passwordInput" name="password" placeholder="Sua senha" required>
                        <div class="invalid-feedback">Por favor, insira sua senha.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPasswordInput" class="form-label">Confirmar Senha</label>
                        <input type="password" class="form-control" id="confirmPasswordInput" name="confirm_password" placeholder="Confirme sua senha" required>
                        <div class="invalid-feedback">Por favor, confirme sua senha.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Cadastrar-se</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        Já possui conta? <a href="login.php">Faça login.</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script src="assets/js/main.js"></script>

<?php include('layouts/footer.php');?>