# Referencia de la API


**Base URL:** `http://localhost:8000/api/v1`

Todas las respuestas son JSON. Envía siempre el header `Accept: application/json`.

Las rutas protegidas requieren el token en el header:

```
Authorization: Bearer <token>
```

## Roles

| Valor | Descripción |
|---|---|
| `student` | Estudiante. Se inscribe a cursos y los consume. **Es el rol con el que nace toda cuenta nueva.** |
| `instructor` | Instructor. Crea y publica cursos. Lo asigna un administrador. |
| `admin` | Administrador. Gestiona cuentas y roles. Se crea desde el seeder inicial o lo otorga otro administrador. |

**El rol no se elige al registrarse.** Toda cuenta se crea como `student`; 

## Endpoints

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | [`/auth/register`](#post-authregister) | Público | Crea una cuenta y devuelve un token |
| `POST` | [`/auth/login`](#post-authlogin) | Público | Valida credenciales y devuelve un token |
| `POST` | [`/auth/logout`](#post-authlogout) | Token | Revoca el token utilizado |
| `GET` | [`/auth/me`](#get-authme) | Token | Devuelve el usuario autenticado |


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

El endpoint **no acepta un campo `role`**. Si se envía, se ignora por completo y
la cuenta se crea igualmente como `student`.

**Ejemplo**

```json
{
  "name": "Juan Rios",
  "email": "juan@ejemplo.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Respuesta 201 Created**

```json
{
  "user": {
    "id": 3,
    "name": "Juan Rios",
    "email": "juan@ejemplo.com",
    "role": "student"
  },
  "token": "3|Roy8JVjKSwluxmYgy6NvYE2ktLbcLnC6oZaOGVwj499962b2"
}
```

El token se devuelve **una sola vez**, en esta respuesta. Guárdalo: no se puede
recuperar después.

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
    "password_confirmation": "password123"
  }'
```

---

### POST `/auth/login`

Inicia sesión con las credenciales del usuario y devuelve un token de acceso.
**Ruta pública.**

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `email` | string | Requerido. Formato de correo válido. |
| `password` | string | Requerido. |

**Ejemplo**

```json
{
  "email": "juan@ejemplo.com",
  "password": "password123"
}
```

**Respuesta 200 OK**

```json
{
  "user": {
    "id": 1,
    "name": "Juan Prueba",
    "email": "juan@test.com",
    "role": "student"
  },
  "token": "3|ckkQQIycud9oxofuTNMXHGl2DSXGMwVbRsQkUKMM81afc5cd"
}
```

**Casos de error**

| Situación | Código | Mensaje |
|---|---|---|
| Correo inexistente | 401 | `Credenciales incorrectas.` |
| Contraseña incorrecta | 401 | `Credenciales incorrectas.` |
| Cuenta inactiva | 403 | `Cuenta no activa.` |
| Datos de login inválidos | 422 | Error de validación |

El correo inexistente y la contraseña incorrecta devuelven **el mismo mensaje**,
para no revelar si una cuenta está registrada.

**Ejemplo con curl**

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@ejemplo.com",
    "password": "password123"
  }'
```

---

### POST `/auth/logout`

Cierra la sesión del usuario autenticado y revoca **únicamente el token utilizado
en la petición**. Otros tokens activos del mismo usuario siguen siendo válidos.
**Ruta protegida.**

**Autenticación**

```
Authorization: Bearer <token>
```

**Cuerpo de la petición**

No requiere cuerpo.

**Respuesta 204 No Content**

La petición se procesa correctamente y no devuelve contenido.

**Casos de error**

| Situación | Código |
|---|---|
| Token inválido o ausente | 401 |
| Token ya revocado | 401 |

**Ejemplo con curl**

```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

---

### GET `/auth/me`

Obtiene la información del usuario autenticado. **Ruta protegida.**

La información se obtiene a partir del token: el endpoint no recibe el id del
usuario.

**Autenticación**

```
Authorization: Bearer <token>
```

**Cuerpo de la petición**

No requiere cuerpo.

**Respuesta 200 OK**

```json
{
  "user": {
    "id": 1,
    "name": "Juan Prueba",
    "email": "juan@test.com",
    "role": "student"
  }
}
```

**Casos de error**

| Situación | Código |
|---|---|
| Token inválido | 401 |
| Token ausente | 401 |
| Token revocado | 401 |

**Ejemplo con curl**

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```