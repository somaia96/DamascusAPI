# Dockerfile جاهز لـ Laravel
FROM php:8.2-fpm-alpine
 
# تثبيت ملحقات PHP الأساسية المطلوبة
RUN apk add --no-cache \
    nginx \
    supervisor \
    openssl \
    curl \
    mysql-client \
    bash \
    make \
    git \
    libxml2-dev \
    ... (أي ملحقات أخرى تحتاجها API) \
    && docker-php-ext-install pdo pdo_mysql opcache
 
# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
 
# إعداد المستخدم
RUN adduser -D -u 1000 appuser
WORKDIR /var/www
 
# نسخ ملفات المشروع
COPY . /var/www
 
# تثبيت التبعيات (Dependencies)
RUN composer install --optimize-autoloader --no-dev
 
# ضبط الصلاحيات (Permissions)
RUN chown -R appuser:appuser /var/www
 
# تعديل إعدادات PHP و Nginx
# (ستحتاج لملفات إعداد مخصصة)
 
USER appuser
 
# تشغيل خادم الويب
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]