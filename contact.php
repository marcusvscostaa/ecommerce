<?php include('layouts/header.php'); ?>
<style>
    /* Estilos específicos para a página contact.php */
    #contact-info {
        padding: 40px 0;
    }
    #contact-info h3 {
        color: #000;
        margin-bottom: 20px;
    }
    #contact-info p {
        font-size: 1rem;
        margin-bottom: 10px;
    }
    #contact-info .icon-box {
        font-size: 1rem;
        color: #000;
        margin-right: 10px;
    }
    #contact-form .container {
        max-width: 700px;
        padding: 30px;
        border-radius: 4px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }
    #contact-form .btn-primary {
        background-color: #000;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    #contact-form .btn-primary:hover {
        background-color: rgba(43, 43, 43, 1);
    }
</style>
<section class="my-5 py-5">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="d-flex">
                    <div class="w-100 p-4" id="contact-info">
                        <div class="border-bottom pb-2 border-secondary  mb-3">
                            <h6 class="fw-bolder">Nosso Contato</h6>
                        </div>
                        <p><i class="fas fa-map-marker-alt icon-box"></i> Rua Exemplo, 123, Cidade, Estado</p>
                        <p><i class="fas fa-phone icon-box"></i> (XX) XXXX-XXXX</p>
                        <p><i class="fas fa-envelope icon-box"></i> contato@minhaloja.com</p>
                        <p><i class="fas fa-clock icon-box"></i> Segunda a Sexta: 9h às 18h</p>
                        <p class="mt-4">Siga-nos nas Redes Sociais:</p>
                        <div class="social-icons">
                            <a href="#" class="btn btn-outline-dark me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-outline-dark me-2"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="btn btn-outline-dark"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8" id="contact-form">
                <div class="card p-4">
                    <div class="border-bottom pb-2 border-secondary  mb-3">
                        <h6 class="fw-bolder">Envie Sua Mensagem</h6>
                    </div>
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info mt-3 dismissible-alert" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="contact.php" method="POST" class="mt-3 needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nameInput" class="form-label">Seu Nome</label>
                            <input type="text" class="form-control" id="nameInput" name="name" required>
                            <div class="invalid-feedback">Por favor, digite seu nome.</div>
                        </div>
                        <div class="mb-3">
                            <label for="emailInput" class="form-label">Seu E-mail</label>
                            <input type="email" class="form-control" id="emailInput" name="email" required>
                            <div class="invalid-feedback">Por favor, digite um e-mail válido.</div>
                        </div>
                        <div class="mb-3">
                            <label for="subjectInput" class="form-label">Assunto</label>
                            <input type="text" class="form-control" id="subjectInput" name="subject" required>
                            <div class="invalid-feedback">Por favor, digite o assunto da mensagem.</div>
                        </div>
                        <div class="mb-3">
                            <label for="messageTextarea" class="form-label">Mensagem</label>
                            <textarea class="form-control" id="messageTextarea" name="message" rows="5" required></textarea>
                            <div class="invalid-feedback">Por favor, digite sua mensagem.</div>
                        </div>
                        <div class="d-flex w-100">
                            <button type="submit" name="send_message" class="btn btn-primary btn-lg mt-3 ms-auto">Enviar Mensagem</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('layouts/footer.php'); ?>
