<?php

include('../server/connection.php'); 

$message = ''; 

if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'] ?? null;
    $order_status = $_POST['order_status'] ?? null;

    if (empty($order_id) || empty($order_status)) {
        $message = "Erro: Dados incompletos para atualização.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param('si', $order_status, $order_id);
        if ($stmt->execute()) {
            $message = "Status do pedido atualizado com sucesso!";
            $stmt_re = $conn->prepare("SELECT order_id, order_cost, order_status, user_id, shipping_city, shipping_uf, shipping_address, order_date FROM orders WHERE order_id = ? LIMIT 1");
            $stmt_re->bind_param('i', $order_id);
            $stmt_re->execute();
            $order = $stmt_re->get_result()->fetch_assoc();
        } else {
            $message = "Erro ao atualizar o status do pedido: " . $stmt->error;
        }
        $stmt->close();
    }
} 
else if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    $stmt = $conn->prepare("SELECT order_id, order_cost, order_status, user_id, shipping_city, shipping_uf, shipping_address, order_date FROM orders WHERE order_id = ? LIMIT 1");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc(); 

    if (!$order) {
        header('Location: orders.php?error=Pedido não encontrado');
        exit;
    }
} else {
    header('Location: orders.php?error=ID do pedido não fornecido');
    exit;
}
?>

<?php include('./header.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar">
                <?php include('./sidemenu.php');  ?>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3">Editar Pedido #<?php echo $order['order_id'] ?? ''; ?></h1>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($order) && $order): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        Detalhes do Pedido
                    </div>
                    <div class="card-body">
                        <form method="POST" action="edit_order.php">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            
                            <div class="mb-3">
                                <label for="order_status" class="form-label">Status do Pedido</label>
                                <select class="form-select" id="order_status" name="order_status" required>
                                    <option value="on_hold" <?php if(isset($order['order_status']) && $order['order_status'] == 'on_hold') echo 'selected'; ?>>Em Análise</option>
                                    <option value="paid" <?php if(isset($order['order_status']) && $order['order_status'] == 'paid') echo 'selected'; ?>>Pago</option>
                                    <option value="shipped" <?php if(isset($order['order_status']) && $order['order_status'] == 'shipped') echo 'selected'; ?>>Enviado</option>
                                    <option value="delivered" <?php if(isset($order['order_status']) && $order['order_status'] == 'delivered') echo 'selected'; ?>>Entregue</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Custo Total:</label>
                                <p class="form-control-static">R$ <?php echo number_format($order['order_cost'], 2, ',', '.'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID do Usuário:</label>
                                <p class="form-control-static"><?php echo $order['user_id']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Endereço de Entrega:</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['shipping_city'] . ' - ' . $order['shipping_uf']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Data do Pedido:</label>
                                <p class="form-control-static"><?php echo $order['order_date']; ?></p>
                            </div>

                            <button type="submit" name="update_order" class="btn btn-success me-2">Atualizar Pedido</button>
                            <a href="orders.php" class="btn btn-secondary">Voltar aos Pedidos</a>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-4" role="alert">
                        Nenhum pedido encontrado ou ID inválido.
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
<?php include('./footer.php'); ?>
