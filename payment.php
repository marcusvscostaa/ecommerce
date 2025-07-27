<?php
session_start(); 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php?message=Por favor, faça login para finalizar a compra.');
    exit;
}

include('server/connection.php');

$error_message = '';
$order = null; 
$order_items = []; 

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$order_id = $_SESSION['order_id'] ?? null;
$order_total_cost = $_SESSION['total_cart_price'] ?? null;

if (isset($_POST['pay_now_btn'])) {
    $order_id_from_post = $_POST['order_id'] ?? null;
    $order_total_cost_from_post = $_POST['order_total_cost'] ?? null;
    
    if ($order_id_from_post !== null && $order_total_cost_from_post !== null && $order_total_cost_from_post > 0) {
        $_SESSION['order_id'] = $order_id_from_post;
        $_SESSION['total_cart_price'] = $order_total_cost_from_post;
        $order_id = $order_id_from_post;
        $order_total_cost = $order_total_cost_from_post;
    } else {
        header('Location: account.php?error_message=Dados do pedido para pagamento incompletos ou inválidos (pay_now).');
        exit;
    }
}

if ($order_id === null || $order_total_cost === null || $order_total_cost <= 0) {
    header('Location: account.php?error_message=Dados do pedido para pagamento incompletos ou inválidos.');
    exit;
}

if (isset($order_id)) {
    $stmt_status = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ? LIMIT 1");
    $stmt_status->bind_param('i', $order_id);
    $stmt_status->execute();
    $result_status = $stmt_status->get_result();
    if ($result_status->num_rows === 1) {
        $order_db = $result_status->fetch_assoc();
        if ($order_db['order_status'] === 'paid') {
            header('Location: account.php?message=Este pedido já foi pago.');
            exit;
        }
    }
    $stmt_status->close();
}

$currency = "BRL"; 

$user_id_session = $_SESSION['user_id'] ?? null; 

if ($user_id_session === null) { 
    header('Location: login.php?message=Sessão de usuário inválida. Por favor, faça login novamente.');
    exit;
}

$stmt_order_details = $conn->prepare("SELECT order_id, order_cost, order_status, shipping_city, shipping_uf, shipping_address, order_date FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
$stmt_order_details->bind_param('ii', $order_id, $user_id_session);
$stmt_order_details->execute();
$order_result_db = $stmt_order_details->get_result();

if ($order_result_db->num_rows === 1) {
    $order = $order_result_db->fetch_assoc(); 
    $order_total_cost = $order['order_cost']; 
    
    if ($order['order_status'] === 'paid') {
        header('Location: account.php?message=Este pedido já foi pago.');
        exit;
    }
} else {
    header('Location: account.php?error_message=Pedido não encontrado ou não pertence à sua conta.');
    exit;
}
$stmt_order_details->close();

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
$stmt_items->bind_param('ii', $order_id, $user_id_session);
$stmt_items->execute();
$order_items_result = $stmt_items->get_result();

while ($row = $order_items_result->fetch_assoc()) {
    $order_items[] = $row;
}
$stmt_items->close();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <style>
       .payment-info-card {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            background-color: #fff;
        }
        .payment-info-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            font-weight: bold;
            color: #333;
            padding: 15px 20px;
            margin: -20px -20px 20px -20px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }
        .payment-info-card p strong {
            color: #555;
        }
        .order-items-table-payment img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }

        .payment-total-summary p {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .payment-total-summary p strong {
            color: coral;
            font-size: 1.5rem;
        }
        #paypal-button-container {
            margin-top: 30px;
            min-height: 50px; 
        }
    </style>
</head>
<body>
    <?php include('layouts/header.php'); ?>

        <section id="payment-form" class="my-5">
        <div class="container mt-4">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php elseif (isset($order) && $order): ?>
                <div class="row justify-content-center">
                    <div class="col-lg-7 col-md-12 col-sm-12">
                        <div class="payment-info-card mb-4">
                            <div class="my-2 border-bottom pb-2 border-secondary fw-bolder">
                                Resumo do Pedido #<?php echo htmlspecialchars($order['order_id']); ?>
                            </div>
                            <p><strong>Status:</strong> <span class="badge bg-<?php 
                                if($order['order_status'] == 'paid') echo 'success';
                                else if($order['order_status'] == 'shipped') echo 'info';
                                else if($order['order_status'] == 'delivered') echo 'primary';
                                else echo 'warning'; 
                            ?>"><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($order['order_status']))); ?></span></p>
                            <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                            <p><strong>Endereço de Entrega:</strong> <?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['shipping_city'] . ' - ' . $order['shipping_uf']); ?></p>
                        </div>

                        <div class="payment-info-card order-items-table-payment">
                            <div class="">
                                Itens do Pedido
                            </div>
                            <?php if (empty($order_items)): ?>
                                <p class="text-center mt-3">Nenhum item encontrado para este pedido.</p>
                            <?php else: ?>
                                <div class="table-responsive mt-3">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">Produto</th>
                                                <th scope="col">Nome</th>
                                                <th scope="col">Preço</th>
                                                <th scope="col">Qtd</th>
                                                <th scope="col">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $total_items_in_table = 0; ?>
                                            <?php foreach ($order_items as $item): ?>
                                                <tr>
                                                    <td><img src="assets/imgs/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></td>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                    <td>R$ <?php echo number_format($item['product_price'], 2, ',', '.'); ?></td>
                                                    <td><?php echo htmlspecialchars($item['product_quantity']); ?></td>
                                                    <td>R$ <?php 
                                                        $subtotal = $item['product_price'] * $item['product_quantity'];
                                                        echo number_format($subtotal, 2, ',', '.');
                                                        $total_items_in_table += $subtotal;
                                                    ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Total dos Itens:</td>
                                                <td class="fw-bold">R$ <?php echo number_format($total_items_in_table, 2, ',', '.'); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12 col-sm-12 ms-lg-auto mt-md-4 mt-lg-0">
                        <div class="payment-info-card">
                            <div class="border-bottom pb-2 border-secondary fw-bolder">
                                Total do Pedido
                            </div>
                            <div class="d-flex mt-4">
                                <p class="h5">Total a Pagar:</p>
                                <p class="ms-auto h5">R$ <?php echo number_format($order_total_cost, 2, ',', '.'); ?></p>
                            </div>
                            
                            <p class="mt-4 text-center">Pagar com PayPal</p>
                            <div id="paypal-button-container" class="mt-3"></div>
                            <p class="mt-4 text-muted text-center"><small>Aguardando a seleção do método de pagamento.</small></p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center" role="alert">
                    Dados do pedido não disponíveis para pagamento. Por favor, tente novamente a partir do carrinho.
                    <a href="cart.php" class="btn btn-secondary mt-2">Ir para o Carrinho</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include('layouts/footer.php'); ?>
    
    <script src="https://www.paypal.com/sdk/js?client-id=AYsPld2DDEjK3MuQfUXlzeoCeqWWIFrHm1uh0WThSNR5e-5SFxLTqtyYIcPdQUl2_hxFMcU03s2JlSn2&currency=<?php echo $currency; ?>"></script>
    <script src="assets/js/paypal_buttons.php?order_id=<?php echo $order_id; ?>&order_total_cost=<?php echo $order_total_cost; ?>&currency=<?php echo $currency; ?>"></script>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo number_format($order_total_cost, 2, '.', ''); ?>', 
                            currency_code: '<?php echo $currency; ?>' 
                        },
                        description: 'Pedido #<?php echo $order_id; ?> do Xain',
                        custom_id: '<?php echo $order_id; ?>'
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    console.log('Transação concluída pelo ' + details.payer.name.given_name);
                    console.log('Detalhes da transação:', details);
                    window.location.href = 'server/complete_payment.php?transaction_id=' + details.id + '&order_id=<?php echo $order_id; ?>';
                });
            },
            onError: function(err) {
                console.error('Erro no PayPal:', err);
                alert('Ocorreu um erro no pagamento. Por favor, tente novamente.');
            },
            onCancel: function(data) {
                console.log('Pagamento cancelado:', data);
                alert('Pagamento cancelado. Você pode tentar novamente no carrinho.');
            }
        }).render('#paypal-button-container'); 
    
    </script>
    <script src="assets/js/main.js"></script> 
</body>
</html>