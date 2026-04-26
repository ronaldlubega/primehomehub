# Testing Guide - User & Admin Dashboards

## 🧪 Complete Testing Walkthrough

### Prerequisites
- Website running at `http://localhost/furn/`
- Browser DevTools available (F12)
- No previous localStorage data (optional: clear all)

---

## Phase 1: User Dashboard Testing

### Test 1.1: User Registration & Login

**Steps:**
1. Open `http://localhost/furn/index.html`
2. Click **Account** button
3. Switch to **Register** tab
4. Fill in:
   - Name: `John Doe`
   - Email: `john@example.com`
   - Password: `password123`
5. Click **Register**
6. Click **Login** tab (auto-switched)
7. Enter same credentials
8. Click **Login**

**Expected Results:**
- Registration succeeds with confirmation message
- Login succeeds and closes modal
- User name appears in Account button
- Account button now shows logged-in state

---

### Test 1.2: Shopping & Order Creation

**Steps:**
1. Click **Shop** from navigation
2. Add 2-3 products to cart:
   - Modern Sofa ($899)
   - Coffee Table ($299)
   - Office Desk ($599)
3. Click **Cart** (or navigate to `pages/cart.html`)
4. Verify quantities can be adjusted
5. Click **Proceed to Checkout**

**Expected Results:**
- Cart displays all items with images
- Quantities can be updated
- Tax calculated (8%)
- Shipping added ($10)
- Order created successfully
- Redirected to User Dashboard
- Order appears in Orders tab

**Verification:**
```javascript
// In console, verify order was created:
JSON.parse(localStorage.getItem('orders'));
// Should show your new order
```

---

### Test 1.3: User Dashboard - Overview Tab

**Steps:**
1. User dashboard should open automatically after checkout
2. Review **Overview** tab (default view)
3. Inspect statistics cards

**Expected Results:**
- **Total Orders:** Shows 1 (from test 1.2)
- **Total Spent:** Shows order total ($1,797.00)
- **Wishlist Items:** Shows 0 (or higher if items added)
- **Member Level:** Shows "Bronze" (for < $500) or "Silver"/$"Gold"
- **Recent Orders** table shows your order
- Status badge shows "Pending"

---

### Test 1.4: User Dashboard - Profile Tab

**Steps:**
1. Click **Profile** in sidebar
2. View profile information:
   - Avatar shows "J" (from John)
   - Name shows "John Doe"
   - Email shows "john@example.com"
   - Member since 2026
3. Click **Personal Info** sub-tab
4. Modify fields:
   - Change Last Name to "Smith"
   - Add Phone: "+1 (555) 123-4567"
5. Click **Save Changes**
6. Switch to **Security** sub-tab
7. Fill security form:
   - Current Password: `password123`
   - New Password: `NewPass456`
   - Confirm Password: `NewPass456`
8. Click **Update Password**

**Expected Results:**
- Profile information displays correctly
- All form fields are editable (except email)
- Profile updates confirmed with alert
- Password changes confirmed with alert
- No page reload needed

---

### Test 1.5: User Dashboard - Orders Tab

**Steps:**
1. Click **Orders** in sidebar
2. Review orders table

**Expected Results:**
- Order ID displayed (first 8 characters of timestamp)
- Order date shows today's date
- Items count shows 3
- Total shows correct amount
- Status badge shows "Pending" in yellow
- View/Details button available

---

### Test 1.6: User Dashboard - Wishlist Tab

**Steps:**
1. Go back to Shop page
2. Click heart icon on any product to add to wishlist
3. Return to Dashboard
4. Click **Wishlist** in sidebar
5. View wishlist items

**Expected Results:**
- Wishlist displays as grid of product cards
- Each card shows image, name, rating, price
- "Add to Cart" button available
- Can add items from wishlist directly to cart
- Clear All button visible

**Verification:**
```javascript
// In console:
JSON.parse(localStorage.getItem('wishlist'));
// Should show wishlist items
```

---

### Test 1.7: User Dashboard - Addresses Tab

**Steps:**
1. Click **Addresses** in sidebar
2. Click **Add New Address**
3. (Modal/form not yet fully implemented)

**Expected Results:**
- Empty state showing "No addresses saved yet"
- Add New Address button present

---

### Test 1.8: User Dashboard - Settings Tab

**Steps:**
1. Click **Settings** in sidebar
2. Test notification toggles:
   - Toggle **Email Notifications** (checked)
   - Toggle **SMS Notifications** (unchecked)
   - Toggle **Marketing Emails** (checked)
3. Click **Save Preferences**
4. Review Danger Zone

**Expected Results:**
- Toggle switches respond to clicks
- Save Preferences shows confirmation message
- Delete Account button visible in Danger Zone
- Preferences persist in localStorage

---

### Test 1.9: Logout Flow

**Steps:**
1. Click **Logout** at bottom of sidebar
2. Verify redirect

**Expected Results:**
- User logged out
- localStorage cleared of currentUser
- Redirected to shop page
- Account button shows "Account" (not logged in state)

---

## Phase 2: Admin Dashboard Testing

### Test 2.1: Admin Access Setup

**Steps:**
1. Open browser DevTools (F12)
2. Go to Console tab
3. Run:
   ```javascript
   localStorage.setItem('isAdmin', 'true');
   ```
4. Navigate to `http://localhost/furn/pages/admin-dashboard.html`

**Expected Results:**
- Admin dashboard loads successfully
- Page shows "Dashboard" as default tab
- Sidebar shows admin menu items
- No "Access Denied" alert

**If error occurs:**
- Make sure you're logged in first:
  ```javascript
  // Check current user
  JSON.parse(localStorage.getItem('currentUser'));
  ```

---

### Test 2.2: Admin Dashboard - Overview Tab

**Steps:**
1. Review statistics cards at top:
   - Total Revenue
   - Total Orders
   - Total Users
   - Products
2. Inspect charts:
   - Sales Overview (line chart)
   - Top Products section
3. Review Recent Orders table

**Expected Results:**
- Statistics show data from your previous orders
- Total Revenue shows sum of all orders
- Sales chart displays 4-week trend
- Top products ranked by rating
- Recent orders table shows latest orders

**Sample Data:**
- If you created one $1,797 order:
  - Total Revenue: $1,797.00
  - Total Orders: 1
  - Total Users: 1 (only John)
  - Products: 12

---

### Test 2.3: Admin Dashboard - Products Tab

**Steps:**
1. Click **Products** in sidebar
2. Review product table
3. Click **Add Product** button
4. Fill modal form:
   - Product Name: `Premium Chair`
   - Category: `Office`
   - Price: `449.99`
   - Rating: `4.8`
   - Image URL: `https://via.placeholder.com/300x250?text=Premium+Chair`
   - Description: `High-end ergonomic office chair with lumbar support`
5. Click **Add Product**
6. Verify product appears in table

**Expected Results:**
- Product list displays all 12 original products
- Product table shows ID, Name, Category, Price, Rating
- Edit and Delete buttons present
- Modal form opens with correct fields
- New product added to table (now 13 products)
- Product appears in shop page

**Verification:**
```javascript
// In console:
products.length;  // Should be 13
products[12];     // Your new product
```

---

### Test 2.4: Admin Dashboard - Product Edit/Delete

**Steps:**
1. In Products tab, click **Edit** button on Premium Chair (just added)
2. Modal opens with current data
3. Change name to `Premium Executive Chair`
4. Click **Add Product** to save (this adds a duplicate for now)
5. Delete the duplicate by clicking **Delete**
6. Confirm deletion in alert

**Expected Results:**
- Edit button populates modal correctly
- Can modify product details
- Delete confirms before removal
- Product removed from table
- Changes persist in localStorage

---

### Test 2.5: Admin Dashboard - Orders Tab

**Steps:**
1. Click **Orders** in sidebar
2. View orders table
3. Click status dropdown on your order
4. Change status from "Pending" to "Processing"
5. Verify status updates

**Expected Results:**
- Orders table shows all orders with customer name
- Status dropdown available on each order
- Status changes reflected immediately
- Badge color changes (yellow → blue)

---

### Test 2.6: Admin Dashboard - Users Tab

**Steps:**
1. Click **Users** in sidebar
2. Review users table
3. Try search functionality:
   - Type `john` in search box
4. Verify user appears

**Expected Results:**
- Users table shows:
  - User ID (timestamp)
  - Name (John Doe)
  - Email (john@example.com)
  - Orders count (1)
  - Total Spent ($1,797.00)
  - Joined date
- Search filters results in real-time

---

### Test 2.7: Admin Dashboard - Analytics Tab

**Steps:**
1. Click **Analytics** in sidebar
2. View analytics page sections:
   - Sales by Category (doughnut chart)
   - Customer Acquisition (bar chart)
   - Metrics (4 KPI boxes)

**Expected Results:**
- Category chart shows distribution
- Acquisition chart displays monthly data
- Conversion Rate calculated correctly
- Average Order Value shows: $1,797.00 (single order)
- Customer Lifetime Value shows: $1,797.00 (per user)
- Return Rate shows: 2.5%

**Calculation Example:**
```javascript
// With 1 order and 1 user:
- Conversion: (1 / 1) * 100 = 100%
- AOV: 1797 / 1 = $1,797
- CLV: 1797 / 1 = $1,797
```

---

### Test 2.8: Admin Dashboard - Settings Tab

**Steps:**
1. Click **Settings** in sidebar
2. Review System Settings:
   - Store Name: "Design Haven"
   - Tax Rate: "8"
   - Shipping Cost: "10"
3. Modify a setting:
   - Change Tax Rate to "10"
4. Click **Save Settings**
5. Review Email Configuration section

**Expected Results:**
- All settings fields displayed with current values
- Form submits successfully
- Confirmation message shown
- Email configuration section visible

---

## Phase 3: Integration Testing

### Test 3.1: Cross-Dashboard Consistency

**Steps:**
1. User creates order in User Dashboard
2. Admin verifies same order in Admin Dashboard
3. Verify data matches

**Expected Results:**
- Same order appears in both dashboards
- Order ID matches
- Amount matches
- Status consistent

---

### Test 3.2: Real-time Updates

**Steps:**
1. User Dashboard open in one tab
2. Admin Dashboard open in another tab
3. In User tab, add item to cart
4. Check Admin tab - verify order count updated

**Expected Results:**
- Changes visible in both dashboards
- Data synchronized via localStorage
- No page reload needed

---

### Test 3.3: Mobile Responsiveness

**Steps:**
1. Open dashboard in DevTools
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select iPhone 12 (390px width)
4. Test navigation:
   - Sidebar accessibility
   - Table readability
   - Form inputs
   - Button sizes
5. Try tablet (768px width)

**Expected Results:**
- Sidebar collapses/responsive
- Tables remain readable
- Single column layouts
- Touch-friendly button sizes
- All content accessible

---

### Test 3.4: Data Persistence

**Steps:**
1. Create order with multiple items
2. Add to wishlist
3. Update profile
4. Close browser completely
5. Reopen and navigate to dashboard
6. Verify all data preserved

**Expected Results:**
- All user data persists
- Orders remain in history
- Wishlist items saved
- Profile changes retained
- No data loss on page reload

```javascript
// Verify localStorage persistence:
console.log('Orders:', JSON.parse(localStorage.getItem('orders')));
console.log('Users:', JSON.parse(localStorage.getItem('users')));
console.log('Cart:', JSON.parse(localStorage.getItem('cart')));
console.log('Wishlist:', JSON.parse(localStorage.getItem('wishlist')));
```

---

## Phase 4: Edge Cases & Error Handling

### Test 4.1: Empty States

**Steps:**
1. New user with no orders
2. View Orders tab
3. View Wishlist tab (no items added)
4. Admin view with no products

**Expected Results:**
- Empty state messages displayed
- Links to create/add items
- No errors in console
- Graceful handling

---

### Test 4.2: Multiple Orders

**Steps:**
1. Create 3 separate orders
2. View in Orders tab
3. Verify sorting/ordering

**Expected Results:**
- All orders display
- Newest first (reverse chronological)
- Order IDs unique
- No duplicate orders

---

### Test 4.3: Browser Console Verification

**Steps:**
1. Open DevTools Console (F12)
2. Run these commands:

```javascript
// Check all users
JSON.parse(localStorage.getItem('users'));

// Check all orders
JSON.parse(localStorage.getItem('orders'));

// Check cart
JSON.parse(localStorage.getItem('cart'));

// Check admin status
localStorage.getItem('isAdmin');

// Check current user
JSON.parse(localStorage.getItem('currentUser'));
```

**Expected Results:**
- All data properly formatted
- No console errors
- localStorage quota not exceeded
- Data valid JSON

---

## 🐛 Common Issues & Solutions

### Issue: Admin dashboard redirects to home
**Solution:**
```javascript
localStorage.setItem('isAdmin', 'true');
// Refresh page
```

### Issue: Orders not showing in dashboard
**Solution:**
- Create a new order by completing checkout
- Check if you're logged in: `userManager.isLoggedIn()`

### Issue: Charts not displaying
**Solution:**
- Check if Chart.js CDN loaded: `Chart` in console
- Verify canvas elements exist in DOM

### Issue: Mobile layout broken
**Solution:**
- Clear browser cache (Ctrl+Shift+Del)
- Hard refresh (Ctrl+Shift+R)
- Check Bootstrap classes applied

---

## ✅ Testing Checklist

### User Dashboard
- [ ] User registration works
- [ ] User login works
- [ ] Profile information displays
- [ ] Orders tab shows created orders
- [ ] Wishlist tab functions
- [ ] Settings can be saved
- [ ] Logout clears session
- [ ] Mobile view responsive

### Admin Dashboard
- [ ] Admin access restricted (checks isAdmin flag)
- [ ] Statistics calculate correctly
- [ ] Product management works (add/edit/delete)
- [ ] Order management works
- [ ] User list displays
- [ ] Charts render properly
- [ ] Settings save correctly
- [ ] Logout clears admin status

### Integration
- [ ] Order creation works end-to-end
- [ ] Data persists across page reloads
- [ ] Checkout redirects to dashboard
- [ ] Cross-dashboard consistency
- [ ] Console has no errors

---

## 📊 Test Data Created

After complete testing, your localStorage should contain:

```javascript
{
    users: [
        { id: 123456, name: 'John Doe', email: 'john@example.com', password: 'password123' }
    ],
    orders: [
        { id: 789012, userId: 123456, items: [...], total: 1797.00, status: 'pending', ... }
    ],
    cart: [],
    wishlist: [
        { id: 1, name: 'Modern Sofa', ... }
    ],
    currentUser: { id: 123456, name: 'John Doe', email: 'john@example.com' },
    isAdmin: 'true'
}
```

---

## 🎯 Test Results Documentation

Create a test results file:

```markdown
# Test Results - January 25, 2026

## User Dashboard: ✅ PASS
- Registration: ✅
- Login: ✅
- Profile: ✅
- Orders: ✅
- Wishlist: ✅
- Settings: ✅

## Admin Dashboard: ✅ PASS
- Overview: ✅
- Products: ✅
- Orders: ✅
- Users: ✅
- Analytics: ✅
- Settings: ✅

## Integration: ✅ PASS
- Order Creation: ✅
- Data Persistence: ✅
- Cross-Dashboard: ✅
- Mobile: ✅

## Issues Found: 0
```

---

**Testing Guide Complete**
Ready for production testing and customization!
