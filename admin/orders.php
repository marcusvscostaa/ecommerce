<?php
include('../server/connection.php');

$limit = 10; 
$page = isset($_GET['page']) ? $_GET['page'] : 1; 
$offset = ($page - 1) * $limit;

$stmt_total = $conn->prepare("SELECT COUNT(*) FROM orders");
$stmt_total->execute();
$stmt_total->bind_result($total_records);
$stmt_total->fetch();
$stmt_total->close();

$total_pages = ceil($total_records / $limit);

$stmt = $conn->prepare("SELECT order_id, order_cost, order_status, user_id, shipping_city, shipping_uf, shipping_address, order_date FROM orders ORDER BY order_date DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$orders = $stmt->get_result();

?>
<?php include('./header.php'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <?php include('./sidemenu.php'); ?>
        </div>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 mt-3">Todos os Pedidos</h1>
            <p>Aqui você pode visualizar e gerenciar todos os pedidos do seu marketplace.</p>

            <div class="table-responsive small mt-4">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Custo</th>
                            <th>Status</th>
                            <th>ID Usuário</th>
                            <th>Cidade</th>
                            <th>UF</th>
                            <th>Endereço</th>
                            <th>Data Pedido</th>
                            <th>Editar</th>
                            <th>Detalhes</th>
                            <th>Excluir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while($row = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['order_id']; ?></td>
                                <td>R$ <?php echo number_format($row['order_cost'], 2, ',', '.'); ?></td>
                                <td><?php echo $row['order_status']; ?></td>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['shipping_city']; ?></td>
                                <td><?php echo $row['shipping_uf']; ?></td>
                                <td><?php echo $row['shipping_address']; ?></td>
                                <td><?php echo $row['order_date']; ?></td>
                                <td><a class="btn btn-primary btn-sm" href="edit_order.php?order_id=<?php echo $row['order_id']; ?>">Editar</a></td>
                                <td><a class="btn btn-info btn-sm" href="order_details.php?order_id=<?php echo $row['order_id']; ?>">Detalhes</a></td>
                                <td><a class="btn btn-danger btn-sm" href="delete_order.php?order_id=<?php echo $row['order_id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este pedido? Esta ação é irreversível.');">Excluir</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">Nenhum pedido encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
        </main>
    </div>
</div>
<?php include('./footer.php'); ?>
