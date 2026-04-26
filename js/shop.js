// Shop page functionality
let filteredProducts = [...products];
let currentPage = 1;
const itemsPerPage = 9;

document.addEventListener('DOMContentLoaded', function() {
    loadShop();
    setupEventListeners();
    applyUrlFilters();
});

function setupEventListeners() {
    // Search
    document.getElementById('search-input').addEventListener('input', filterProducts);
    
    // Sort
    document.getElementById('sort-select').addEventListener('change', sortProducts);
    
    // Filters
    document.getElementById('apply-filters').addEventListener('click', filterProducts);
    document.getElementById('reset-filters').addEventListener('click', resetFilters);
    document.getElementById('price-filter').addEventListener('input', function() {
        document.getElementById('price-value').textContent = this.value;
    });
}

function loadShop() {
    displayProducts();
    setupPagination();
}

function displayProducts() {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = '';

    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageProducts = filteredProducts.slice(start, end);

    pageProducts.forEach(product => {
        const isFavorite = wishlist.isFavorite(product.id);
        const productCard = document.createElement('div');
        productCard.className = 'col-md-6 col-lg-4';
        productCard.innerHTML = `
            <div class="product-card">
                <div class="position-relative">
                    <img src="${product.image}" class="card-img-top" alt="${product.name}">
                    <button class="btn btn-sm position-absolute top-2 end-2 btn-light wishlist-icon ${isFavorite ? 'active' : ''}" 
                            onclick="toggleWishlist(${product.id})" title="Add to Wishlist">
                        <i class="bi ${isFavorite ? 'bi-heart-fill' : 'bi-heart'}"></i>
                    </button>
                </div>
                <div class="product-body">
                    <h6 class="product-title">${product.name}</h6>
                    <div class="product-rating">
                        ${'★'.repeat(Math.floor(product.rating))} (${product.rating})
                    </div>
                    <div class="product-price">UG SHS ${product.price}</div>
                    <button class="btn btn-primary btn-sm w-100 mb-2" onclick="openProductModal(${product.id})">
                        <i class="bi bi-info-circle"></i> View Details
                    </button>
                    <button class="btn btn-outline-primary btn-sm w-100" onclick="quickAdd(${product.id})">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(productCard);
    });
}

function filterProducts() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const priceMax = parseInt(document.getElementById('price-filter').value);
    const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked'))
        .map(cb => cb.value);
    const selectedRatings = Array.from(document.querySelectorAll('.rating-filter:checked'))
        .map(cb => parseFloat(cb.value));

    filteredProducts = products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm) || 
                            product.description.toLowerCase().includes(searchTerm);
        const matchesPrice = product.price <= priceMax;
        const matchesCategory = selectedCategories.length === 0 || selectedCategories.includes(product.category);
        const matchesRating = selectedRatings.length === 0 || selectedRatings.some(r => product.rating >= r);

        return matchesSearch && matchesPrice && matchesCategory && matchesRating;
    });

    currentPage = 1;
    loadShop();
}

function sortProducts() {
    const sortBy = document.getElementById('sort-select').value;

    switch(sortBy) {
        case 'price-low':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'rating':
            filteredProducts.sort((a, b) => b.rating - a.rating);
            break;
        default:
            filteredProducts.sort((a, b) => b.id - a.id);
    }

    currentPage = 1;
    displayProducts();
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('price-filter').value = 2000;
    document.getElementById('price-value').textContent = 2000;
    document.querySelectorAll('.category-filter, .rating-filter').forEach(cb => cb.checked = false);
    document.getElementById('sort-select').value = 'newest';
    
    filteredProducts = [...products];
    currentPage = 1;
    loadShop();
}

function setupPagination() {
    const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = '<a class="page-link" href="#" onclick="changePage(' + (currentPage - 1) + ')">Previous</a>';
    pagination.appendChild(prevLi);

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${currentPage === i ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i})">${i}</a>`;
        pagination.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = '<a class="page-link" href="#" onclick="changePage(' + (currentPage + 1) + ')">Next</a>';
    pagination.appendChild(nextLi);
}

function changePage(page) {
    const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        displayProducts();
        setupPagination();
        window.scrollTo(0, 0);
    }
}

function openProductModal(productId) {
    const product = products.find(p => p.id === productId);
    if (product) {
        document.getElementById('product-modal-title').textContent = product.name;
        document.getElementById('product-modal-image').src = product.image;
        document.getElementById('product-modal-name').textContent = product.name;
        document.getElementById('product-modal-rating').innerHTML = `${'★'.repeat(Math.floor(product.rating))} (${product.rating} stars)`;
        document.getElementById('product-modal-price').textContent = `UG SHS ${product.price}`;
        document.getElementById('product-modal-description').textContent = product.description;
        document.getElementById('quantity-input').value = 1;
        document.getElementById('quantity-input').dataset.productId = productId;
        
        const wishlistBtn = document.getElementById('wishlist-btn');
        if (wishlist.isFavorite(productId)) {
            wishlistBtn.innerHTML = '<i class="bi bi-heart-fill"></i> Remove from Wishlist';
            wishlistBtn.onclick = () => removeFromWishlist(productId);
        } else {
            wishlistBtn.innerHTML = '<i class="bi bi-heart"></i> Add to Wishlist';
            wishlistBtn.onclick = () => addToWishlist(productId);
        }

        document.getElementById('add-to-cart-btn').onclick = function() {
            const qty = parseInt(document.getElementById('quantity-input').value);
            cart.addItem(product, qty);
            alert(`${product.name} added to cart!`);
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
        };

        new bootstrap.Modal(document.getElementById('productModal')).show();
    }
}

function quickAdd(productId) {
    const product = products.find(p => p.id === productId);
    if (product) {
        cart.addItem(product, 1);
        alert(`${product.name} added to cart!`);
    }
}

function toggleWishlist(productId) {
    const product = products.find(p => p.id === productId);
    if (wishlist.isFavorite(productId)) {
        wishlist.removeItem(productId);
    } else {
        wishlist.addItem(product);
    }
    displayProducts();
}

function addToWishlist(productId) {
    const product = products.find(p => p.id === productId);
    wishlist.addItem(product);
}

function removeFromWishlist(productId) {
    wishlist.removeItem(productId);
}

function applyUrlFilters() {
    const params = new URLSearchParams(window.location.search);
    const category = params.get('category');
    if (category) {
        const checkbox = document.querySelector(`input[value="${category}"]`);
        if (checkbox) {
            checkbox.checked = true;
            filterProducts();
        }
    }
}