# Prime Home Hub - Quick Start Implementation

## ✅ Complete Backend Package Created

Your Prime Home Hub furniture store now has a production-ready backend with:

### 📦 What's Included

**Database Layer** (13 tables)
- ✅ Users with authentication
- ✅ Products & Categories
- ✅ Orders & Order Items
- ✅ Wishlist
- ✅ Room Plans & Mood Boards (for design tools)
- ✅ Analytics & Backup Logs
- ✅ Reviews & Shipping Info

**PHP APIs** (5 API endpoints)
- ✅ User Authentication (Register, Login, Profile)
- ✅ Product Management (Browse, Search, Admin CRUD)
- ✅ Order Processing (Create, Track, Cancel, Pay)
- ✅ Wishlist Management (Add, Remove, Check)
- ✅ Category Listing

**Security**
- ✅ Bcrypt password hashing
- ✅ Session-based authentication
- ✅ Role-based access control (Customer/Admin)
- ✅ SQL injection prevention
- ✅ CORS enabled

---

## 🚀 Getting Started (3 Steps)

### Step 1: Create MySQL Database
Open phpMyAdmin or MySQL terminal:

```sql
CREATE DATABASE prime_home_hub CHARACTER SET utf8mb4;
```

### Step 2: Import Database Schema
**In phpMyAdmin:**
1. Select `prime_home_hub` database
2. Click "Import" tab
3. Select: `c:\wamp\www\furn\database\database.sql`
4. Click "Go"

**OR via command line:**
```bash
mysql -u root prime_home_hub < c:\wamp\www\furn\database\database.sql
```

### Step 3: Verify Setup
In phpMyAdmin, run:
```sql
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'prime_home_hub';
```

You should see 13 tables ✅

---

## 📁 Files Created

```
c:\wamp\www\furn\
├── database/
│   └── database.sql                    # MySQL schema
├── api/
│   ├── users.php                       # Auth endpoints
│   ├── products.php                    # Product endpoints
│   ├── orders.php                      # Order endpoints
│   ├── wishlist.php                    # Wishlist endpoints
│   └── categories.php                  # Category endpoints
├── includes/
│   ├── database.php                    # DB connection class
│   └── auth.php                        # Auth helpers
└── DATABASE_SETUP.md                   # Full documentation
```

---

## 🧪 Test API Endpoints

### 1. Test Product Listing (No Auth Required)
```
GET: http://localhost/api/products.php?action=list&limit=5
```

Expected: Returns 5 products with real Unsplash furniture images

### 2. Register New User
```
POST: http://localhost/api/users.php?action=register
Body (JSON):
{
  "email": "test@example.com",
  "username": "testuser",
  "password": "Test@1234",
  "confirm_password": "Test@1234",
  "first_name": "Test",
  "last_name": "User"
}
```

Expected: `201 Created` with user_id

### 3. Login User
```
POST: http://localhost/api/users.php?action=login
Body (JSON):
{
  "email": "test@example.com",
  "password": "Test@1234"
}
```

Expected: `200 OK` with authentication token

### 4. Create Order
```
POST: http://localhost/api/orders.php?action=create
Headers:
Authorization: Bearer {token_from_login}
Body (JSON):
{
  "items": [
    {"product_id": 1, "quantity": 1},
    {"product_id": 2, "quantity": 2}
  ],
  "shipping_address": "123 Main St",
  "shipping_city": "Kampala",
  "shipping_country": "Uganda",
  "shipping_postal": "00256"
}
```

Expected: `201 Created` with order_number and total

---

## 🔌 Connect Frontend to APIs

### Update shop.html to Use API

Replace the products array fetch:

```javascript
// OLD (Client-side only)
const products = [...hardcoded array...];

// NEW (From API)
async function loadProducts() {
  const response = await fetch('/api/products.php?action=list&limit=20');
  const data = await response.json();
  
  if (data.success) {
    displayProducts(data.data);
  }
}

loadProducts();
```

### Add to Cart → Save to Order (via API)

```javascript
async function createOrder(cartItems) {
  const token = localStorage.getItem('authToken');
  
  const response = await fetch('/api/orders.php?action=create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      items: cartItems,
      shipping_address: userAddress,
      shipping_city: userCity,
      shipping_country: userCountry,
      shipping_postal: userPostal
    })
  });

  const data = await response.json();
  if (data.success) {
    alert(`Order ${data.order_number} created!\nTotal: UG SHS ${data.total.toLocaleString()}`);
  }
}
```

---

## 👤 User Authentication Flow

### Registration
```javascript
async function register() {
  const response = await fetch('/api/users.php?action=register', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      email: email,
      username: username,
      password: password,
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
}
```

### Login & Store Token
```javascript
async function login() {
  const response = await fetch('/api/users.php?action=login', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      email: email,
      password: password
    })
  });
  
  const data = await response.json();
  if (data.success) {
    // Store token
    localStorage.setItem('authToken', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));
    
    // Redirect to dashboard
    window.location.href = '/pages/user-dashboard.html';
  }
}
```

### Check Authentication
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
```

---

## 📊 Database Structure Reference

### products table
- Default 12 products with real Unsplash images
- Categories: Living Room, Bedroom, Office, Decor
- Fields: id, name, price, image_url, category_id, rating, stock

### users table
- Passwords: Bcrypt hashed
- Roles: customer (default), admin
- Fields: id, username, email, first_name, last_name, role, session_token

### orders table
- Status: pending, confirmed, processing, shipped, delivered, cancelled
- Payment: pending, completed, failed, refunded
- Links to order_items (1:many)

### wishlist table
- Stores user's saved products
- Links users and products

---

## 🔐 Admin User Setup (Optional)

To create an admin user in phpMyAdmin:

```sql
-- First, register via API or insert manually
-- To make existing user admin:
UPDATE users 
SET role = 'admin' 
WHERE username = 'adminuser';
```

---

## 📈 Next Features to Implement

1. **Email Notifications**
   - Order confirmation emails
   - Shipping updates
   - Promotional newsletters

2. **Payment Gateway**
   - Integrate Stripe/PayPal
   - Handle card payments
   - Invoice generation

3. **Admin Dashboard**
   - Product management UI
   - Order tracking dashboard
   - Sales analytics
   - User management

4. **Product Reviews**
   - Submit reviews (already in DB)
   - Display ratings
   - Moderate reviews

5. **Design Tools Storage**
   - Save/Load Room Plans
   - Save/Load Mood Boards
   - Share designs

6. **Analytics Dashboard**
   - Track user behavior
   - Sales reports
   - Popular products

---

## 🐛 Troubleshooting

### "Database Connection Failed"
- Check `includes/database.php` credentials
- Verify MySQL is running
- Confirm database `prime_home_hub` exists

### "Table doesn't exist"
- Run `SHOW TABLES;` in phpMyAdmin
- Re-import `database.sql`

### "Not authenticated"
- Ensure token is sent in Authorization header
- Check `localStorage.getItem('authToken')`
- Re-login if token expired

### Products not showing
- Check API: `http://localhost/api/products.php?action=list`
- Verify products in database: `SELECT * FROM products;`

---

## 🎯 Next Step

**Import the database.sql file now!**

Once done, test the APIs with the curl commands above and start connecting frontend pages to the backend.

---

**Version**: 1.0 - Production Ready
**Last Updated**: January 2024
**Support**: Check DATABASE_SETUP.md for detailed documentation
