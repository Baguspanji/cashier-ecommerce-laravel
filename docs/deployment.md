# Deployment Guide - Cashier E-commerce PWA

This guide covers deployment strategies for the Cashier E-commerce PWA application in various environments.

## Prerequisites

### System Requirements
- **PHP >= 8.3** with extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- **Node.js >= 18** with npm/pnpm
- **Database**: SQLite (dev), MySQL 8.0+ or PostgreSQL 13+ (production)
- **Web Server**: Nginx (recommended) or Apache
- **SSL Certificate**: Required for PWA functionality

### Performance Requirements
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 10GB minimum for application and logs
- **CPU**: 2 cores minimum for production workload

## Environment Configuration

### Production Environment Variables

```bash
# Application
APP_NAME="Cashier E-Commerce"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:your-generated-key

# Database (MySQL Example)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cashier_db
DB_USERNAME=cashier_user
DB_PASSWORD=secure_password

# Queue Configuration
QUEUE_CONNECTION=database
# Or use Redis for better performance
# QUEUE_CONNECTION=redis
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379

# Session & Cache
SESSION_DRIVER=database
# For production, consider Redis
# SESSION_DRIVER=redis
# CACHE_DRIVER=redis

# PWA Configuration
PWA_ENABLED=true
PWA_OFFLINE_SUPPORT=true
PWA_BACKGROUND_SYNC=true

# Security
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Database Setup

### MySQL Production Setup

```sql
-- Create database and user
CREATE DATABASE cashier_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cashier_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON cashier_db.* TO 'cashier_user'@'localhost';
FLUSH PRIVILEGES;

-- Optimize for InnoDB
SET GLOBAL innodb_file_per_table = 1;
SET GLOBAL innodb_buffer_pool_size = 1G; -- Adjust based on available RAM
```

### Migration and Seeding

```bash
# Run migrations
php artisan migrate --force

# Seed with production data (optional)
php artisan db:seed --class=ProductionSeeder

# Or seed with sample data for testing
php artisan db:seed
```

## Server Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/cashier-app/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;

    # Security Headers for PWA
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: ws: wss: data: blob: 'unsafe-inline'; frame-ancestors 'self';" always;

    # PWA Headers
    add_header Service-Worker-Allowed "/" always;
    
    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Service Worker (should not be cached)
    location = /sw.js {
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires "0";
        try_files $uri =404;
    }

    # Manifest file
    location = /manifest.json {
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        try_files $uri =404;
    }

    index index.php;

    charset utf-8;

    # Laravel application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache Configuration

```apache
<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/cashier-app/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/ssl/certificate.crt
    SSLCertificateKeyFile /path/to/ssl/private.key

    # Security Headers for PWA
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "no-referrer-when-downgrade"
    Header always set Service-Worker-Allowed "/"

    # Cache static assets
    <LocationMatch "\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
        ExpiresActive On
        ExpiresDefault "access plus 1 year"
        Header set Cache-Control "public, immutable"
    </LocationMatch>

    # Service Worker (no cache)
    <Location "/sw.js">
        Header set Cache-Control "no-cache, no-store, must-revalidate"
        Header set Pragma "no-cache"
        Header set Expires "0"
    </Location>

    # Laravel Rewrite Rules
    <Directory /var/www/cashier-app/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/cashier_error.log
    CustomLog ${APACHE_LOG_DIR}/cashier_access.log combined
</VirtualHost>
```

## Application Deployment

### Automated Deployment Script

```bash
#!/bin/bash
# deploy.sh - Production deployment script

set -e

echo "🚀 Starting Cashier E-commerce deployment..."

# Configuration
APP_DIR="/var/www/cashier-app"
BACKUP_DIR="/var/backups/cashier-app"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create backup
echo "📦 Creating backup..."
mkdir -p $BACKUP_DIR
sudo -u www-data cp -r $APP_DIR $BACKUP_DIR/backup_$TIMESTAMP

# Pull latest code
echo "📥 Pulling latest code..."
cd $APP_DIR
sudo -u www-data git pull origin main

# Install/update dependencies
echo "📦 Installing dependencies..."
sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction
sudo -u www-data npm ci --only=production

# Build assets
echo "🏗️ Building assets..."
sudo -u www-data npm run build

# Database migrations
echo "🗄️ Running migrations..."
sudo -u www-data php artisan migrate --force

# Cache optimization
echo "⚡ Optimizing caches..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Clear old caches
sudo -u www-data php artisan queue:restart

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# Health check
echo "🏥 Running health check..."
if curl -f http://localhost > /dev/null 2>&1; then
    echo "✅ Deployment successful!"
else
    echo "❌ Health check failed! Rolling back..."
    sudo -u www-data cp -r $BACKUP_DIR/backup_$TIMESTAMP/* $APP_DIR/
    exit 1
fi

echo "🎉 Deployment completed successfully!"
```

### Zero-Downtime Deployment

```bash
#!/bin/bash
# zero-downtime-deploy.sh - Blue-green deployment

BLUE_DIR="/var/www/cashier-app-blue"
GREEN_DIR="/var/www/cashier-app-green"
CURRENT_LINK="/var/www/cashier-app"

# Determine current and next deployment
if [ -L $CURRENT_LINK ]; then
    CURRENT=$(readlink $CURRENT_LINK)
    if [[ $CURRENT == *"blue"* ]]; then
        NEXT_DIR=$GREEN_DIR
        NEXT_COLOR="green"
    else
        NEXT_DIR=$BLUE_DIR
        NEXT_COLOR="blue"
    fi
else
    NEXT_DIR=$BLUE_DIR
    NEXT_COLOR="blue"
fi

echo "🚀 Deploying to $NEXT_COLOR environment..."

# Deploy to next environment
cd $NEXT_DIR
git pull origin main
composer install --optimize-autoloader --no-dev
npm ci --only=production
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Health check
if curl -f http://localhost:8080 > /dev/null 2>&1; then
    echo "✅ Health check passed. Switching traffic..."
    
    # Switch symlink
    ln -sfn $NEXT_DIR $CURRENT_LINK
    
    # Reload web server
    sudo systemctl reload nginx
    
    echo "🎉 Zero-downtime deployment completed!"
else
    echo "❌ Health check failed! Keeping current version."
    exit 1
fi
```

## Performance Optimization

### PHP-FPM Configuration

```ini
; /etc/php/8.3/fpm/pool.d/cashier.conf
[cashier]
user = www-data
group = www-data
listen = /var/run/php/php8.3-fpm-cashier.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 1000

; Performance tuning
request_terminate_timeout = 60
request_slowlog_timeout = 10
slowlog = /var/log/php8.3-fpm-slow.log

; Memory limit
php_admin_value[memory_limit] = 256M
```

### Database Optimization

```sql
-- MySQL optimization for production
-- Add to /etc/mysql/mysql.conf.d/mysqld.cnf

[mysqld]
# Performance
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_type = 1
query_cache_size = 256M

# Connection limits
max_connections = 200
max_user_connections = 180

# Timeouts
wait_timeout = 600
interactive_timeout = 600
```

### Queue Worker Configuration

```bash
# /etc/supervisor/conf.d/cashier-worker.conf
[program:cashier-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cashier-app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/cashier-worker.log
stopwaitsecs=3600
```

## Monitoring and Maintenance

### Log Rotation

```bash
# /etc/logrotate.d/cashier-app
/var/www/cashier-app/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        php /var/www/cashier-app/artisan config:cache
    endscript
}
```

### Health Check Script

```bash
#!/bin/bash
# health-check.sh - Application health monitoring

APP_URL="https://your-domain.com"
LOG_FILE="/var/log/cashier-health.log"

# Check application response
if curl -f --max-time 10 $APP_URL > /dev/null 2>&1; then
    echo "$(date): ✅ Application is healthy" >> $LOG_FILE
else
    echo "$(date): ❌ Application is down!" >> $LOG_FILE
    # Send alert (email, Slack, etc.)
    echo "Application is down!" | mail -s "Cashier App Alert" admin@your-domain.com
fi

# Check database connection
cd /var/www/cashier-app
if php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; then
    echo "$(date): ✅ Database is healthy" >> $LOG_FILE
else
    echo "$(date): ❌ Database connection failed!" >> $LOG_FILE
fi

# Check queue workers
if supervisorctl status cashier-worker | grep RUNNING > /dev/null; then
    echo "$(date): ✅ Queue workers are running" >> $LOG_FILE
else
    echo "$(date): ❌ Queue workers are down!" >> $LOG_FILE
    supervisorctl restart cashier-worker
fi
```

### Backup Strategy

```bash
#!/bin/bash
# backup.sh - Automated backup script

BACKUP_DIR="/var/backups/cashier-app"
APP_DIR="/var/www/cashier-app"
DB_NAME="cashier_db"
DB_USER="cashier_user"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR/$TIMESTAMP

# Database backup
mysqldump -u $DB_USER -p $DB_NAME > $BACKUP_DIR/$TIMESTAMP/database.sql

# Application files backup
tar -czf $BACKUP_DIR/$TIMESTAMP/application.tar.gz $APP_DIR

# Storage files backup
tar -czf $BACKUP_DIR/$TIMESTAMP/storage.tar.gz $APP_DIR/storage/app

# Keep only last 30 days of backups
find $BACKUP_DIR -type d -mtime +30 -exec rm -rf {} +

echo "Backup completed: $BACKUP_DIR/$TIMESTAMP"
```

## Security Considerations

### SSL/TLS Configuration

```bash
# Generate strong SSL configuration
openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048

# Update Nginx SSL configuration
ssl_dhparam /etc/ssl/certs/dhparam.pem;
ssl_session_timeout 1d;
ssl_session_cache shared:SSL:50m;
ssl_stapling on;
ssl_stapling_verify on;
```

### Firewall Configuration

```bash
# UFW firewall rules
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw enable
```

### File Permissions

```bash
# Secure file permissions
chown -R www-data:www-data /var/www/cashier-app
chmod -R 755 /var/www/cashier-app
chmod -R 775 /var/www/cashier-app/storage
chmod -R 775 /var/www/cashier-app/bootstrap/cache
chmod 600 /var/www/cashier-app/.env
```

## Troubleshooting

### Common Issues

**PWA not installing:**
- Verify HTTPS is properly configured
- Check service worker registration in browser DevTools
- Ensure manifest.json is accessible

**Slow performance:**
- Check PHP-FPM pool status: `sudo systemctl status php8.3-fpm`
- Monitor database queries: Enable slow query log
- Check application logs: `tail -f storage/logs/laravel.log`

**Queue jobs not processing:**
- Check supervisor status: `supervisorctl status`
- Restart queue workers: `supervisorctl restart cashier-worker`
- Check queue configuration in `.env`

### Debug Commands

```bash
# Check application status
php artisan route:list
php artisan config:show
php artisan queue:monitor

# Clear all caches
php artisan optimize:clear

# Check logs
tail -f /var/log/nginx/error.log
tail -f storage/logs/laravel.log
```

## Post-Deployment Checklist

- [ ] Application loads successfully over HTTPS
- [ ] PWA install prompt appears on mobile devices
- [ ] Database migrations completed without errors
- [ ] Queue workers are running
- [ ] SSL certificate is valid and properly configured
- [ ] All static assets load correctly
- [ ] Service worker registers successfully
- [ ] Manifest.json is accessible
- [ ] Error pages display correctly
- [ ] Performance metrics meet requirements (< 3s first load)
- [ ] Backup system is configured and tested
- [ ] Monitoring and alerting are active
- [ ] Security headers are properly set

---

This deployment guide ensures a production-ready PWA application with proper security, performance, and reliability considerations.
