<?php
session_start();
include('connection.php'); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$transaction_id = $_GET['transaction_id'] ?? null;
$order_id_from_url = $_GET['order_id'] ?? null;
$user_id_from_session = $_SESSION['user_id'] ?? null; 

$success_message = '';
$error_message = '';

if ($transaction_id === null || $order_id_from_url === null || $user_id_from_session === null) {
    header('Location: ../account.php?error_message=Dados da transação incompletos ou sessão inválida.');
    exit;
}

$conn->autocommit(FALSE);

try {

    $stmt_order = $conn->prepare("SELECT order_id, order_cost, order_status, user_id FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
    $stmt_order->bind_param('ii', $order_id_from_url, $user_id_from_session);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    $order = $order_result->fetch_assoc();
    $stmt_order->close();

    if (!$order) {
        throw new Exception("Pedido não encontrado ou não pertence a este usuário.");
    }

    if ($order['order_status'] !== 'on_hold') {
        throw new Exception("Pedido já processado ou com status inválido: " . $order['order_status']);
    }

    $new_status = 'paid';
    $stmt_update_order = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt_update_order->bind_param('si', $new_status, $order['order_id']);

    if (!$stmt_update_order->execute()) {
        throw new Exception("Falha ao atualizar status do pedido: " . $stmt_update_order->error);
    }
    $stmt_update_order->close();

    $stmt_payment = $conn->prepare("INSERT INTO payments (order_id, user_id, transaction_id, payment_date) VALUES (?, ?, ?, NOW())");
    $stmt_payment->bind_param('iis', $order['order_id'], $user_id_from_session, $transaction_id);

    if (!$stmt_payment->execute()) {
        throw new Exception("Falha ao registrar pagamento: " . $stmt_payment->error);
    }
    $stmt_payment->close();

    $conn->commit(); 

    unset($_SESSION['cart']);
    unset($_SESSION['total_cart_price']);
    unset($_SESSION['total_cart_quantity']);
    unset($_SESSION['order_id']);

    $success_message = "Pagamento do Pedido #" . $order['order_id'] . " confirmado com sucesso!";
    header('Location: ../success.php?order_id=' . urlencode($order['order_id']));
    exit;

} catch (Exception $e) {
    $conn->rollback(); 
    error_log("ERRO FATAL EM complete_payment.php: " . $e->getMessage()); 
    $error_message = "Erro ao finalizar pagamento: " . $e->getMessage();
    header('Location: ../account.php?error_message=' . urlencode($error_message));
    exit;
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->autocommit(TRUE); 
    }
}
?>