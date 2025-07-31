<?php
include('../server/connection.php'); 

$limit = 10; 
$page = isset($_GET['page']) ? $_GET['page'] : 1; 
$offset = ($page - 1) * $limit;

$stmt_total = $conn->prepare("SELECT COUNT(*) FROM products");
$stmt_total->execute();
$stmt_total->bind_result($total_records); 
$stmt_total->fetch(); 
$stmt_total->close(); 

$total_pages = ceil($total_records / $limit);

$stmt = $conn->prepare("SELECT product_id, product_name, product_category, product_price, product_special_offer, product_color, product_image 
                        FROM products 
                        LIMIT ? 
                        OFFSET ?");
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$products = $stmt->get_result();
?>

<?php include('./header.php'); ?>
<div class="container-fluid">
    <div class="row">
        <?php include('./sidemenu.php'); ?>
        <p>Orders</p>
    </div>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Produtos</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add_product.php" class="btn btn-primary">Adicionar Novo Produto</a>
                </div>
            </div>

            <p>Gerencie os produtos do seu marketplace Xain.</p>

            <div class="table-responsive small mt-4">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Oferta (%)</th>
                            <th>Cor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0): ?>
                            <?php while($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['product_id']; ?></td>
                                <td><img src="../assets/imgs/<?php echo $row['product_image']; ?>" style="width: 70px; height: 70px; object-fit: cover;" alt="<?php echo $row['product_name']; ?>"></td>
                                <td><?php echo $row['product_name']; ?></td>
                                <td><?php echo $row['product_category']; ?></td>
                                <td>R$ <?php echo number_format($row['product_price'], 2, ',', '.'); ?></td>
                                <td><?php echo $row['product_special_offer'] ?? '-'; ?></td>
                                <td><?php echo $row['product_color'] ?? '-'; ?></td>
                                <td>
                                    <a class="btn btn-info btn-sm mb-1" href="edit_images.php?product_id=<?php echo $row['product_id']; ?>">Imagens</a>
                                    <a class="btn btn-primary btn-sm mb-1" href="edit_product.php?product_id=<?php echo $row['product_id']; ?>">Editar</a>
                                    <a class="btn btn-danger btn-sm mb-1" href="delete_product.php?product_id=<?php echo $row['product_id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este produto? Esta ação é irreversível.');">Excluir</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Nenhum produto encontrado. <a href="add_product.php">Adicione um novo produto!</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation example" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span> Anterior
                        </a>
                    </li>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                            Próxima <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </main>
</div>
<?php include('./footer.php'); ?>