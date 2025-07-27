<?php

include('server/connection.php');

$stmt_promo = $conn->prepare("SELECT product_id, product_name, product_image, product_price, product_special_offer FROM products WHERE product_special_offer IS NOT NULL AND product_special_offer > 0 LIMIT 5"); // Limite para 3 itens no carrossel
$stmt_promo->execute();
$promo_products = $stmt_promo->get_result();

if ($promo_products->num_rows == 0) {
    $stmt_promo = $conn->prepare("SELECT product_id, product_name, product_image, product_price, product_special_offer FROM products ORDER BY product_id DESC LIMIT 5");
    $stmt_promo->execute();
    $promo_products = $stmt_promo->get_result();
}

$stmt_featured = $conn->prepare("SELECT product_id, product_name, product_image, product_price, product_special_offer FROM products ORDER BY product_id DESC LIMIT 12");
$stmt_featured->execute();
$featured_products = $stmt_featured->get_result();

?>
  <?php include('layouts/header.php'); ?>
  <section id="promo-carousel" class="mt-5 pt-5">
       <div class="container-fluid row mx-auto container">
            <div class="heading mt-3 mb-3">
                </div>

            <div class="multi-carousel-container" id="multiCarousel">
                <div class="multi-carousel-inner" id="carouselInner">
                    <?php 
                    if ($promo_products->num_rows > 0) {
                        $index = 0;
                        while($promo_row = $promo_products->fetch_assoc()):
                        ?>
                        <div class="multi-carousel-item" data-index="<?php echo $index++; ?>">
                            <div class="img-container d-flex flex-row">
                                <a href="single_product.php?product_id=<?php echo $promo_row['product_id']; ?>">
                                    <img src="assets/imgs/<?php echo htmlspecialchars($promo_row['product_image']); ?>" alt="<?php echo htmlspecialchars($promo_row['product_name']); ?>">
                                </a>
                                <div class="carousel-item-caption-rotation">
                                </div>
                                  <div class="carousel-item-caption"> 
                                    <h5><?php echo htmlspecialchars($promo_row['product_name']); ?></h5>
                                        <?php if ($promo_row['product_special_offer'] > 0): ?>
                                          <?php 
                                            $original_price = $promo_row['product_price'] / (1 - $promo_row['product_special_offer'] / 100);
                                          ?>
                                          <p class="text-decoration-line-through text-muted"><small>De R$ <?php echo number_format($original_price, 2, ',', '.'); ?><span class="badge bg-danger ms-2">-<?php echo htmlspecialchars($promo_row['product_special_offer']); ?>%</span></small></p>
                                        <?php endif; ?>
                                    <h4 class="p-price">R$ <?php echo number_format($promo_row['product_price'], 2, ',', '.'); ?></h4>
                                    <a href="single_product.php?product_id=<?php echo $promo_row['product_id']; ?>" class="btn btn-outline-light">Comprar</a>  
                                </div> 
                            </div>
                        </div>
                        <?php endwhile; 
                    } else { ?>
                        <p class="text-center">Nenhum produto em promoção encontrado para o carrossel.</p>
                    <?php } ?>
                </div>

                <button class="multi-carousel-control-prev" id="prevBtn">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="multi-carousel-control-next" id="nextBtn">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
  </section>

    <section id="products" class="my-2 py-2">
        <div class="container mx-auto  row">
            <div class="font-section text-center mt-2 border-bottom border-3 mb-2 bg-white p-1">
              <p class="text-center mt-2 fs-6 fw-bolder">Confira os produtos mais recentes ou populares do Xain.</p>
            </div>
        </div>
      <div class="row mx-auto container d-flex align-items-stretch">
            <?php if ($featured_products->num_rows > 0): ?>
                <?php while($row = $featured_products->fetch_assoc()): ?>
                <div class="product text-start col-lg-2 col-md-3 col-sm-6 col-12">
                    <a href="single_product.php?product_id=<?php echo $row['product_id']; ?>" class="h-100">
                        <div class="bg-white divProduct">
                            <div id="product-card-img">
                                <img class="img-fluid mb-3" src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                            </div>
                            <h5 class="p-name fw-light"><?php echo htmlspecialchars($row['product_name']); ?></h5>
                            <p class="p-price text-decoration-line-through text-muted mt-1 mb-1"><small>De R$ <?php echo number_format($original_price, 2, ',', '.'); ?><span class="badge bg-danger ms-2">-<?php echo htmlspecialchars($row['product_special_offer']); ?>%</span></small></p>
                            <h4 class="p-price fw-bold">R$ <?php echo number_format($row['product_price'], 2, ',', '.'); ?></h4>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="lead">Nenhum produto em destaque encontrado.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-dark btn-lg">Ver Todos os Produtos</a>
        </div>
    </section>
  <?php include('layouts/footer.php'); ?>
<script src="assets/js/custom_carousel.js"></script> 
<script src="assets/js/main.js"></script> 

