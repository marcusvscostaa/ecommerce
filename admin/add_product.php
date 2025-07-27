<?php
include('../server/connection.php'); 
$message = '';

if (isset($_POST['add_product'])) {
    $product_name = $_POST['name'] ?? '';
    $product_category = $_POST['category'] ?? '';
    $product_description = $_POST['description'] ?? '';
    $product_price = $_POST['price'] ?? 0.00;
    $product_special_offer = $_POST['offer'] ?? NULL; 
    $product_color = $_POST['color'] ?? NULL;      
    if (empty($product_name) || empty($product_category) || empty($product_description) || empty($product_price)) {
        $message = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        $image_names = []; 
        for ($i = 1; $i <= 4; $i++) {
            $image_key = 'image' . ($i > 1 ? $i : ''); 
            
            if (isset($_FILES[$image_key]) && $_FILES[$image_key]['error'] === UPLOAD_ERR_OK) {
                $file_tmp_name = $_FILES[$image_key]['tmp_name'];
                $file_name = $_FILES[$image_key]['name'];
                
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                
                $new_file_name = uniqid() . '.' . $file_extension;
                
                $upload_dir = '../assets/imgs/'; 

                $target_file = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_name, $target_file)) {
                    $image_names[$i] = $new_file_name; 
                } else {
                    $message = "Erro ao mover o arquivo de imagem " . $file_name . ".";
                    if ($i === 1) { 
                        $message = "Erro ao fazer upload da imagem principal.";
                        break; 
                    } else {
                        $image_names[$i] = NULL; 
                    }
                }
            } else {
                $image_names[$i] = NULL;
            }
        }
        
        if (empty($image_names[1])) {
             if (empty($message)) { 
                 $message = "A imagem principal do produto é obrigatória.";
             }
        } else {
            $stmt = $conn->prepare("INSERT INTO products (product_name, product_category, product_description, product_image, product_image2, product_image3, product_image4, product_price, product_special_offer, product_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param('sssssssdis', 
                $product_name, 
                $product_category, 
                $product_description, 
                $image_names[1],    
                $image_names[2],    
                $image_names[3],    
                $image_names[4],    
                $product_price, 
                $product_special_offer, 
                $product_color
            );

            if ($stmt->execute()) {
                $message = "Produto adicionado com sucesso!";
                header('Location: products.php?success_message=' . urlencode($message));
                exit;
            } else {
                $message = "Erro ao adicionar produto: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<?php include('./header.php');?>
<div class="container-fluid">
    <div class="row">
        <?php include('./sidemenu.php'); ?>
        <p>Orders</p>
    </div>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Adicionar Novo Produto</h1>
        </div>

        <p>Preencha os detalhes para cadastrar um novo produto no seu marketplace.</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card mt-4 mb-5">
            <div class="card-header">
                Dados do Produto
            </div>
            <div class="card-body ">
                <form method="POST" action="add_product.php" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label for="productName" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="productCategory" class="form-label">Categoria</label>
                        <select class="form-select" id="productCategory" name="category" required>
                            <option value="">Selecione uma categoria</option>
                            <option value="Roupas">Roupas</option>
                            <option value="Calçados">Calçados</option>
                            <option value="Eletrônicos">Eletrônicos</option>
                            <option value="Acessórios">Acessórios</option>
                            <option value="Casa e Decoração">Casa e Decoração</option>
                            </select>
                    </div>

                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Descrição</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Preço (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="productPrice" name="price" required>
                    </div>

                    <div class="mb-3">
                        <label for="productOffer" class="form-label">Oferta Especial (%)</label>
                        <input type="number" class="form-control" id="productOffer" name="offer" placeholder="Ex: 10 (para 10%)">
                    </div>

                    <div class="mb-3">
                        <label for="productColor" class="form-label">Cor</label>
                        <input type="text" class="form-control" id="productColor" name="color" placeholder="Ex: Azul, Preto">
                    </div>

                    <h5 class="mt-4 mb-3">Imagens do Produto (até 4)</h5>
                    <div class="mb-3">
                        <label for="productImage1" class="form-label">Imagem Principal (Obrigatória)</label>
                        <input type="file" class="form-control" id="productImage1" name="image" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="productImage2" class="form-label">Imagem 2</label>
                        <input type="file" class="form-control" id="productImage2" name="image2" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="productImage3" class="form-label">Imagem 3</label>
                        <input type="file" class="form-control" id="productImage3" name="image3" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="productImage4" class="form-label">Imagem 4</label>
                        <input type="file" class="form-control" id="productImage4" name="image4" accept="image/*">
                    </div>

                    <button type="submit" name="add_product" class="btn btn-success mt-3 me-2">Adicionar Produto</button>
                    <a href="products.php" class="btn btn-secondary mt-3">Cancelar</a>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include('./footer.php');?>