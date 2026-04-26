// Product Database
const products = [
    // Living Room
    { id: 1, name: 'Modern Sofa', category: 'living-room', price: 899, rating: 4.5, image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', description: 'Elegant modern sofa perfect for contemporary living rooms' },
    { id: 2, name: 'Coffee Table', category: 'living-room', price: 299, rating: 4.2, image: 'https://images.unsplash.com/photo-1555939594-58d7cb561404?w=400&h=300&fit=crop', description: 'Stylish wood coffee table with storage' },
    { id: 3, name: 'Wall Art', category: 'living-room', price: 199, rating: 4.7, image: 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=400&h=300&fit=crop', description: 'Abstract wall art for modern spaces' },
    
    // Bedroom
    { id: 4, name: 'King Bed Frame', category: 'bedroom', price: 1299, rating: 4.6, image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', description: 'Premium king-size bed with storage' },
    { id: 5, name: 'Nightstand', category: 'bedroom', price: 299, rating: 4.3, image: 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=400&h=300&fit=crop', description: 'Modern nightstand with drawer' },
    { id: 6, name: 'Bedside Lamp', category: 'bedroom', price: 89, rating: 4.4, image: 'https://images.unsplash.com/photo-1565636192335-14c6b42ce068?w=400&h=300&fit=crop', description: 'Adjustable bedside lamp with USB charging' },
    
    // Office
    { id: 7, name: 'Office Desk', category: 'office', price: 599, rating: 4.5, image: 'https://images.unsplash.com/photo-1593062096033-9a26b09da705?w=400&h=300&fit=crop', description: 'Ergonomic office desk with monitor stand' },
    { id: 8, name: 'Office Chair', category: 'office', price: 399, rating: 4.6, image: 'https://images.unsplash.com/photo-1592078615290-033ee584e267?w=400&h=300&fit=crop', description: 'Comfortable ergonomic office chair' },
    { id: 9, name: 'Desk Organizer', category: 'office', price: 49, rating: 4.2, image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop', description: 'Wooden desk organizer set' },
    
    // Decor
    { id: 10, name: 'Plant Pot', category: 'decor', price: 59, rating: 4.3, image: 'https://images.unsplash.com/photo-1610055945828-949d8edd5d55?w=400&h=300&fit=crop', description: 'Modern ceramic plant pot' },
    { id: 11, name: 'Mirror', category: 'decor', price: 149, rating: 4.5, image: 'https://images.unsplash.com/photo-1578926314433-b961e6a4d8e7?w=400&h=300&fit=crop', description: 'Decorative wall mirror' },
    { id: 12, name: 'Throw Pillow', category: 'decor', price: 39, rating: 4.4, image: 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&h=300&fit=crop', description: 'Comfortable throw pillow set' }
];

// User Management
class UserManager {
    constructor() {
        this.users = JSON.parse(localStorage.getItem('users')) || [];
        this.currentUser = JSON.parse(localStorage.getItem('currentUser')) || null;
    }

    register(name, email, password) {
        if (this.users.find(u => u.email === email)) {
            return { success: false, message: 'Email already registered' };
        }
        const user = { id: Date.now(), name, email, password };
        this.users.push(user);
        localStorage.setItem('users', JSON.stringify(this.users));
        return { success: true, message: 'Registration successful' };
    }

    login(email, password) {
        const user = this.users.find(u => u.email === email && u.password === password);
        if (user) {
            this.currentUser = { id: user.id, name: user.name, email: user.email };
            localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
            return { success: true, message: 'Login successful' };
        }
        return { success: false, message: 'Invalid credentials' };
    }

    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
    }

    isLoggedIn() {
        return this.currentUser !== null;
    }
}

// Shopping Cart Management
class ShoppingCart {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('cart')) || [];
    }

    addItem(product, quantity = 1) {
        const existingItem = this.items.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.items.push({ ...product, quantity });
        }
        this.save();
        this.updateBadge();
    }

    removeItem(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.save();
        this.updateBadge();
    }

    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            item.quantity = quantity;
            this.save();
        }
    }

    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    save() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    }

    updateBadge() {
        const badge = document.getElementById('cart-badge');
        if (badge) {
            badge.textContent = this.items.reduce((sum, item) => sum + item.quantity, 0);
        }
    }

    clear() {
        this.items = [];
        this.save();
        this.updateBadge();
    }
}

// Wishlist Management
class Wishlist {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('wishlist')) || [];
    }

    addItem(product) {
        if (!this.items.find(item => item.id === product.id)) {
            this.items.push(product);
            this.save();
            return true;
        }
        return false;
    }

    removeItem(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.save();
    }

    isFavorite(productId) {
        return this.items.some(item => item.id === productId);
    }

    save() {
        localStorage.setItem('wishlist', JSON.stringify(this.items));
    }
}

// Order Management
class OrderManager {
    constructor() {
        this.orders = JSON.parse(localStorage.getItem('orders')) || [];
    }

    createOrder(items, total, userId) {
        const order = {
            id: Date.now(),
            userId: userId,
            items: items,
            total: total,
            status: 'pending',
            date: new Date().toISOString(),
            customerName: userManager.currentUser ? userManager.currentUser.name : 'Guest'
        };
        this.orders.push(order);
        this.save();
        return order;
    }

    getOrdersByUser(userId) {
        return this.orders.filter(order => order.userId === userId);
    }

    updateOrderStatus(orderId, status) {
        const order = this.orders.find(o => o.id === orderId);
        if (order) {
            order.status = status;
            this.save();
        }
    }

    save() {
        localStorage.setItem('orders', JSON.stringify(this.orders));
    }
}

// Initialize Global Objects
const userManager = new UserManager();
const cart = new ShoppingCart();
const wishlist = new Wishlist();
const orderManager = new OrderManager();

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Update cart badge on load
    cart.updateBadge();

    // Auth Form Handlers
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            const result = userManager.login(email, password);
            alert(result.message);
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
                loginForm.reset();
                updateUserButton();
            }
        });
    }

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('register-name').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const result = userManager.register(name, email, password);
            alert(result.message);
            if (result.success) {
                registerForm.reset();
                document.getElementById('login-tab').click();
            }
        });
    }

    // User Account Button
    const userButton = document.getElementById('user-account');
    if (userButton) {
        userButton.addEventListener('click', function(e) {
            if (userManager.isLoggedIn()) {
                e.preventDefault();
                const dropdown = confirm(`Logged in as ${userManager.currentUser.name}.\n\nClick OK for Dashboard or Cancel to Logout`);
                if (dropdown) {
                    // Go to user dashboard
                    window.location.href = 'pages/user-dashboard.html';
                } else {
                    userManager.logout();
                    alert('Logged out successfully');
                    updateUserButton();
                }
            }
        });
    }

    updateUserButton();
});

function updateUserButton() {
    const userButton = document.getElementById('user-account');
    if (userButton) {
        if (userManager.isLoggedIn()) {
            userButton.innerHTML = `<i class="bi bi-person-check-fill"></i> ${userManager.currentUser.name}`;
            userButton.removeAttribute('data-bs-toggle');
            userButton.removeAttribute('data-bs-target');
        } else {
            userButton.innerHTML = `<i class="bi bi-person-circle"></i> Account`;
            userButton.setAttribute('data-bs-toggle', 'modal');
            userButton.setAttribute('data-bs-target', '#authModal');
        }
    }
}