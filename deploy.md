# دليل النشر — Family Tree System

---

## المتطلبات

### على السيرفر
| المتطلب | الإصدار المطلوب |
|---------|----------------|
| PHP | >= 8.2 |
| Composer | >= 2.x |
| Node.js | >= 18.x |
| npm | >= 9.x |
| MySQL / MariaDB | >= 8.0 |
| Apache أو Nginx | أي إصدار حديث |

### امتدادات PHP المطلوبة
```
php-pdo, php-mysql, php-mbstring, php-xml, php-bcmath,
php-curl, php-gd, php-fileinfo, php-tokenizer, php-zip
```

---

## الخطوات

---

### الخطوة 1 — رفع الملفات

انسخ المجلد كاملاً للسيرفر:
```
Family Tree System/
├── backend/
└── frontend/
```

---

### الخطوة 2 — إعداد قاعدة البيانات

```sql
CREATE DATABASE family_tree CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'family_user'@'localhost' IDENTIFIED BY 'StrongPassword123';
GRANT ALL PRIVILEGES ON family_tree.* TO 'family_user'@'localhost';
FLUSH PRIVILEGES;
```

---

### الخطوة 3 — إعداد Backend (Laravel)

```bash
cd backend
```

**تثبيت الاعتماديات:**
```bash
composer install --no-dev --optimize-autoloader
```

**إنشاء ملف البيئة:**
```bash
cp .env.example .env
```

**تعديل `.env`:**
```env
APP_NAME="Family Tree"
APP_ENV=production
APP_KEY=                          # سيُملأ في الخطوة التالية
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=family_tree
DB_USERNAME=family_user
DB_PASSWORD=StrongPassword123

FILESYSTEM_DISK=public
```

**توليد المفتاح السري:**
```bash
php artisan key:generate
```

**تشغيل الـ migrations:**
```bash
php artisan migrate --force
```

**اختياري — تحميل بيانات تجريبية:**
```bash
php artisan db:seed --class=PeopleSeeder
```

**إنشاء رابط الـ storage للصور:**
```bash
php artisan storage:link
```

**تحسين الأداء (مهم للـ production):**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**صلاحيات المجلدات:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

### الخطوة 4 — بناء Frontend (Vue)

```bash
cd ../frontend
```

**تثبيت الاعتماديات:**
```bash
npm install
```

**تعديل `vite.config.js` قبل البناء:**

في بيئة الـ production لا نحتاج الـ proxy لأن الـ frontend والـ backend على نفس الدومين.
أضف `base` إذا كان التطبيق في subdirectory:

```js
// frontend/vite.config.js
export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: { alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) } },
  build: {
    outDir: '../backend/public/app',   // نخرج الـ build داخل Laravel public
    emptyOutDir: true,
  },
  // لا تضع server.proxy هنا — هي للـ dev فقط
})
```

**أو** إذا أردت إبقاء `frontend/dist/` منفصلاً:
```js
build: { outDir: 'dist' }
```

**تشغيل البناء:**
```bash
npm run build
```

---

### الخطوة 5 — إعداد الـ Web Server

---

#### الخيار A — Apache

**أنشئ ملف** `/etc/apache2/sites-available/family-tree.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com

    # Frontend (Vue dist) — يُخدَّم أولاً
    DocumentRoot /var/www/family-tree/frontend/dist

    <Directory /var/www/family-tree/frontend/dist>
        Options -Indexes
        AllowOverride All
        Require all granted

        # SPA routing — أعد توجيه كل الطلبات لـ index.html
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_URI} !^/api/
        RewriteCond %{REQUEST_URI} !^/storage/
        RewriteRule ^ index.html [L]
    </Directory>

    # API — مرّر للـ Laravel
    Alias /api /var/www/family-tree/backend/public
    Alias /storage /var/www/family-tree/backend/public/storage

    <Directory /var/www/family-tree/backend/public>
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/family-tree-error.log
    CustomLog ${APACHE_LOG_DIR}/family-tree-access.log combined
</VirtualHost>
```

```bash
a2ensite family-tree.conf
a2enmod rewrite
systemctl reload apache2
```

**`.htaccess` في `backend/public/`** (Laravel يُنشئه تلقائياً):
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)/$ /$1 [L,R=301]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

#### الخيار B — Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;

    # Frontend
    root /var/www/family-tree/frontend/dist;
    index index.html;

    # SPA routing
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API → Laravel
    location /api {
        root /var/www/family-tree/backend/public;
        try_files $uri $uri/ /index.php?$query_string;

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/family-tree/backend/public$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    # صور الـ storage
    location /storage {
        alias /var/www/family-tree/backend/public/storage;
    }

    error_log  /var/log/nginx/family-tree-error.log;
    access_log /var/log/nginx/family-tree-access.log;
}
```

```bash
ln -s /etc/nginx/sites-available/family-tree /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

#### الخيار C — Windows (XAMPP / LocalServer)

1. انسخ `backend/` إلى `C:\xampp\htdocs\family-tree\`
2. انسخ `frontend/dist/` إلى `C:\xampp\htdocs\family-tree-app\`
3. في `httpd-vhosts.conf` أضف:

```apache
<VirtualHost *:80>
    ServerName family-tree.local
    DocumentRoot "C:/xampp/htdocs/family-tree-app"

    <Directory "C:/xampp/htdocs/family-tree-app">
        AllowOverride All
        Require all granted
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.html [L]
    </Directory>

    Alias /api "C:/xampp/htdocs/family-tree/public"
    Alias /storage "C:/xampp/htdocs/family-tree/public/storage"

    <Directory "C:/xampp/htdocs/family-tree/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. في `C:\Windows\System32\drivers\etc\hosts` أضف:
```
127.0.0.1  family-tree.local
```

---

### الخطوة 6 — HTTPS (اختياري لكن موصى به)

باستخدام **Let's Encrypt** (Linux):
```bash
apt install certbot python3-certbot-apache   # أو nginx
certbot --apache -d yourdomain.com           # أو --nginx
```

---

### الخطوة 7 — التحقق من التشغيل

```bash
# تحقق من أن Laravel يعمل
curl https://yourdomain.com/api/tree

# تحقق من أن الصور تعمل
curl -I https://yourdomain.com/storage/photos/test.jpg

# تحقق من أن الـ frontend يعمل
curl -I https://yourdomain.com
```

---

## التطوير المحلي (Dev Mode)

```bash
# Terminal 1 — Backend
cd backend
php artisan serve
# يعمل على: http://localhost:8000

# Terminal 2 — Frontend
cd frontend
npm run dev
# يعمل على: http://localhost:5173
# الـ proxy في vite.config.js يوجّه /api → :8000
```

---

## التحديث (بعد أي تغيير)

### تحديث Backend فقط:
```bash
cd backend
git pull                          # أو انسخ الملفات يدوياً
composer install --no-dev
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

### تحديث Frontend فقط:
```bash
cd frontend
git pull
npm install
npm run build
# انسخ dist/ للمكان الصحيح
```

---

## استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| `500 Server Error` | تحقق من `backend/storage/logs/laravel.log` |
| الصور لا تظهر | شغّل `php artisan storage:link` مجدداً |
| `/api` يرجع 404 | تحقق من إعداد الـ web server و `.htaccess` |
| الصفحة بيضاء | تحقق من `APP_KEY` في `.env` |
| `SQLSTATE` error | تحقق من بيانات الـ DB في `.env` |
| الـ Frontend يرجع `index.html` لكل route | تأكد من SPA routing في الـ server config |
| الصور لا تُرفع | تحقق من صلاحيات `storage/` و `php-gd` مفعّل |
