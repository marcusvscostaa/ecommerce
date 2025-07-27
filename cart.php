<?php
session_start();
include('server/connection.php');
$message = '';
$cartClass = '';
$vh = '';

if (isset($_POST['add_to_cart'])) {
    if (isset($_SESSION['cart'])) {
        $product_ids = array_column($_SESSION['cart'], 'product_id'); 

        if (in_array($_POST['product_id'], $product_ids)) {
            $_SESSION['cart'][$_POST['product_id']]['product_quantity'] += $_POST['product_quantity'];
            $message = "Quantidade do produto atualizada no carrinho!";
        } else {
            $product_array = array(
                'product_id' => $_POST['product_id'],
                'product_name' => $_POST['product_name'],
                'product_price' => $_POST['product_price'],
                'product_image' => $_POST['product_image'],
                'product_quantity' => $_POST['product_quantity']
            );
            $_SESSION['cart'][$_POST['product_id']] = $product_array; 
            $message = "Produto adicionado ao carrinho!";
        }
    } else {
        $product_array = array(
            'product_id' => $_POST['product_id'],
            'product_name' => $_POST['product_name'],
            'product_price' => $_POST['product_price'],
            'product_image' => $_POST['product_image'],
            'product_quantity' => $_POST['product_quantity']
        );
        $_SESSION['cart'][$_POST['product_id']] = $product_array;
        $message = "Primeiro produto adicionado ao carrinho!";
    }

    calculateTotalCart(); 
}

if (isset($_POST['remove_product'])) {
    $product_id_to_remove = $_POST['product_id_to_remove'];
    if (isset($_SESSION['cart'][$product_id_to_remove])) {
        unset($_SESSION['cart'][$product_id_to_remove]); 
        $message = "Produto removido do carrinho!";
        calculateTotalCart(); 
    }
}

if (isset($_POST['edit_quantity'])) {
    $product_id_to_edit = $_POST['product_id_to_edit'];
    $new_quantity = $_POST['new_quantity'];

    if (isset($_SESSION['cart'][$product_id_to_edit])) {
        if ($new_quantity > 0) {
            $_SESSION['cart'][$product_id_to_edit]['product_quantity'] = $new_quantity;
            $message = "Quantidade atualizada com sucesso!";
        } else {
            unset($_SESSION['cart'][$product_id_to_edit]);
            $message = "Produto removido do carrinho (quantidade zero).";
        }
        calculateTotalCart(); 
    }
}

function calculateTotalCart() {
    $total_price = 0;
    $total_quantity = 0;

    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $value) {
            $product = $_SESSION['cart'][$key];
            $total_price += ($product['product_price'] * $product['product_quantity']);
            $total_quantity += $product['product_quantity'];
        }
    }
    $_SESSION['total_cart_price'] = $total_price;
    $_SESSION['total_cart_quantity'] = $total_quantity;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    calculateTotalCart(); 
} else if (!isset($_SESSION['total_cart_price']) || !isset($_SESSION['total_cart_quantity'])) {
    calculateTotalCart(); 
}
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])){
    $cartClass ="container p-3 row mx-auto mt-5";
    $vh = "";
} else {
    $cartClass ="d-flex justify-content-center mt-5";
    $vh = "vh-100";
}

?>

<?php include('layouts/header.php'); ?>

<section id="cart" class="my-5 py-5 <?php echo $vh; ?>">

    <div class="<?php echo $cartClass; ?>">
        <div class="bg-white col-md-8 border border-1 rounded">
            <?php if (!empty($message)): ?>
                <div class="alert alert-info mt-3 dismissible-alert" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button id="btnclosalert"type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php echo "<script>document.addEventListener('DOMContentLoaded', function() {
                                        setTimeout(function() {document.getElementById('btnclosalert').click()}, 4000)});</script>";?>
            <?php endif; ?>

            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Produto</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Preço</th>
                        <th scope="col">Quantidade</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $product): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center product-info">
                                <img src="assets/imgs/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            </div>
                        </td>
                        <td>
                            <p><?php echo htmlspecialchars($product['product_name']); ?></p>
                        </td>
                        <td>
                            <span>R$ <?php echo number_format($product['product_price'], 2, ',', '.'); ?></span>
                        </td>
                        <td>
                            <form action="cart.php" method="POST" class="update-quantity-form">
                                <input type="hidden" name="product_id_to_edit" value="<?php echo $product['product_id']; ?>">
                                <input type="hidden" name="edit_quantity" value="1"> 
                                <input type="number" name="new_quantity" value="<?php echo $product['product_quantity']; ?>" min="1" class="form-control cart-quantity-input d-inline-block" onchange="this.form.submit();">
                            </form>
                        </td>
                        <td>
                            <span>R$ <?php echo number_format($product['product_price'] * $product['product_quantity'], 2, ',', '.'); ?></span>
                        </td>
                        <td>
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="product_id_to_remove" value="<?php echo $product['product_id']; ?>">
                                <button type="submit" name="remove_product" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja remover este item?');">Excluir</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
        <div div class="col-md-3 ms-auto p-0">
            <div class="bg-white border border-1 rounded p-3 d-flex flex-column">
            <div class="mb-auto border-bottom pb-2 border-secondary">
                <h6 class="fw-bolder">Resumo da compra</h6>
            </div>
            <div class="cart-total rounded d-flex mt-3">
                <p>Total: </p>
                <p class="ms-auto">R$ <?php echo number_format($_SESSION['total_cart_price'], 2, ',', '.'); ?></p>
            </div>
            <div class="d-grid gap-2">
                <a href="checkout.php" class="btn checkout-btn">Finalizar Compra</a>
            </div>
            </div>
        </div>
        
    </div>

    <?php else: ?>
       
        <div class="alert alert-warning text-center m-auto" role="alert">
            Seu carrinho está vazio. <a href="products.php" class="alert-link">Explore nossos produtos!</a>
        </div>
    <?php endif; ?>
</section>

<?php include('layouts/footer.php'); ?>

