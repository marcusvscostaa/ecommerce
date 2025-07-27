<?php
include('session.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php?message=Por favor, faça login para finalizar a compra.');
    exit;
}

include('server/connection.php');

$error_message = '';
$success_message = '';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php?message=Seu carrinho está vazio. Por favor, adicione produtos antes de finalizar a compra.');
    exit;
}

$shipping_address = '';
$shipping_city = '';
$shipping_uf = '';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt_user = $conn->prepare("SELECT shipping_address, shipping_city, shipping_uf FROM orders WHERE user_id = ? LIMIT 1");
    $stmt_user->bind_param('i', $user_id);
    $stmt_user->execute();
    $user_data = $stmt_user->get_result()->fetch_assoc();
    if ($user_data) {
        $shipping_address = $user_data['shipping_address'];
        $shipping_city = $user_data['shipping_city'];
        $shipping_uf = $user_data['shipping_uf'];
    }
    $stmt_user->close();
}

if (!isset($_SESSION['total_cart_price']) || !isset($_SESSION['total_cart_quantity'])) {

    $total_price_recalc = 0;
    $total_quantity_recalc = 0;
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product) {
            $total_price_recalc += ($product['product_price'] * $product['product_quantity']);
            $total_quantity_recalc += $product['product_quantity'];
        }
    }
    $_SESSION['total_cart_price'] = $total_price_recalc;
    $_SESSION['total_cart_quantity'] = $total_quantity_recalc;
}

?>

<?php include('layouts/header.php'); ?>

<section id="checkout-form" class="my-5 py-5">

    <div class="container mt-4">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
            <div class="alert alert-warning text-center" role="alert">
                Seu carrinho está vazio. <a href="products.php" class="alert-link">Voltar para os produtos.</a>
            </div>
        <?php else: ?>
            <div class="">
                    <form action="server/place_order.php" method="POST" class="needs-validation row" novalidate>
                        <div class="col-lg-7 col-md-12 col-sm-12 card p-3">
                            <h4 class="mb-3">Informações de Envio</h4>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="address" class="form-label">Endereço Completo</label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Rua, número, complemento" value="<?php echo htmlspecialchars($shipping_address); ?>" required>
                                    <div class="invalid-feedback">Por favor, insira seu endereço de envio.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">Cidade</label>
                                    <input type="text" class="form-control" id="city" name="city" placeholder="Sua cidade" value="<?php echo htmlspecialchars($shipping_city); ?>" required>
                                    <div class="invalid-feedback">Por favor, insira sua cidade.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="uf" class="form-label">Estado (UF)</label>
                                    <input type="text" class="form-control" id="uf" name="uf" placeholder="Ex: SP" maxlength="2" value="<?php echo htmlspecialchars($shipping_uf); ?>" required>
                                    <div class="invalid-feedback">Por favor, insira seu estado (UF).</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 col-sm-12 ms-auto card p-3">
                            <h4 class="mb-3">Resumo do Pedido</h4>
                            <div class="order-summary">
                                <div class="d-flex">
                                    <p>Total de Itens: </p>
                                    <p class="ms-auto"><?php echo $_SESSION['total_cart_quantity'] ?? 0; ?></p>
                                </div>
                                <div class="d-flex">
                                    <p>Subtotal: </p>
                                    <p class="ms-auto">R$ <?php echo number_format($_SESSION['total_cart_price'] ?? 0, 2, ',', '.'); ?></p>
                                </div>

                            </div>
                               <div class="d-flex mt-5">
                                    <p>Total a Pagar: </p>
                                    <p class="ms-auto">R$ <?php echo number_format($_SESSION['total_cart_price'] ?? 0, 2, ',', '.'); ?></p>
                                </div>
                            <button type="submit" name="place_order_btn" class="btn btn-primary btn-lg w-100 mt-4">Continuar para o Pagamento</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include('layouts/footer.php'); ?>