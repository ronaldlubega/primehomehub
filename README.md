# Design Haven - Interior Design & Furniture E-Commerce Website

## Project Overview

Design Haven is a modern, fully-functional interior design and furniture e-commerce website built with HTML5, CSS3, Bootstrap 5, and vanilla JavaScript. It provides a complete solution for showcasing interior design services and selling premium furniture online.

## Website Structure

### Pages Included

1. **index.html** - Homepage
   - Hero carousel with multiple design showcases
   - Featured collections by category
   - Services preview section
   - Latest blog posts
   - Call-to-action section

2. **pages/shop.html** - Product Catalog
   - Advanced filtering (category, price, rating)
   - Search functionality
   - Product sorting options
   - Pagination system
   - Wishlist integration
   - Product detail modal

3. **pages/cart.html** - Shopping Cart
   - View cart items
   - Update quantities
   - Remove items
   - Order summary with tax calculation
   - Shipping cost calculation

4. **pages/about.html** - About Us
   - Company story
   - Team members showcase
   - Company values

5. **pages/services.html** - Services
   - Residential design services
   - Commercial design services
   - Consultation packages
   - Pricing tiers

6. **pages/portfolio.html** - Project Gallery
   - Before & after project showcases
   - Filter by project type
   - Project detail modal

7. **pages/contact.html** - Contact & Consultation Booking
   - Contact form
   - Service inquiry
   - Location map
   - Business hours

8. **pages/blog.html** - Design Tips & Trends
   - Blog post listings
   - Categories
   - Recent posts
   - Newsletter signup
   - Search functionality

## Features

### User Account System
- User registration with password storage
- User login functionality
- Current user display in navigation
- Logout functionality
- Local storage-based authentication

### Shopping Cart
- Add/remove products
- Update quantities
- Cart persistence using localStorage
- Real-time cart badge updates
- Tax and shipping calculations

### Wishlist
- Add/remove items to wishlist
- Mark favorite products
- Wishlist persistence

### Product Management
- 12 sample products across 4 categories
- Product ratings and reviews
- Product search
- Advanced filtering by:
  - Category (Living Room, Bedroom, Office, Decor)
  - Price range
  - Rating

### Design Features
- Responsive mobile-first design
- Bootstrap 5 grid system
- Smooth animations and transitions
- Hover effects on cards
- Modal windows for product details
- Image optimization with placeholders

## Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Custom styling with animations
- **Bootstrap 5** - Responsive framework
- **Bootstrap Icons** - Icon library
- **JavaScript (ES6+)** - Vanilla JavaScript (no frameworks)

### Data Storage
- **LocalStorage** - Client-side data persistence
  - User accounts
  - Shopping cart
  - Wishlist items
  - Preferences

### Features Without Backend
- Client-side user authentication
- LocalStorage-based cart
- JSON product data structure

## File Structure

```
furn/
├── index.html                 # Homepage
├── styles.css                # Global styles
├── script.js                 # Legacy script (can be removed)
├── css/
│   └── styles.css           # Additional styles (optional)
├── js/
│   ├── app.js               # Main application logic
│   └── shop.js              # Shop page functionality
├── pages/
│   ├── shop.html            # Product catalog
│   ├── cart.html            # Shopping cart
│   ├── about.html           # About page
│   ├── services.html        # Services page
│   ├── portfolio.html       # Portfolio/Gallery
│   ├── contact.html         # Contact page
│   └── blog.html            # Blog page
└── data/
    └── products.json        # Product database
```

## Product Categories

1. **Living Room**
   - Modern Sofa ($899)
   - Coffee Table ($299)
   - Wall Art ($199)

2. **Bedroom**
   - King Bed Frame ($1,299)
   - Nightstand ($299)
   - Bedside Lamp ($89)

3. **Office**
   - Office Desk ($599)
   - Office Chair ($399)
   - Desk Organizer ($49)

4. **Decor**
   - Plant Pot ($59)
   - Mirror ($149)
   - Throw Pillow ($39)

## How to Use

### Accessing Locally
1. Open `http://localhost/furn/index.html` in your browser
2. Navigate through pages using the navigation menu
3. Test features on different pages

### Shopping Workflow
1. Browse products in **Shop** page
2. Use filters to find items
3. Click "View Details" for more information
4. Click "Add to Cart" to purchase
5. View cart at **Cart** page
6. Proceed to checkout (integration point for payment gateway)

### User Account
1. Click "Account" in navigation
2. Register a new account or login
3. Logged-in users see their name in the account button
4. Click account button while logged in to logout

### Filter Products
1. Use category checkboxes
2. Adjust price range slider
3. Select minimum rating
4. Use search bar for keywords
5. Click "Apply Filters" or filter resets automatically

## Integration Points

### Payment Gateway Integration
The checkout process in `cart.html` has a placeholder for payment integration:
- **Stripe**: Industry standard, supports multiple payment methods
- **PayPal**: Popular, familiar to users
- **Square**: Good for inventory management

Example implementation:
```javascript
document.getElementById('checkout-btn').addEventListener('click', function() {
    // Integrate Stripe/PayPal SDK here
    // Process payment
    // Confirm order
});
```

### Email Notifications
Contact form and order confirmations need backend setup:
- **SendGrid**: Email delivery service
- **Mailchimp**: Email marketing
- **Firebase Functions**: Serverless backend

### Backend (Future Enhancement)
When moving to production, implement:
- Node.js/Express server
- MongoDB or PostgreSQL database
- User authentication with JWT
- Payment processing
- Email notifications
- Order management system

## Customization Guide

### Change Site Name
1. Find "Design Haven" throughout HTML files
2. Replace with your company name
3. Update favicon and logo

### Update Products
1. Edit `data/products.json` or modify the products array in `js/app.js`
2. Add new products with:
   - id, name, category, price, rating, image URL, description

### Change Colors
1. Open `styles.css`
2. Modify primary color (currently Bootstrap primary #0d6efd)
3. Update in CSS variables or class definitions

### Update Contact Information
In all pages, find and update:
- Phone: (555) 123-4567
- Email: info@designhaven.com
- Address: 123 Design Street, NY

### Add Real Images
1. Replace placeholder URLs (`https://via.placeholder.com/...`)
2. Upload images to hosting service
3. Update image URLs in HTML and JavaScript

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Android)

## Performance Optimization

### Already Implemented
- Responsive images
- Lazy loading ready
- Minimal CSS/JS
- Bootstrap CDN for caching

### Recommended Improvements
1. Implement image lazy loading
2. Minify CSS and JavaScript
3. Enable gzip compression
4. Use image CDN (Cloudinary, Imgix)
5. Implement service worker for offline access

## SEO Optimization

### Implemented
- Semantic HTML5 markup
- Meta tags (title, description, keywords)
- Proper heading hierarchy
- Image alt attributes

### Recommendations
1. Add structured data (Schema.org)
2. Create XML sitemap
3. Setup robots.txt
4. Implement breadcrumb navigation
5. Add Open Graph tags for social sharing

## Deployment Options

### Static Hosting (Recommended for Current Setup)
1. **GitHub Pages** - Free, automatic
2. **Netlify** - Free tier, continuous deployment
3. **Vercel** - Free, optimized for web apps
4. **AWS S3 + CloudFront** - Scalable

### With Backend (Future)
1. **Heroku** - Easy deployment for Node.js
2. **AWS EC2** - Full control
3. **DigitalOcean** - Simple VPS
4. **Google Cloud** - Enterprise-grade

## Testing Checklist

- [ ] All pages load correctly
- [ ] Navigation works on all pages
- [ ] Responsive design on mobile/tablet/desktop
- [ ] Add to cart functionality works
- [ ] Cart updates correctly
- [ ] User registration works
- [ ] User login/logout works
- [ ] Search functionality works
- [ ] Filters work correctly
- [ ] Wishlist adds/removes items
- [ ] Forms submit without errors
- [ ] Modal windows open/close correctly
- [ ] All external links work
- [ ] Images load properly
- [ ] No console errors

## Future Enhancements

1. **Backend Development**
   - Database for products and users
   - Real payment processing
   - Order management

2. **Advanced Features**
   - Product reviews and ratings
   - Customer wishlists
   - Order history
   - Inventory management
   - Admin dashboard

3. **Marketing**
   - Email marketing integration
   - Analytics (Google Analytics)
   - Social media integration
   - Promotion codes/coupons

4. **UX Improvements**
   - Live chat support
   - Virtual tour/AR preview
   - Product comparison
   - Style quiz
   - Personalized recommendations

## License

This project is open source and available for commercial use.

## Support

For questions or issues, contact:
- Email: info@designhaven.com
- Phone: (555) 123-4567

---

**Last Updated**: January 23, 2026
**Version**: 1.0.0