<?php
session_start();
include('server/connection.php');

$error_message = ''; 

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: account.php');
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Por favor, preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, user_name, user_email, user_password FROM users WHERE user_email = ? LIMIT 1");
        $stmt->bind_param('s', $email); 
        $stmt->execute();
        $stmt->store_result(); 

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $user_name, $user_email_db, $hashed_password_from_db); 
            $stmt->fetch(); 

            if (md5($password) === $hashed_password_from_db) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $user_name;
                $_SESSION['user_email'] = $user_email_db;
                $_SESSION['logged_in'] = true;

                header('Location: account.php');
                exit;
            } else {
                $error_message = "E-mail ou senha incorretos.";
            }
        } else {
            $error_message = "E-mail ou senha incorretos.";
        }
    }
}
?>
<?php include('layouts/header.php'); ?>

<section class="my-5 py-5" id="login-form">
    <div class="container d-flex mt-3">
        <div class=" w-100 m-auto">
            <div class="">
                <div class="text-center mb-4">
                    <h4 class="font-weight-bold">Já sou cliente</h4>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="needs-validation" novalidate>
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
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn">Entrar</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        Não possui conta? <a href="register.php">Cadastre-se aqui.</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script src="assets/js/main.js"></script>

<?php include('layouts/footer.php'); ?>
