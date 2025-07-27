document.addEventListener("DOMContentLoaded", function () {
    let itemsPerSlide = window.innerWidth < 720 ? 1 : 3; 
    const originalItemsCount = document.querySelectorAll(".multi-carousel-item:not(.clone)").length;
    let totalItems = originalItemsCount > 0 ? originalItemsCount : 1; 

    let slideBy = window.innerWidth < 720 ? 1 : 1; 

    const carousel = document.getElementById("multiCarousel");
    const carouselInner = document.getElementById("carouselInner");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");

    function updateConfig() {
        const isMobile = window.innerWidth < 720;
        itemsPerSlide = isMobile ? 1 : 3;
        slideBy = isMobile ? 1 : 1;
        totalItems = document.querySelectorAll(".multi-carousel-item:not(.clone)").length;
        if (totalItems === 0) totalItems = 1; 
    }

    function initializeClones() {
        const originalItems = Array.from(
            document.querySelectorAll(".multi-carousel-item:not(.clone)")
        );
        totalItems = originalItems.length; 
        if (totalItems === 0) { 
             console.warn("Nenhum item original encontrado para o carrossel.");
             return;
        }

        document.querySelectorAll(".clone").forEach((clone) => clone.remove());

        const lastClones = originalItems
            .slice(-itemsPerSlide)
            .map((item) => {
                const clone = item.cloneNode(true);
                clone.classList.add("clone");
                return clone;
            })
            .reverse();
        lastClones.forEach((clone) => carouselInner.prepend(clone));

        const firstClones = originalItems.slice(0, itemsPerSlide).map((item) => {
            const clone = item.cloneNode(true);
            clone.classList.add("clone");
            return clone;
        });
        firstClones.forEach((clone) => carouselInner.append(clone));
    }

    function setCarouselHeight() {
        const windowHeight = window.innerHeight;
        const carouselContainer = carousel ? carousel.closest(".container-fluid") : null;

        const containerRect = carouselContainer
            ? carouselContainer.getBoundingClientRect()
            : { top: 0 };
        const availableHeight = windowHeight - containerRect.top - 100; 

        const carouselHeight = Math.max(availableHeight, 300); 

        document.documentElement.style.setProperty(
            "--carousel-height",
            `${carouselHeight}px`
        );
    }

    updateConfig(); 
    initializeClones(); 
    setCarouselHeight();

    let currentIndex = 0;
    let position = itemsPerSlide; 
    let isAnimating = false;

    function updateCarouselPosition(animate = true) {
        if (animate) {
            carouselInner.style.transition = "transform 0.5s ease";
        } else {
            carouselInner.style.transition = "none";
        }

        const translateX = (position * -100) / itemsPerSlide;
        carouselInner.style.transform = `translateX(${translateX}%)`;
    }

    updateCarouselPosition(false);

    carouselInner.addEventListener("transitionend", function () {
        isAnimating = false;

        if (position >= totalItems + itemsPerSlide) {
            position = itemsPerSlide + (position - (totalItems + itemsPerSlide));
            updateCarouselPosition(false); 
        } 
        else if (position < itemsPerSlide) {
            position = totalItems + position;
            updateCarouselPosition(false); 
        }

        currentIndex = (position - itemsPerSlide) % totalItems;
    });

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

    if (nextBtn) nextBtn.addEventListener("click", next);
    if (prevBtn) prevBtn.addEventListener("click", prev);

    let isDragging = false;
    let startX = 0;
    let startPosition = 0;

    const carouselImages = document.querySelectorAll("#carouselInner img");
    carouselImages.forEach((img) => {
        img.addEventListener("dragstart", (e) => {
            e.preventDefault();
        });
    });

    if (carousel) {
        carousel.addEventListener("mousedown", startDrag);
        carousel.addEventListener("touchstart", startDrag, { passive: true });

        carousel.addEventListener("mousemove", drag);
        carousel.addEventListener("touchmove", drag, { passive: true });

        carousel.addEventListener("mouseup", endDrag);
        carousel.addEventListener("touchend", endDrag); 
        carousel.addEventListener("mouseleave", endDrag);

    }

    function startDrag(e) {
        if (e.target.tagName === "IMG") {
            e.preventDefault();
        }

        if (isAnimating) return; 
        isDragging = true;
        startX = e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
        startPosition = position; 
        carousel.classList.add("dragging");
        carouselInner.style.transition = "none"; 
        document.body.style.cursor = "grabbing"; 
        document.body.style.userSelect = "none"; 
        registerUserActivity(); 
    }

    function drag(e) {
        if (!isDragging) return;

        const x = e.type.includes("mouse") ? e.clientX : e.touches[0].clientX;
        const walk = ((x - startX) / carousel.offsetWidth) * itemsPerSlide; 
        const newPosition = startPosition - walk; 
        const translateX = (newPosition * -100) / itemsPerSlide; 
        carouselInner.style.transform = `translateX(${translateX}%)`;
    }

    function endDrag(e) {
        if (!isDragging) return;

        isDragging = false; 
        carousel.classList.remove("dragging"); 
        document.body.style.cursor = ""; 
        document.body.style.userSelect = ""; 
        carouselInner.style.transition = "transform 0.5s ease"; 
        const x = e.type?.includes("mouse")
            ? e.clientX
            : e.changedTouches 
            ? e.changedTouches[0].clientX
            : startX; 
        const walk = ((x - startX) / carousel.offsetWidth) * itemsPerSlide;

        if (walk > 0.2) { 
            prev();
        } else if (walk < -0.2) { 
            next();
        } else {
            updateCarouselPosition(); 
        }

        registerUserActivity(); 
    }

    document.addEventListener("keydown", function (e) {
        if (
            carousel && carousel.offsetParent === null || 
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

    let autoAdvanceInterval;
    let userActivityTimeout;

    function startAutoAdvance() {
        clearInterval(autoAdvanceInterval); 
        autoAdvanceInterval = setInterval(next, 5000); 
    }

    function resetAutoAdvanceTimer() {
        clearTimeout(userActivityTimeout); 
        clearInterval(autoAdvanceInterval);
        userActivityTimeout = setTimeout(startAutoAdvance, 10000); 
    }

    function registerUserActivity() {
        resetAutoAdvanceTimer(); 
    }

    startAutoAdvance();

    if (carousel) { 
        carousel.addEventListener("mouseenter", () => {
            clearInterval(autoAdvanceInterval);
        });

        carousel.addEventListener("mouseleave", () => {
            resetAutoAdvanceTimer();
        });

        carousel.addEventListener("click", registerUserActivity);
        carousel.addEventListener("wheel", registerUserActivity); 
    }


    window.addEventListener("resize", function () {
        const wasMobile = itemsPerSlide === 1;
        updateConfig();
        setCarouselHeight();

        if (
            (wasMobile && itemsPerSlide > 1) ||
            (!wasMobile && itemsPerSlide === 1)
        ) {
            initializeClones();
            position = itemsPerSlide; 
            updateCarouselPosition(false);
        }
    });
    
});