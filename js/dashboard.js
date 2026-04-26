// ===== USER DASHBOARD JAVASCRIPT =====

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    if (!userManager.isLoggedIn()) {
        window.location.href = 'shop.html';
        return;
    }

    initDashboard();
    setupEventListeners();
    loadUserDashboardData();
});

// Initialize Dashboard
function initDashboard() {
    const user = userManager.currentUser;
    const userName = user.name || user.email.split('@')[0];
    
    document.getElementById('user-name').textContent = userName;
    document.getElementById('profile-name').textContent = userName;
    document.getElementById('profile-email').textContent = user.email;
    document.getElementById('profile-avatar').textContent = userName.charAt(0).toUpperCase();
    document.getElementById('member-since').textContent = new Date().getFullYear();
    document.getElementById('first-name').value = userName.split(' ')[0] || '';
    document.getElementById('last-name').value = userName.split(' ')[1] || '';
    document.getElementById('email').value = user.email;
}

// Setup Event Listeners
function setupEventListeners() {
    // Sidebar navigation
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const tabName = link.dataset.tab;
            if (tabName && tabName !== 'orders') {
                switchTab(tabName);
            }
        });
    });

    // Tab switching
    document.querySelectorAll('[data-tab]').forEach(link => {
        link.addEventListener('click', (e) => {
            if (e.target.dataset.tab) {
                e.preventDefault();
                switchTab(e.target.dataset.tab);
            }
        });
    });

    // Logout
    document.getElementById('logout-btn').addEventListener('click', (e) => {
        e.preventDefault();
        userManager.logout();
        window.location.href = 'shop.html';
    });

    // Profile form
    document.getElementById('profile-form').addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Profile updated successfully!');
    });

    // Password form
    document.getElementById('password-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const newPass = document.getElementById('new-password').value;
        const confirmPass = document.getElementById('confirm-password').value;
        
        if (newPass !== confirmPass) {
            alert('Passwords do not match!');
            return;
        }
        
        // Update password in localStorage
        const users = JSON.parse(localStorage.getItem('users')) || [];
        const userIndex = users.findIndex(u => u.email === userManager.currentUser.email);
        if (userIndex !== -1) {
            users[userIndex].password = newPass;
            localStorage.setItem('users', JSON.stringify(users));
        }
        
        alert('Password updated successfully!');
        document.getElementById('password-form').reset();
    });

    // Settings form
    document.getElementById('settings-form').addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Settings saved successfully!');
    });

    // Login form
    document.getElementById('login-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        const result = userManager.login(email, password);
        
        if (result.success) {
            location.reload();
        } else {
            alert(result.message);
        }
    });

    // Register form
    document.getElementById('register-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const name = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;
        const result = userManager.register(name, email, password);
        
        if (result.success) {
            alert('Registration successful! Please login.');
            document.getElementById('register-form').reset();
            document.querySelector('[data-bs-target="#login-tab"]').click();
        } else {
            alert(result.message);
        }
    });
}

// Switch Dashboard Tab
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('d-none');
    });

    // Remove active from sidebar
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.classList.remove('active');
    });

    // Show selected tab
    const selectedTab = document.getElementById(`tab-${tabName}`);
    if (selectedTab) {
        selectedTab.classList.remove('d-none');
    }

    // Update page title
    const titles = {
        overview: 'Dashboard Overview',
        profile: 'My Profile',
        orders: 'My Orders',
        wishlist: 'My Wishlist',
        addresses: 'Saved Addresses',
        settings: 'Settings'
    };
    document.getElementById('page-title').textContent = titles[tabName] || 'Dashboard';

    // Mark sidebar link as active
    document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');
}

// Load User Dashboard Data
function loadUserDashboardData() {
    // Load stats
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const userOrders = orders.filter(o => o.userId === userManager.currentUser.id);
    const wishlistItems = JSON.parse(localStorage.getItem(`wishlist-${userManager.currentUser.id}`)) || [];

    document.getElementById('stat-orders').textContent = userOrders.length;
    
    const totalSpent = userOrders.reduce((sum, order) => sum + order.total, 0);
    document.getElementById('stat-spent').textContent = '$' + totalSpent.toFixed(2);
    
    document.getElementById('stat-wishlist').textContent = wishlistItems.length;
    
    // Determine membership level
    let level = 'Bronze';
    if (totalSpent > 1000) level = 'Gold';
    else if (totalSpent > 500) level = 'Silver';
    document.getElementById('stat-level').textContent = level;

    // Load recent orders
    loadRecentOrders(userOrders);

    // Load wishlist items
    loadWishlistTab(wishlistItems);

    // Load orders tab
    loadOrdersTab(userOrders);
}

// Load Recent Orders
function loadRecentOrders(orders) {
    const tbody = document.getElementById('recent-orders-body');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No orders yet. <a href="shop.html">Start shopping</a></td></tr>';
        return;
    }

    const recentOrders = orders.slice(-5).reverse();
    tbody.innerHTML = recentOrders.map(order => `
        <tr>
            <td>#${order.id.toString().slice(0, 8)}</td>
            <td>${new Date(order.date).toLocaleDateString()}</td>
            <td>${order.items.length} item(s)</td>
            <td>UG SHS ${order.total.toFixed(2)}</td>
            <td><span class="status-badge status-${order.status || 'pending'}">${order.status || 'Pending'}</span></td>
            <td><button class="btn btn-sm btn-view">View</button></td>
        </tr>
    `).join('');
}

// Load Orders Tab
function loadOrdersTab(orders) {
    const tbody = document.getElementById('orders-body');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No orders yet. <a href="shop.html">Start shopping</a></td></tr>';
        return;
    }

    tbody.innerHTML = orders.reverse().map(order => `
        <tr>
            <td><strong>#${order.id.toString().slice(0, 8)}</strong></td>
            <td>${new Date(order.date).toLocaleDateString()}</td>
            <td>${order.items.length}</td>
            <td>UG SHS ${order.total.toFixed(2)}</td>
            <td><span class="status-badge status-${order.status || 'pending'}">${order.status || 'Pending'}</span></td>
            <td><button class="btn btn-sm btn-view">Details</button></td>
        </tr>
    `).join('');
}

// Load Wishlist Tab
function loadWishlistTab(wishlistItems) {
    const grid = document.getElementById('wishlist-grid');

    if (wishlistItems.length === 0) {
        grid.innerHTML = '<div class="col-12 py-4 text-center text-muted"><i class="bi bi-heart"></i> Your wishlist is empty. <a href="shop.html">Add items</a></div>';
        return;
    }

    grid.innerHTML = wishlistItems.map(item => `
        <div class="col-md-6 col-lg-4">
            <div class="product-card-dashboard">
                <div class="product-card-image">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="product-card-content">
                    <h5 class="product-card-title">${item.name}</h5>
                    <div class="product-card-rating">
                        <i class="bi bi-star-fill"></i> ${item.rating}
                    </div>
                    <div class="product-card-price">UG SHS ${item.price}</div>
                    <button class="btn btn-primary btn-sm w-100 mt-auto">Add to Cart</button>
                </div>
            </div>
        </div>
    `).join('');
}