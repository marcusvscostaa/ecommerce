<?php
session_start(); 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php?message=Por favor, faça login.');
    exit;
}

$order_id_display = $_GET['order_id'] ?? null; 

?>

<?php include('layouts/header.php'); ?>
<style>
    /* Página de sucesso de pagamento */
    .success-container {
        max-width: 700px;
        padding: 40px;
        border-radius: 4px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        background-color: #fff;
    }
    .success-container h2 {
        color: #28a745; 
        font-size: 2rem;
        margin-bottom: 20px;
    }
    .success-container .icon-success {
        font-size: 4rem;
        color: #28a745;
        margin-bottom: 30px;
    }
    .success-container p {
        font-size: 1.2rem;
        line-height: 1.6;
        color: #555;
    }
    .success-container .btn-primary {
        background-color: #000;
    }
    .success-container .btn-primary:hover {
        background-color: #1C1C1C;
    }
    .success-container .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    .success-container .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

</style>
<section class="my-5 py-5 text-center">
    <div class="container mt-5 success-container mx-auto">
        <i class="fas fa-check-circle icon-success"></i>
        <h2 class="font-weight-bold">Pedido Confirmado com Sucesso!</h2>
        <p>Agradecemos a sua compra no Xain!</p>
        <?php if ($order_id_display): ?>
            <p>Seu pedido de número <strong>#<?php echo htmlspecialchars($order_id_display); ?></strong> foi processado.</p>
        <?php endif; ?>
        <p>Você pode acompanhar o status do seu pedido na sua conta.</p>
        
        <div class="mt-5">
            <a href="account.php" class="btn btn-secondary btn-lg me-3">Ver Meus Pedidos</a>
            <a href="products.php" class="btn btn-primary btn-lg">Continuar Comprando</a>
        </div>
    </div>
</section>

<?php include('layouts/footer.php'); ?>
