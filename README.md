# 🛍️ Expde Shop Admin Panel

[![PHP Version](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Proprietary-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen.svg)]()

> A modern, professional, and feature-rich admin panel for e-commerce management with beautiful UI/UX design and mobile-responsive interface.

## ✨ Features

### 🔐 **Authentication & Security**
- 🔒 Secure login system with role-based access control
- 👥 Three user roles: **Super Admin**, **Admin**, **Staff**
- 🔑 Password reset functionality with email verification
- 🍪 Remember me functionality (30-day cookies)
- 🛡️ Session management and CSRF protection
- 🔐 Secure password hashing with bcrypt

### 📊 **Modern Dashboard**
- 📈 Real-time analytics and statistics
- 📋 Today's pending/completed orders tracking
- 💰 Earnings and revenue tracking
- 📦 Product count by category
- ⚠️ Low stock alerts and notifications
- 🏆 Top selling products with charts
- 📊 Recent orders table with status updates
- 📱 Mobile-responsive design

### 🛍️ **Advanced Product Management**
- 🏷️ **Smart Category System**: Three main types (Gadget Items, Clothes Items, Food Items)
- 📁 **Unlimited Subcategories**: Hierarchical category structure
- ⚙️ **Dynamic Attributes**: Custom attributes for each product (Color, Size, Storage, etc.)
- ➕ Product CRUD operations with validation
- 🖼️ Image management and optimization
- 📊 Stock tracking and inventory management
- 🏷️ SKU and barcode support
- 🔍 Advanced search and filtering

### 📦 **Order Management System**
- 📋 View all orders with advanced filtering
- 🔄 Order status updates (Pending, Processing, Shipped, Delivered, Cancelled, Refunded)
- 💳 Payment status tracking
- 👤 Order details with customer information
- 🖨️ Print functionality for invoices
- 📧 Email notifications for order updates

### 👥 **Customer Management**
- 👤 Customer list with search functionality
- 📊 Customer order history and analytics
- 🏠 Customer profile management
- 📈 Order statistics per customer
- 💬 Customer communication tools

### 💰 **Payments & Earnings**
- 💳 Payment history tracking
- 📊 Earnings reports by day/week/month
- 📈 Payment method breakdown
- 🎯 Success rate analytics
- 💰 Revenue forecasting

### ⚙️ **System Settings**
- 🌐 Website configuration (name, logo, favicon, contact info)
- 👤 Admin profile management
- 🔧 Feature toggles (registration, guest checkout, maintenance mode)
- 📊 System information and health monitoring
- 🔒 Security settings and access control

## 🚀 Quick Start

### 📋 **System Requirements**
- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher  
- **Web Server**: Apache/Nginx
- **Extensions**: PDO, PDO_MySQL, MBString, OpenSSL, Tokenizer, XML, CType, JSON, BCMath, Fileinfo

### 🎯 **Easy GUI Installer (Recommended)**

> **No command line required!** Our beautiful web-based installer makes setup a breeze.

1. **📁 Upload Files**
   ```bash
   # Upload all files to your web server root directory
   ```

2. **🔧 Set Permissions**
   ```bash
   chmod 755 storage/
   chmod 755 bootstrap/cache/
   ```

3. **🌐 Access Installer**
   ```
   Navigate to: https://your-domain.com/install/
   ```

4. **✨ Follow the 5-Step Wizard**
   - **Step 1**: System requirements check ✅
   - **Step 2**: Database configuration 🗄️
   - **Step 3**: Admin account creation 👤
   - **Step 4**: Installation process ⚙️
   - **Step 5**: Success confirmation 🎉

### 🔧 **Manual Installation (Advanced Users)**

<details>
<summary>Click to expand manual installation steps</summary>

1. **Database Setup**
   ```sql
   CREATE DATABASE expde_shop;
   CREATE USER 'expde_user'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON expde_shop.* TO 'expde_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Environment Configuration**
   ```bash
   cp env.example .env
   # Edit .env file with your database credentials
   ```

3. **Install Dependencies**
   ```bash
   composer install --optimize-autoloader
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Run Database Migrations**
   ```bash
   php artisan migrate --seed
   ```

6. **Set Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

</details>

## 📁 **Pre-configured Categories**

The system comes with a smart category structure:

### 📱 **Gadget Items**
- 📱 Mobile Phones
- 💻 Laptops & Computers  
- ⌚ Smart Watches
- 🎧 Accessories

### 👕 **Clothes Items**
- 👕 T-shirts
- 👖 Pants & Jeans
- 👔 Polo Shirts
- 👘 Panjabi & Traditional

### 🍯 **Food Items**
- 🍚 Rice & Grains
- 🥜 Lentils & Pulses
- 🍯 Honey & Natural Products
- 🫒 Oil & Cooking Essentials
- 🗓️ Dates & Dried Fruits

## 👥 **User Roles & Permissions**

### 🔑 **Super Admin**
- ✅ Full system access
- 👥 User management
- ⚙️ All administrative functions
- 🔧 System configuration

### 👨‍💼 **Admin**
- 📦 Product management
- 📋 Order management
- 👤 Customer management
- ⚙️ Settings access
- 📊 Reports & analytics

### 👨‍💻 **Staff**
- 📋 Limited order access
- 👤 Customer support
- 📦 Basic product management
- 📊 View-only reports

## 🏗️ **Project Structure**

```
📁 website/
├── 📁 app/
│   ├── 📁 Http/Controllers/
│   │   ├── 📁 Auth/          # 🔐 Authentication controllers
│   │   └── 📁 Admin/         # 🛍️ Admin panel controllers
│   └── 📁 Models/            # 🗄️ Eloquent models
├── 📁 database/
│   └── 📁 migrations/        # 🗃️ Database migrations
├── 📁 public/
│   ├── 📁 admin/             # 🎛️ Admin panel files
│   ├── 📁 install/           # ⚙️ GUI installer
│   └── 📁 uploads/           # 📁 File uploads
├── 📁 resources/
│   └── 📁 views/             # 🎨 Blade templates
└── 📁 routes/
    └── 📄 web.php            # 🛣️ Web routes
```

## 🛡️ **Security Features**

- 🛡️ **CSRF Protection**: Cross-site request forgery prevention
- 🔒 **SQL Injection Prevention**: Parameterized queries
- 🚫 **XSS Protection**: Input sanitization and output escaping
- 🔐 **Secure Password Hashing**: bcrypt with salt
- 👥 **Role-based Access Control**: Granular permissions
- 🍪 **Secure Session Management**: Encrypted sessions
- 🔑 **Remember Me Security**: Secure cookie handling

## 🌐 **Browser Support**

| Browser | Version | Status |
|---------|---------|--------|
| 🌐 Chrome | Latest | ✅ Fully Supported |
| 🦊 Firefox | Latest | ✅ Fully Supported |
| 🦁 Safari | Latest | ✅ Fully Supported |
| 🔷 Edge | Latest | ✅ Fully Supported |
| 📱 Mobile | All | ✅ Responsive Design |

## 📱 **Mobile Responsive**

- 📱 **Mobile-First Design**: Optimized for mobile devices
- 🖥️ **Tablet Support**: Perfect tablet experience
- 💻 **Desktop Optimized**: Full desktop functionality
- 🎯 **Touch-Friendly**: Large buttons and touch targets
- 📐 **Flexible Layout**: Adapts to all screen sizes

## 🎨 **UI/UX Features**

- 🎨 **Modern Design**: Clean and professional interface
- 🌈 **Beautiful Colors**: Green theme with professional gradients
- ✨ **Smooth Animations**: CSS transitions and hover effects
- 📊 **Interactive Elements**: Dynamic charts and graphs
- 🔍 **Advanced Search**: Real-time search functionality
- 📋 **Data Tables**: Sortable and filterable tables

## 🚀 **Performance**

- ⚡ **Fast Loading**: Optimized code and assets
- 🗄️ **Database Optimization**: Efficient queries and indexing
- 📦 **Asset Optimization**: Minified CSS and JavaScript
- 🖼️ **Image Optimization**: Compressed and responsive images
- 💾 **Caching**: Smart caching strategies

## 📞 **Support & Documentation**

- 📚 **Comprehensive Documentation**: Detailed setup guides
- 🎥 **Video Tutorials**: Step-by-step installation videos
- 💬 **Community Support**: Active community forum
- 🐛 **Bug Reports**: GitHub issues for bug tracking
- 💡 **Feature Requests**: Submit new feature ideas

## 📄 **License**

This project is **proprietary software**. All rights reserved.

---

## ⚠️ **Important Security Note**

> **🔒 After installation, delete the `install` folder for security reasons.**

---

<div align="center">

### 🌟 **Star this repository if you find it helpful!**

[![GitHub stars](https://img.shields.io/github/stars/username/expde-shop-admin.svg?style=social&label=Star)]([https://github.com/username/expde-shop-admin](https://github.com/expde/AdminPanel/stargazers))
[![GitHub forks](https://img.shields.io/github/forks/expde/AdminPanel.svg?style=social&label=Fork)](https://github.com/expde/AdminPanel/fork)

**Made with ❤️ for the e-commerce community**

</div>


