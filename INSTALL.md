# Linux 部署指南 (Ubuntu & CentOS Stream 9)

本指南详细说明了如何在 Linux 环境下配置并运行 Hooray-IPTV 订阅服务。

## 1. 系统环境安装

### Ubuntu (22.04/24.04)
```bash
# 更新系统
sudo apt update && sudo apt upgrade -y

# 安装 PHP 8.1 及常用扩展
sudo apt install -y php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-intl php8.1-gd

# 安装 MariaDB
sudo apt install -y mariadb-server
sudo systemctl enable --now mariadb

# 安装 Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# 安装 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### CentOS Stream 9
```bash
# 更新系统
sudo dnf update -y

# 安装 PHP 8.1
sudo dnf module enable php:8.1 -y
sudo dnf install -y php php-fpm php-mysqlnd php-xml php-curl php-mbstring php-zip php-bcmath php-intl php-gd

# 安装 MariaDB
sudo dnf install -y mariadb-server
sudo systemctl enable --now mariadb

# 安装 Node.js 18
sudo dnf module install nodejs:18 -y

# 安装 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 2. 数据库配置
```bash
sudo mysql -u root -e "CREATE DATABASE iptv_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# 建议创建专用用户
# CREATE USER 'iptv_user'@'localhost' IDENTIFIED BY 'your_password';
# GRANT ALL PRIVILEGES ON iptv_platform.* TO 'iptv_user'@'localhost';
```

---

## 3. 后端配置 (Laravel)
```bash
cd backend

# 安装依赖
composer install --no-dev --optimize-autoloader

# 配置文件
cp .env.example .env
# 编辑 .env 修改数据库连接信息

# 初始化
php artisan key:generate
php artisan migrate --seed

# 权限设置
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 4. 前端配置 (React/Vite)
```bash
cd admin-panel

# 安装依赖
npm install

# 编译生产版本
# 确保 .env 中的 VITE_API_URL 指向正确的后端地址
npm run build
```

---

## 5. Nginx 配置示例
创建一个配置文件（如 `/etc/nginx/sites-available/iptv.conf`）：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/project/admin-panel/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        alias /path/to/project/backend/public;
        try_files $uri $uri/ @backend;
    }

    location @backend {
        rewrite /api/(.*)$ /api/index.php?/$1 last;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME /path/to/project/backend/public$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 6. 注意事项
- **SELinux**: CentOS 用户若遇到权限问题，请尝试 `sudo setenforce 0`。
- **防火墙**: 确保放行 80/443 端口。
- **PHP-FPM**: 确保 `php-fpm` 服务已启动并运行。
