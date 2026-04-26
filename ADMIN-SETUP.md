# Admin User Setup Guide

## Quick Start

To create an admin account for Prime Home Hub, follow these steps:

### Step 1: Start WAMP Server
1. Open the WAMP taskbar icon
2. Click "Put Online" to start all services
3. Ensure all services are green (Apache, MySQL, etc.)

### Step 2: Access Admin Setup Page
Open your browser and navigate to:
```
http://localhost/admin-setup.html
```

Or alternatively:
```
http://localhost:80/admin-setup.html
```

### Step 3: Fill in Admin Details
The form comes pre-filled with default values:
- **Username**: admin
- **Email**: admin@primehomehub.com
- **Password**: Admin@123456
- **First Name**: Admin
- **Last Name**: User

You can modify these values before creating the account.

### Step 4: Create Admin
1. Click "Create Admin Account" button
2. Wait for success confirmation
3. You'll see the admin credentials displayed

### Step 5: Login
1. Navigate to the admin dashboard: `http://localhost/pages/admin-dashboard.html`
2. Login with the admin credentials you created
3. **Important**: Change the password immediately after first login

## Default Admin Credentials

If using the default values:
```
Username: admin
Email: admin@primehomehub.com
Password: Admin@123456
```

## Alternative: Command Line Setup

If you prefer using command line:

### Using PHP CLI
```bash
cd c:\wamp\www\furn
php create-admin.php
```

### Using MySQL Directly
```bash
mysql -u root -e "USE prime_home_hub; INSERT INTO users (username, email, password_hash, first_name, last_name, role, created_at) VALUES ('admin', 'admin@primehomehub.com', '\$2y\$10\$...(bcrypt hash)', 'Admin', 'User', 'admin', NOW());"
```

## Security Notes

⚠️ **IMPORTANT SECURITY REMINDERS:**

1. **Change Password**: Change the default password immediately after first login
2. **Use Strong Password**: Use a mix of uppercase, lowercase, numbers, and symbols
3. **Secure Access**: Restrict admin access to trusted IP addresses
4. **Database Security**: Change MySQL root password if not already done
5. **Environment Variables**: Keep sensitive credentials in environment variables, not in code

## Troubleshooting

### "Admin user already exists"
An admin user has already been created. If you forgot the password:
1. Access phpMyAdmin
2. Go to the `users` table
3. Delete the admin user
4. Run the setup again

### "Failed to create admin user"
Check that:
1. Database is running and connected
2. `users` table exists (run database setup first)
3. Database credentials in `includes/database.php` are correct

### "Missing required fields"
Ensure all form fields are filled:
- Username (required)
- Email (required, valid format)
- Password (required, 8+ characters)
- First Name (required)

## Admin Dashboard Access

After creating the admin account, access the admin dashboard at:
```
http://localhost/pages/admin-dashboard.html
```

### Admin Capabilities:
- ✓ Manage products (create, edit, delete)
- ✓ Manage orders (view, update status)
- ✓ Manage categories
- ✓ View analytics
- ✓ Manage users
- ✓ System settings

## Need Help?

If you encounter any issues:
1. Check WAMP services are running
2. Verify database is created and schema imported
3. Check browser console for JavaScript errors
4. Check server logs for PHP errors
5. Verify API endpoint: `http://localhost/api/users.php?action=create-admin`

---
**Created**: February 2026
**Updated**: February 5, 2026
