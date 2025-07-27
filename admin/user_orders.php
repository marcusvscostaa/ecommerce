<?php
// Inclui a conexão com o banco de dados
include('../server/connection.php'); // Caminho relativo da pasta 'admin/'

$error_message = '';
$user_name = 'Usuário Desconhecido'; // Variável para exibir o nome do usuário
$user_id_filter = null; // ID do usuário que estamos filtrando

// 1. Obter o user_id da URL (via GET)
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id_filter = $_GET['user_id'];

    // Buscar o nome do usuário
    $stmt_user_name = $conn->prepare("SELECT user_name FROM users WHERE user_id = ? LIMIT 1");
    $stmt_user_name->bind_param('i', $user_id_filter);
    $stmt_user_name->execute();
    $result_user_name = $stmt_user_name->get_result();
    if ($result_user_name->num_rows === 1) {
        $user_data = $result_user_name->fetch_assoc();
        $user_name = $user_data['user_name'];
    } else {
        $error_message = "Usuário não encontrado.";
    }
    $stmt_user_name->close();

} else {
    // Se nenhum user_id for fornecido, redireciona para a lista de usuários ou exibe erro
    header('Location: account.php?error_message=ID do usuário não fornecido para ver os pedidos.');
    exit;
}

// --- Lógica de Paginação ---
$limit = 10; // Número de pedidos por página
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Página atual, padrão é 1
$offset = ($page - 1) * $limit;

// 2. Consulta para contar o total de pedidos para ESTE USUÁRIO
$stmt_total = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt_total->bind_param('i', $user_id_filter);
$stmt_total->execute();
$stmt_total->bind_result($total_records);
$stmt_total->fetch();
$stmt_total->close();

$total_pages = ceil($total_records / $limit);

// 3. Consulta para buscar os pedidos para ESTE USUÁRIO com paginação
$orders = []; // Array para armazenar os pedidos

if ($user_id_filter !== null) { // Apenas busca se o ID do usuário for válido
    $stmt = $conn->prepare("SELECT order_id, order_cost, order_status, user_id, shipping_city, shipping_uf, shipping_address, order_date FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('iii', $user_id_filter, $limit, $offset);
    $stmt->execute();
    $orders = $stmt->get_result();
}

?>
<?php include('./header.php'); // Inclui o cabeçalho do admin com a proteção de sessão ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <?php include('./sidemenu.php'); // Inclui o menu lateral ?>
        </div>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Pedidos de <?php echo htmlspecialchars($user_name); ?> (ID: <?php echo htmlspecialchars($user_id_filter); ?>)</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="account.php" class="btn btn-secondary">Voltar para Usuários</a>
                </div>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive small mt-4">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Custo</th>
                            <th>Status</th>
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
                                <td><?php echo $row['shipping_city']; ?></td>
                                <td><?php echo $row['shipping_uf']; ?></td>
                                <td><?php echo $row['shipping_address']; ?></td>
                                <td><?php echo $row['order_date']; ?></td>
                                <td><a class="btn btn-primary btn-sm" href="edit_order.php?order_id=<?php echo $row['order_id']; ?>">Editar</a></td>
                                <td><a class="btn btn-info btn-sm" href="order_details.php?order_id=<?php echo $row['order_id']; ?>">Detalhes</a></td>
                                <td><a class="btn btn-danger btn-sm" href="delete_order.php?order_id=<?php echo $row['order_id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este pedido?');">Excluir</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Nenhum pedido encontrado para este usuário.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation example" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?user_id=<?php echo $user_id_filter; ?>&page=<?php echo $page-1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span> Anterior
                        </a>
                    </li>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="?user_id=<?php echo $user_id_filter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                        <a class="page-link" href="?user_id=<?php echo $user_id_filter; ?>&page=<?php echo $page+1; ?>" aria-label="Next">
                            Próxima <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </main>
    </div>
</div>
<?php include('./footer.php'); ?>