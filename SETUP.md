# IPTV订阅管理平台 - 安装指南

## 前置要求

### 1. 安装PHP 8.1和Composer（如果尚未完成）
```bash
brew install php@8.1 composer
```

### 2. 安装MariaDB 10.x
```bash
brew install mariadb@10.11
brew services start mariadb@10.11
```

### 3. 安装Node.js 18+
```bash
brew install node@18
```

## 后端设置 (Laravel 9)

### 步骤1: 安装Laravel项目（如果composer.json已存在）
```bash
cd backend
composer install
```

**或者重新创建Laravel项目（如果需要）:**
```bash
cd /Users/kim/Desktop/dev_project/Hooray-IPTV-Subscription-Service
rm -rf backend
composer create-project laravel/laravel backend "9.*"
cd backend
```

### 步骤2: 安装额外依赖包
```bash
composer require laravel/sanctum:^3.2
composer require spatie/laravel-permission:^5.10
composer require spatie/laravel-activitylog:^4.7
composer require doctrine/dbal:^3.6
```

### 步骤3: 配置环境变量
```bash
cp .env.example .env
php artisan key:generate
```

编辑 `.env` 文件，配置数据库：
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iptv_platform
DB_USERNAME=root
DB_PASSWORD=

# 调整PHP配置限制
PHP_MAX_EXECUTION_TIME=180
PHP_MEMORY_LIMIT=512M
PHP_UPLOAD_MAX_FILESIZE=50M
```

### 步骤4: 创建数据库
```bash
mysql -u root -e "CREATE DATABASE iptv_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 步骤5: 发布vendor配置
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 步骤6: 运行迁移和Seeder
```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
```

### 步骤7: 启动开发服务器
```bash
php artisan serve
# 访问: http://localhost:8000
```

## 前端设置 (React 18)

### 步骤1: 安装依赖
```bash
cd admin-panel
npm install
```

### 步骤2: 配置环境变量
```bash
cp .env.example .env
```

编辑 `.env` 文件：
```env
VITE_API_URL=http://localhost:8000/api
```

### 步骤3: 初始化shadcn/ui（如果需要）
```bash
npx shadcn-ui@latest init
# 选择: Default style, Zinc color, CSS variables
```

### 步骤4: 启动开发服务器
```bash
npm run dev
# 访问: http://localhost:5173
```

## 默认登录凭据

- **用户名**: admin
- **密码**: admin123

## 开发命令

### 后端
```bash
# 创建新的迁移
php artisan make:migration create_xxx_table

# 创建新的模型
php artisan make:model ModelName -m

# 创建新的控制器
php artisan make:controller API/Admin/ControllerName

# 清除缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### 前端
```bash
# 添加shadcn/ui组件
npx shadcn-ui@latest add button
npx shadcn-ui@latest add input
npx shadcn-ui@latest add table

# 构建生产版本
npm run build
```

## 项目结构

```
Hooray-IPTV-Subscription-Service/
├── backend/                  # Laravel 9 后端
│   ├── app/
│   │   ├── Http/Controllers/API/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
├── admin-panel/              # React 18 管理面板
│   ├── src/
│   │   ├── api/
│   │   ├── components/
│   │   ├── pages/
│   │   └── i18n/
│   └── package.json
└── README.md
```

## 常见问题

### Q: Composer安装很慢？
A: 使用国内镜像：
```bash
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

### Q: npm安装很慢？
A: 使用淘宝镜像：
```bash
npm config set registry https://registry.npmmirror.com
```

### Q: MariaDB连接失败？
A: 检查服务是否运行：
```bash
brew services list
brew services start mariadb@10.11
```
