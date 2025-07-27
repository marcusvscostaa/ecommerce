<?php include('layouts/header.php'); ?>
<style>
    /* Estilos específicos para a página blog.php */
    .blog-post {
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    .blog-post img {
        max-width: 100%;
        height: auto;
        border-radius: 6px;
        margin-bottom: 15px;
    }
    .blog-post h3 {
        color: #333;
        margin-bottom: 10px;
    }
    .blog-post p {
        color: #666;
        line-height: 1.6;
    }
    .blog-post .read-more {
        color: coral;
        text-decoration: none;
        font-weight: bold;
    }
    .blog-post .read-more:hover {
        text-decoration: underline;
    }

</style>
<section class="my-5 py-5">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="blog-post">
                    <p class="text-muted"><small>25 de Julho de 2025 por Equipe Xain</small></p>
                    <h3>Como Escolher o Notebook Ideal para Você</h3>
                    <p>No universo dos notebooks, a variedade é enorme. Desde modelos compactos e leves para o dia a dia até máquinas potentes para jogos e trabalho pesado, saber escolher o ideal pode ser um desafio. Considere o processador, a memória RAM, o armazenamento (SSD é fundamental!) e a placa de vídeo...</p>
                    <a href="#" class="read-more">Leia Mais &rarr;</a>
                </div>

                <div class="blog-post">
                    <p class="text-muted"><small>18 de Julho de 2025 por Marketing Xain</small></p>
                    <h3>Dicas para Organizar Sua Casa com Produtos do Marketplace</h3>
                    <p>Manter a casa organizada pode transformar seu bem-estar. Com os produtos certos do nosso marketplace, essa tarefa se torna muito mais fácil. Cestas organizadoras, prateleiras flutuantes e potes herméticos são apenas alguns exemplos de como você pode otimizar seus espaços...</p>
                    <a href="#" class="read-more">Leia Mais &rarr;</a>
                </div>

                <div class="blog-post">
                    <p class="text-muted"><small>10 de Julho de 2025 por Equipe de Produtos</small></p>
                    <h3>As Tendências da Moda Verão 2026: O que Você Precisa Ter!</h3>
                    <p>Prepare-se para o verão de 2026 com as tendências mais quentes que já estão bombando. Cores vibrantes, tecidos leves e cortes ousados dominam as coleções. Descubra como montar looks incríveis com as peças disponíveis no Xain...</p>
                    <a href="#" class="read-more">Leia Mais &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('layouts/footer.php'); ?>
