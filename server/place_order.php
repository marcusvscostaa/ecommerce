<?php
include('../session.php');

// Inclui a conexão com o banco de dados. O caminho é relativo à pasta 'server/'.
include('connection.php');

// Habilita a exibição de erros para depuração. REMOVER EM PRODUÇÃO.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Proteção de Acesso: Redireciona se o usuário não estiver logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php?message=Por favor, faça login para finalizar a compra.');
    exit;
}

// 2. Verifica se o carrinho está vazio
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ../cart.php?message=Seu carrinho está vazio. Por favor, adicione produtos antes de finalizar a compra.');
    exit;
}

// 3. Verifica se o formulário de checkout foi submetido via POST
// Isso impede que a página seja acessada diretamente sem passar pelo checkout.
if (!isset($_POST['place_order_btn'])) {
    header('Location: ../checkout.php?error=Acesso inválido à página de processamento do pedido.');
    exit;
}

// Coletar dados necessários da sessão
$order_cost = $_SESSION['total_cart_price'] ?? 0;
$order_status = 'on_hold'; // Status inicial do pedido: pendente de pagamento
$user_id = $_SESSION['user_id'] ?? null;

// Dados de envio vêm da sessão (onde foram armazenados por checkout.php)
$shipping_address = $_POST['address'] ?? '';
$shipping_city = $_POST['city'] ?? '';
$shipping_uf = $_POST['uf'] ?? '';
$order_date = date('Y-m-d H:i:s'); // Data e hora atuais do pedido


// Validação final de dados essenciais antes de inserir no banco
if ($user_id === null || $order_cost <= 0 || empty($shipping_address) || empty($shipping_city) || empty($shipping_uf)) {
    header('Location: ../checkout.php?error=Dados do pedido incompletos ou inválidos. Por favor, revise seu carrinho e endereço.');
    exit;
}

// Inicia uma transação no banco de dados para garantir que todas as inserções ocorram juntas ou nenhuma.
$conn->autocommit(FALSE);

try {
    // 4. Inserir o Pedido Principal na Tabela 'orders'
    $stmt_order = $conn->prepare("INSERT INTO orders (order_cost, order_status, user_id, shipping_city, shipping_uf, shipping_address, order_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    // 'disssss' -> double (order_cost), int (user_id), string (order_status), string (city), string (uf), string (address), string (date)
    $stmt_order->bind_param('dsissss', 
        $order_cost, 
        $order_status, 
        $user_id, 
        $shipping_city, 
        $shipping_uf, 
        $shipping_address, 
        $order_date
    );

    if (!$stmt_order->execute()) {
        throw new Exception("Falha ao inserir pedido na tabela 'orders': " . $stmt_order->error);
    }

    $order_id = $conn->insert_id; // Obtém o ID do pedido recém-inserido
    $stmt_order->close();

    // 5. Inserir Cada Item do Carrinho na Tabela 'order_items'
    foreach($_SESSION['cart'] as $product) {
        $product_id = $product['product_id'];
        $product_quantity = $product['product_quantity']; // Corresponde ao seu campo 'qtd'
        
        // ESTA É A CONSULTA CORRETA PARA A SUA ESTRUTURA DE TABELA 'order_items'
        // (que possui order_id, product_id, user_id, qtd, order_date)
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, user_id, qtd, order_date) VALUES (?, ?, ?, ?, ?)");
        // 'iiiis' -> int (order_id), int (product_id), int (user_id), int (qtd), string (order_date)
        $stmt_item->bind_param('iiiis', 
            $order_id, 
            $product_id, 
            $user_id, 
            $product_quantity, // Usando 'product_quantity' que é o valor de 'qtd' do carrinho
            $order_date
        );

        if (!$stmt_item->execute()) {
            throw new Exception("Falha ao inserir item do pedido para produto ID " . $product_id . ": " . $stmt_item->error);
        }
        $stmt_item->close();
    }

    $conn->commit(); // Confirma todas as operações no banco de dados se não houve erros

    // 6. Limpar o carrinho e dados de envio da sessão após o registro bem-sucedido
    unset($_SESSION['cart']);
    unset($_SESSION['shipping_address']);
    unset($_SESSION['shipping_city']);
    unset($_SESSION['shipping_uf']);

    // Armazena o order_id para a próxima página (pagamento)
    $_SESSION['order_id'] = $order_id;

    // 7. Redirecionar para a página de pagamento (Prática 8)
    header('Location: ../payment.php'); 
    exit;

} catch (Exception $e) {
    $conn->rollback(); // Desfaz todas as operações no banco de dados em caso de qualquer erro
    error_log("Erro ao processar pedido: " . $e->getMessage()); // Registra o erro no log do servidor
    // Redireciona para o checkout com uma mensagem de erro
    header('Location: ../checkout.php?error=Erro ao processar seu pedido. Por favor, tente novamente. Detalhes: ' . urlencode($e->getMessage()));
    exit;
} finally {
    // Garante que o autocommit seja restaurado para o comportamento padrão (TRUE)
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->autocommit(TRUE); 
    }
}

?>