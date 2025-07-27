<?php
include('server/connection.php');
$search_query = $_GET['search_query'] ?? ''; // Pega a query de busca da URL
$limit = 24; 
$page = isset($_GET['page']) ? $_GET['page'] : 1; 

$offset = ($page - 1) * $limit;

$stmt_total = $conn->prepare("SELECT COUNT(*) FROM products");
$stmt_total->execute();
$stmt_total->bind_result($total_records); 
$stmt_total->fetch(); 
$stmt_total->close(); 

$total_pages = ceil($total_records / $limit);
// Adapte a consulta SQL principal para buscar produtos
if (!empty($search_query)) {
    $stmt_total = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_name LIKE ?");
    $search_param = "%" . $search_query . "%";
    $stmt_total->bind_param('s', $search_param);
    $stmt_total->execute();
    $stmt_total->bind_result($total_records);
    $stmt_total->fetch();
    $stmt_total->close();

    $stmt = $conn->prepare("SELECT product_id, product_name, product_category, product_price, product_special_offer, product_color, product_image FROM products WHERE product_name LIKE ? ORDER BY product_id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('sii', $search_param, $limit, $offset);
} else {
    // ... sua consulta original sem busca ...
    $stmt = $conn->prepare("SELECT product_id, product_name, product_category, product_price, product_special_offer, product_color, product_image FROM products LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$products = $stmt->get_result(); 
?>
<?php include('layouts/header.php'); ?>
<section id="products" class="my-5 py-5">
    <div class="row mx-auto container d-flex align-items-stretch">
        <?php if ($products->num_rows > 0): ?>
            <?php while($row = $products->fetch_assoc()): ?>
            <div class="product text-start col-lg-2 col-md-3 col-sm-6 col-12">
                <a href="single_product.php?product_id=<?php echo $row['product_id']; ?>">
                <div class="bg-white divProduct">
                    <div id="product-card-img">
                        <img class="img-fluid mb-3" src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    </div>
                    <h5 class="p-name fw-light"><?php echo htmlspecialchars($row['product_name']); ?></h5>
                    <?php if ($row['product_special_offer'] > 0): ?>
                    <?php 
                    // Calcula o preço original
                    $original_price = $row['product_price'] / (1 - $row['product_special_offer'] / 100);
                    ?>
                    <p class="p-price text-decoration-line-through text-muted mt-1 mb-1"><small>De R$ <?php echo number_format($original_price, 2, ',', '.'); ?><span class="badge bg-danger ms-2">-<?php echo htmlspecialchars($row['product_special_offer']); ?>%</span></small></p>
                <?php endif; ?>
                    <h4 class="p-price">R$ <?php echo number_format($row['product_price'], 2, ',', '.'); ?></h4>
                </div>
                </a>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p class="lead">Nenhum produto encontrado no catálogo no momento.</p>
            </div>
        <?php endif; ?>
    </div>

    <nav aria-label="Page navigation example" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</section>
<?php include('layouts/footer.php'); ?>
