<?php
include('../server/connection.php');

$stmt_latest = $conn->prepare("SELECT order_id, order_cost, order_status, order_date FROM orders ORDER BY order_date DESC LIMIT 5");
$stmt_latest->execute();
$latest_orders = $stmt_latest->get_result();
$stmt_pending_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE order_status = 'on_hold'");
$stmt_pending_orders->execute();
$stmt_pending_orders->bind_result($pending_orders_count);
$stmt_pending_orders->fetch();
$stmt_pending_orders->close();


$stmt_total_revenue = $conn->prepare("SELECT SUM(order_cost) FROM orders WHERE order_status IN ('paid', 'delivered')");
$stmt_total_revenue->execute();
$stmt_total_revenue->bind_result($total_revenue);
$stmt_total_revenue->fetch();
$stmt_total_revenue->close();
$total_revenue = $total_revenue ?? 0;
$stmt_total_users = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt_total_users->execute();
$stmt_total_users->bind_result($total_users_count); 
$stmt_total_users->fetch();
$stmt_total_users->close();
?>
<?php include('./header.php'); ?>
<div class="container-fluid">
    <div class="row">
        <?php include('./sidemenu.php'); ?>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar">
                <?php include('./sidemenu.php'); ?>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3">Dashboard - Visão Geral</h1>
                <p>Bem-vindo ao painel administrativo do Xain, <?php echo $_SESSION['admin_name'] ?? 'Administrador'; ?>!</p>
                
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">Pedidos Pendentes</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $pending_orders_count; ?> Pedidos</h5> <p class="card-text">Total de pedidos aguardando processamento.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">Faturamento Total</div>
                            <div class="card-body">
                                <h5 class="card-title">R$ <?php echo number_format($total_revenue, 2, ',', '.'); ?></h5> <p class="card-text">Receita total até o momento.</p>
                            </div>
                        </div>
                    </div>
                   <div class="col-lg-4 col-md-6">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-header">Total de Usuários</div> <div class="card-body">
                                <h5 class="card-title"><?php echo $total_users_count; ?> Usuários</h5> <p class="card-text">Número total de usuários cadastrados.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h2 class="mt-5">Últimos Pedidos (Visão Rápida)</h2>
                <div class="table-responsive small mt-3">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Custo</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($latest_orders->num_rows > 0): ?>
                                <?php while($row = $latest_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['order_id']; ?></td>
                                    <td>R$ <?php echo number_format($row['order_cost'], 2, ',', '.'); ?></td>
                                    <td><?php echo $row['order_status']; ?></td>
                                    <td><?php echo $row['order_date']; ?></td>
                                    <td><a class="btn btn-primary btn-sm" href="edit_order.php?order_id=<?php echo $row['order_id']; ?>">Ver/Editar</a></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum pedido recente.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-2">
                    <a href="orders.php" class="btn btn-outline-secondary">Ver Todos os Pedidos <i class="fas fa-arrow-right"></i></a>
                </div>
            </main>
        </div>
    </div>
</div>
<?php include('./footer.php'); ?>