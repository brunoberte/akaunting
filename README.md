# Controle financeiro

## Run using docker

```sh
# Run the app
docker compose up -d
```

```sh
# Make sure you the dependencies are installed
docker compose exec app composer install
```

```sh
# Stream logs
docker compose logs -f app
```

```sh
# Access the container
docker compose exec web /bin/sh
```

```sh
# Stop & Delete everything
docker compose down -v
```
