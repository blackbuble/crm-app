web: bash deploy.sh && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
queue: php artisan queue:work --tries=3 --timeout=90
