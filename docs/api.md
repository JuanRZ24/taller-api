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
| `instructor` | Instructor. Crea cursos y los envía a revisión. Cualquier estudiante puede darse de alta completando su perfil público. |
| `admin` | Administrador. Gestiona cuentas y aprueba cursos. Se crea desde el seeder inicial. |

**El rol no se elige al registrarse.** Toda cuenta se crea como `student`; para
volverse `instructor`, el propio usuario se da de alta en
[`POST /instructors`](#post-instructors) completando su perfil público. El rol
`admin` solo se crea desde el seeder inicial.

## Endpoints

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | [`/auth/register`](#post-authregister) | Público | Crea una cuenta y devuelve un token |
| `POST` | [`/auth/login`](#post-authlogin) | Público | Valida credenciales y devuelve un token |
| `POST` | [`/auth/logout`](#post-authlogout) | Token | Revoca el token utilizado |
| `GET` | [`/auth/me`](#get-authme) | Token | Devuelve el usuario autenticado |
| `POST` | [`/instructors`](#post-instructors) | Token · Estudiante | Convierte al usuario autenticado en instructor |
| `POST` | [`/instructors/me/photo`](#post-instructorsmephoto) | Token · Instructor | Sube o reemplaza la foto de perfil del instructor |

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

Este endpoint **no incluye el perfil de instructor**, aunque el usuario lo
tenga. Si el `role` es `instructor`, el perfil existe por definición y se
consulta aparte.

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

---

### POST `/instructors`

Convierte al usuario autenticado en instructor, creando su perfil público en la
misma operación. **Requiere token.**

Solo un usuario con rol `student` puede usar este endpoint. El perfil es
obligatorio: no existe un instructor sin perfil.

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `headline` | string | Requerido. Máximo 150 caracteres. |
| `specialty` | string | Requerido. Máximo 100 caracteres. |
| `biography` | string | Requerido. Entre 50 y 2000 caracteres. |

El endpoint **no acepta un campo `user_id`**. El dueño del perfil se toma del
token, nunca del cuerpo de la petición.

**Ejemplo**

```json
{
  "headline": "Ingeniero de software | 10 años enseñando backend",
  "specialty": "Desarrollo web",
  "biography": "Trabajo con Laravel desde 2018 y he dado clases de backend a más de mil alumnos en línea."
}
```

**Respuesta 201 Created**

```json
{
  "user": {
    "id": 14,
    "name": "Juan Rios",
    "email": "juan@ejemplo.com",
    "role": "instructor"
  },
  "profile": {
    "headline": "Ingeniero de software | 10 años enseñando backend",
    "specialty": "Desarrollo web",
    "biography": "Trabajo con Laravel desde 2018 y he dado clases de backend a más de mil alumnos en línea.",
    "photo_url": null
  }
}
```

El campo `photo_url` viene en `null` porque la foto se sube aparte, en
[`POST /instructors/me/photo`](#post-instructorsmephoto). El campo **siempre
está presente** en la respuesta, con valor o en `null`.

La respuesta incluye el usuario porque **su rol acabó de cambiar**. El cliente
debe guardar el rol nuevo y actualizar la interfaz con él, sin necesidad de
pedir `/auth/me`.

**El token sigue siendo válido.** No hace falta volver a iniciar sesión: el rol
se lee del usuario en cada petición, no del token.

**Respuesta 403 Forbidden**

```json
{
  "message": "Solo un estudiante puede darse de alta como instructor."
}
```

**Respuesta 422 Unprocessable Content**

```json
{
  "message": "The biography field must be at least 50 characters.",
  "errors": {
    "biography": ["The biography field must be at least 50 characters."]
  }
}
```

**Casos de error**

| Situación | Código | Campo en `errors` |
|---|---|---|
| Sin token o token revocado | 401 | — |
| El usuario ya es instructor | 403 | — |
| El usuario es administrador | 403 | — |
| Biografía menor a 50 caracteres | 422 | `biography` |
| Titular mayor a 150 caracteres | 422 | `headline` |
| Campo requerido faltante | 422 | el campo correspondiente |

**Ejemplo con curl**

```bash
curl -X POST http://localhost:8000/api/v1/instructors \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "headline": "Ingeniero de software | 10 años enseñando backend",
    "specialty": "Desarrollo web",
    "biography": "Trabajo con Laravel desde 2018 y he dado clases de backend a más de mil alumnos en línea."
  }'
```

---

### POST `/instructors/me/photo`

Sube o reemplaza la foto de perfil del instructor autenticado.
**Requiere token.** Solo un usuario con rol `instructor` puede usarlo.

**Este endpoint no recibe JSON.** El cuerpo tiene que ser
`multipart/form-data`, porque lleva un archivo.

> **No establezcas el encabezado `Content-Type` a mano.** Multipart necesita un
> separador que el cliente genera solo. En `fetch` con `FormData` y en curl con
> `-F` se pone automáticamente; si lo escribes tú, el servidor no puede leer el
> cuerpo y responde 422 diciendo que no mandaste foto.

**Autenticación**

```
Authorization: Bearer <token>
```

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `photo` | archivo | Requerido. Imagen `jpg`, `jpeg`, `png` o `webp`. Máximo 4 MB. Mínimo 200×200 píxeles. |

El método tiene que ser `POST`, no `PUT` ni `PATCH`: PHP solo interpreta
`multipart/form-data` en peticiones `POST`.

**Respuesta 200 OK**

```json
{
  "profile": {
    "headline": "Ingeniero de software | 10 años enseñando backend",
    "specialty": "Desarrollo web",
    "biography": "Trabajo con Laravel desde 2018 y he dado clases de backend a más de mil alumnos en línea.",
    "photo_url": "http://localhost:8000/storage/instructors/yapGZ9cNobPTzbPTOATy6biwtJWuUZexYKIeroSn.jpg"
  }
}
```

La foto anterior se borra del servidor automáticamente. El nombre del archivo
lo genera el servidor; el nombre original que subió el usuario se descarta.

**El dominio de `photo_url` depende de la variable `APP_URL`** de quien levante
la API. Si la consumes desde un emulador o un teléfono, `localhost` no va a
resolver: quien levante el servidor tiene que poner su IP de red local en
`APP_URL`.

**Respuesta 403 Forbidden**

```json
{
  "message": "Solo un instructor puede cambiar su foto de perfil."
}
```

**Respuesta 422 Unprocessable Content**

```json
{
  "message": "The photo field must be an image.",
  "errors": {
    "photo": ["The photo field must be an image."]
  }
}
```

**Casos de error**

| Situación | Código | Campo en `errors` |
|---|---|---|
| Sin token o token revocado | 401 | — |
| El usuario no es instructor | 403 | — |
| El archivo no es una imagen | 422 | `photo` |
| Formato distinto a jpg, jpeg, png o webp | 422 | `photo` |
| Imagen mayor a 4 MB | 422 | `photo` |
| Imagen menor a 200×200 píxeles | 422 | `photo` |
| No se envió el campo `photo` | 422 | `photo` |

**Ejemplo con curl**

```bash
curl -X POST http://localhost:8000/api/v1/instructors/me/photo \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>" \
  -F "photo=@/ruta/a/foto.jpg"
```