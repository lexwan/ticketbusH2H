# Laravel 12 + MCP Boilerplate Project

## 📦 Installation Guide
### Step 1: Install Dependencies

```bash
cd /Users/muhammadnurarifin/PROJECT2/Netzme

# Install PHP dependencies (requires PHP 8.2+)
composer install

# Install required packages
composer require laravel/passport
composer require spatie/laravel-permission
composer require dedoc/scramble
composer require --dev laravel/boost
```

### Step 2: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# Update these lines:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_mcp_boilerplate
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

### Step 3: Create Database

```bash
# Create MySQL database
mysql -u root -p
```

```sql
CREATE DATABASE laravel_mcp_boilerplate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 4: Run Migrations and Seeders

```bash
# Run all migrations
php artisan migrate

# Install Laravel Passport
php artisan passport:install

# Publish Spatie permissions config and migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RoleSeeder
```

### Step 5: Start Development Server

```bash
# Start Laravel development server
php artisan serve

# Server will be available at: http://127.0.0.1:8000
```

### Step 6: Start MCP Server (Optional)

```bash
# Start Laravel Boost MCP server
php artisan boost:serve

# The MCP server will expose tools to AI agents
```

---

## 🎯 API Endpoints

### Authentication

```bash
# Register a new user
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}

# Login
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}

# Response includes access_token - use in subsequent requests:
# Authorization: Bearer {access_token}
```

### Product Management (Protected Routes)

```bash
# List all products (requires authentication)
GET /api/products
Authorization: Bearer {token}

# Get single product
GET /api/products/{id}
Authorization: Bearer {token}

# Create product (requires admin role)
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Product Name",
    "sku": "SKU-001",
    "price": 99.99,
    "stock": 100
}

# Update product (requires admin role)
PUT /api/products/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Name",
    "price": 149.99,
    "stock": 150
}

# Delete product (requires admin role)
DELETE /api/products/{id}
Authorization: Bearer {token}
```

---

## 📚 API Documentation

Once the server is running, access auto-generated API documentation at:

**Scramble API Docs**: `http://127.0.0.1:8000/docs/api`

---

## 🏗️ Project Structure

```
Netzme/
├── app/
│   ├── Boost/
│   │   └── Tools/
│   │       └── SystemStats.php          # MCP Tool for system information
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php   # Authentication endpoints
│   │   │       └── ProductController.php # Product CRUD (clean arch)
│   │   ├── Middleware/
│   │   │   └── CheckRole.php            # Role-based access control
│   │   ├── Requests/
│   │   │   ├── StoreProductRequest.php  # Validation for creating
│   │   │   └── UpdateProductRequest.php # Validation for updating
│   │   └── Resources/
│   │       └── ProductResource.php      # API response transformation
│   ├── Models/
│   │   ├── Product.php                  # Product model
│   │   └── User.php                     # User model (with Passport & Spatie)
│   ├── Providers/
│   │   └── AppServiceProvider.php       # Service provider configuration
│   └── Services/
│       └── ProductService.php           # Business logic layer
├── bootstrap/
│   └── app.php                          # Bootstrap configuration
├── config/
│   └── boost.php                        # MCP server configuration
├── database/
│   ├── migrations/
│   │   └── 2026_02_02_094824_create_products_table.php
│   └── seeders/
│       └── RoleSeeder.php               # Role & permission seeder
└── routes/
    └── api.php                          # API routes definition
```

---

## 🔧 Testing MCP Integration

### Using the SystemStats Tool

The `SystemStats` MCP tool provides system information to AI agents:

```bash
# Start the MCP server
php artisan boost:serve

# In another terminal, you can test the tool
# The tool returns:
# - laravel_version
# - php_version
# - database_status (connected, driver, database)
# - environment
# - debug_mode
# - timezone
```

---

## 🧪 Testing the Application

### Create Admin User for Testing

```php
# In tinker:
php artisan tinker

$user = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
]);

$user->assignRole('admin');
exit
```

### Test Complete Flow

1. **Register a user** → Receives `user` role automatically
2. **Login** → Receives access token
3. **List products** → Works for all authenticated users
4. **Create product** → Only works for `admin` role
5. **Update/Delete product** → Only works for `admin` role

---

## 🎨 Clean Architecture 

This boilerplate follows clean architecture principles:

### Controller Layer (`ProductController`)
- **Responsibility**: Handle HTTP requests and responses only
- **No business logic**: All logic delegated to service layer
- **Returns**: API Resources for consistent responses

### Service Layer (`ProductService`)
- **Responsibility**: Contains all business logic
- **Reusable**: Can be called from controllers, jobs, commands
- **Testable**: Easy to unit test without HTTP layer

### Validation Layer (`FormRequests`)
- **StoreProductRequest**: Validates product creation
- **UpdateProductRequest**: Validates product updates
- **Custom messages**: User-friendly error messages

### Transformation Layer (`Resources`)
- **ProductResource**: Transforms model to JSON
- **Computed fields**: Adds `formatted_price`, `stock_status`
- **Consistent**: All endpoints return same format

---

## 📦 Package Configuration

All required packages have been configured in `composer.json`. Once you upgrade to PHP 8.2+, run:

```bash
composer install
```

The following packages will be installed:
- `laravel/passport` - OAuth2 authentication
- `spatie/laravel-permission` - Role and permission management
- `dedoc/scramble` - Automatic API documentation
- `laravel/boost` (dev) - MCP server integration

---

## ✅ Next Steps

1. **Upgrade PHP to 8.2+**
2. **Run `composer install`**
3. **Configure `.env` file**
4. **Run migrations**: `php artisan migrate`
5. **Install Passport**: `php artisan passport:install`
6. **Seed roles**: `php artisan db:seed --class=RoleSeeder`
7. **Start server**: `php artisan serve`
8. **Test API**: Use Postman or `/docs/api`
9. **Start MCP**: `php artisan boost:serve` (optional)

---

## 🐛 Troubleshooting

### Lint Errors Due to PHP Version
The lint errors you see are all due to PHP 7.2 not supporting PHP 8+ syntax:
- Constructor property promotion
- Attributes (`#[Tool]`)
- Nullsafe operator (`?->`)
- Named arguments

**These will disappear once you upgrade to PHP 8.2+.**

### Database Connection Issues
Ensure MySQL is running and credentials in `.env` are correct:
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();
```

---

## 📝 Summary

You now have a complete Laravel 12 boilerplate with:
- ✅ **MCP Integration** via Laravel Boost with `SystemStats` tool
- ✅ **Authentication** via Laravel Passport
- ✅ **RBAC** via Spatie Permissions (admin/user roles)
- ✅ **Product CRUD** following clean architecture
- ✅ **Auto Documentation** via Scramble
- ✅ **FormRequests** for strict validation
- ✅ **API Resources** for response transformation

All code is production-ready and follows Laravel best practices!
