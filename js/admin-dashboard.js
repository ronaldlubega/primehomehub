// ===== ADMIN DASHBOARD JAVASCRIPT =====

// Check if user is admin
const isAdmin = localStorage.getItem('isAdmin') === 'true';

document.addEventListener('DOMContentLoaded', () => {
    if (!isAdmin) {
        alert('Access Denied: Admin only');
        window.location.href = '../index.html';
        return;
    }

    initAdminDashboard();
    setupAdminEventListeners();
    loadAdminData();
    initCharts();
});

// Initialize Admin Dashboard
function initAdminDashboard() {
    const now = new Date();
    updateDashboardDate(now);
}

// Update Dashboard Date
function updateDashboardDate(date) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = date.toLocaleDateString('en-US', options);
}

// Setup Admin Event Listeners
function setupAdminEventListeners() {
    // Tab switching
    document.querySelectorAll('[data-tab]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            if (e.currentTarget.dataset.tab) {
                switchAdminTab(e.currentTarget.dataset.tab);
            }
        });
    });

    // Logout
    document.getElementById('admin-logout').addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.removeItem('isAdmin');
        window.location.href = '../index.html';
    });

    // Add Product
    document.getElementById('save-product-btn').addEventListener('click', addNewProduct);

    // Admin Settings Form
    document.getElementById('admin-settings-form').addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Settings saved successfully!');
    });
}

// Switch Admin Tab
function switchAdminTab(tabName) {
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
        dashboard: 'Dashboard',
        products: 'Product Management',
        orders: 'Orders Management',
        users: 'User Management',
        analytics: 'Analytics',
        settings: 'Settings'
    };
    document.getElementById('page-title').textContent = titles[tabName] || 'Dashboard';

    // Mark sidebar link as active
    document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');
}

// Load Admin Data
function loadAdminData() {
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const orders = JSON.parse(localStorage.getItem('orders')) || [];

    // Calculate metrics
    const totalRevenue = orders.reduce((sum, order) => sum + order.total, 0);
    const totalOrders = orders.length;
    const totalUsers = users.length;
    const totalProducts = products.length;

    // Update dashboard stats
    document.getElementById('admin-revenue').textContent = '$' + totalRevenue.toFixed(2);
    document.getElementById('admin-orders').textContent = totalOrders;
    document.getElementById('admin-users').textContent = totalUsers;
    document.getElementById('admin-products').textContent = totalProducts;

    // Load products
    loadProductsList();

    // Load orders
    loadOrdersList(orders);

    // Load users
    loadUsersList(users);

    // Load recent orders for overview
    loadAdminRecentOrders(orders);

    // Load top products
    loadTopProducts();

    // Load analytics
    loadAnalytics(orders, totalUsers);
}

// Load Products List
function loadProductsList() {
    const tbody = document.getElementById('products-list-body');

    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No products</td></tr>';
        return;
    }

    tbody.innerHTML = products.map(product => `
        <tr>
            <td>${product.id}</td>
            <td><strong>${product.name}</strong></td>
            <td><span class="badge bg-info">${product.category}</span></td>
            <td>UG SHS ${product.price}</td>
            <td><i class="bi bi-star-fill text-warning"></i> ${product.rating}</td>
            <td>
                <button class="btn btn-sm btn-edit me-2"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `).join('');

    // Add edit/delete functionality
    document.querySelectorAll('.btn-edit').forEach((btn, index) => {
        btn.addEventListener('click', () => editProduct(products[index]));
    });

    document.querySelectorAll('.btn-delete').forEach((btn, index) => {
        btn.addEventListener('click', () => deleteProduct(index));
    });
}

// Load Orders List
function loadOrdersList(orders) {
    const tbody = document.getElementById('orders-list-body');

    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No orders</td></tr>';
        return;
    }

    tbody.innerHTML = orders.map(order => `
        <tr>
            <td><strong>#${order.id.toString().slice(0, 8)}</strong></td>
            <td>${order.customerName || 'Guest'}</td>
            <td>${order.items.length}</td>
            <td>UG SHS ${order.total.toFixed(2)}</td>
            <td><span class="status-badge status-${order.status || 'pending'}">${order.status || 'Pending'}</span></td>
            <td>${new Date(order.date).toLocaleDateString()}</td>
            <td>
                <select class="form-select form-select-sm d-inline-block" style="width: 120px;">
                    <option value="pending" ${(order.status || 'pending') === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="processing" ${(order.status || 'pending') === 'processing' ? 'selected' : ''}>Processing</option>
                    <option value="completed" ${(order.status || 'pending') === 'completed' ? 'selected' : ''}>Completed</option>
                    <option value="cancelled" ${(order.status || 'pending') === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </td>
        </tr>
    `).join('');
}

// Load Users List
function loadUsersList(users) {
    const tbody = document.getElementById('users-list-body');

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No users registered</td></tr>';
        return;
    }

    const orders = JSON.parse(localStorage.getItem('orders')) || [];

    tbody.innerHTML = users.map(user => {
        const userOrders = orders.filter(o => o.userId === user.id);
        const totalSpent = userOrders.reduce((sum, o) => sum + o.total, 0);
        const joinDate = new Date(user.id);

        return `
            <tr>
                <td>${user.id}</td>
                <td><strong>${user.name}</strong></td>
                <td>${user.email}</td>
                <td>${userOrders.length}</td>
                <td>UG SHS ${totalSpent.toFixed(2)}</td>
                <td>${joinDate.toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-view"><i class="bi bi-eye"></i></button>
                </td>
            </tr>
        `;
    }).join('');
}

// Load Admin Recent Orders
function loadAdminRecentOrders(orders) {
    const tbody = document.getElementById('admin-recent-orders');

    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No orders</td></tr>';
        return;
    }

    const recentOrders = orders.slice(-5).reverse();
    tbody.innerHTML = recentOrders.map(order => `
        <tr>
            <td><strong>#${order.id.toString().slice(0, 8)}</strong></td>
            <td>${order.customerName || 'Guest'}</td>
            <td>UG SHS ${order.total.toFixed(2)}</td>
            <td><span class="status-badge status-${order.status || 'pending'}">${order.status || 'Pending'}</span></td>
            <td>${new Date(order.date).toLocaleDateString()}</td>
            <td><button class="btn btn-sm btn-view">View</button></td>
        </tr>
    `).join('');
}

// Load Top Products
function loadTopProducts() {
    const container = document.getElementById('top-products-list');

    if (products.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No products</p>';
        return;
    }

    const topProducts = [...products].sort((a, b) => b.rating - a.rating).slice(0, 5);

    container.innerHTML = topProducts.map((product, index) => `
        <div style="padding: 0.75rem 0; border-bottom: 1px solid #e9ecef;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h6 style="margin: 0 0 0.25rem 0; font-weight: 600;">${index + 1}. ${product.name}</h6>
                    <small class="text-muted">UG SHS ${product.price}</small>
                </div>
                <div style="text-align: right;">
                    <div style="color: #ffc107;">
                        <i class="bi bi-star-fill"></i> ${product.rating}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Load Analytics
function loadAnalytics(orders, totalUsers) {
    const totalRevenue = orders.reduce((sum, o) => sum + o.total, 0);
    const conversionRate = totalUsers > 0 ? ((orders.length / totalUsers) * 100).toFixed(2) : 0;
    const aov = orders.length > 0 ? (totalRevenue / orders.length).toFixed(2) : 0;
    const clv = totalUsers > 0 ? (totalRevenue / totalUsers).toFixed(2) : 0;

    document.getElementById('analytics-conversion').textContent = conversionRate + '%';
    document.getElementById('analytics-aov').textContent = '$' + aov;
    document.getElementById('analytics-customer-ltv').textContent = '$' + clv;
    document.getElementById('analytics-return-rate').textContent = '2.5%';
}

// Add New Product
function addNewProduct() {
    const name = document.getElementById('product-name').value;
    const category = document.getElementById('product-category').value;
    const price = parseFloat(document.getElementById('product-price').value);
    const rating = parseFloat(document.getElementById('product-rating').value);
    const imageUrlInput = document.getElementById('product-image');
    const imageFileInput = document.getElementById('product-image-file');
    const description = document.getElementById('product-description').value;

    if (!name || !category || isNaN(price)) {
        alert('Please fill in all required fields');
        return;
    }

    // Helper to create and save product after image is ready
    function createProductWithImage(imageData) {
        const newProduct = {
            id: Math.max(...products.map(p => p.id), 0) + 1,
            name,
            category,
            price,
            rating: isNaN(rating) ? 4.0 : rating,
            image: imageData || ('https://via.placeholder.com/300x250?text=' + encodeURIComponent(name)),
            description
        };

        products.push(newProduct);
        localStorage.setItem('products', JSON.stringify(products));

        alert('Product added successfully!');
        document.getElementById('add-product-form').reset();
        // close bootstrap modal
        const modalEl = document.getElementById('productModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
        loadProductsList();
    }

    // If a file was chosen, read it as Data URL then create the product
    const file = imageFileInput && imageFileInput.files && imageFileInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            createProductWithImage(e.target.result);
        };
        reader.onerror = function() {
            alert('Unable to read image file. Using fallback URL.');
            createProductWithImage(imageUrlInput.value || null);
        };
        reader.readAsDataURL(file);
    } else {
        // use provided image URL or fallback
        createProductWithImage(imageUrlInput.value || null);
    }
}

// Edit Product
function editProduct(product) {
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-category').value = product.category;
    document.getElementById('product-price').value = product.price;
    document.getElementById('product-rating').value = product.rating;
    document.getElementById('product-image').value = product.image;
    document.getElementById('product-description').value = product.description || '';
    
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
}

// Delete Product
function deleteProduct(index) {
    if (confirm('Are you sure you want to delete this product?')) {
        products.splice(index, 1);
        localStorage.setItem('products', JSON.stringify(products));
        loadProductsList();
        alert('Product deleted successfully!');
    }
}

// Initialize Charts
function initCharts() {
    // Sales Chart
    const salesCtx = document.getElementById('sales-chart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Sales',
                    data: [1200, 1900, 1500, 2200],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Category Chart
    const categoryCtx = document.getElementById('category-chart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Living Room', 'Bedroom', 'Office', 'Decor'],
                datasets: [{
                    data: [30, 25, 20, 25],
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#4facfe']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Acquisition Chart
    const acquisitionCtx = document.getElementById('acquisition-chart');
    if (acquisitionCtx) {
        new Chart(acquisitionCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr'],
                datasets: [{
                    label: 'New Users',
                    data: [12, 19, 15, 25],
                    backgroundColor: '#667eea'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
}