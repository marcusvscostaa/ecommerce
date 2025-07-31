<?php
include('../server/connection.php'); 
$error_message = '';
$order = null;
$order_items = [];

if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    $stmt_order = $conn->prepare("SELECT order_id, order_cost, order_status, user_id, shipping_city, shipping_uf, shipping_address, order_date 
                                  FROM orders 
                                  WHERE order_id = ? 
                                  LIMIT 1");
    $stmt_order->bind_param('i', $order_id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    
    if ($order_result->num_rows === 1) {
        $order = $order_result->fetch_assoc();
    } else {
        $error_message = "Pedido não encontrado.";
        header('Location: orders.php?error_message=' . urlencode($error_message)); 
        exit;
    }
    $stmt_order->close();

    $stmt_items = $conn->prepare("SELECT 
                                    oi.product_id, 
                                    p.product_name, 
                                    p.product_image, 
                                    p.product_price, 
                                    oi.qtd as product_quantity 
                                FROM 
                                    order_items oi 
                                JOIN 
                                    products p ON oi.product_id = p.product_id 
                                WHERE 
                                    oi.order_id = ?");
    $stmt_items->bind_param('i', $order_id);
    $stmt_items->execute();
    $order_items_result = $stmt_items->get_result();
    
    while ($row = $order_items_result->fetch_assoc()) {
        $order_items[] = $row;
    }
    $stmt_items->close();

} else {
    $error_message = "ID do pedido não fornecido.";
    header('Location: orders.php?error_message=' . urlencode($error_message)); 
    exit;
}
?>

<?php include('./header.php');  ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <?php include('./sidemenu.php'); ?>
        </div>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detalhes do Pedido #<?php echo htmlspecialchars($order['order_id'] ?? ''); ?> (Admin)</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="orders.php" class="btn btn-secondary">Voltar para Pedidos</a>
                </div>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php elseif (isset($order) && $order): ?>
                <div class="row">
                    <div class="col-lg-12">
                        
                        <div class="card mb-4 order-details-info">
                            <div class="card-header">
                                Informações do Pedido
                            </div>
                            <div class="card-body">
                                <p><strong>Status do Pedido:</strong> <span class="badge bg-<?php 
                                    if($order['order_status'] == 'paid') echo 'success';
                                    else if($order['order_status'] == 'shipped') echo 'info';
                                    else if($order['order_status'] == 'delivered') echo 'primary';
                                    else echo 'warning'; 
                                ?>"><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($order['order_status']))); ?></span></p>
                                <p><strong>Custo Total:</strong> R$ <?php echo number_format($order['order_cost'], 2, ',', '.'); ?></p>
                                <p><strong>Usuário (ID):</strong> <?php echo htmlspecialchars($order['user_id']); ?></p>
                                <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                                <p><strong>Endereço de Entrega:</strong> <?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['shipping_city'] . ' - ' . $order['shipping_uf']); ?></p>

                                <div class="mt-3">
                                    <a href="edit_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary me-2">Editar Status</a>
                                    <a href="delete_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este pedido?');">Excluir Pedido</a>
                                </div>
                            </div>
                        </div>

                        <div class="card order-items-table">
                            <div class="card-header">
                                Itens do Pedido
                            </div>
                            <div class="card-body">
                                <?php if (empty($order_items)): ?>
                                    <p class="text-center">Nenhum item encontrado para este pedido.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Produto</th>
                                                    <th scope="col">Nome</th>
                                                    <th scope="col">Preço Unitário</th>
                                                    <th scope="col">Quantidade</th>
                                                    <th scope="col">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $total_order_items_cost = 0; ?>
                                                <?php foreach ($order_items as $item): ?>
                                                    <tr>
                                                        <td><img src="../assets/imgs/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></td>
                                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                        <td>R$ <?php echo number_format($item['product_price'], 2, ',', '.'); ?></td>
                                                        <td><?php echo htmlspecialchars($item['product_quantity']); ?></td>
                                                        <td>R$ <?php 
                                                            $subtotal = $item['product_price'] * $item['product_quantity'];
                                                            echo number_format($subtotal, 2, ',', '.');
                                                            $total_order_items_cost += $subtotal;
                                                        ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total dos Itens:</td>
                                                    <td class="fw-bold">R$ <?php echo number_format($total_order_items_cost, 2, ',', '.'); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<?php include('./footer.php'); ?>