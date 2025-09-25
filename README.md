# Expde Shop Admin Panel

A modern, feature-rich admin panel for e-commerce management built with Laravel 10.

## Features

### 🔐 Authentication & Security
- Secure login system with role-based access control
- Three user roles: Super Admin, Admin, Staff
- Password reset functionality
- Session management

### 📊 Dashboard
- Real-time analytics and statistics
- Today's pending/completed orders
- Earnings tracking
- Product count by category
- Low stock alerts
- Top selling products
- Recent orders table
- Interactive charts and graphs

### 🛍️ Product Management
- **Category System**: Three main types (Gadget Items, Clothes Items, Food Items)
- **Subcategories**: Unlimited subcategories for each main type
- **Dynamic Attributes**: Custom attributes for each product (Color, Size, Storage, etc.)
- Product CRUD operations
- Image management
- Stock tracking
- SKU and barcode support

### 📦 Order Management
- View all orders with filtering
- Order status updates (Pending, Processing, Shipped, Delivered, Cancelled, Refunded)
- Payment status tracking
- Order details with customer information
- Print functionality

### 👥 Customer Management
- Customer list with search functionality
- Customer order history
- Customer profile management
- Order statistics per customer

### 💰 Payments & Earnings
- Payment history tracking
- Earnings reports by day/week/month
- Payment method breakdown
- Success rate analytics

### ⚙️ Settings
- Website configuration (name, logo, favicon, contact info)
- Admin profile management
- Feature toggles (registration, guest checkout, maintenance mode)
- System information

## Installation

### Easy GUI Installer (Recommended)

1. **Upload Files**: Upload all files to your web server
2. **Set Permissions**: Ensure the following directories are writable:
   - `storage/`
   - `bootstrap/cache/`
3. **Access Installer**: Navigate to `your-domain.com/installer/`
4. **Follow Wizard**: Complete the 5-step installation process:
   - Step 1: System requirements check
   - Step 2: Database configuration
   - Step 3: Admin account creation
   - Step 4: Installation
   - Step 5: Success confirmation

### Manual Installation (Advanced)

1. **Requirements**:
   - PHP 8.1 or higher
   - MySQL 5.7 or higher
   - Web server (Apache/Nginx)
   - Required PHP extensions: PDO, PDO_MySQL, MBString, OpenSSL, Tokenizer, XML, CType, JSON, BCMath, Fileinfo

2. **Database Setup**:
   ```sql
   CREATE DATABASE expde_shop;
   ```

3. **Environment Configuration**:
   - Copy `env.example` to `.env`
   - Update database credentials
   - Generate application key

4. **Install Dependencies**:
   ```bash
   composer install
   ```

5. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

6. **Seed Database**:
   ```bash
   php artisan db:seed
   ```

## Default Categories

The system comes with pre-configured categories:

### Gadget Items
- Mobile
- Laptop
- Smart Watch
- Accessories

### Clothes Items
- T-shirt
- Pant
- Polo Shirt
- Panjabi

### Food Items
- Rice
- Lentils
- Honey
- Oil
- Dates

## User Roles

### Super Admin
- Full system access
- User management
- All administrative functions

### Admin
- Product management
- Order management
- Customer management
- Settings access

### Staff
- Limited access to orders and customers
- Basic product management

## File Structure

```
website/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/          # Authentication controllers
│   │   └── Admin/         # Admin panel controllers
│   └── Models/            # Eloquent models
├── database/
│   └── migrations/        # Database migrations
├── public/
│   ├── installer/         # GUI installer
│   └── uploads/           # File uploads
├── resources/
│   └── views/             # Blade templates
└── routes/
    └── web.php            # Web routes
```

## Security Features

- CSRF protection
- SQL injection prevention
- XSS protection
- Secure password hashing
- Role-based access control
- Session security

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile responsive design

## Support

For support and questions, please contact the development team.

## License

This project is proprietary software. All rights reserved.

---

**Note**: After installation, delete the `installer` folder for security reasons.
