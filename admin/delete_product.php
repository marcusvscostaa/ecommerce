<?php
session_start(); 
include('../server/connection.php');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $product_id = $_GET['product_id']; 

    $stmt_select_images = $conn->prepare("SELECT product_image, product_image2, product_image3, product_image4 FROM products WHERE product_id = ? LIMIT 1");
    $stmt_select_images->bind_param('i', $product_id);
    $stmt_select_images->execute();
    $result_images = $stmt_select_images->get_result();
    $product_images = $result_images->fetch_assoc();
    $stmt_select_images->close();

    $upload_dir = '../assets/imgs/'; 

    if ($product_images) {
        foreach ($product_images as $image_column => $image_name) {
            if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                unlink($upload_dir . $image_name); 
            }
        }
    }

    $stmt_delete = $conn->prepare("DELETE FROM products WHERE product_id = ? LIMIT 1");
    $stmt_delete->bind_param('i', $product_id);

    if ($stmt_delete->execute()) {
        header('Location: products.php?success_message=Produto e suas imagens excluídos com sucesso!');
        exit;
    } else {
        header('Location: products.php?error_message=Erro ao excluir o produto do banco de dados: ' . $stmt_delete->error);
        exit;
    }
    $stmt_delete->close();

} else {
    header('Location: products.php?error_message=ID do produto não fornecido para exclusão.');
    exit;
}
?>