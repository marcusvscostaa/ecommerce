<?php
include('../server/connection.php'); 

$limit = 10; 
$page = isset($_GET['page']) ? $_GET['page'] : 1; 

$offset = ($page - 1) * $limit;

$stmt_total = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt_total->execute();
$stmt_total->bind_result($total_records); 
$stmt_total->fetch(); 
$stmt_total->close(); 

$total_pages = ceil($total_records / $limit);

$stmt = $conn->prepare("SELECT user_id, user_name, user_email FROM users LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $limit, $offset); 
$stmt->execute();
$users = $stmt->get_result(); 
?>
<?php include('./header.php'); ?>
 <div class="container-fluid">
        <div class="row">
                <?php include('./sidemenu.php'); ?>
        </div>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Usuários</h1>
                </div>

            <p>Visualize todos os usuários cadastrados no seu marketplace Xain.</p>

            <div class="table-responsive small mt-4">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID Usuário</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while($row = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                <td>
                                    <a class="btn btn-info btn-sm mb-1" href="user_orders.php?user_id=<?php echo $row['user_id']; ?>">Ver Pedidos</a>
                                    </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Nenhum usuário encontrado.</td>
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
<?php include('./footer.php');?>
