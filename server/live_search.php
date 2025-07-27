<?php
include('connection.php'); // Sua conexão com o banco de dados

header('Content-Type: application/json'); // Informa que a resposta é JSON

$search_query = $_GET['query'] ?? ''; // Pega a query de busca (ex: 'note')
$results = [];

if (!empty($search_query)) {
    $search_param = "%" . $search_query . "%";
    
    // Busca até 5 produtos que correspondam ao nome
    $stmt = $conn->prepare("SELECT product_id, product_name, product_image, product_price FROM products WHERE product_name LIKE ? LIMIT 5");
    $stmt->bind_param('s', $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $results[] = [
            'id' => $row['product_id'],
            'name' => $row['product_name'],
            'image' => 'assets/imgs/' . $row['product_image'], // Caminho relativo da raiz
            'price' => number_format($row['product_price'], 2, ',', '.')
        ];
    }
    $stmt->close();
}

echo json_encode($results); // Retorna os resultados como JSON
?>