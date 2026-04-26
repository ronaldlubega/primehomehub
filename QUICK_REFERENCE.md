# Prime Home Hub - Implementation Checklist & Quick Reference

## ✅ SETUP CHECKLIST

### Phase 1: Database Setup (5 minutes)
- [ ] **Create MySQL Database**
  ```sql
  CREATE DATABASE prime_home_hub CHARACTER SET utf8mb4;
  ```

- [ ] **Import Schema**
  - Option A: phpMyAdmin → Import → `database/database.sql`
  - Option B: CLI → `mysql -u root prime_home_hub < database/database.sql`

- [ ] **Verify Tables Created**
  ```sql
  SHOW TABLES; -- Should show 13 tables
  ```

### Phase 2: Configuration (2 minutes)
- [ ] **Update Database Credentials**
  - File: `includes/database.php`
  - Update: DB_HOST, DB_USER, DB_PASSWORD, DB_NAME

- [ ] **Verify File Permissions**
  - Ensure `api/`, `includes/`, `database/` directories are readable

### Phase 3: Testing (10 minutes)
- [ ] **Test Product API**
  - Visit: `http://localhost/api/products.php?action=list`
  - Should return: 12 products with JSON

- [ ] **Test Register Endpoint**
  - Use curl or Postman to register test user

- [ ] **Test Login Endpoint**
  - Login and save auth token

- [ ] **Test Create Order**
  - Create test order with 2-3 items

---

## 📋 API QUICK REFERENCE

### Authentication
```javascript
// Register
POST /api/users.php?action=register
Body: {email, username, password, confirm_password, first_name, last_name}

// Login
POST /api/users.php?action=login
Body: {email, password}
Returns: {token, user}

// Get Profile (requires token)
GET /api/users.php?action=profile
Header: Authorization: Bearer {token}

// Logout
GET /api/users.php?action=logout
```

### Products
```javascript
// List (no auth needed)
GET /api/products.php?action=list&page=1&limit=20&category=1&sort=name

// Search (no auth needed)
GET /api/products.php?action=search&q=sofa

// Get single (no auth needed)
GET /api/products.php?action=get&id=1

// Create (admin only)
POST /api/products.php?action=create
Body: {name, description, price, category_id, image_url, stock}

// Update (admin only)
PUT /api/products.php?action=update&id=1
Body: {name, price, stock, ...}
```

### Orders
```javascript
// Create (requires auth)
POST /api/orders.php?action=create
Body: {
  items: [{product_id, quantity}, ...],
  shipping_address, shipping_city, shipping_country, shipping_postal
}

// List user orders (requires auth)
GET /api/orders.php?action=list&page=1&limit=10

// Get order details (requires auth)
GET /api/orders.php?action=get&id=1

// Cancel order (requires auth)
POST /api/orders.php?action=cancel&id=1

// Process payment (requires auth)
POST /api/orders.php?action=payment&id=1
Body: {payment_method, card_token}
```

### Wishlist
```javascript
// Get wishlist (requires auth)
GET /api/wishlist.php?action=list

// Add to wishlist (requires auth)
POST /api/wishlist.php?action=add
Body: {product_id}

// Remove (requires auth)
DELETE /api/wishlist.php?action=remove&product_id=1

// Check if in wishlist (requires auth)
GET /api/wishlist.php?action=check&product_id=1
```

### Categories
```javascript
// List categories (no auth needed)
GET /api/categories.php?action=list
```

---

## 🔑 KEY INFORMATION

### Database Credentials
```
Host: localhost
Port: 3306
Database: prime_home_hub
User: root
Password: (your MySQL password)
```

### Default Test Accounts
| Email | Password | Role |
|-------|----------|------|
| john@example.com | password123 | customer |
| jane@example.com | password123 | customer |
| admin@example.com | admin123 | admin |

(Available after running `database/sample-data.php`)

### Default Products Count
- Total: 12 furniture items
- Categories: 4 (Living Room, Bedroom, Office, Decor)
- All with real Unsplash images
- Price range: 250,000 - 2,000,000 UG SHS

---

## 🚀 FRONTEND INTEGRATION EXAMPLES

### 1. Load Products on Page
```javascript
async function loadProducts() {
  try {
    const response = await fetch('/api/products.php?action=list&limit=20');
    const data = await response.json();
    
    if (data.success) {
      displayProducts(data.data);
    }
  } catch (error) {
    console.error('Failed to load products:', error);
  }
}

loadProducts(); // Call on page load
```

### 2. Search Products
```javascript
async function searchProducts(query) {
  const response = await fetch(`/api/products.php?action=search&q=${encodeURIComponent(query)}`);
  const data = await response.json();
  return data.results;
}
```

### 3. Register User
```javascript
async function registerUser(email, username, password, firstName, lastName) {
  const response = await fetch('/api/users.php?action=register', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      email, username, password,
      confirm_password: password,
      first_name: firstName,
      last_name: lastName
    })
  });
  
  const data = await response.json();
  if (data.success) {
    // Redirect to login
    window.location.href = '/pages/login.html';
  }
  return data;
}
```

### 4. Login User
```javascript
async function loginUser(email, password) {
  const response = await fetch('/api/users.php?action=login', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({email, password})
  });
  
  const data = await response.json();
  if (data.success) {
    // Store token and user info
    localStorage.setItem('authToken', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));
    
    // Show welcome message
    alert(`Welcome, ${data.user.first_name}!`);
    
    // Redirect to dashboard
    window.location.href = '/pages/user-dashboard.html';
  }
  return data;
}
```

### 5. Create Order
```javascript
async function createOrder(items, shippingInfo) {
  const token = localStorage.getItem('authToken');
  
  const response = await fetch('/api/orders.php?action=create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      items: items,
      shipping_address: shippingInfo.address,
      shipping_city: shippingInfo.city,
      shipping_country: shippingInfo.country,
      shipping_postal: shippingInfo.postal
    })
  });
  
  const data = await response.json();
  if (data.success) {
    alert(`Order created!\nOrder #: ${data.order_number}\nTotal: UG SHS ${data.total.toLocaleString()}`);
  }
  return data;
}
```

### 6. Get User Orders
```javascript
async function getUserOrders() {
  const token = localStorage.getItem('authToken');
  
  const response = await fetch('/api/orders.php?action=list', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  if (data.success) {
    displayOrders(data.orders);
  }
  return data;
}
```

### 7. Manage Wishlist
```javascript
// Add to wishlist
async function addToWishlist(productId) {
  const token = localStorage.getItem('authToken');
  
  const response = await fetch('/api/wishlist.php?action=add', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({product_id: productId})
  });
  
  const data = await response.json();
  return data.success;
}

// Get wishlist
async function getWishlist() {
  const token = localStorage.getItem('authToken');
  
  const response = await fetch('/api/wishlist.php?action=list', {
    headers: {'Authorization': `Bearer ${token}`}
  });
  
  const data = await response.json();
  return data.items;
}
```

### 8. Check Authentication
```javascript
function getAuthToken() {
  return localStorage.getItem('authToken');
}

function isLoggedIn() {
  return getAuthToken() !== null;
}

function getCurrentUser() {
  const user = localStorage.getItem('user');
  return user ? JSON.parse(user) : null;
}

function logout() {
  localStorage.removeItem('authToken');
  localStorage.removeItem('user');
  window.location.href = '/';
}
```

---

## 🐛 TROUBLESHOOTING

### Issue: "Database Connection Failed"
**Solution:**
1. Check MySQL is running: `mysql -u root -p`
2. Verify database exists: `SHOW DATABASES;`
3. Update credentials in `includes/database.php`

### Issue: "Table doesn't exist"
**Solution:**
1. Verify schema imported: `SHOW TABLES;`
2. Check for import errors in phpMyAdmin
3. Re-import `database/database.sql`

### Issue: "Not authenticated" on API
**Solution:**
1. Verify token is sent in Authorization header
2. Check session_token exists in database
3. Token format should be: `Authorization: Bearer {token}`

### Issue: "Products returning empty"
**Solution:**
1. Test API directly: `http://localhost/api/products.php?action=list`
2. Check database: `SELECT COUNT(*) FROM products;`
3. Verify products have status='active'

### Issue: "404 Not Found on API"
**Solution:**
1. Check URL spelling matches exactly
2. Verify action parameter is correct
3. Ensure PHP files in `api/` directory

### Issue: "CORS Error in Browser Console"
**Solution:**
1. APIs have CORS headers enabled
2. If still failing, check browser console for exact error
3. Ensure requests are from same origin or CORS is properly configured

---

## 📊 DATABASE QUERIES FOR TESTING

### Check Product Count
```sql
SELECT COUNT(*) as total FROM products;
```

### View All Users
```sql
SELECT id, email, username, role FROM users;
```

### Check Order Status
```sql
SELECT o.id, o.order_number, o.total_amount, o.status, u.email
FROM orders o
JOIN users u ON o.user_id = u.id
ORDER BY o.created_at DESC;
```

### Check Stock Levels
```sql
SELECT name, stock FROM products ORDER BY stock ASC;
```

### View Recent Activity
```sql
SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 20;
```

---

## 📈 MONITORING & LOGGING

### Enable Error Logging
Add to PHP files:
```php
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php-errors.log');
```

### Monitor API Usage
```sql
SELECT action, COUNT(*) as count
FROM activity_logs
WHERE DATE(created_at) = CURDATE()
GROUP BY action;
```

### Track Failed Logins
```sql
SELECT * FROM activity_logs
WHERE action = 'failed_login'
ORDER BY created_at DESC;
```

---

## 🔒 SECURITY REMINDERS

- ✅ Passwords are bcrypt hashed
- ✅ Tokens are SHA256 hashed when stored
- ✅ All inputs validated before database
- ✅ SQL injection prevented with prepared statements
- ✅ CSRF tokens available if needed
- ⚠️ In production: Enable HTTPS/SSL
- ⚠️ In production: Set secure cookie flags
- ⚠️ In production: Configure firewall rules

---

## 📱 MOBILE APP INTEGRATION

All APIs work with mobile apps:
- Use same endpoints (REST-based)
- Include Authorization header for authenticated endpoints
- Handle token refresh on 401 response
- Implement offline caching for product list

---

## 📞 QUICK SUPPORT LINKS

| Need | File |
|------|------|
| API Details | `api/index.html` |
| Full Docs | `DATABASE_SETUP.md` |
| Quick Start | `QUICK_START.md` |
| Files Info | `FILE_STRUCTURE.md` |
| Summary | `IMPLEMENTATION_COMPLETE.md` |

---

## 🎯 NEXT ACTIONS

1. **Import Database** (5 min)
   - Run `database/database.sql` in MySQL

2. **Update Config** (2 min)
   - Edit `includes/database.php`

3. **Test APIs** (10 min)
   - Use curl/Postman to test endpoints

4. **Connect Frontend** (1-2 hours)
   - Update HTML/JS to call APIs
   - Test user registration → order flow

5. **Go Live** 🚀
   - Deploy to production
   - Configure HTTPS
   - Set up monitoring

---

**Status**: Ready to Deploy ✅
**Version**: 1.0 Production Ready
**Last Updated**: January 2024

---

*For detailed information, see the complete documentation files in the root directory.*
