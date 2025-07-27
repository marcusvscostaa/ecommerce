<?php
include('../server/connection.php'); 
$message = ''; 
$product = null; 

if (isset($_POST['edit_product_btn'])) {
    $product_id = $_POST['product_id'] ?? null;
    $product_name = $_POST['name'] ?? '';
    $product_category = $_POST['category'] ?? '';
    $product_description = $_POST['description'] ?? '';
    $product_price = $_POST['price'] ?? 0.00;
    $product_special_offer = $_POST['offer'] ?? null; 
    $product_color = $_POST['color'] ?? null;  

    if (empty($product_id) || empty($product_name) || empty($product_category) || empty($product_description) || empty($product_price)) {
        $message = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, product_category=?, product_description=?, product_price=?, product_special_offer=?, product_color=? WHERE product_id=?");
        
        $stmt->bind_param('ssssisi',
            $product_name,
            $product_category,
            $product_description,
            $product_price,
            $product_special_offer, 
            $product_color,
            $product_id
        );

        if ($stmt->execute()) {
            $message = "Produto atualizado com sucesso!";
            $stmt_re = $conn->prepare("SELECT product_id, product_name, product_category, product_description, product_price, product_special_offer, product_color, product_image, product_image2, product_image3, product_image4 FROM products WHERE product_id = ? LIMIT 1");
            $stmt_re->bind_param('i', $product_id);
            $stmt_re->execute();
            $product = $stmt_re->get_result()->fetch_assoc();
        } else {
            $message = "Erro ao atualizar produto: " . $stmt->error;
        }
        $stmt->close();
    }
}
else if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    
    $stmt = $conn->prepare("SELECT product_id, product_name, product_category, product_description, product_price, product_special_offer, product_color, product_image, product_image2, product_image3, product_image4 FROM products WHERE product_id = ? LIMIT 1");
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
<?php include('./header.php'); ?>

<div class="container-fluid">
    <div class="row">
        <?php include('./sidemenu.php');  ?>
    </div>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Editar Produto</h1>
        </div>

        <p>Modifique os detalhes do produto selecionado.</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($product) && $product): ?>
        <div class="card mt-4">
            <div class="card-header">
                Dados do Produto #<?php echo $product['product_id']; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="edit_product.php">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    
                    <div class="mb-3">
                        <label for="productName" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="productName" name="name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="productCategory" class="form-label">Categoria</label>
                        <select class="form-select" id="productCategory" name="category" required>
                            <option value="">Selecione uma categoria</option>
                            <option value="Roupas" <?php if($product['product_category'] == 'Roupas') echo 'selected'; ?>>Roupas</option>
                            <option value="Calçados" <?php if($product['product_category'] == 'Calçados') echo 'selected'; ?>>Calçados</option>
                            <option value="Eletrônicos" <?php if($product['product_category'] == 'Eletrônicos') echo 'selected'; ?>>Eletrônicos</option>
                            <option value="Acessórios" <?php if($product['product_category'] == 'Acessórios') echo 'selected'; ?>>Acessórios</option>
                            <option value="Casa e Decoração" <?php if($product['product_category'] == 'Casa e Decoração') echo 'selected'; ?>>Casa e Decoração</option>
                            </select>
                    </div>

                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Descrição</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="3" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Preço (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="productPrice" name="price" value="<?php echo htmlspecialchars($product['product_price']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="productOffer" class="form-label">Oferta Especial (%)</label>
                        <input type="number" class="form-control" id="productOffer" name="offer" value="<?php echo htmlspecialchars($product['product_special_offer']); ?>" placeholder="Ex: 10 (para 10%)">
                    </div>

                    <div class="mb-3">
                        <label for="productColor" class="form-label">Cor</label>
                        <input type="text" class="form-control" id="productColor" name="color" value="<?php echo htmlspecialchars($product['product_color']); ?>" placeholder="Ex: Azul, Preto">
                    </div>

                    <button type="submit" name="edit_product_btn" class="btn btn-success mt-3 me-2">Salvar Alterações</button>
                    <a href="products.php" class="btn btn-secondary mt-3">Cancelar</a>
                </form>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-warning mt-4" role="alert">
                Nenhum produto encontrado ou ID inválido.
            </div>
        <?php endif; ?>
    </main>
</div>
<?php include('./footer.php'); ?>
