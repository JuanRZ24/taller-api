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

Todas las respuestas son JSON. Envía siempre el header `Accept: application/json`.

Las rutas protegidas requieren el token en el header:

```
Authorization: Bearer <token>
```

### Roles

| Valor | Descripción |
|---|---|
| `student` | Estudiante. Se inscribe a cursos y los consume. |
| `instructor` | Instructor. Crea y publica cursos. |
| `admin` | Administrador. **No puede elegirse al registrarse.** Se crea desde el seeder inicial o lo otorga otro administrador. |

---

### POST `/auth/register`

Crea una cuenta y devuelve un token de acceso. **Ruta pública.**

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `name` | string | Requerido. Máximo 255 caracteres. |
| `email` | string | Requerido. Formato de correo válido. Máximo 255. Único en el sistema. |
| `password` | string | Requerido. Mínimo 8 caracteres. |
| `password_confirmation` | string | Requerido. Debe coincidir con `password`. |
| `role` | string | Requerido. Solo `student` o `instructor`. |

**Ejemplo**

```json
{
  "name": "Juan Rios",
  "email": "juan@ejemplo.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "instructor"
}
```

**Respuesta 201 Created**

```json
{
  "user": {
    "id": 3,
    "name": "Juan Rios",
    "email": "juan@ejemplo.com",
    "role": "instructor"
  },
  "token": "3|Roy8JVjKSwluxmYgy6NvYE2ktLbcLnC6oZaOGVwj499962b2"
}
```

El token se devuelve **una sola vez**, en esta respuesta. Guárdalo: no se puede recuperar después.

**Respuesta 422 Unprocessable Content**

Cuando la validación falla. Formato uniforme para todos los endpoints:

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

**Casos de error**

| Situación | Código | Campo en `errors` |
|---|---|---|
| Correo ya registrado | 422 | `email` |
| `role` con valor `admin` | 422 | `role` |
| `role` con valor no reconocido | 422 | `role` |
| Contraseña sin confirmación o no coincide | 422 | `password` |
| Contraseña menor a 8 caracteres | 422 | `password` |
| Campo requerido faltante | 422 | el campo correspondiente |

**Ejemplo con curl**

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Rios",
    "email": "juan@ejemplo.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "instructor"
  }'
```

---

### Pendientes

- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`