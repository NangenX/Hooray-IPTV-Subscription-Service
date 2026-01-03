# Hooray IPTV Subscription Service

一个基于 Laravel 9 + React 18 的现代化 IPTV 订阅管理平台

## 项目特性

✅ **已完成功能（第一阶段）**

### 后端 (Laravel 9)
- ✅ 完整的管理员认证系统 (Laravel Sanctum)
- ✅ 管理员CRUD管理（支持多角色：super_admin、admin、moderator）
- ✅ 频道管理系统（创建、编辑、删除、批量操作）
- ✅ **M3U文件导入功能**（支持最多3000个频道）
  - 自动解析 Extended M3U 格式
  - 提取 tvg-id、tvg-name、tvg-logo、group-title 等属性
  - 智能重复检测（基于频道名+URL）
  - 批量导入（500条/批次优化性能）
  - 详细的导入日志（TXT格式可下载）
- ✅ 完整的系统日志记录（Spatie Activity Log）
- ✅ 多语言支持（英文 + 简体中文）
- ✅ RESTful API 架构
- ✅ 数据库迁移和Seeder（MariaDB 10.x优化）

### 前端 (React 18 + TypeScript)
- ✅ 现代化管理后台界面
- ✅ 基于 shadcn/ui + TailwindCSS 的UI组件
- ✅ 管理员登录/登出系统
- ✅ 仪表板（Dashboard）
- ✅ **M3U导入界面**
  - 拖拽上传支持
  - 实时上传进度
  - 导入结果统计展示
  - 错误日志查看
- ✅ 频道列表管理界面
- ✅ 多语言切换（英文/简体中文）
- ✅ 响应式设计

## 技术栈

### 后端
- **框架**: Laravel 9.x
- **认证**: Laravel Sanctum (Token-based API)
- **数据库**: MariaDB 10.x
- **日志**: Spatie Laravel Activity Log
- **权限**: Spatie Laravel Permission

### 前端
- **框架**: React 18.2 + TypeScript
- **构建工具**: Vite 5
- **路由**: React Router DOM 6
- **UI库**: shadcn/ui + Radix UI
- **样式**: TailwindCSS 3.4
- **HTTP客户端**: Axios
- **表单**: React Hook Form + Zod
- **国际化**: react-i18next
- **通知**: Sonner

## 快速开始

### 环境要求
- PHP 8.1+
- Composer 2.x
- MariaDB 10.11+
- Node.js 18+
- npm/yarn

### 安装步骤

请参考 [SETUP.md](SETUP.md) 获取详细的安装指南。

### 快速命令

#### 后端
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

#### 前端
```bash
cd admin-panel
npm install
npm run dev
```

## 默认登录凭据

- **用户名**: admin
- **密码**: admin123

⚠️ **请在首次登录后立即修改默认密码！**

## 项目结构

```
Hooray-IPTV-Subscription-Service/
├── backend/                          # Laravel 9 后端
│   ├── app/
│   │   ├── Http/Controllers/API/     # API控制器
│   │   ├── Models/                   # Eloquent模型
│   │   └── Services/                 # 业务逻辑服务
│   │       ├── M3UParserService.php  # M3U解析服务
│   │       ├── M3UImportService.php  # M3U导入服务
│   │       └── SystemLogService.php  # 系统日志服务
│   ├── database/
│   │   ├── migrations/               # 数据库迁移文件
│   │   └── seeders/                  # 数据填充文件
│   ├── resources/lang/               # 多语言文件
│   └── routes/api.php                # API路由
│
├── admin-panel/                      # React 18 管理面板
│   ├── src/
│   │   ├── api/                      # API服务层
│   │   ├── components/               # React组件
│   │   │   ├── ui/                   # shadcn/ui组件
│   │   │   └── layout/               # 布局组件
│   │   ├── pages/                    # 页面组件
│   │   │   ├── Auth/                 # 认证页面
│   │   │   ├── Dashboard/            # 仪表板
│   │   │   └── Channels/             # 频道管理
│   │   ├── i18n/                     # 国际化配置
│   │   ├── types/                    # TypeScript类型定义
│   │   └── lib/                      # 工具函数
│   └── package.json
│
├── README.md                         # 项目说明
└── SETUP.md                          # 安装指南
```

## 核心功能说明

### M3U导入功能

支持导入标准的 Extended M3U 格式文件：

**支持的属性**:
- `tvg-id`: EPG标识符
- `tvg-name`: EPG显示名称
- `tvg-logo`: 频道Logo URL
- `group-title`: 频道分组
- `tvg-country`: 国家代码
- `tvg-language`: 语言代码

**限制**:
- 最大文件大小: 50MB
- 最大频道数量: 3000个
- 支持格式: .m3u, .m3u8

**重复检测规则**:
系统会检查频道名称和流URL，只有当两者完全相同时才认定为重复并跳过。

### 数据库设计

#### 核心表
- `admins`: 管理员账号
- `users`: 用户账号
- `channels`: 频道信息
- `packages`: 订阅套餐
- `package_channels`: 套餐-频道关联
- `invitation_codes`: 邀请码
- `orders`: 订单记录
- `activity_logs`: 系统活动日志
- `import_logs`: M3U导入日志

## API文档

### 认证相关
- `POST /api/admin/login` - 管理员登录
- `GET /api/admin/me` - 获取当前管理员信息
- `POST /api/admin/logout` - 退出登录

### 频道管理
- `GET /api/admin/channels` - 获取频道列表
- `POST /api/admin/channels` - 创建频道
- `PUT /api/admin/channels/{id}` - 更新频道
- `DELETE /api/admin/channels/{id}` - 删除频道
- `GET /api/admin/channels/groups` - 获取所有分组

### M3U导入
- `POST /api/admin/m3u/import` - 导入M3U文件
- `GET /api/admin/m3u/history` - 获取导入历史
- `GET /api/admin/m3u/download-log/{id}` - 下载导入日志

### 管理员管理
- `GET /api/admin/admins` - 获取管理员列表
- `POST /api/admin/admins` - 创建管理员
- `PUT /api/admin/admins/{id}` - 更新管理员
- `DELETE /api/admin/admins/{id}` - 删除管理员

## 开发规划

### 第二阶段（计划中）
- [ ] 套餐管理系统
- [ ] 邀请码生成和管理
- [ ] 订单管理系统
- [ ] 用户前端界面
- [ ] 直播播放器集成

### 第三阶段（计划中）
- [ ] EPG（电子节目指南）支持
- [ ] 多设备并发控制
- [ ] 支付集成
- [ ] 统计分析dashboard
- [ ] 移动端适配

## 常见问题

### 1. Composer安装很慢？
使用国内镜像：
```bash
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```

### 2. npm安装很慢？
使用淘宝镜像：
```bash
npm config set registry https://registry.npmmirror.com
```

### 3. M3U导入失败？
- 确认文件格式是否正确（必须以 #EXTM3U 开头）
- 检查文件大小是否超过50MB
- 确认频道数量是否超过3000个

## 贡献指南

欢迎提交 Issue 和 Pull Request！

## 许可证

MIT License

## 联系方式

如有问题，请提交 Issue 或联系项目维护者。

---

**注意**: 本项目当前处于开发阶段，第一阶段核心功能已完成，适合用于测试和开发环境。

---

## 原始需求规格（供参考）

### 1. 后端系统基于最新的php架构laravel9
### 2. 后端管理界面基于最新的shadcn，tailwindcss，react18，typescript
### 3. 数据库采用Mysql或者Mariadb，支持基本的数据存储和查询功能。
### 4. 后端管理功能：
- 4.1 一套完整的管理人员处理机制，默认admin登陆，admin可以创建，删除，修改其他管理人员账号。
- 4.2 一套完整的直播节目列表处理支持，支持手动添加节目或者通过m3u列表导入节目列表，直播节目列表可以对直播节目进行管理，支持创建，删除，修改直播节目。
- 4.3 一套完整的订阅套餐处理机制，支持创建，删除，修改订阅套餐，支持为用户分配订阅套餐，套餐创建通过生成邀请码的方式创建，用户在终端设备上输入对应后直接获取对应套餐权限。直播节目可以通过套餐进行分配，支持套餐内直播节目的增删改查。
- 4.4 一套完整的订单处理机制，支持管理员查看当前套餐订单，支持订单的查看，删除，修改等功能。

### 5. 前端用户功能：
- 5.1 用户注册，登录功能，支持通过邀请码注册获取对应套餐权限。
- 5.2 用户个人中心功能，支持查看当前套餐权限，支持修改个人信息，支持查看订单等功能。
- 5.3 直播节目播放功能，支持通过web播放器进行直播节目播放，支持多种终端设备播放。

### 6. 安全机制：
- 6.1 支持https协议，保障数据传输安全。
- 6.2 支持用户密码加密存储，保障用户信息安全。

### 7. 其他功能：
- 7.1 支持多语言切换，满足不同地区用户需求。
- 7.2 支持日志记录功能，记录用户操作日志，方便管理员查看。
- 7.3 支持数据备份与恢复功能，保障数据安全。
- 7.4 完整的系统日志，保障平台稳定运行。

### 8. 部署与维护：
- 8.1 提供详细的部署文档，方便用户进行平台部署。
- 8.2 提供定期更新和维护，保障平台稳定运行。
