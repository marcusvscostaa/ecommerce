<?php
include('../server/connection.php'); 

$message = ''; 
$product = null; 

if (isset($_POST['edit_images_btn'])) {
    $product_id = $_POST['product_id'] ?? null;

    if (empty($product_id)) {
        $message = "Erro: ID do produto não fornecido para atualização de imagens.";
    } else {
        $stmt_old_images = $conn->prepare("SELECT product_image, product_image2, product_image3, product_image4 FROM products WHERE product_id = ? LIMIT 1");
        $stmt_old_images->bind_param('i', $product_id);
        $stmt_old_images->execute();
        $old_images = $stmt_old_images->get_result()->fetch_assoc();
        $stmt_old_images->close();

        $updated_image_names = [];

        for ($i = 1; $i <= 4; $i++) {
            $input_name = 'image' . ($i > 1 ? $i : ''); 
            $db_column = 'product_image' . ($i > 1 ? $i : ''); 

            if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                $file_tmp_name = $_FILES[$input_name]['tmp_name'];
                $file_name = $_FILES[$input_name]['name'];
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '.' . $file_extension;
                $upload_dir = '../assets/imgs/';
                $target_file = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_name, $target_file)) {
                    if (!empty($old_images[$db_column]) && file_exists($upload_dir . $old_images[$db_column])) {
                        unlink($upload_dir . $old_images[$db_column]); 
                    }
                    $updated_image_names[$db_column] = $new_file_name; 
                } else {
                    $message = "Erro ao mover o arquivo de imagem " . $file_name . ".";
                    if ($i === 1 && empty($old_images['product_image'])) { 
                         $message = "Erro crítico no upload da imagem principal.";
                         break;
                    }
                    $updated_image_names[$db_column] = $old_images[$db_column]; 
                }
            } else {
                $updated_image_names[$db_column] = $old_images[$db_column];
            }
        }

        $stmt_update = $conn->prepare("UPDATE products SET product_image=?, product_image2=?, product_image3=?, product_image4=? WHERE product_id=?");
        
        $stmt_update->bind_param('ssssi',
            $updated_image_names['product_image'],
            $updated_image_names['product_image2'],
            $updated_image_names['product_image3'],
            $updated_image_names['product_image4'],
            $product_id
        );

        if ($stmt_update->execute()) {
            $message = "Imagens do produto atualizadas com sucesso!";
            $stmt_re = $conn->prepare("SELECT product_id, product_name, product_image, product_image2, product_image3, product_image4 FROM products WHERE product_id = ? LIMIT 1");
            $stmt_re->bind_param('i', $product_id);
            $stmt_re->execute();
            $product = $stmt_re->get_result()->fetch_assoc();
        } else {
            $message = "Erro ao atualizar imagens no banco de dados: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
} 
else if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    
    $stmt = $conn->prepare("SELECT product_id, product_name, product_image, product_image2, product_image3, product_image4 FROM products WHERE product_id = ? LIMIT 1");
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
            <?php include('./sidemenu.php'); ?>
    </div>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Editar Imagens do Produto</h1>
        </div>

        <p>Modifique as imagens do produto "<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>".</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($product) && $product): ?>
        <div class="card mt-4 mb-5">
            <div class="card-header">
                Imagens Atuais do Produto #<?php echo $product['product_id']; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="edit_images.php" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    
                    <div class="mb-3">
                        <h5>Imagens Atuais:</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="../assets/imgs/<?php echo htmlspecialchars($product['product_image']); ?>" class="product-image-thumbnail" alt="Imagem Principal">
                            <?php endif; ?>
                            <?php if (!empty($product['product_image2'])): ?>
                                <img src="../assets/imgs/<?php echo htmlspecialchars($product['product_image2']); ?>" class="product-image-thumbnail" alt="Imagem 2">
                            <?php endif; ?>
                            <?php if (!empty($product['product_image3'])): ?>
                                <img src="../assets/imgs/<?php echo htmlspecialchars($product['product_image3']); ?>" class="product-image-thumbnail" alt="Imagem 3">
                            <?php endif; ?>
                            <?php if (!empty($product['product_image4'])): ?>
                                <img src="../assets/imgs/<?php echo htmlspecialchars($product['product_image4']); ?>" class="product-image-thumbnail" alt="Imagem 4">
                            <?php endif; ?>
                            <?php if (empty($product['product_image']) && empty($product['product_image2']) && empty($product['product_image3']) && empty($product['product_image4'])): ?>
                                <p class="text-muted">Nenhuma imagem cadastrada para este produto.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3">Upload de Novas Imagens:</h5>
                    <p class="text-muted">Selecione um arquivo para substituir a imagem existente no slot. Deixe em branco para manter a imagem atual.</p>
                    
                    <div class="mb-3">
                        <label for="productImage1" class="form-label">Imagem Principal (Slot 1)</label>
                        <input type="file" class="form-control" id="productImage1" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="productImage2" class="form-label">Imagem 2 (Slot 2)</label>
                        <input type="file" class="form-control" id="productImage2" name="image2" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="productImage3" class="form-label">Imagem 3 (Slot 3)</label>
                        <input type="file" class="form-control" id="productImage3" name="image3" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="productImage4" class="form-label">Imagem 4 (Slot 4)</label>
                        <input type="file" class="form-control" id="productImage4" name="image4" accept="image/*">
                    </div>

                    <button type="submit" name="edit_images_btn" class="btn btn-success mt-3 me-2">Atualizar Imagens</button>
                    <a href="products.php" class="btn btn-secondary mt-3">Voltar aos Produtos</a>
                </form>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-warning mt-4" role="alert">
                Nenhum produto encontrado ou ID inválido para edição de imagens.
            </div>
        <?php endif; ?>
    </main>
</div>
<?php include('./footer.php'); ?>
