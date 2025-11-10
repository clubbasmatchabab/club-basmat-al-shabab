# استخدام صورة PHP رسمية تحتوي على FPM و Alpine Linux
FROM php:8.1-fpm-alpine

# تثبيت متطلبات التشغيل الأساسية (مثل cURL)
RUN apk add --no-cache nginx

# نسخ ملفات المشروع إلى المجلد الافتراضي لـ Nginx
COPY . /var/www/html

# نسخ ملف إعداد Nginx الافتراضي
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# تهيئة النقل
EXPOSE 8080
CMD php-fpm -D && nginx -g "daemon off;"