<?php
include('../session.php');
include('connection.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php?message=Por favor, faça login para finalizar a compra.');
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ../cart.php?message=Seu carrinho está vazio. Por favor, adicione produtos antes de finalizar a compra.');
    exit;
}

if (!isset($_POST['place_order_btn'])) {
    header('Location: ../checkout.php?error=Acesso inválido à página de processamento do pedido.');
    exit;
}

$order_cost = $_SESSION['total_cart_price'] ?? 0;
$order_status = 'on_hold'; 
$user_id = $_SESSION['user_id'] ?? null;

$shipping_address = $_POST['address'] ?? '';
$shipping_city = $_POST['city'] ?? '';
$shipping_uf = $_POST['uf'] ?? '';
$order_date = date('Y-m-d H:i:s'); 


if ($user_id === null || $order_cost <= 0 || empty($shipping_address) || empty($shipping_city) || empty($shipping_uf)) {
    header('Location: ../checkout.php?error=Dados do pedido incompletos ou inválidos. Por favor, revise seu carrinho e endereço.');
    exit;
}

$conn->autocommit(FALSE);

try {
    $stmt_order = $conn->prepare("INSERT INTO orders (order_cost, order_status, user_id, shipping_city, shipping_uf, shipping_address, order_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_order->bind_param('dsissss', 
        $order_cost, 
        $order_status, 
        $user_id, 
        $shipping_city, 
        $shipping_uf, 
        $shipping_address, 
        $order_date
    );

    if (!$stmt_order->execute()) {
        throw new Exception("Falha ao inserir pedido na tabela 'orders': " . $stmt_order->error);
    }

    $order_id = $conn->insert_id; 

    foreach($_SESSION['cart'] as $product) {
        $product_id = $product['product_id'];
        $product_quantity = $product['product_quantity']; 
        
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, user_id, qtd, order_date) VALUES (?, ?, ?, ?, ?)");
        $stmt_item->bind_param('iiiis', 
            $order_id, 
            $product_id, 
            $user_id, 
            $product_quantity, 
            $order_date
        );

        if (!$stmt_item->execute()) {
            throw new Exception("Falha ao inserir item do pedido para produto ID " . $product_id . ": " . $stmt_item->error);
        }
        $stmt_item->close();
    }

    $conn->commit(); 

    unset($_SESSION['cart']);
    unset($_SESSION['shipping_address']);
    unset($_SESSION['shipping_city']);
    unset($_SESSION['shipping_uf']);

    $_SESSION['order_id'] = $order_id;

    header('Location: ../payment.php'); 
    exit;

} catch (Exception $e) {
    $conn->rollback(); 
    error_log("Erro ao processar pedido: " . $e->getMessage());
    header('Location: ../checkout.php?error=Erro ao processar seu pedido. Por favor, tente novamente. Detalhes: ' . urlencode($e->getMessage()));
    exit;
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->autocommit(TRUE); 
    }
}

?>