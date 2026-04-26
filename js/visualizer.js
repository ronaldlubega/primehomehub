// ===== PRODUCT VISUALIZER JAVASCRIPT =====

let currentProduct = null;
let currentHue = 0;
let currentBg = 'light-bg';

document.addEventListener('DOMContentLoaded', () => {
    initVisualizer();
    setupEventListeners();
    loadProducts();
});

// Initialize Visualizer
function initVisualizer() {
    cart.updateBadge();
    updateUserButton();
}

// Setup Event Listeners
function setupEventListeners() {
    // Product selection
    document.getElementById('productGrid').addEventListener('click', (e) => {
        if (e.target.closest('.product-option')) {
            selectProduct(e.target.closest('.product-option'));
        }
    });

    // Background selection
    document.querySelectorAll('.bg-option').forEach(btn => {
        btn.addEventListener('click', () => {
            switchBackground(btn);
        });
    });

    // Color selection
    document.querySelectorAll('.color-option').forEach(btn => {
        btn.addEventListener('click', () => {
            selectColorTint(btn);
        });
    });

    // Sliders
    document.getElementById('intensitySlider').addEventListener('input', updateProductFilters);
    document.getElementById('brightnessSlider').addEventListener('input', updateProductFilters);
    document.getElementById('contrastSlider').addEventListener('input', updateProductFilters);
    document.getElementById('saturationSlider').addEventListener('input', updateProductFilters);

    // Action buttons
    document.getElementById('addToCartBtn').addEventListener('click', addToCart);
    document.getElementById('resetBtn').addEventListener('click', resetVisualizer);
    document.getElementById('wishlistBtn').addEventListener('click', toggleWishlist);
    document.getElementById('shareBtn').addEventListener('click', shareVisualization);
}

// Load Products
function loadProducts() {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = products.map((product, index) => `
        <div class="product-option" data-product-id="${product.id}" data-index="${index}">
            <img src="${product.image}" alt="${product.name}">
        </div>
    `).join('');

    // Select first product by default
    if (products.length > 0) {
        selectProduct(grid.querySelector('.product-option'));
    }
}

// Select Product
function selectProduct(element) {
    // Remove active from all
    document.querySelectorAll('.product-option').forEach(el => {
        el.classList.remove('active');
    });

    // Add active to clicked
    element.classList.add('active');

    // Get product data
    const index = element.dataset.index;
    currentProduct = products[index];

    // Update preview
    updatePreview();
}

// Update Preview
function updatePreview() {
    if (!currentProduct) return;

    const preview = document.getElementById('productPreview');
    const img = preview.querySelector('img');
    
    img.src = currentProduct.image;
    img.alt = currentProduct.name;

    // Update product info
    document.getElementById('productName').textContent = currentProduct.name;
    document.getElementById('productRating').innerHTML = `★★★★★ (${currentProduct.rating})`;
    document.getElementById('productPrice').textContent = `UG SHS ${currentProduct.price}`;
    document.getElementById('productDescription').textContent = currentProduct.description;

    // Reset filters
    document.getElementById('intensitySlider').value = 50;
    document.getElementById('brightnessSlider').value = 100;
    document.getElementById('contrastSlider').value = 100;
    document.getElementById('saturationSlider').value = 100;
    updateProductFilters();
}

// Switch Background
function switchBackground(element) {
    // Remove active from all
    document.querySelectorAll('.bg-option').forEach(el => {
        el.classList.remove('active');
    });

    // Add active to clicked
    element.classList.add('active');

    // Get background class
    currentBg = element.dataset.bg;

    // Handle custom background
    if (currentBg === 'custom-bg') {
        const customColor = prompt('Enter background color (e.g., #ff6b6b, rgb(255,0,0), or color name):');
        if (customColor) {
            const canvas = document.getElementById('previewCanvas');
            canvas.style.backgroundColor = customColor;
            canvas.style.backgroundImage = 'none';
            return;
        }
    }

    // Remove all bg classes
    const canvas = document.getElementById('previewCanvas');
    ['dark-bg', 'light-bg', 'warm-bg', 'modern-bg', 'nature-bg', 'custom-bg'].forEach(cls => {
        canvas.classList.remove(cls);
    });

    // Add selected class
    canvas.classList.add(currentBg);
}

// Select Color Tint
function selectColorTint(element) {
    // Remove active from all
    document.querySelectorAll('.color-option').forEach(el => {
        el.classList.remove('active');
    });

    // Add active to clicked
    element.classList.add('active');

    // Get hue rotation
    currentHue = element.dataset.hue;
    updateProductFilters();
}

// Update Product Filters
function updateProductFilters() {
    const intensity = document.getElementById('intensitySlider').value;
    const brightness = document.getElementById('brightnessSlider').value;
    const contrast = document.getElementById('contrastSlider').value;
    const saturation = document.getElementById('saturationSlider').value;

    // Update slider values display
    document.getElementById('intensityValue').textContent = intensity + '%';
    document.getElementById('brightnessValue').textContent = brightness + '%';
    document.getElementById('contrastValue').textContent = contrast + '%';
    document.getElementById('saturationValue').textContent = saturation + '%';

    // Apply filters to image
    const img = document.getElementById('productPreview').querySelector('img');
    const overlay = document.getElementById('colorOverlay');

    // Calculate hue rotation from intensity
    let hueRotate = currentHue * (intensity / 100);

    // Apply CSS filters
    img.style.filter = `
        brightness(${brightness}%)
        contrast(${contrast}%)
        saturate(${saturation}%)
        hue-rotate(${hueRotate}deg)
    `;

    // Apply color overlay
    if (currentHue !== 0 && intensity > 0) {
        overlay.classList.add('active');
        overlay.style.setProperty('--overlay-opacity', Math.min(intensity / 100, 0.3));
        
        // Convert hue to RGB for overlay
        const hsl = `hsla(${hueRotate}, 100%, 50%, ${Math.min(intensity / 100, 0.3)})`;
        overlay.style.backgroundColor = hsl;
    } else {
        overlay.classList.remove('active');
    }
}

// Add to Cart
function addToCart() {
    if (!currentProduct) {
        alert('Please select a product first');
        return;
    }

    cart.addItem(currentProduct, 1);
    alert(`${currentProduct.name} added to cart!`);
}

// Toggle Wishlist
function toggleWishlist() {
    if (!currentProduct) {
        alert('Please select a product first');
        return;
    }

    if (wishlist.isFavorite(currentProduct.id)) {
        wishlist.removeItem(currentProduct.id);
        alert('Removed from wishlist');
    } else {
        wishlist.addItem(currentProduct);
        alert('Added to wishlist');
    }
}

// Share Visualization
function shareVisualization() {
    if (!currentProduct) {
        alert('Please select a product first');
        return;
    }

    const shareText = `Check out this ${currentProduct.name} from Prime Home Hub! 
Background: ${currentBg.replace('-bg', '')}
Color Tint: ${currentHue}°
Price: UG SHS ${currentProduct.price}
🎨 Visualizer: ${window.location.href}`;

    if (navigator.share) {
        navigator.share({
            title: 'Prime Home Hub Visualizer',
            text: shareText,
            url: window.location.href
        }).catch(err => console.log('Share failed', err));
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(shareText);
        alert('Visualization details copied to clipboard!');
    }
}

// Reset Visualizer
function resetVisualizer() {
    // Reset sliders
    document.getElementById('intensitySlider').value = 50;
    document.getElementById('brightnessSlider').value = 100;
    document.getElementById('contrastSlider').value = 100;
    document.getElementById('saturationSlider').value = 100;

    // Reset background
    document.querySelectorAll('.bg-option').forEach(el => el.classList.remove('active'));
    document.querySelector('[data-bg="light-bg"]').classList.add('active');
    currentBg = 'light-bg';
    const canvas = document.getElementById('previewCanvas');
    ['dark-bg', 'light-bg', 'warm-bg', 'modern-bg', 'nature-bg', 'custom-bg'].forEach(cls => {
        canvas.classList.remove(cls);
    });
    canvas.classList.add('light-bg');

    // Reset color tint
    document.querySelectorAll('.color-option').forEach(el => el.classList.remove('active'));
    document.querySelector('[data-hue="0"]').classList.add('active');
    currentHue = 0;

    // Update filters
    updateProductFilters();

    alert('Visualizer reset to defaults');
}

// Helper: Convert hex to RGB for overlay
function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}
