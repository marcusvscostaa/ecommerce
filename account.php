<?php
    session_start(); 

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_unset();
    session_destroy();
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
    header('Location: login.php');
    exit;
}
include('server/connection.php');
$error_message = ''; 
$success_message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($password) || empty($confirm_password)) {
        $error_message = "Por favor, preencha todos os campos de senha.";
    } else if ($password !== $confirm_password) {
        $error_message = "As senhas não coincidem.";
    } else if (strlen($password) < 6) {
        $error_message = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $hashed_password = md5($password); 

        $stmt = $conn->prepare("UPDATE users SET user_password = ? WHERE user_id = ? LIMIT 1");
        $stmt->bind_param('si', $hashed_password, $user_id);

        if ($stmt->execute()) {
            $success_message = "Senha atualizada com sucesso!";
        } else {
            $error_message = "Erro ao atualizar a senha: " . $stmt->error;
        }
        $stmt->close();
    }
}

$user_id = $_SESSION['user_id'];
$orders = [];

$stmt_orders = $conn->prepare("SELECT order_id, order_cost, order_status, order_date FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt_orders->bind_param('i', $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

while ($row = $result_orders->fetch_assoc()) {
    $orders[] = $row;
}
$stmt_orders->close();

?>

<?php include('layouts/header.php'); ?>

<section class="my-5 py-5">
    <div class="account-info container mt-4">
        <div class="row">
            <div class="col-lg-9 col-md-10 col-sm-12 card mb-4">
                <div class="border-bottom pb-2 border-secondary fw-bolder mt-3">
                    Meus Pedidos
                </div>
                <div class="">
                    <?php if (empty($orders)): ?>
                        <p class="text-center">Você ainda não fez nenhum pedido.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">ID do Pedido</th>
                                        <th scope="col">Custo</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Data</th>
                                        <th scope="col">Detalhes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                            <td>R$ <?php echo number_format($order['order_cost'], 2, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                            <td><a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">Ver</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-10 col-sm-12">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="border-bottom pb-2 border-secondary fw-bolder m-3">
                        Detalhes da Conta
                    </div>
                    <div class="card-body">
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                        <a href="account.php?logout=1" id="logout-btn" class="btn btn-danger mt-3">Sair da Conta</a>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="border-bottom pb-2 border-secondary fw-bolder m-3">
                        Alterar Senha
                    </div>
                    <div class="card-body">
                        <form action="account.php" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="change_password" value="1">
                            <div class="mb-3">
                                <label for="newPasswordInput" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="newPasswordInput" name="password" placeholder="Nova senha" required>
                                <div class="invalid-feedback">Por favor, insira sua nova senha.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmNewPasswordInput" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirmNewPasswordInput" name="confirm_password" placeholder="Confirme a nova senha" required>
                                <div class="invalid-feedback">Por favor, confirme sua nova senha.</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Atualizar Senha</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
            

        </div>
    </div>
</section>
<?php include('layouts/footer.php'); ?>
