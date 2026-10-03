# Taller Integrador — API

Construida con Laravel 13 sobre PHP 8.3, PostgreSQL 16 y Redis, orquestada con Docker Compose.

## Requisitos

- Docker y Docker Compose

No necesitas PHP, Composer ni Node instalados: todo corre en contenedores.

## Instalación

```bash
git clone <url-del-repo> && cd api
cp .env.example .env
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

La API queda en `http://localhost:8000`.

## Servicios

| Servicio | URL |
|---|---|
| API | http://localhost:8000 |
| Mailpit (correos) | http://localhost:8025 |
| PostgreSQL | localhost:5432 |

## Comandos útiles

```bash
docker compose exec app php artisan migrate        # aplicar migraciones nuevas
docker compose exec app php artisan migrate:fresh  # recrear la base desde cero
docker compose logs -f app                         # ver logs
docker compose restart                             # reiniciar todo
```

> Después de instalar un paquete con Composer, reinicia los contenedores o el
> autoloader se queda en caché.

## Conectarse desde un dispositivo móvil

Un celular no puede alcanzar `localhost`. Usa la IP local de la máquina que
corre la API:

```bash
hostname -I
```

Y apunta la app a `http://<esa-ip>:8000/api/v1`. Ambos dispositivos deben estar
en la misma red.

En el emulador de Android, la dirección del host es `10.0.2.2`.

## API

**Base URL:** `http://localhost:8000/api/v1`

Todas las respuestas son JSON. Envía el header `Accept: application/json`.
Las rutas protegidas requieren `Authorization: Bearer <token>`.

La referencia completa de endpoints está en [docs/api.md](docs/api.md).
