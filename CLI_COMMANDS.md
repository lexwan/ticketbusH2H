# Quick Reference: CLI Commands

## 🚀 Initial Setup Commands

```bash
# 1. Navigate to project
cd /Users/muhammadnurarifin/PROJECT2/Netzme

# 2. Install dependencies (requires PHP 8.2+)
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env file
# Update these values:
# DB_CONNECTION=mysql
# DB_DATABASE=laravel_mcp_boilerplate
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 5. Create database
mysql -u root -p
# In MySQL shell:
CREATE DATABASE laravel_mcp_boilerplate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# 6. Run migrations
php artisan migrate

# 7. Install Passport
php artisan passport:install
# Save the Client ID and Secret from output

# 8. Publish Spatie permissions
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

# 9. Seed roles and permissions
php artisan db:seed --class=RoleSeeder

# 10. Start development server
php artisan serve
# Server at: http://127.0.0.1:8000

# 11. Start MCP server (optional, in new terminal)
php artisan boost:serve
```

---

## 📦 Package Management

```bash
# Install all packages
composer install

# Update packages
composer update

# Install specific package
composer require package/name

# Remove package
composer remove package/name

# Dump autoloader
composer dump-autoload
```

---

## 🗄️ Database Commands

```bash
# Run all migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Fresh migration (drop all tables and re-migrate)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=RoleSeeder

# Run all seeders
php artisan db:seed
```

---

## 🔑 Laravel Passport Commands

```bash
# Install Passport (generates encryption keys)
php artisan passport:install

# Generate new keys
php artisan passport:keys

# Create a client
php artisan passport:client

# Purge revoked tokens
php artisan passport:purge
```

---

## 🧹 Cache & Optimization

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize for production
php artisan optimize
```

---

## 🧪 Testing & Development

```bash
# Run PHP interactive shell
php artisan tinker

# Run tests
php artisan test

# Create admin user in tinker:
php artisan tinker
$user = \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
$user->assignRole('admin');
exit
```

---

## 🛠️ Artisan Make Commands

```bash
# Create controller
php artisan make:controller Api/NameController

# Create model with migration
php artisan make:model Name -m

# Create migration
php artisan make:migration create_table_name

# Create seeder
php artisan make:seeder NameSeeder

# Create request
php artisan make:request StoreNameRequest

# Create resource
php artisan make:resource NameResource

# Create middleware
php artisan make:middleware NameMiddleware

# Create service (manual - create in app/Services/)
mkdir -p app/Services
touch app/Services/NameService.php
```

---

## 📚 API Documentation

```bash
# Access Scramble documentation
# Start server first: php artisan serve
# Then visit: http://127.0.0.1:8000/docs/api
```

---

## 🔧 MCP Server Commands

```bash
# Start MCP server
php artisan boost:serve

# List available MCP tools
php artisan boost:list

# The SystemStats tool will be available to AI agents
```

---

## 🐛 Troubleshooting Commands

```bash
# Check Laravel version
php artisan --version

# Check PHP version
php --version

# Check composer version
composer --version

# Test database connection
php artisan tinker
DB::connection()->getPdo();
exit

# List all routes
php artisan route:list

# Show application information
php artisan about
---

## 📝 Quick API Testing with curl

```bash
# Register user
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# List products (replace TOKEN with actual token)
curl -X GET http://127.0.0.1:8000/api/products \
  -H "Authorization: Bearer TOKEN"

# Create product (requires admin)
curl -X POST http://127.0.0.1:8000/api/products \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Product","sku":"TEST-001","price":99.99,"stock":100}'
```

---

## 🎯 Production Deployment

```bash
# Set environment to production
# In .env: APP_ENV=production, APP_DEBUG=false

# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Run migrations on production
php artisan migrate --force
```
