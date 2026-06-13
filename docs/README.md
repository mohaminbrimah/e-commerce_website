# MAB Shop - E-Commerce Platform Documentation

## Overview

**MAB Shop** is a full-stack e-commerce shopping website built with PHP, MySQL, HTML5, CSS3, JavaScript, and Bootstrap 5. It includes a customer-facing storefront and a complete admin management panel.

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite (or Nginx equivalent)
- XAMPP, WAMP, or LAMP stack

## Installation

### 1. Clone/Copy Project

Place the project in your web server directory (e.g., `C:\xampp\htdocs\Shop` or `/var/www/html/Shop`).

### 2. Database Setup

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/sample_data.sql
```

### 3. Configuration

Edit `config/database.php` with your MySQL credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'mab_shop');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Edit `config/app.php` and set `APP_URL` to match your local URL:

```php
define('APP_URL', 'http://localhost/Shop');
```

### 4. Access the Application

- **Storefront:** http://localhost/Shop/
- **Admin Panel:** http://localhost/Shop/admin/

### Test Accounts

| Role     | Email               | Password   |
|----------|---------------------|------------|
| Admin    | admin@mabshop.com   | password   |
| Customer | customer@mabshop.com| password   |

> Sample data uses bcrypt hash for `password` (Laravel default test hash).

## Project Structure

```
Shop/
├── admin/              # Admin dashboard pages
├── api/                # REST/AJAX API endpoints
├── assets/
│   ├── css/            # Stylesheets (dark/light theme)
│   ├── js/             # JavaScript (cart, search, chat)
│   └── images/         # Product images and icons
├── auth/               # Social login OAuth handlers
├── classes/            # PHP model classes (Product, Cart, Order, Wishlist)
├── config/             # App and database configuration
├── database/           # SQL schema and sample data
├── docs/               # Documentation
├── includes/           # Core PHP (auth, security, session, helpers)
├── templates/          # Reusable HTML partials
│   └── admin/          # Admin layout templates
├── uploads/            # User-uploaded files
├── index.php           # Homepage
├── products.php        # Product listing with filters
├── product.php         # Product detail page
├── cart.php            # Shopping cart
├── checkout.php        # Checkout and payment
├── dashboard.php       # Customer dashboard
├── manifest.json       # PWA manifest
└── sw.js               # Service worker
```

## Module Documentation

### Authentication (`includes/auth.php`)

- User registration with email verification tokens
- Login with rate limiting and remember-me cookies
- Password reset via email token (1-hour expiry)
- Google/Facebook OAuth ready (configure in `config/app.php`)
- Guest cart merges into user cart on login

### Security (`includes/security.php`)

- **CSRF:** Token validation on all POST requests
- **XSS:** `e()` helper for output escaping
- **SQL Injection:** PDO prepared statements throughout
- Rate limiting on login attempts
- Secure session cookies (httponly, samesite)

### Product Management (`classes/Product.php`)

- Filter by category, brand, price, color, size, rating
- Sort by newest, price, popularity, ratings
- Natural language search: "Show me black sneakers under GH₵300"
- Autocomplete search suggestions
- Related products, frequently bought together
- Recently viewed tracking

### Shopping Cart (`classes/Cart.php`)

- Session-based cart for guests
- Database-persistent cart for logged-in users
- Save for later functionality
- Coupon code application
- Tax (12.5% VAT) and shipping calculation

### Orders (`classes/Order.php`)

- Order placement with stock reduction
- Status tracking: Processing → Packed → Shipped → Out for Delivery → Delivered
- Invoice generation (print to PDF)
- In-app notifications on status changes

### AI Assistant (`api/chat.php`)

- Natural language product search
- FAQ responses (shipping, returns, payments)
- Product recommendations
- Chat history stored in database

### Admin Panel (`admin/`)

| Page        | Features                                      |
|-------------|-----------------------------------------------|
| Dashboard   | Users, orders, revenue stats, monthly reports |
| Products    | CRUD, inventory management                    |
| Categories  | Hierarchical categories/subcategories         |
| Orders      | Status updates, tracking, refunds             |
| Users       | View, block/unblock customers                  |
| Reviews     | Approve/reject moderation                     |
| Coupons     | Create discount codes with expiry             |

## API Endpoints

| Endpoint           | Method | Description                    |
|--------------------|--------|--------------------------------|
| `/api/cart.php`    | POST   | Add/update/remove cart items   |
| `/api/wishlist.php`| POST   | Wishlist management            |
| `/api/search.php`  | GET    | Autocomplete suggestions       |
| `/api/product.php` | GET    | Quick view product data        |
| `/api/chat.php`    | POST   | AI shopping assistant          |
| `/api/compare.php` | POST   | Add to product comparison      |
| `/api/newsletter.php`| POST | Newsletter subscription        |
| `/api/review.php`  | POST   | Submit product review          |
| `/api/user.php`    | POST   | User settings (theme toggle)   |

## Payment Methods

- Mobile Money (MTN, Vodafone, AirtelTigo)
- Visa / Mastercard
- PayPal (integration ready)
- Bank Transfer

## PWA Support

The app includes `manifest.json` and `sw.js` for Progressive Web App installation. Add icon files at `assets/images/icon-192.png` and `icon-512.png`.

## Multi-Language

Language preference stored per user. Framework ready for i18n — add translation files to extend.

## Coupon Codes (Sample)

| Code      | Discount        | Min Order |
|-----------|-----------------|-----------|
| WELCOME10 | 10% off         | GH₵100    |
| SAVE50    | GH₵50 off       | GH₵500    |
| FLASH20   | 20% off         | GH₵200    |

## License

Built for educational and commercial use. Customize as needed.
