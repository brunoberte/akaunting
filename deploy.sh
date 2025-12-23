#!/bin/bash

# Aborta o script em caso de erro
set -e

echo "🚀 Iniciando deploy da aplicação Financeiro..."

# 1. Puxar as últimas alterações do repositório
echo "Pulling latest changes from git..."
git pull origin main

# 2. Reconstruir e subir os containers
# O flag --build garante que o Dockerfile seja processado novamente (npm run build, etc)
echo "Rebuilding containers..."
docker compose -f compose.prod.yaml build --build-arg SOURCE_DATE_EPOCH="$(date +%s)"
docker compose -f compose.prod.yaml up -d

echo "Update public/build files on volume"
docker run --rm -v akaunting_laravel-public-assets:/volume_dest akaunting-web:latest sh -c "cp -a /var/www/public/build/. /volume_dest"

echo "Copying env file"
docker compose -f compose.prod.yaml cp .env php-fpm:/var/www/

# 3. Instalar dependências do Composer (dentro do container PHP)
echo "Installing composer dependencies..."
COMPOSER_HASH=$(md5sum composer.lock | cut -d' ' -f1)
CACHE_FILE=".composer_hash_cache"

if [ -f "$CACHE_FILE" ] && [ "$(cat $CACHE_FILE)" == "$COMPOSER_HASH" ]; then
    echo "⏭️ composer.lock não alterado, pulando instalação..."
else
    echo "Installing composer dependencies..."
    docker compose -f compose.prod.yaml exec -T php-fpm composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist
    echo "$COMPOSER_HASH" > "$CACHE_FILE"
fi

# 4. Executar migrações do banco de dados
#echo "Running database migrations..."
#docker compose -f compose.prod.yaml exec -T php-fpm php artisan migrate --force

# 5. Limpar caches para garantir que as novas configurações/rotas sejam lidas
echo "Clearing cache..."
docker compose -f compose.prod.yaml exec -T php-fpm php artisan config:cache
docker compose -f compose.prod.yaml exec -T php-fpm php artisan route:cache
docker compose -f compose.prod.yaml exec -T php-fpm php artisan view:clear

# 6. Reiniciar o Queue Worker (se houver) para ler o novo código
echo "Restarting queue worker..."
docker compose -f compose.prod.yaml exec -T php-fpm php artisan queue:restart

# 7. Limpeza de imagens antigas/órfãs para economizar espaço em disco
echo "Cleaning up old images..."
docker image prune -f

echo "✅ Deploy finalizado com sucesso!"
