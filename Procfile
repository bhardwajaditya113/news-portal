web: vendor/bin/heroku-php-apache2 public/
scheduler: php artisan schedule:work
release: php artisan migrate --force && php artisan db:seed --class=ActivateAllRolesSeeder
