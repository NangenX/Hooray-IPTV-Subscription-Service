#!/bin/bash

###############################################################################
# Hooray-IPTV 订阅服务 - CentOS Stream 9 自动化安装脚本
# 功能：自动安装和配置所有依赖环境，并部署应用
###############################################################################

set -e  # 遇到错误立即退出

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查是否以 root 运行
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "此脚本需要 root 权限运行"
        echo "请使用: sudo $0"
        exit 1
    fi
}

# 获取项目路径
PROJECT_DIR=$(cd "$(dirname "$0")" && pwd)
log_info "项目路径: $PROJECT_DIR"

# 用户输入配置
get_config() {
    log_info "==== 配置信息收集 ===="
    
    # 数据库配置
    read -p "数据库名称 [iptv_platform]: " DB_NAME
    DB_NAME=${DB_NAME:-iptv_platform}
    
    read -p "数据库用户名 [iptv_user]: " DB_USER
    DB_USER=${DB_USER:-iptv_user}
    
    read -sp "数据库密码 [随机生成]: " DB_PASS
    echo
    if [ -z "$DB_PASS" ]; then
        DB_PASS=$(openssl rand -base64 16)
        log_info "自动生成数据库密码: $DB_PASS"
    fi
    
    # 域名配置
    read -p "域名或服务器 IP [localhost]: " DOMAIN
    DOMAIN=${DOMAIN:-localhost}
    
    # Web 服务器配置
    read -p "使用的 Web 服务器端口 [80]: " WEB_PORT
    WEB_PORT=${WEB_PORT:-80}
    
    log_info "配置完成！"
}

# 1. 更新系统
update_system() {
    log_info "==== 更新系统 ===="
    dnf update -y
    dnf install -y epel-release
}

# 2. 安装 PHP 8.1 及扩展
install_php() {
    log_info "==== 安装 PHP 8.1 ===="
    
    # 启用 PHP 8.1 模块
    dnf module reset php -y
    dnf module enable php:8.1 -y
    
    # 安装 PHP 及扩展
    dnf install -y php php-fpm php-mysqlnd php-xml php-curl \
        php-mbstring php-zip php-bcmath php-intl php-gd \
        php-json php-opcache php-pdo
    
    # 启动并启用 PHP-FPM
    systemctl enable --now php-fpm
    
    php -v
    log_info "PHP 安装完成"
}

# 3. 安装 MariaDB
install_mariadb() {
    log_info "==== 安装 MariaDB ===="
    
    dnf install -y mariadb-server
    systemctl enable --now mariadb
    
    # 配置 MariaDB
    log_info "创建数据库和用户..."
    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    log_info "MariaDB 安装并配置完成"
}

# 4. 安装 Node.js 18
install_nodejs() {
    log_info "==== 安装 Node.js 18 ===="
    
    dnf module reset nodejs -y
    dnf module enable nodejs:18 -y
    dnf install -y nodejs
    
    node -v
    npm -v
    log_info "Node.js 安装完成"
}

# 5. 安装 Composer
install_composer() {
    log_info "==== 安装 Composer ===="
    
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    
    composer --version
    log_info "Composer 安装完成"
}

# 6. 安装 Nginx
install_nginx() {
    log_info "==== 安装 Nginx ===="
    
    dnf install -y nginx
    systemctl enable nginx
    
    log_info "Nginx 安装完成"
}

# 7. 配置后端 Laravel
configure_backend() {
    log_info "==== 配置 Laravel 后端 ===="
    
    cd "$PROJECT_DIR/backend"
    
    # 安装依赖
    log_info "安装 Composer 依赖..."
    composer install --no-dev --optimize-autoloader
    
    # 配置环境变量
    if [ ! -f .env ]; then
        log_info "创建 .env 文件..."
        cp .env.example .env
        
        # 更新数据库配置
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
        
        # 生成应用密钥
        php artisan key:generate
    fi
    
    # 运行迁移
    log_info "运行数据库迁移..."
    php artisan migrate --force
    php artisan db:seed --class=AdminSeeder --force
    
    # 设置权限
    log_info "设置目录权限..."
    chown -R nginx:nginx storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    
    # 清理缓存
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    log_info "后端配置完成"
}

# 8. 配置前端 React
configure_frontend() {
    log_info "==== 配置 React 前端 ===="
    
    cd "$PROJECT_DIR/admin-panel"
    
    # 安装依赖
    log_info "安装 npm 依赖..."
    npm install
    
    # 配置环境变量
    if [ ! -f .env ]; then
        echo "VITE_API_URL=http://$DOMAIN/api" > .env
    fi
    
    # 编译生产版本
    log_info "编译前端项目..."
    npm run build
    
    log_info "前端配置完成"
}

# 9. 配置 Nginx
configure_nginx() {
    log_info "==== 配置 Nginx ===="
    
    # 创建 Nginx 配置文件
    cat > /etc/nginx/conf.d/iptv.conf <<EOF
server {
    listen $WEB_PORT;
    server_name $DOMAIN;
    
    root $PROJECT_DIR/admin-panel/dist;
    index index.html;
    
    # 前端静态文件
    location / {
        try_files \$uri \$uri/ /index.html;
    }
    
    # 后端 API 代理
    location /api {
        alias $PROJECT_DIR/backend/public;
        try_files \$uri \$uri/ @backend;
        
        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/run/php-fpm/www.sock;
            fastcgi_param SCRIPT_FILENAME $PROJECT_DIR/backend/public/index.php;
            fastcgi_param SCRIPT_NAME /api/index.php;
            fastcgi_index index.php;
        }
    }
    
    location @backend {
        rewrite ^/api/(.*)$ /api/index.php?/\$1 last;
    }
    
    # 静态资源缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # 日志
    access_log /var/log/nginx/iptv_access.log;
    error_log /var/log/nginx/iptv_error.log;
}
EOF
    
    # 测试 Nginx 配置
    nginx -t
    
    # 重启 Nginx
    systemctl restart nginx
    
    log_info "Nginx 配置完成"
}

# 10. 配置防火墙
configure_firewall() {
    log_info "==== 配置防火墙 ===="
    
    if systemctl is-active --quiet firewalld; then
        firewall-cmd --permanent --add-service=http
        firewall-cmd --permanent --add-service=https
        firewall-cmd --permanent --add-port=$WEB_PORT/tcp
        firewall-cmd --reload
        log_info "防火墙配置完成"
    else
        log_warn "防火墙未运行，跳过配置"
    fi
}

# 11. 配置 SELinux
configure_selinux() {
    log_info "==== 配置 SELinux ===="
    
    if getenforce | grep -q "Enforcing"; then
        log_warn "检测到 SELinux 处于强制模式"
        
        # 设置 SELinux 上下文
        chcon -R -t httpd_sys_rw_content_t "$PROJECT_DIR/backend/storage"
        chcon -R -t httpd_sys_rw_content_t "$PROJECT_DIR/backend/bootstrap/cache"
        
        # 允许 Nginx 网络连接
        setsebool -P httpd_can_network_connect 1
        
        log_info "SELinux 配置完成"
    else
        log_info "SELinux 未启用或处于宽松模式，跳过配置"
    fi
}

# 12. 显示安装信息
show_info() {
    log_info "==== 安装完成 ===="
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}  Hooray-IPTV 安装成功！${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo "访问地址: http://$DOMAIN"
    echo ""
    echo "数据库信息:"
    echo "  - 数据库名: $DB_NAME"
    echo "  - 用户名: $DB_USER"
    echo "  - 密码: $DB_PASS"
    echo ""
    echo "默认管理员账号:"
    echo "  - 用户名: admin"
    echo "  - 密码: 请查看 backend/database/seeders/AdminSeeder.php"
    echo ""
    echo "重要文件位置:"
    echo "  - 项目目录: $PROJECT_DIR"
    echo "  - Nginx 配置: /etc/nginx/conf.d/iptv.conf"
    echo "  - PHP-FPM 配置: /etc/php-fpm.d/www.conf"
    echo ""
    echo "常用命令:"
    echo "  - 重启 Nginx: sudo systemctl restart nginx"
    echo "  - 重启 PHP-FPM: sudo systemctl restart php-fpm"
    echo "  - 查看日志: sudo tail -f /var/log/nginx/iptv_error.log"
    echo ""
    echo -e "${YELLOW}请妥善保管数据库密码！${NC}"
    echo -e "${GREEN}========================================${NC}"
}

# 主函数
main() {
    log_info "开始安装 Hooray-IPTV 订阅服务..."
    
    check_root
    get_config
    
    update_system
    install_php
    install_mariadb
    install_nodejs
    install_composer
    install_nginx
    
    configure_backend
    configure_frontend
    configure_nginx
    
    configure_firewall
    configure_selinux
    
    show_info
}

# 执行主函数
main
