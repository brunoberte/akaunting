
## Controle financeiro

### Local environment

based on https://docs.docker.com/guides/frameworks/laravel/development-setup

Run the following commands

- Setup local DNS
```sh
sudo sh -c '{ echo 15.0.0.100 financeiro.local ; echo 15.0.0.101 mysql.financeiro.local ; } >> /etc/hosts'
```

- Create self-signed certificate to use HTTPS
```sh
openssl req -x509 -newkey rsa:4096 -sha256 -days 365 -nodes \
    -keyout ./docker/development/nginx/certs/selfsigned.key \
    -out ./docker/development/nginx/certs/selfsigned.crt \
    -subj '/CN=financeiro.local' \
    -extensions san \
    -config <( \
    echo '[req]'; \
    echo 'distinguished_name=req'; \
    echo '[san]'; \
    echo 'subjectAltName=DNS:financeiro.local')
```

- Start containers
```sh
docker compose -f compose.dev.yaml up --build -d
```

- Install composer dependencies
```sh
docker compose exec app composer install
```

- create database
```sh
docker compose -f compose.dev.yaml exec mysql mysql -u root -pSenhaMy -e "create DATABASE financeiro;"
docker compose -f compose.dev.yaml exec mysql mysql -u root -pSenhaMy -e "create DATABASE financeiro_test;"
docker compose -f compose.dev.yaml exec mysql mysql -u root -pSenhaMy financeiro -e "\. /var/workspace/financeiro.sql"
```

- Run migrations
```sh
docker compose -f compose.dev.yaml exec workspace php artisan migrate --seed
```

- Run vite
```sh
docker compose -f compose.dev.yaml exec workspace bash
npm install
npm run dev
```

then open https://financeiro.local/

---
