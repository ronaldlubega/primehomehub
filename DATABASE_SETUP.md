# Prime Home Hub - Database & Backend Setup Guide

## Overview
Complete backend infrastructure for Prime Home Hub furniture e-commerce platform with MySQL database, authentication, order management, and analytics.

## Project Structure
```
c:\wamp\www\furn\
├── database/
│   └── database.sql          # MySQL schema (13 tables)
├── api/
│   ├── users.php             # User authentication API
│   ├── products.php          # Product management API
│   ├── orders.php            # Order processing API
│   ├── wishlist.php          # Wishlist management API
│   └── categories.php        # Category listing API
├── includes/
│   ├── database.php          # Database connection class
│   └── auth.php              # Authentication helper functions
└── [existing frontend files]
```

## Database Setup

### Step 1: Create the Database
Open phpMyAdmin or MySQL command line and run:

```sql
CREATE DATABASE IF NOT EXISTS prime_home_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prime_home_hub;
```

### Step 2: Import Schema
Execute the SQL file at `database/database.sql`:

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin
2. Select the `prime_home_hub` database
3. Click "Import"
4. Choose file: `database/database.sql`
5. Click "Go"

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p prime_home_hub < c:\wamp\www\furn\database\database.sql
```

**Option C: Using Command Line (no password)**
```bash
mysql -u root prime_home_hub < c:\wamp\www\furn\database\database.sql
```

### Step 3: Verify Tables Created
Run this query to confirm all tables exist:

```sql
SHOW TABLES;
```

You should see 13 tables:
- categories
- products
- users
- orders
- order_items
- wishlist
- room_plans
- mood_boards
- analytics
- backup_logs
- reviews
- shipping_info
- activity_logs

## Database Schema Summary

### Core Tables

#### `users` Table
Stores user account information with authentication
```
Columns: id, username, email, password_hash, first_name, last_name, role, 
         avatar_url, phone, address, city, country, session_token, 
         last_login, created_at
```
- **Roles**: customer, admin
- **Security**: Passwords hashed with bcrypt

#### `products` Table
All furniture products with pricing and inventory
```
Columns: id, name, description, price, image_url, category_id, rating, 
         stock, status, created_at, updated_at
```
- **Categories**: Living Room, Bedroom, Office, Decor
- **12 Default Products**: Pre-loaded with Unsplash images

#### `orders` Table
E-commerce order tracking
```
Columns: id, user_id, order_number, total_amount, status, payment_status, 
         created_at, updated_at
```
- **Status**: pending, confirmed, processing, shipped, delivered, cancelled
- **Payment**: pending, completed, failed, refunded

#### `order_items` Table
Individual items within orders
```
Columns: id, order_id, product_id, quantity, price
```

#### `categories` Table
Product categories
```
Columns: id, name, description, image_url
```

#### `wishlist` Table
User's saved products
```
Columns: id, user_id, product_id, created_at
```

#### `reviews` Table
Product reviews and ratings
```
Columns: id, product_id, user_id, rating, comment, created_at
```

### Design Tables

#### `room_plans` Table
Saved room design layouts
```
Columns: id, user_id, name, description, canvas_data, created_at, updated_at
```

#### `mood_boards` Table
Saved mood board collections
```
Columns: id, user_id, name, description, items_data, created_at, updated_at
```

### Analytics & Admin Tables

#### `analytics` Table
User behavior and session tracking
```
Columns: id, user_id, action, page_url, timestamp
```

#### `backup_logs` Table
Database backup history
```
Columns: id, backup_filename, backup_size, backup_date, status
```

#### `activity_logs` Table
User account activity logging
```
Columns: id, user_id, action, description, created_at
```

#### `shipping_info` Table
Order shipping details
```
Columns: id, order_id, address, city, country, postal_code, created_at
```

## PHP Configuration

### Update Database Credentials
Edit `includes/database.php`:

```php
define('DB_HOST', 'localhost');      // Your MySQL host
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASSWORD', '');           // Your MySQL password
define('DB_NAME', 'prime_home_hub'); // Database name
define('DB_PORT', 3306);             // MySQL port
```

### File Permissions
Ensure these directories are writable:
```
chmod 755 database/
chmod 755 api/
chmod 755 includes/
```

## API Endpoints

### Authentication APIs

#### Register User
```
POST /api/users.php?action=register
Content-Type: application/json

{
  "email": "user@example.com",
  "username": "johndoe",
  "password": "securepass123",
  "confirm_password": "securepass123",
  "first_name": "John",
  "last_name": "Doe"
}

Response: 201 Created
{
  "success": true,
  "message": "User registered successfully",
  "user_id": 1
}
```

#### Login User
```
POST /api/users.php?action=login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "securepass123"
}

Response: 200 OK
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "user@example.com",
    "role": "customer",
    "first_name": "John"
  },
  "token": "abc123..."
}
```

#### Get User Profile
```
GET /api/users.php?action=profile
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "user@example.com",
    ...
  }
}
```

#### Update Profile
```
POST /api/users.php?action=update
Authorization: Bearer {token}

{
  "first_name": "John",
  "phone": "+256700123456",
  "address": "123 Main Street",
  "city": "Kampala",
  "country": "Uganda"
}
```

#### Logout
```
GET /api/users.php?action=logout
Authorization: Bearer {token}
```

### Product APIs

#### List Products
```
GET /api/products.php?action=list&page=1&limit=20&category=1&min_price=0&max_price=5000000&sort=name&order=ASC

Response: 200 OK
{
  "success": true,
  "data": [...products...],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 50,
    "pages": 3
  }
}
```

#### Search Products
```
GET /api/products.php?action=search&q=sofa

Response: 200 OK
{
  "success": true,
  "query": "sofa",
  "results": [...matching products...]
}
```

#### Get Single Product
```
GET /api/products.php?action=get&id=1

Response: 200 OK
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Modern Sofa",
    "price": 1500000,
    "image_url": "...",
    "reviews": [...]
  }
}
```

#### Create Product (Admin Only)
```
POST /api/products.php?action=create
Authorization: Bearer {admin_token}

{
  "name": "Luxury Dining Table",
  "description": "Premium wooden dining table",
  "price": 2500000,
  "category_id": 2,
  "image_url": "https://...",
  "stock": 5
}

Response: 201 Created
{
  "success": true,
  "message": "Product created",
  "product_id": 13
}
```

### Order APIs

#### Create Order
```
POST /api/orders.php?action=create
Authorization: Bearer {token}

{
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 3, "quantity": 1}
  ],
  "shipping_address": "123 Main Street",
  "shipping_city": "Kampala",
  "shipping_country": "Uganda",
  "shipping_postal": "00256"
}

Response: 201 Created
{
  "success": true,
  "message": "Order created successfully",
  "order_id": 1,
  "order_number": "ORD-20240115143022-5678",
  "total": 3500000
}
```

#### Get User Orders
```
GET /api/orders.php?action=list&page=1&limit=10&status=completed
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "orders": [...orders with items...],
  "pagination": {...}
}
```

#### Get Single Order
```
GET /api/orders.php?action=get&id=1
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "order": {
    "id": 1,
    "order_number": "ORD-20240115143022-5678",
    "total_amount": 3500000,
    "status": "pending",
    "items": [...],
    "shipping": {...}
  }
}
```

#### Update Order Status (Admin Only)
```
PUT /api/orders.php?action=update-status&id=1
Authorization: Bearer {admin_token}

{
  "status": "shipped"
}
```

#### Cancel Order
```
POST /api/orders.php?action=cancel&id=1
Authorization: Bearer {token}
```

#### Process Payment
```
POST /api/orders.php?action=payment&id=1
Authorization: Bearer {token}

{
  "payment_method": "card",
  "card_token": "tok_visa"
}
```

### Wishlist APIs

#### Get Wishlist
```
GET /api/wishlist.php?action=list
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "count": 5,
  "items": [...]
}
```

#### Add to Wishlist
```
POST /api/wishlist.php?action=add
Authorization: Bearer {token}

{
  "product_id": 5
}
```

#### Remove from Wishlist
```
DELETE /api/wishlist.php?action=remove&product_id=5
Authorization: Bearer {token}
```

#### Check if in Wishlist
```
GET /api/wishlist.php?action=check&product_id=5
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "in_wishlist": true
}
```

### Category APIs

#### List Categories
```
GET /api/categories.php?action=list

Response: 200 OK
{
  "success": true,
  "categories": [
    {"id": 1, "name": "Living Room", "description": "...", "image_url": "..."},
    ...
  ]
}
```

## Security Features

### Password Security
- Passwords hashed with bcrypt (PHP's `password_hash()`)
- Minimum 8 characters required
- Salt automatically generated per password

### Session Management
- Token-based authentication using SHA256
- Secure HTTP-only cookies
- 30-day session expiration
- Logout clears session token

### Role-Based Access Control
- **Customer**: Can browse, order, manage profile
- **Admin**: Can create/edit products, manage orders, view analytics

### Input Validation
- Email format validation
- Required field checking
- SQL injection prevention with prepared statements
- XSS protection with proper escaping

## Frontend Integration

### Example: Register User (JavaScript)
```javascript
const response = await fetch('/api/users.php?action=register', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    email: 'user@example.com',
    username: 'johndoe',
    password: 'securepass123',
    confirm_password: 'securepass123',
    first_name: 'John',
    last_name: 'Doe'
  })
});

const data = await response.json();
if (data.success) {
  console.log('User registered!');
  // Redirect to login
}
```

### Example: Create Order (JavaScript)
```javascript
const response = await fetch('/api/orders.php?action=create', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${authToken}`
  },
  body: JSON.stringify({
    items: [
      {product_id: 1, quantity: 2},
      {product_id: 3, quantity: 1}
    ],
    shipping_address: '123 Main Street',
    shipping_city: 'Kampala',
    shipping_country: 'Uganda',
    shipping_postal: '00256'
  })
});

const data = await response.json();
if (data.success) {
  console.log(`Order ${data.order_number} created!`);
  console.log(`Total: UG SHS ${data.total.toLocaleString()}`);
}
```

## Testing the APIs

### Using cURL
```bash
# Register
curl -X POST http://localhost/api/users.php?action=register \
  -H "Content-Type: application/json" \
  -d '{
    "email":"test@example.com",
    "username":"testuser",
    "password":"password123",
    "confirm_password":"password123",
    "first_name":"Test",
    "last_name":"User"
  }'

# Login
curl -X POST http://localhost/api/users.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# List products
curl http://localhost/api/products.php?action=list&limit=10
```

### Using Postman
1. Import API collection (create for your team)
2. Set environment variables: `{{base_url}}`, `{{token}}`
3. Test each endpoint systematically
4. Save responses for documentation

## Troubleshooting

### Database Connection Failed
**Error**: "Database Connection Failed"
- Check database credentials in `includes/database.php`
- Verify MySQL service is running
- Check database name exists
- Verify user has appropriate permissions

### Table Not Found
**Error**: "Table doesn't exist"
- Run `SHOW TABLES;` to verify schema imported
- Re-import `database.sql` file
- Check for errors in import process

### Authentication Fails
**Error**: "Not authenticated" or "Invalid token"
- Ensure token is being passed in Authorization header
- Check session_token in users table is not NULL
- Verify cookie is being set correctly

### Stock Issues
**Error**: "Insufficient stock"
- Check products table stock quantities
- Verify stock updates in order_items inserts

## Next Steps

1. **Update Frontend**: Connect existing HTML/JS to these APIs
2. **Implement Payment Gateway**: Add Stripe/PayPal integration
3. **Create Admin Dashboard**: Build admin panel for product/order management
4. **Setup Backups**: Create automated MySQL backup script
5. **Enable Analytics**: Implement tracking in frontend JavaScript
6. **Add Reviews**: Implement product review system
7. **Email Notifications**: Add order confirmation emails

## Support & Debugging

### View Database Logs
```sql
SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 20;
SELECT * FROM backup_logs ORDER BY backup_date DESC LIMIT 10;
```

### Monitor Orders
```sql
SELECT o.id, o.order_number, o.total_amount, o.status, o.payment_status, u.email
FROM orders o
JOIN users u ON o.user_id = u.id
ORDER BY o.created_at DESC;
```

### Check User Sessions
```sql
SELECT id, username, email, session_token, last_login FROM users WHERE session_token IS NOT NULL;
```

### Monitor Stock
```sql
SELECT name, stock FROM products WHERE stock < 5;
```

## Performance Tips

1. **Add Indexes**: Already included in schema
2. **Cache Products**: Cache product list in frontend (localStorage)
3. **Pagination**: Always use pagination for large result sets
4. **Database Optimization**: Run `OPTIMIZE TABLE` monthly
5. **Connection Pooling**: Consider for high-traffic scenarios

---

**Database Last Updated**: January 2024
**Version**: 1.0
**Status**: Production Ready
