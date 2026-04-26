# Quick Start Guide - Design Haven

## Getting Started in 5 Minutes

### Step 1: Access the Website
1. Open your browser
2. Go to `http://localhost/furn/index.html`
3. You should see the beautiful Design Haven homepage

### Step 2: Explore the Website
- Click **"Shop Now"** to browse products
- Use **filters** to narrow down products
- Click **"View Details"** on any product to see more
- Add items to your **Cart**

### Step 3: Create an Account
- Click **"Account"** in the top right
- Click the **"Register"** tab
- Fill in your details and click **"Register"**
- Return to **"Login"** tab and sign in

### Step 4: Add to Cart and Checkout
1. Browse the shop
2. Click **"Add to Cart"** on any product
3. Go to **"Cart"** page
4. Review your order
5. Click **"Proceed to Checkout"** (payment integration needed)

### Step 5: Explore Other Sections
- **About** - Learn about Design Haven and the team
- **Services** - View design services and pricing
- **Portfolio** - See before & after project transformations
- **Blog** - Read design tips and trends
- **Contact** - Get in touch or book a consultation

---

## File Organization

```
furn/
├── index.html                    # Homepage
├── styles.css                   # Main CSS file
├── script.js                    # Legacy (can be removed)
├── README.md                    # Full documentation
├── ARCHITECTURE.md              # Technical details
├── QUICKSTART.md               # This file
│
├── css/
│   └── styles.css              # Additional CSS
│
├── js/
│   ├── app.js                  # Main app logic
│   └── shop.js                 # Shop page logic
│
├── pages/
│   ├── shop.html               # Product catalog
│   ├── cart.html               # Shopping cart
│   ├── about.html              # About page
│   ├── services.html           # Services page
│   ├── portfolio.html          # Portfolio/gallery
│   ├── contact.html            # Contact page
│   └── blog.html               # Blog page
│
└── data/
    └── products.json           # Product data
```

---

## Key Features

### 1. Shopping System
- ✅ Browse 12+ products
- ✅ Filter by category, price, rating
- ✅ Search for products
- ✅ Add to cart
- ✅ Wishlist/favorites
- ✅ Shopping cart with calculations

### 2. User Accounts
- ✅ Register new accounts
- ✅ Login functionality
- ✅ User authentication
- ✅ Account display in navigation
- ✅ Logout feature

### 3. Product Management
- ✅ 4 categories (Living Room, Bedroom, Office, Decor)
- ✅ 12 sample products
- ✅ Product ratings
- ✅ Detailed product information
- ✅ Modal product viewer

### 4. Design Content
- ✅ Design services showcase
- ✅ Portfolio/before & after
- ✅ Blog with tips & trends
- ✅ Team information
- ✅ Contact form

### 5. Responsive Design
- ✅ Mobile friendly
- ✅ Tablet optimized
- ✅ Desktop experience
- ✅ Bootstrap 5 grid
- ✅ Smooth animations

---

## Testing the Website

### Test Account
Use these credentials to login:
- Email: `test@example.com`
- Password: `password123`

Or create your own account through registration.

### Test Products
Browse these sample products:
- **Living Room**: Modern Sofa ($899), Coffee Table ($299)
- **Bedroom**: King Bed Frame ($1,299), Nightstand ($299)
- **Office**: Office Desk ($599), Office Chair ($399)
- **Decor**: Plant Pot ($59), Mirror ($149)

### Test Scenarios

**Scenario 1: Shopping**
1. Go to Shop
2. Add 2-3 items to cart
3. Go to Cart page
4. Verify items and prices
5. Check total calculation

**Scenario 2: Filtering**
1. Go to Shop
2. Select "Bedroom" category
3. Set price range to $500
4. See only matching products

**Scenario 3: Search**
1. Go to Shop
2. Type "sofa" in search
3. Should find Modern Sofa
4. Click product for details

**Scenario 4: User Account**
1. Click Account
2. Register with new email
3. Logout
4. Login with new credentials
5. Should see your name in Account button

---

## Customization Examples

### Change Product Name
In `js/app.js`, find:
```javascript
{ id: 1, name: 'Modern Sofa', ... }
```
Change to:
```javascript
{ id: 1, name: 'Your Sofa Name', ... }
```

### Update Price
In `js/app.js`:
```javascript
price: 899  // Change this number
```

### Change Site Colors
In `styles.css`, look for:
```css
.btn-primary { background-color: #0d6efd; }
```

### Add New Product
In `js/app.js` products array, add:
```javascript
{ 
    id: 13, 
    name: 'New Product', 
    category: 'decor', 
    price: 199, 
    rating: 4.5, 
    image: 'URL_HERE',
    description: 'Product description'
}
```

### Update Contact Info
Search for in all HTML files:
- Phone: `(555) 123-4567`
- Email: `info@designhaven.com`
- Address: `123 Design Street, NY`

Replace with your actual information.

---

## Troubleshooting

### Images Not Loading
- Replace placeholder URLs with real image paths
- Check image file permissions
- Use full URLs or relative paths

### Cart Not Working
- Check browser console for errors (F12)
- Clear browser localStorage
- Hard refresh page (Ctrl+Shift+R)

### Filters Not Working
- Ensure JavaScript is enabled
- Check console for errors
- Verify product data in app.js

### Mobile Layout Issues
- Clear browser cache
- Zoom out (Ctrl + minus)
- Try different browser
- Check Bootstrap CSS is loading

---

## Next Steps

### To Add Payment Processing
1. Sign up for Stripe or PayPal
2. Get API keys
3. Add payment form to checkout
4. Implement payment processing
5. Add order confirmation

### To Add Backend
1. Setup Node.js server
2. Create database
3. Build REST API
4. Connect frontend to backend
5. Implement user authentication

### To Deploy Live
1. Choose hosting (Netlify, Vercel, etc.)
2. Connect your domain
3. Setup SSL certificate
4. Deploy code
5. Monitor performance

### To Optimize Performance
1. Minify CSS/JavaScript
2. Optimize images
3. Setup CDN
4. Enable caching
5. Use lazy loading

---

## Common Tasks

### Add More Products
1. Open `js/app.js`
2. Find `const products = [`
3. Add new product object
4. Include: id, name, category, price, rating, image, description

### Create New Page
1. Create new HTML file in `pages/` folder
2. Copy navigation from existing page
3. Update active menu item
4. Add page content
5. Link from navigation

### Change Hero Image
In `index.html`, find:
```html
<img src="https://via.placeholder.com/1920x600?text=Modern+Living+Room"
```
Replace URL with your image.

### Update Team Members
In `pages/about.html`, update:
- Name
- Title
- Image URL
- Description

### Add Blog Post
In `pages/blog.html`:
1. Copy existing post structure
2. Update date, title, content
3. Update image URL
4. Add to "Recent Posts" sidebar

---

## Support Resources

### Documentation Files
- **README.md** - Complete documentation
- **ARCHITECTURE.md** - Technical architecture
- **QUICKSTART.md** - This file

### Online Resources
- Bootstrap 5: https://getbootstrap.com
- JavaScript: https://developer.mozilla.org
- HTML5: https://html.spec.whatwg.org

### Tools Needed
- Text Editor: VS Code, Sublime, Notepad++
- Browser: Chrome, Firefox, Safari, Edge
- Server: WAMP (included)
- Version Control: Git (optional)

---

## Feature Checklist

Current Features:
- [x] Responsive design
- [x] Product catalog
- [x] Shopping cart
- [x] User accounts
- [x] Search & filter
- [x] Wishlist
- [x] About page
- [x] Services page
- [x] Portfolio/gallery
- [x] Blog section
- [x] Contact form

Future Features:
- [ ] Payment processing
- [ ] Email notifications
- [ ] User reviews
- [ ] Inventory management
- [ ] Admin dashboard
- [ ] Analytics
- [ ] Live chat
- [ ] Virtual tours

---

## Performance Tips

### For Development
1. Use browser DevTools (F12)
2. Check Performance tab
3. Monitor Network requests
4. Clear cache when testing

### For Production
1. Minify CSS and JS
2. Optimize images (use TinyPNG)
3. Enable gzip compression
4. Use CDN for static files
5. Implement caching headers

---

## Security Notes

### Current (Development)
- Passwords stored in localStorage (not secure)
- No HTTPS required
- Simple authentication

### Production Requirements
- Use HTTPS/SSL
- Hash passwords with bcrypt
- Implement proper authentication (JWT)
- Validate all inputs
- Use secure payment gateway
- Regular security audits

---

## Getting Help

### Issues to Check
1. Browser console errors (F12)
2. Network tab for failed requests
3. LocalStorage for data issues
4. Cache cleared (Ctrl+Shift+Del)

### Questions?
Contact: info@designhaven.com
Phone: (555) 123-4567

---

**Happy Shopping! 🎨**

Design Haven - Transforming Spaces, Creating Dreams

Last Updated: January 23, 2026