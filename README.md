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

## Datos de demostración

Para trabajar con datos realistas —instructores con perfil y cursos en los
cuatro estados— hay un seeder aparte del inicial:

```bash
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan db:seed --class=DemoSeeder
```

> **`migrate:fresh` borra toda la base.** Úsalo solo en local.

Siembra 13 cursos: 10 publicados, 1 en revisión, 1 en borrador y 1 rechazado.
El reparto es a propósito, para que las pantallas tengan que resolver los
cuatro estados y no solo el caso feliz.

Todas las cuentas de demostración usan la contraseña `password`:

| Correo | Rol |
|---|---|
| `admin@demo.test` | Administrador |
| `ana@demo.test` | Instructora, 5 cursos |
| `diego@demo.test` | Instructor, 4 cursos |
| `laura@demo.test` | Instructora, 4 cursos |
| `sofia@demo.test` | Estudiante, 3 cursos inscritos |
| `mateo@demo.test` | Estudiante, 2 cursos inscritos |
| `valeria@demo.test` | Estudiante, 5 cursos inscritos |
| `emilio@demo.test` | Estudiante, 1 curso inscrito |
| `renata@demo.test` | Estudiante, sin inscripciones |

El reparto de inscripciones es desigual a propósito: Valeria sirve para probar
la lista llena y Renata el estado vacío.

Los tres instructores traen foto de perfil generada automáticamente, para que
los clientes puedan probar el avatar sin subir nada.

Este seeder **no se ejecuta con `db:seed` a secas**: hay que nombrarlo. No
queremos cuentas con contraseña conocida en el servidor.

## Servicios

| Servicio | URL |
|---|---|
| API | http://localhost:8000 |
| Documentación interactiva | http://localhost:8000/docs/api |
| Mailpit (correos) | http://localhost:8025 |
| PostgreSQL | localhost:5432 |

## Comandos útiles

```bash
docker compose exec app php artisan migrate        # aplicar migraciones nuevas
docker compose exec app php artisan migrate:fresh  # recrear la base desde cero
docker compose logs -f app                         # ver logs
docker compose restart                             # reiniciar todo
```

> Después de instalar un paquete con Composer, o de crear una clase o una ruta
> nueva, reinicia el contenedor `app`. El opcache de PHP-FPM está configurado
> para no revalidar archivos, así que sigue sirviendo la versión anterior.

## Conectarse desde un dispositivo móvil

Un celular no puede alcanzar `localhost`. Usa la IP local de la máquina que
corre la API:

```bash
hostname -I
```

Y apunta la app a `http://<esa-ip>:8000/api/v1`. Ambos dispositivos deben estar
en la misma red.

En el emulador de Android, la dirección del host es `10.0.2.2`.

> La misma IP tiene que ir en `APP_URL` dentro del `.env`, o las URLs de las
> imágenes que devuelve la API van a apuntar a `localhost` y no se van a ver
> desde el celular.

## API

**Base URL:** `http://localhost:8000/api/v1`

Todas las respuestas son JSON. Envía el header `Accept: application/json`.
Las rutas protegidas requieren `Authorization: Bearer <token>`.

La referencia completa de endpoints está en [docs/api.md](docs/api.md), y la
documentación interactiva, donde puedes probar las peticiones, en
[`/docs/api`](http://localhost:8000/docs/api).