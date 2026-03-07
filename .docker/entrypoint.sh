#!/bin/sh
set -e

# Instalar dependencias si no existe vendor
if [ ! -d "vendor" ]; then
    echo "Instalando dependencias de Composer..."
    composer install --no-progress --no-interaction
fi

# Generar key si no está configurada
if [ -f ".env" ] && ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generando APP_KEY..."
    php artisan key:generate
fi

# Ajustar permisos para storage y cache
echo "Ajustando permisos..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Ejecutar migraciones (opcional, descomentar si se desea automático)
# echo "Ejecutando migraciones..."
# php artisan migrate --force

echo "Iniciando PHP-FPM..."
exec php-fpm
