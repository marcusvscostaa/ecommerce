<?php
include('server/connection.php');

$product = null; 
if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $product_id = $_GET['product_id']; 
    $stmt = $conn->prepare("SELECT product_id, product_name, product_category, product_description, product_image, product_image2, product_image3, product_image4, product_price, product_special_offer, product_color FROM products WHERE product_id = ? LIMIT 1");
    $stmt->bind_param('i', $product_id); 
    $stmt->execute(); 
    
    $product = $stmt->get_result()->fetch_assoc(); 

    if (!$product) {
        header('Location: products.php?error_message=Produto não encontrado.');
        exit;
    }
} else {
    header('Location: products.php?error_message=ID do produto não fornecido.');
    exit;
}
?>

<?php include('layouts/header.php');  ?>
<section class="container single-product pt-5" id="single-product">
    <div class="row mt-5 bg-white p-3 rounded border border-1 ">
        <?php if (isset($product) && $product): ?>
        <div class="col-lg-1 col-md-6 col-sm-12 d-flex">
            
            <div class="small-img-group mt-3 ">
                <?php 
                $image_columns = ['product_image', 'product_image2', 'product_image3', 'product_image4'];
                foreach ($image_columns as $col) {
                    if (!empty($product[$col])) {
                        echo '<div class="small-img-col">';
                        echo '<img height="54" class="small-img img-fluid" src="assets/imgs/' . htmlspecialchars($product[$col]) . '" alt="' . htmlspecialchars($product['product_name']) . '">';                        
                        echo '</div>';
                    }
                }
                ?>
            </div>

        </div>
        <div class="col-lg-8 img-center">
            <img class="img-fluid w-100 pb-1 main-img" src="assets/imgs/<?php echo htmlspecialchars($product['product_image']); ?>" id="mainImg" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 product-details p-3 ms-auto">
            <h6><span class="badge bg-dark text-white text-uppercase"><?php echo htmlspecialchars($product['product_category']); ?></span></h6>
            <h4 class="mt-3"><?php echo htmlspecialchars($product['product_name']); ?></h4>
            <?php if ($product['product_special_offer'] > 0): ?>
            <?php 
            $original_price = $product['product_price'] / (1 - $product['product_special_offer'] / 100);
            ?>
            <p class="p-price text-decoration-line-through text-muted mt-1 mb-1"><small>De R$ <?php echo number_format($original_price, 2, ',', '.'); ?><span class="badge bg-danger ms-2">-<?php echo htmlspecialchars($product['product_special_offer']); ?>%</span></small></p>
        <?php endif; ?>
            <h2 class="">R$ <?php echo number_format($product['product_price'], 2, ',', '.'); ?></h2>
            
            <form action="cart.php" method="POST">
                <div class="d-flex align-items-center mb-3">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product['product_price']); ?>">
                    <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['product_image']); ?>">
                    <input type="number" name="product_quantity" value="1" min="1" class="form-control me-2">
                    <button class="buy-btn" type="submit" name="add_to_cart">Adicionar ao Carrinho</button>
                </div>
            </form>
            
            <h5 class="mt-5 mb-3"><span class="badge bg-light text-dark">Detalhes do Produto</span></h5>
            <span><?php echo nl2br(htmlspecialchars($product['product_description'])); ?></span>
            <?php if (!empty($product['product_color'])): ?>
                <p class="mt-3"><strong>Cor:</strong> <?php echo htmlspecialchars($product['product_color']); ?></p>
            <?php endif; ?>
        </div>
        <?php else: ?>
            <div class="col-12 text-center">
                <p class="lead alert alert-warning">Produto não encontrado. Volte para a <a href="products.php">lista de produtos</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<script>
    let mainImg = document.getElementById('mainImg');
    let smallImgs = document.getElementsByClassName('small-img');

    function removeActiveThumbnailClass() {
        for (let i = 0; i < smallImgs.length; i++) {
            smallImgs[i].parentElement.classList.remove('active-thumbnail');
        }
    }

    for (let i = 0; i < smallImgs.length; i++) {
        smallImgs[i].addEventListener('mouseover', function() {
            mainImg.src = smallImgs[i].src; 
            removeActiveThumbnailClass(); 
            this.parentElement.classList.add('active-thumbnail'); 
        });
    }

    window.addEventListener('load', function() {
        if (smallImgs.length > 0) {
            smallImgs[0].parentElement.classList.add('active-thumbnail'); 
        }
    });
    </script>

<?php include('layouts/footer.php'); ?>
