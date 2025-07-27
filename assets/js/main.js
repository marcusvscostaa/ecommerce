document.addEventListener('DOMContentLoaded', function() {

    const mainImg = document.getElementById('mainImg');
    const smallImgs = document.getElementsByClassName('small-img');

    if (mainImg && smallImgs.length > 0) {
        function removeActiveThumbnailClass() {
            for (let i = 0; i < smallImgs.length; i++) {
                smallImgs[i].parentElement.classList.remove('active-thumbnail');
            }
        }

        for (let i = 0; i < smallImgs.length; i++) {
            smallImgs[i].addEventListener('mouseover', function() {
                mainImg.src = smallImgs[i].src; 
                removeActiveThumbnailClass(); 
                this.parentElement.classList.add('active-thumbnail'); 
            });
        }

        window.addEventListener('load', function() {
            if (smallImgs.length > 0) {
                smallImgs[0].parentElement.classList.add('active-thumbnail');
            }
        });
    }

    const forms = document.querySelectorAll('.needs-validation');

    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    const logoutBtn = document.getElementById('logout-btn');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(event) {
           
        });
    }
const searchInput = document.getElementById('live-search-input');
const searchResultsContainer = document.getElementById('live-search-results');
let searchTimeout;

if (searchInput && searchResultsContainer) {
    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout); 
        const query = this.value;

        if (query.length > 0) { 
            searchTimeout = setTimeout(() => {
                fetch(`server/live_search.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResultsContainer.innerHTML = ''; 
                        if (data.length > 0) {
                            data.forEach(product => {
                                const item = document.createElement('a'); 
                                item.href = `single_product.php?product_id=${product.id}`;
                                item.classList.add('list-group-item', 'list-group-item-action', 'd-flex', 'align-items-center');
                                item.innerHTML = `
                                    <img src="${product.image}" class="search-thumbnail" alt="${product.name}">
                                    <div>
                                        <span>${product.name}</span>
                                        <span class="search-price">R$ ${product.price}</span>
                                    </div>
                                `;
                                searchResultsContainer.appendChild(item);
                            });
                            searchResultsContainer.style.display = 'block'; 
                        } else {
                            searchResultsContainer.style.display = 'none'; 
                        }
                    })
                    .catch(error => console.error('Erro na busca AJAX:', error));
            }, 300); 
        } else {
            searchResultsContainer.innerHTML = '';
            searchResultsContainer.style.display = 'none'; 
        }
    });

    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !searchResultsContainer.contains(event.target)) {
            searchResultsContainer.style.display = 'none';
        }
    });
}
}); 