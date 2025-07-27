<?php
session_start();
include('../server/connection.php');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ? LIMIT 1");
    $stmt->bind_param('i', $order_id);

    if ($stmt->execute()) {
        header('Location: orders.php?success_message=Pedido excluído com sucesso!');
        exit;
    } else {
        header('Location: orders.php?error_message=Erro ao excluir o pedido: ' . $stmt->error);
        exit;
    }
    $stmt->close();
} else {
    header('Location: orders.php?error_message=ID do pedido não fornecido para exclusão.');
    exit;
}
?>