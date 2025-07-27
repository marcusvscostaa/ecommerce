<?php
// Linha 1: session_start() está no layouts/header.php (VERSÃO CONDICIONAL OU INCONDICIONAL)
// Inclua o header.php AQUI para que o session_start() seja executado e as variáveis de sessão estejam disponíveis.
include('layouts/header.php'); // Isto já traz session_start(), $is_logged_in, $user_name

// Inclua a conexão com o banco de dados.
// Isso deve vir APÓS o session_start() do header, mas antes de qualquer consulta ao DB.
include('server/connection.php');

// Lógica de Proteção da Página:
// Usamos a variável $is_logged_in que vem do layouts/header.php.
// Se o usuário NÃO estiver logado, redireciona para o login.
if (!$is_logged_in) { 
    header('Location: login.php?message=Por favor, faça login para ver os detalhes do seu pedido.');
    exit;
}

$order = null;
$order_items = [];
$error_message = '';

// Verifica se o order_id foi passado via GET e não está vazio
if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $user_id = $_SESSION['user_id']; // ID do usuário logado (agora $_SESSION estará ativa e populada)

    // --- 1. Buscar Detalhes do Pedido ---
    $stmt_order = $conn->prepare("SELECT order_id, order_cost, order_status, shipping_city, shipping_uf, shipping_address, order_date FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
    $stmt_order->bind_param('ii', $order_id, $user_id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    
    if ($order_result->num_rows === 1) {
        $order = $order_result->fetch_assoc();
    } else {
        $error_message = "Pedido não encontrado ou não pertence à sua conta.";
        header('Location: account.php?error_message=' . urlencode($error_message));
        exit;
    }
    $stmt_order->close();

    // --- 2. Buscar Itens do Pedido ---
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
                                    oi.order_id = ? AND oi.user_id = ?");
    $stmt_items->bind_param('ii', $order_id, $user_id);
    $stmt_items->execute();
    $order_items_result = $stmt_items->get_result();
    
    while ($row = $order_items_result->fetch_assoc()) {
        $order_items[] = $row;
    }
    $stmt_items->close();

} else {
    $error_message = "ID do pedido não fornecido.";
    header('Location: account.php?error_message=' . urlencode($error_message));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido #<?php echo htmlspecialchars($order['order_id'] ?? ''); ?> - Xain</title>
</head>
<body>
    <section class="my-5 py-5">
        <div class="container mt-4">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <div class="text-center mt-4">
                    <a href="account.php" class="btn btn-secondary">Voltar para Minha Conta</a>
                </div>
            <?php elseif (isset($order) && $order): ?>
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-md-12 col-sm-12">
                        
                        <div class="card mb-4 order-details-info">
                            <div class="border-bottom pb-2 border-secondary fw-bolder m-3">
                                Informações do Pedido #<?php echo htmlspecialchars($order['order_id']); ?>
                            </div>
                            <div class="card-body">
                                <p><strong>Status do Pedido:</strong> <span class="badge bg-<?php 
                                    if($order['order_status'] == 'paid') echo 'success';
                                    else if($order['order_status'] == 'shipped') echo 'info';
                                    else if($order['order_status'] == 'delivered') echo 'primary';
                                    else echo 'warning'; 
                                ?>"><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($order['order_status']))); ?></span></p>
                                <p><strong>Custo Total:</strong> R$ <?php echo number_format($order['order_cost'], 2, ',', '.'); ?></p>
                                <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                                <p><strong>Endereço de Entrega:</strong> <?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['shipping_city'] . ' - ' . $order['shipping_uf']); ?></p>
                                <?php if ($order['order_status'] === 'on_hold'): ?>
                                    <form method="POST" action="payment.php" class="mt-3">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="order_total_cost" value="<?php echo $order['order_cost']; ?>">
                                        <input type="hidden" name="order_status" value="<?php echo $order['order_status']; ?>"> <button type="submit" class="btn btn-warning" name="pay_now_btn">Pagar Agora</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card order-items-table">
                            <div class="border-bottom pb-2 border-secondary fw-bolder m-3">
                                Itens do Pedido
                            </div>
                            <div class="card-body">
                                <?php if (empty($order_items)): ?>
                                    <p class="text-center">Nenhum item encontrado para este pedido.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
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
                                                        <td><img src="assets/imgs/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></td>
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
                        <div class="order-details-actions">
                            <a href="account.php" class="btn btn-secondary">Voltar para Minha Conta</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include('layouts/footer.php'); // Inclui o rodapé modular ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0DbyWq+l7h2pKLfUaK3bKq1t/d/G0/n+I1N1R/A9c4D3E" crossorigin="anonymous"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>