document.addEventListener("DOMContentLoaded", function () {
    // Configuration - dynamic based on screen size
    let itemsPerSlide = window.innerWidth < 720 ? 1 : 3; // Responsive items per slide
    // totalItems deve ser o número real de produtos buscados do seu banco de dados
    // Você precisa passar esse valor do PHP para o JavaScript.
    // Ex: <script> const totalItems = <?php echo $promo_products->num_rows; ?>; </script>
    // Ou, como alternativa, contar os itens multi-carousel-item após o DOMContentLoaded
    const originalItemsCount = document.querySelectorAll(".multi-carousel-item:not(.clone)").length;
    let totalItems = originalItemsCount > 0 ? originalItemsCount : 1; // Pelo menos 1 para evitar divisão por zero

    let slideBy = window.innerWidth < 720 ? 1 : 1; // How many items to advance/retreat per click

    // DOM elements
    const carousel = document.getElementById("multiCarousel");
    const carouselInner = document.getElementById("carouselInner");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");

    // Função para update configuration based on screen size
    function updateConfig() {
        const isMobile = window.innerWidth < 720;
        itemsPerSlide = isMobile ? 1 : 3;
        slideBy = isMobile ? 1 : 1;
        // Recalcular totalItems se necessário, embora idealmente venha do PHP
        totalItems = document.querySelectorAll(".multi-carousel-item:not(.clone)").length;
        if (totalItems === 0) totalItems = 1; // Fallback
    }

    // Dynamically add clone elements
    function initializeClones() {
        const originalItems = Array.from(
            document.querySelectorAll(".multi-carousel-item:not(.clone)")
        );
        totalItems = originalItems.length; // Atualiza totalItems
        if (totalItems === 0) { // Não faz nada se não houver itens
             console.warn("Nenhum item original encontrado para o carrossel.");
             return;
        }

        // Clear existing clones
        document.querySelectorAll(".clone").forEach((clone) => clone.remove());

        // Prepend clones of last items
        const lastClones = originalItems
            .slice(-itemsPerSlide)
            .map((item) => {
                const clone = item.cloneNode(true);
                clone.classList.add("clone");
                return clone;
            })
            .reverse();
        lastClones.forEach((clone) => carouselInner.prepend(clone));

        // Append clones of first items
        const firstClones = originalItems.slice(0, itemsPerSlide).map((item) => {
            const clone = item.cloneNode(true);
            clone.classList.add("clone");
            return clone;
        });
        firstClones.forEach((clone) => carouselInner.append(clone));
    }

    // Calculate and set the height for carousel items
    function setCarouselHeight() {
        const windowHeight = window.innerHeight;
        // Certifique-se de que carouselContainer exista
        const carouselContainer = carousel ? carousel.closest(".container-fluid") : null;

        const containerRect = carouselContainer
            ? carouselContainer.getBoundingClientRect()
            : { top: 0 };
        const availableHeight = windowHeight - containerRect.top - 100; // 100px for padding/margins

        const carouselHeight = Math.max(availableHeight, 300); // Minimum height

        document.documentElement.style.setProperty(
            "--carousel-height",
            `${carouselHeight}px`
        );
    }

    // Initial setup
    updateConfig(); // Atualiza itensPerSlide e slideBy
    initializeClones(); // Cria os clones e atualiza totalItems
    setCarouselHeight();

    // Start with the first real set of images
    let currentIndex = 0;
    let position = itemsPerSlide; // Posição inicial para exibir os primeiros itens reais
    let isAnimating = false;

    // Update carousel position
    function updateCarouselPosition(animate = true) {
        if (animate) {
            carouselInner.style.transition = "transform 0.5s ease";
        } else {
            carouselInner.style.transition = "none";
        }

        const translateX = (position * -100) / itemsPerSlide;
        carouselInner.style.transform = `translateX(${translateX}%)`;
    }

    // Initialize position
    updateCarouselPosition(false);

    // Handle transition end
    carouselInner.addEventListener("transitionend", function () {
        isAnimating = false;

        // Handle infinite loop logic
        // Se a posição atual for igual ou maior que totalItems + itemsPerSlide, significa que passamos dos itens reais e entramos nos clones do início.
        if (position >= totalItems + itemsPerSlide) {
            position = itemsPerSlide + (position - (totalItems + itemsPerSlide));
            updateCarouselPosition(false); // Reset sem animação
        } 
        // Se a posição for menor que itemsPerSlide, significa que passamos dos clones do final e entramos nos itens reais do final.
        else if (position < itemsPerSlide) {
            position = totalItems + position;
            updateCarouselPosition(false); // Reset sem animação
        }

        currentIndex = (position - itemsPerSlide) % totalItems;
    });

    // Navigation functions
    function next() {
        if (isAnimating) return;
        isAnimating = true;
        position += slideBy;
        updateCarouselPosition();
    }

    function prev() {
        if (isAnimating) return;
        isAnimating = true;
        position -= slideBy;
        updateCarouselPosition();
    }

    // Event listeners for buttons
    if (nextBtn) nextBtn.addEventListener("click", next);
    if (prevBtn) prevBtn.addEventListener("click", prev);

    // Mouse drag functionality
    let isDragging = false;
    let startX = 0;
    let startPosition = 0;

    // Prevent image drag (added pointer-events: none in CSS too)
    const carouselImages = document.querySelectorAll("#carouselInner img");
    carouselImages.forEach((img) => {
        img.addEventListener("dragstart", (e) => {
            e.preventDefault();
        });
        // img.style.pointerEvents = "none"; // Já está no CSS
    });

    // Certifique-se de que o carrossel existe antes de adicionar listeners
    if (carousel) {
        carousel.addEventListener("mousedown", startDrag);
        carousel.addEventListener("touchstart", startDrag, { passive: true });

        carousel.addEventListener("mousemove", drag);
        carousel.addEventListener("touchmove", drag, { passive: true });

        carousel.addEventListener("mouseup", endDrag);
        carousel.addEventListener("touchend", endDrag);
        carousel.addEventListener("mouseleave", endDrag); // Para quando o mouse sai do contêiner durante o arrasto
    }


    function startDrag(e) {
        // Prevenir o comportamento padrão de arrastar para imagens
        if (e.target.tagName === "IMG") {
            e.preventDefault();
        }

        if (isAnimating) return; // Se uma animação está em andamento, ignore novo arrasto

        isDragging = true;
        startX = e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
        startPosition = position; // Armazena a posição atual antes de arrastar
        carousel.classList.add("dragging");
        carouselInner.style.transition = "none"; // Remove transição durante o arrasto
        document.body.style.cursor = "grabbing"; // Feedback visual do cursor
        document.body.style.userSelect = "none"; // Previne seleção de texto
        registerUserActivity(); // Reseta o timer de auto-avanço
    }

    function drag(e) {
        if (!isDragging) return;

        const x = e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
        const walk = ((x - startX) / carousel.offsetWidth) * itemsPerSlide; // Calcula o "passo" do arrasto
        const newPosition = startPosition - walk; // Nova posição baseada no arrasto
        const translateX = (newPosition * -100) / itemsPerSlide; // Calcula a translação CSS
        carouselInner.style.transform = `translateX(${translateX}%)`; // Aplica a translação
    }

    function endDrag(e) {
        if (!isDragging) return;

        isDragging = false; // Termina o estado de arrasto
        carousel.classList.remove("dragging"); // Remove classe de arrasto
        document.body.style.cursor = ""; // Restaura cursor
        document.body.style.userSelect = ""; // Restaura seleção de texto
        carouselInner.style.transition = "transform 0.5s ease"; // Restaura transição para animação suave

        // Calcula o "walk" final para decidir se avança ou retrocede
        const x = e.type?.includes("mouse")
            ? e.clientX
            : e.changedTouches // Para touchend, use changedTouches
            ? e.changedTouches[0].clientX
            : startX; // Fallback
        const walk = ((x - startX) / carousel.offsetWidth) * itemsPerSlide;

        if (walk > 0.2) { // Se arrastou o suficiente para a direita
            prev();
        } else if (walk < -0.2) { // Se arrastou o suficiente para a esquerda
            next();
        } else {
            updateCarouselPosition(); // Volta para a posição original se não arrastou o suficiente
        }

        registerUserActivity(); // Reseta o timer
    }

    // Keyboard navigation
    document.addEventListener("keydown", function (e) {
        if (
            carousel && carousel.offsetParent === null || // Adicionado verificação de carousel
            document.activeElement.tagName === "INPUT" ||
            document.activeElement.tagName === "TEXTAREA" ||
            document.activeElement.isContentEditable
        ) {
            return;
        }

        switch (e.key) {
            case "ArrowLeft":
                e.preventDefault();
                prev();
                registerUserActivity();
                break;
            case "ArrowRight":
                e.preventDefault();
                next();
                registerUserActivity();
                break;
        }
    });

    // Auto-advance system
    let autoAdvanceInterval;
    let userActivityTimeout;

    function startAutoAdvance() {
        clearInterval(autoAdvanceInterval); // Limpa qualquer intervalo existente
        autoAdvanceInterval = setInterval(next, 5000); // Inicia o avanço automático a cada 5 segundos
    }

    function resetAutoAdvanceTimer() {
        clearTimeout(userActivityTimeout); // Limpa o timeout de atividade do usuário
        clearInterval(autoAdvanceInterval); // Limpa o intervalo de avanço automático
        userActivityTimeout = setTimeout(startAutoAdvance, 10000); // Reinicia o timer para auto-avanço após 10 segundos de inatividade
    }

    function registerUserActivity() {
        resetAutoAdvanceTimer(); // Chama para registrar qualquer atividade do usuário
    }

    // Inicia o avanço automático quando o DOM está pronto
    startAutoAdvance();

    // Pausa o auto-avanço ao passar o mouse sobre o carrossel
    if (carousel) { // Adicionada verificação para carousel
        carousel.addEventListener("mouseenter", () => {
            clearInterval(autoAdvanceInterval);
        });

        // Reinicia o auto-avanço ao remover o mouse do carrossel
        carousel.addEventListener("mouseleave", () => {
            resetAutoAdvanceTimer();
        });

        // Registra atividade do usuário em outros eventos
        carousel.addEventListener("click", registerUserActivity);
        carousel.addEventListener("wheel", registerUserActivity); // Roda do mouse
    }


    // Handle window resize
    window.addEventListener("resize", function () {
        const wasMobile = itemsPerSlide === 1;
        updateConfig();
        setCarouselHeight();

        // Only reinitialize if mobile state changed
        if (
            (wasMobile && itemsPerSlide > 1) ||
            (!wasMobile && itemsPerSlide === 1)
        ) {
            initializeClones();
            position = itemsPerSlide; // Reset position
            updateCarouselPosition(false);
        }
    });
    
});