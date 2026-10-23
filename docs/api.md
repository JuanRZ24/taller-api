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
| `GET` | [`/categories`](#get-categories) | Público | Lista el catálogo de categorías |
| `GET` | [`/courses`](#get-courses) | Público | Catálogo de cursos publicados, con filtros |
| `GET` | [`/courses/{course}`](#get-coursescourse) | Público · Token | Devuelve un curso |
| `POST` | [`/courses`](#post-courses) | Token · Instructor | Crea un curso en borrador |
| `GET` | [`/courses/me`](#get-coursesme) | Token | Cursos del instructor autenticado |
| `PATCH` | [`/courses/{course}`](#patch-coursescourse) | Token · Dueño | Modifica un curso |
| `DELETE` | [`/courses/{course}`](#delete-coursescourse) | Token · Dueño | Elimina un curso |
| `POST` | [`/courses/{course}/modules`](#post-coursescoursemodules) | Token · Dueño | Agrega un módulo al final del curso |
| `PATCH` | [`/modules/{module}`](#patch-modulesmodule) | Token · Dueño | Renombra un módulo |
| `DELETE` | [`/modules/{module}`](#delete-modulesmodule) | Token · Dueño | Elimina un módulo y sus contenidos |
| `PATCH` | [`/courses/{course}/modules/order`](#patch-coursescoursemodulesorder) | Token · Dueño | Reordena los módulos |
| `POST` | [`/modules/{module}/contents`](#post-modulesmodulecontents) | Token · Dueño | Agrega un contenido al módulo |
| `PATCH` | [`/contents/{content}`](#patch-contentscontent) | Token · Dueño | Modifica un contenido |
| `DELETE` | [`/contents/{content}`](#delete-contentscontent) | Token · Dueño | Elimina un contenido |
| `PATCH` | [`/modules/{module}/contents/order`](#patch-modulesmodulecontentsorder) | Token · Dueño | Reordena los contenidos del módulo |

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

---

### GET `/categories`

Lista el catálogo completo de categorías, ordenado por nombre.
**Ruta pública.**

Las categorías son un catálogo fijo que se siembra desde el seeder. No hay
endpoints para crearlas, editarlas ni borrarlas.

**Cuerpo de la petición**

No requiere cuerpo ni parámetros.

**Respuesta 200 OK**

```json
{
  "categories": [
    { "id": 3, "name": "Bases de datos" },
    { "id": 4, "name": "Ciencia de datos" },
    { "id": 2, "name": "Desarrollo móvil" },
    { "id": 1, "name": "Desarrollo web" },
    { "id": 5, "name": "Diseño" },
    { "id": 8, "name": "Idiomas" },
    { "id": 7, "name": "Negocios y marketing" },
    { "id": 6, "name": "Redes y seguridad" }
  ]
}
```

**La respuesta no está paginada.** El catálogo es fijo y corto, así que llega
completo en una sola petición.

**Los ids no vienen en orden.** El orden es alfabético por nombre, no por id.
No asumas que el id corresponde a la posición en la lista.

**Para qué sirve el `id`:** es el valor que se manda como `category_id` al
crear un curso. El desplegable del formulario muestra el `name` y envía el
`id`.

**Casos de error**

Este endpoint no tiene casos de error. Si no hay categorías sembradas,
devuelve 200 con un arreglo vacío.

**Ejemplo con curl**

```bash
curl -X GET http://localhost:8000/api/v1/categories \
  -H "Accept: application/json"
```
---

## Cursos

Un curso pasa por cuatro estados:

| Estado | Significado |
|---|---|
| `draft` | Borrador. Lo está armando el instructor. Es el estado inicial. |
| `pending` | Enviado a revisión. El instructor ya no lo puede modificar. |
| `published` | Aprobado y visible en el catálogo público. |
| `rejected` | Rechazado por un administrador. Vuelve a ser editable. |

**El estado nunca se envía en el formulario.** Todo curso nace en `draft` y
solo cambia mediante endpoints específicos.

Un curso solo se puede **modificar o eliminar** cuando está en `draft` o en
`rejected`. En `pending` y `published` está congelado, y eso incluye sus
módulos y contenidos.

El nivel acepta tres valores: `beginner`, `intermediate` y `advanced`. El
desplegable muestra el texto en español pero **envía el valor en inglés**.

---

### GET `/courses`

Catálogo de cursos publicados. **Ruta pública.**

Solo devuelve cursos en estado `published`. Los borradores y los pendientes
nunca aparecen aquí, ni siquiera para su dueño.

**Parámetros de consulta**

| Parámetro | Tipo | Reglas |
|---|---|---|
| `category_id` | entero | Opcional. Debe existir en el catálogo de categorías. |
| `level` | string | Opcional. `beginner`, `intermediate` o `advanced`. |
| `search` | string | Opcional. Busca en el título, sin distinguir mayúsculas. Máximo 100 caracteres. |
| `per_page` | entero | Opcional. Entre 1 y 50. Por omisión 12. |

**Respuesta 200 OK**

```json
{
  "courses": [
    {
      "id": 5,
      "title": "Laravel desde cero",
      "description": "Un curso completo de Laravel para quienes vienen de otros lenguajes.",
      "level": "beginner",
      "cover_url": null,
      "category": { "id": 2, "name": "Desarrollo móvil" },
      "instructor": { "id": 14, "name": "Juan Rios" }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 12,
    "total": 1
  }
}
```

Este listado **no incluye** `status` ni `rejection_reason`: el primero sería
redundante y el segundo es información privada entre el instructor y el
administrador.

**Ejemplo con curl**

```bash
curl -X GET "http://localhost:8000/api/v1/courses?level=beginner&category_id=2" \
  -H "Accept: application/json"
```

---

### GET `/courses/{course}`

Devuelve un curso con su temario.

**Acceso mixto.** Si el curso está `published`, cualquiera lo puede ver, sin
token. En cualquier otro estado, solo su dueño.

**El temario se recorta según quién pregunta.** Todos ven la lista de módulos
y los títulos de las lecciones; solo el dueño del curso ve el material
(`body`, `url`, `file_url`). Un visitante puede evaluar si le interesa el
curso, pero no consumirlo sin inscribirse.

**Respuesta 200 OK — cualquiera**

```json
{
  "course": {
    "id": 3,
    "title": "APIs REST con PHP moderno",
    "description": "Diseño de APIs que no se rompen: versionado, códigos de estado, formatos de error uniformes y documentación generada desde el código.",
    "level": "intermediate",
    "status": "published",
    "rejection_reason": null,
    "cover_url": null,
    "category": { "id": 1, "name": "Desarrollo web" },
    "instructor": {
      "id": 5,
      "name": "Diego Morales",
      "profile": {
        "headline": "Desarrollador Android | Kotlin y Java",
        "specialty": "Desarrollo móvil",
        "biography": "Llevo seis años construyendo aplicaciones Android para empresas de logística.",
        "photo_url": null
      }
    },
    "created_at": "2026-10-14T20:02:14.000000Z",
    "modules": [
      {
        "id": 5,
        "title": "Diseño de la API",
        "position": 1,
        "contents": [
          { "id": 11, "title": "Sustantivos, no verbos", "type": "text", "position": 1 },
          { "id": 12, "title": "Códigos de estado que importan", "type": "text", "position": 2 }
        ]
      }
    ]
  }
}
```

**Respuesta 200 OK — el dueño del curso**

Idéntica, salvo que cada contenido trae además su material:

```json
{
  "id": 11,
  "title": "Sustantivos, no verbos",
  "type": "text",
  "position": 1,
  "body": "La ruta dice qué cosa y el método HTTP dice qué le haces.",
  "url": null,
  "file_url": null
}
```

**Campos del curso**

| Campo | Notas |
|---|---|
| `instructor.id` | Id del usuario, para enlazar a su perfil |
| `instructor.profile` | `null` si el instructor no tiene perfil |
| `modules` | Ordenados por `position`. Arreglo vacío si no hay |
| `modules[].contents` | Ordenados por `position` |

**Campos del contenido**

| Campo | Quién lo ve |
|---|---|
| `id`, `title`, `type`, `position` | Todos |
| `body` | Solo el dueño. Texto de la lección cuando `type` es `text` |
| `url` | Solo el dueño. Enlace cuando `type` es `link` o `video` |
| `file_url` | Solo el dueño. Archivo cuando `type` es `file` |

Las tres llaves **se omiten**, no vienen en `null`. Si el cliente las necesita,
tiene que comprobar que existan.

**Casos de error**

| Situación | Código |
|---|---|
| El curso no existe | 404 |
| El `{course}` no es un número | 404 |
| El curso no está publicado y no eres su dueño | 403 |

**Ejemplo con curl**

```bash
curl http://localhost:8000/api/v1/courses/3 \
  -H "Accept: application/json"
```

Con token, para ver el material si eres el dueño:

```bash
curl http://localhost:8000/api/v1/courses/3 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```
---

### POST `/courses`

Crea un curso a nombre del instructor autenticado. **Requiere token.**
Solo usuarios con rol `instructor`.

El curso se crea en estado `draft`, sin módulos y sin portada.

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `title` | string | Requerido. Máximo 150 caracteres. |
| `description` | string | Requerido. Entre 100 y 5000 caracteres. |
| `category_id` | entero | Requerido. Debe existir en el catálogo. |
| `level` | string | Requerido. `beginner`, `intermediate` o `advanced`. |

El endpoint **no acepta** `user_id`, `status` ni `cover_path`. El dueño sale
del token y el estado lo decide el servidor.

**Ejemplo**

```json
{
  "title": "Laravel desde cero",
  "description": "Un curso completo de Laravel para quienes vienen de otros lenguajes y quieren entender el framework a fondo.",
  "category_id": 2,
  "level": "beginner"
}
```

**Respuesta 201 Created**

```json
{
  "course": {
    "id": 11,
    "title": "Laravel desde cero",
    "description": "Un curso completo de Laravel...",
    "level": "beginner",
    "status": "draft",
    "rejection_reason": null,
    "cover_url": null,
    "category": { "id": 2, "name": "Desarrollo móvil" },
    "created_at": "2026-10-10T03:14:22.000000Z"
  }
}
```

**Casos de error**

| Situación | Código | Campo en `errors` |
|---|---|---|
| Sin token o token revocado | 401 | — |
| El usuario no es instructor | 403 | — |
| Descripción menor a 100 caracteres | 422 | `description` |
| Categoría inexistente | 422 | `category_id` |
| Nivel fuera de los tres valores | 422 | `level` |

**Ejemplo con curl**

```bash
curl -X POST http://localhost:8000/api/v1/courses \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "title": "Laravel desde cero",
    "description": "Un curso completo de Laravel para quienes vienen de otros lenguajes y quieren entender el framework a fondo.",
    "category_id": 2,
    "level": "beginner"
  }'
```

---

### GET `/courses/me`

Lista paginada de los cursos del instructor autenticado, del más reciente al
más antiguo. **Requiere token.**

Incluye **todos los estados**, porque es la pantalla desde la que el
instructor administra sus cursos.

**Parámetros de consulta**

No acepta filtros por el momento.

**Respuesta 200 OK**

```json
{
  "courses": [
    {
      "id": 11,
      "title": "Laravel desde cero",
      "description": "Un curso completo de Laravel...",
      "level": "beginner",
      "status": "draft",
      "rejection_reason": null,
      "cover_url": null,
      "category": { "id": 2, "name": "Desarrollo móvil" },
      "created_at": "2026-10-10T03:14:22.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 10,
    "total": 14
  }
}
```

Un usuario que no sea instructor recibe **200 con la lista vacía**, no un 403.
No tiene cursos, y eso no es un error.

**Ejemplo con curl**

```bash
curl -X GET http://localhost:8000/api/v1/courses/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

---

### PATCH `/courses/{course}`

Modifica un curso. **Requiere token.** Solo su dueño, y solo si está en
`draft` o en `rejected`.

**Es una actualización parcial:** envía únicamente los campos que quieras
cambiar. Los que no mandes se quedan como están.

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `title` | string | Opcional. Si viene, máximo 150 y no vacío. |
| `description` | string | Opcional. Si viene, entre 100 y 5000. |
| `category_id` | entero | Opcional. Si viene, debe existir. |
| `level` | string | Opcional. Si viene, uno de los tres valores. |

**Ejemplo**

```json
{
  "title": "Laravel desde cero (edición 2026)"
}
```

**Respuesta 200 OK**

Devuelve el curso completo, con el mismo formato que `POST /courses`.

**Respuesta 403 Forbidden**

```json
{
  "message": "No puedes modificar este curso."
}
```

El mensaje es el mismo cuando no eres el dueño y cuando el curso está
congelado, a propósito: distinguirlos revelaría que el curso existe y es de
alguien más.

**Casos de error**

| Situación | Código | Campo en `errors` |
|---|---|---|
| Sin token | 401 | — |
| No eres el dueño | 403 | — |
| El curso está en `pending` o `published` | 403 | — |
| El curso no existe | 404 | — |
| Un campo enviado viene vacío | 422 | el campo |

**Ejemplo con curl**

```bash
curl -X PATCH http://localhost:8000/api/v1/courses/11 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"title": "Laravel desde cero (edición 2026)"}'
```

---

### DELETE `/courses/{course}`

Elimina un curso. **Requiere token.** Solo su dueño, y solo si está en
`draft` o en `rejected`.

**El borrado es definitivo y arrastra todo lo que cuelga del curso:** sus
módulos, los contenidos de cada módulo y su imagen de portada. No hay papelera
ni forma de recuperarlo.

La interfaz debe pedir confirmación.

**Cuerpo de la petición**

No requiere cuerpo.

**Respuesta 204 No Content**

Sin contenido. Una petición posterior a `GET /courses/{course}` devuelve 404.

**Casos de error**

| Situación | Código |
|---|---|
| Sin token | 401 |
| No eres el dueño | 403 |
| El curso está en `pending` o `published` | 403 |
| El curso no existe | 404 |

**Ejemplo con curl**

```bash
curl -X DELETE http://localhost:8000/api/v1/courses/11 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```
---

## Módulos y contenidos

Un curso se estructura en **módulos**, y cada módulo contiene **contenidos**.
No existe un contenido suelto: siempre pertenece a un módulo.

**Todos estos endpoints autorizan contra el curso**, no contra el módulo ni el
contenido. La propiedad vive en el curso: modificar un contenido es modificar
el curso al que pertenece. En la práctica eso significa dos cosas:

- Solo el dueño del curso puede tocar su estructura.
- Si el curso está en `pending` o `published`, **toda su estructura queda
  congelada**: no admite módulos nuevos, ni ediciones, ni reordenamientos.

El 403 de todos ellos devuelve el mismo mensaje, `No puedes modificar este
curso.`, sin distinguir entre "no eres el dueño" y "está congelado".

**La estructura completa se lee con [`GET /courses/{course}`](#get-coursescourse)**,
que devuelve los módulos con sus contenidos anidados. No hay un endpoint
aparte para listarlos.

### Tipos de contenido

| Tipo | Campo que exige | Disponible |
|---|---|---|
| `text` | `body` | Sí |
| `link` | `url` | Sí |
| `video` | `url` (enlace de YouTube o Vimeo) | Sí |
| `file` | archivo subido | **No, en un sprint posterior** |

El tipo `file` existe en el modelo y en la base de datos, pero **los endpoints
lo rechazan** mientras no exista la subida de archivos. Enviarlo devuelve 422.

El campo que no corresponde al tipo **está prohibido**, no solo es opcional:
enviar `body` junto con `type: video` devuelve 422. Y al cambiar el tipo de un
contenido, los campos que dejan de aplicar se limpian automáticamente.

---

### POST `/courses/{course}/modules`

Agrega un módulo **al final** del curso. **Requiere token.**

La posición la calcula el servidor; no se puede elegir. Para cambiarla, usa
[`PATCH /courses/{course}/modules/order`](#patch-coursescoursemodulesorder).

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `title` | string | Requerido. Máximo 150 caracteres. |

**Respuesta 201 Created**

```json
{
  "module": {
    "id": 3,
    "title": "Inyección SQL",
    "position": 2,
    "contents": []
  }
}
```

**Casos de error**

| Situación | Código |
|---|---|
| Sin token | 401 |
| No eres el dueño del curso | 403 |
| El curso está en `pending` o `published` | 403 |
| El curso no existe | 404 |
| Título vacío o mayor a 150 | 422 |

**Ejemplo con curl**

```bash
curl -X POST http://localhost:8000/api/v1/courses/13/modules \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"title": "Inyección SQL"}'
```

---

### PATCH `/modules/{module}`

Renombra un módulo. **Requiere token.**

Solo cambia el título. La posición se modifica con el endpoint de
reordenamiento.

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `title` | string | Requerido. Máximo 150 caracteres. |

**Respuesta 200 OK**

Mismo formato que `POST /courses/{course}/modules`.

**Casos de error**

| Situación | Código |
|---|---|
| Sin token | 401 |
| No eres el dueño del curso | 403 |
| El curso está congelado | 403 |
| El módulo no existe | 404 |
| Título vacío o mayor a 150 | 422 |

**Ejemplo con curl**

```bash
curl -X PATCH http://localhost:8000/api/v1/modules/3 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"title": "Inyección SQL y XSS"}'
```

---

### DELETE `/modules/{module}`

Elimina un módulo. **Requiere token.**

> **Se lleva todos sus contenidos.** El borrado es definitivo y no hay
> papelera. La interfaz debe pedir confirmación indicando cuántos contenidos
> se van a perder.

Las posiciones de los módulos restantes **no se recalculan**: pueden quedar
huecos (1, 3, 4). No importa, porque lo único que se usa es el orden relativo,
y el endpoint de reordenamiento los normaliza la próxima vez que se use.

**Respuesta 204 No Content**

**Casos de error**

| Situación | Código |
|---|---|
| Sin token | 401 |
| No eres el dueño del curso | 403 |
| El curso está congelado | 403 |
| El módulo no existe | 404 |

**Ejemplo con curl**

```bash
curl -X DELETE http://localhost:8000/api/v1/modules/3 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

---

### PATCH `/courses/{course}/modules/order`

Reordena los módulos de un curso. **Requiere token.**

**No se envía un movimiento, se envía el orden completo.** El cliente manda
todos los ids en el orden deseado y el servidor reescribe las posiciones
empezando en 1.

Eso significa que sirve igual para flechas de subir y bajar que para arrastrar
y soltar: la interfaz reacomoda su arreglo local y envía la lista. Y de paso
normaliza los huecos que hayan dejado los borrados.

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `modules` | arreglo de enteros | Requerido. Debe contener **exactamente** todos los módulos del curso, sin repetir ni omitir ninguno. |

**Ejemplo**

```json
{ "modules": [5, 3, 4] }
```

**Respuesta 200 OK**

Devuelve los módulos con sus posiciones nuevas, leídas de la base de datos.

```json
{
  "modules": [
    { "id": 5, "title": "Manejo de sesiones", "position": 1, "contents": [] },
    { "id": 3, "title": "Inyección SQL", "position": 2, "contents": [] },
    { "id": 4, "title": "XSS", "position": 3, "contents": [] }
  ]
}
```

**Casos de error**

| Situación | Código | Mensaje de validación |
|---|---|---|
| Sin token | 401 | — |
| No eres el dueño o el curso está congelado | 403 | — |
| Faltan o sobran módulos en la lista | 422 | `must contain N items` |
| Un id repetido | 422 | `has a duplicate value` |
| Un módulo que no es de este curso | 422 | `the selected modules.N is invalid` |

Las tres reglas de validación garantizan, juntas, que la lista sea una
permutación exacta de los módulos del curso. La tercera es la importante: sin
ella se podría reordenar el curso de otra persona.

**Ejemplo con curl**

```bash
curl -X PATCH http://localhost:8000/api/v1/courses/13/modules/order \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"modules": [5, 3, 4]}'
```

---

### POST `/modules/{module}/contents`

Agrega un contenido **al final** del módulo. **Requiere token.**

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `title` | string | Requerido. Máximo 150 caracteres. |
| `type` | string | Requerido. `text`, `link` o `video`. El tipo `file` se rechaza. |
| `body` | string | Requerido **si** el tipo es `text`. Prohibido en cualquier otro caso. |
| `url` | string | Requerido **si** el tipo es `link` o `video`. Prohibido en cualquier otro caso. Debe ser una URL válida, máximo 500 caracteres. |

**Ejemplo**

```json
{
  "title": "Qué es la inyección SQL",
  "type": "text",
  "body": "Una inyección SQL ocurre cuando entrada del usuario se concatena en una consulta."
}
```

```json
{
  "title": "Demostración en video",
  "type": "video",
  "url": "https://youtube.com/watch?v=abc"
}
```

**Respuesta 201 Created**

```json
{
  "content": {
    "id": 2,
    "title": "Demostración en video",
    "type": "video",
    "position": 2,
    "body": null,
    "url": "https://youtube.com/watch?v=abc",
    "file_url": null
  }
}
```

Los tres campos de contenido —`body`, `url` y `file_url`— **vienen siempre en
la respuesta**, con valor o en `null` según el tipo. El cliente no tiene que
preguntar si existen.

**Casos de error**

| Situación | Código | Campo en `errors` |
|---|---|---|
| Sin token | 401 | — |
| No eres el dueño o el curso está congelado | 403 | — |
| El módulo no existe | 404 | — |
| Tipo `file` | 422 | `type` |
| Tipo `text` sin `body` | 422 | `body` |
| Tipo `video` o `link` sin `url` | 422 | `url` |
| `body` enviado con un tipo que no es `text` | 422 | `body` |
| `url` enviada con tipo `text` | 422 | `url` |
| `url` con formato inválido | 422 | `url` |

**Ejemplo con curl**

```bash
curl -X POST http://localhost:8000/api/v1/modules/3/contents \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"title": "Enlace a OWASP", "type": "link", "url": "https://owasp.org/Top10/"}'
```

---

### PATCH `/contents/{content}`

Modifica un contenido. **Requiere token.**

**No es una actualización parcial:** hay que enviar el contenido completo,
incluido el `type`. La razón es que el tipo determina qué campo aplica, así
que no tendría sentido cambiar el `body` sin saber si el contenido sigue
siendo de texto.

**Al cambiar el tipo, los campos que dejan de aplicar se limpian solos.** Si un
contenido era de tipo `video` con una `url` y lo cambias a `text`, la `url`
queda en `null` en la base de datos. No hay que mandarla vacía a mano.

**Cuerpo de la petición**

Las mismas reglas que `POST /modules/{module}/contents`.

**Respuesta 200 OK**

Mismo formato que la creación.

**Casos de error**

Los mismos que la creación, más 404 si el contenido no existe.

**Ejemplo con curl**

```bash
curl -X PATCH http://localhost:8000/api/v1/contents/2 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"title": "Ahora es texto", "type": "text", "body": "El contenido en texto plano."}'
```

---

### DELETE `/contents/{content}`

Elimina un contenido. **Requiere token.** El borrado es definitivo.

Igual que con los módulos, las posiciones restantes pueden quedar con huecos
hasta el próximo reordenamiento.

**Respuesta 204 No Content**

**Casos de error**

| Situación | Código |
|---|---|
| Sin token | 401 |
| No eres el dueño o el curso está congelado | 403 |
| El contenido no existe | 404 |

**Ejemplo con curl**

```bash
curl -X DELETE http://localhost:8000/api/v1/contents/2 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

---

### PATCH `/modules/{module}/contents/order`

Reordena los contenidos de un módulo. **Requiere token.**

Funciona igual que el reordenamiento de módulos: se envía la lista completa de
ids en el orden deseado.

**Cuerpo de la petición**

| Campo | Tipo | Reglas |
|---|---|---|
| `contents` | arreglo de enteros | Requerido. Debe contener **exactamente** todos los contenidos del módulo, sin repetir ni omitir ninguno. |

**Ejemplo**

```json
{ "contents": [3, 2] }
```

**Respuesta 200 OK**

```json
{
  "contents": [
    { "id": 3, "title": "Enlace a OWASP", "type": "link", "position": 1, "body": null, "url": "https://owasp.org/Top10/", "file_url": null },
    { "id": 2, "title": "Ahora es texto", "type": "text", "position": 2, "body": "El contenido en texto plano.", "url": null, "file_url": null }
  ]
}
```

**Casos de error**

| Situación | Código | Mensaje de validación |
|---|---|---|
| Sin token | 401 | — |
| No eres el dueño o el curso está congelado | 403 | — |
| Faltan o sobran contenidos | 422 | `must contain N items` |
| Un id repetido | 422 | `has a duplicate value` |
| Un contenido que no es de este módulo | 422 | `the selected contents.N is invalid` |

**Ejemplo con curl**

```bash
curl -X PATCH http://localhost:8000/api/v1/modules/3/contents/order \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"contents": [3, 2]}'
```