
## API genérica (CRUD) — Español

Base: `/api/v1/{recurso}`

### Recursos disponibles
- `schools` → tabla `schools`
- `subjects` → `subject`
- `grades` → `grade`
- `weeks` → `week`
- `topics` → `topic`
- `components` → `component`
- `didactic-units` → `didactic_unit`
- `standards` → `standard`
- `competences` → `competence`
- `tipe-competences` → `tipe_competence`
- `affirmations-dna-dba` → `affirmation_dna_dba`
- `evidences-dna-dba` → `evidence_dna_dba`
- `activities` → `activity`

Excluidos: `schedule` (cronograma) y tablas pivote/intermedias.

### Autenticación
Los endpoints protegidos requieren token Bearer (Sanctum).

Públicos:
- POST `/api/v1/auth/register` → registra usuario y devuelve token
- POST `/api/v1/auth/login` → devuelve token

Protegidos (header `Authorization: Bearer {TOKEN}`):
- GET `/api/v1/auth/me` → usuario autenticado con roles
- POST `/api/v1/auth/logout` → invalida el token actual

### Paginación
Parámetro opcional `per_page` (1–100). Ejemplo: `GET /api/v1/schools?per_page=20`.

### Endpoints comunes

Cabeceras para endpoints protegidos: `Authorization: Bearer {TOKEN}`

1) Listar
```
GET /api/v1/{recurso}
```
Respuesta: JSON paginado.

2) Obtener por ID
```
GET /api/v1/{recurso}/{id}
```
Respuesta: objeto JSON.

3) Crear
```
POST /api/v1/{recurso}
Content-Type: application/json
```
Cuerpo: campos de la tabla (sin `id`, `created_at`, `updated_at`).
Respuesta: objeto creado (201 Created).

4) Actualizar
```
PUT /api/v1/{recurso}/{id}
PATCH /api/v1/{recurso}/{id}
Content-Type: application/json
```
Respuesta: objeto actualizado.

5) Eliminar
```
DELETE /api/v1/{recurso}/{id}
```
Respuesta: 204 No Content.

### Esquemas de datos (campos por tabla)

- `schools`
  - id, name, nit, resolution, phone, address, created_at, updated_at

- `subject`
  - id, name, description, created_at, updated_at

- `grade`
  - id, name, description, created_at, updated_at

- `week`
  - id, week_number, month, description, created_at, updated_at

- `topic`
  - id, name, description, created_at, updated_at

- `component`
  - id, name, description, created_at, updated_at

- `didactic_unit`
  - id, name, description, created_at, updated_at

- `standard`
  - id, name, description, created_at, updated_at

- `tipe_competence`
  - id, name, created_at, updated_at

- `competence`
  - id, description, tipe_competence_id, created_at, updated_at

- `affirmation_dna_dba`
  - id, name, description, created_at, updated_at

- `evidence_dna_dba`
  - id, name, description, created_at, updated_at

- `activity`
  - id, name, description, resource_url, created_at, updated_at

Nota: Campos foráneos se deben proveer con IDs válidos donde apliquen (por ejemplo, `tipe_competence_id` al crear `competence`).

### Errores comunes
- 404 Not Found: recurso no permitido o id inexistente.
- 422 Unprocessable Entity: si agregas validación posteriormente.
- 500 Server Error: errores en base de datos, claves foráneas, etc.

### Cómo extender/limitar recursos
Editar `App/Http/Controllers/Api/GenericCrudController.php`, constante `RESOURCE_TO_TABLE`.

### Usuarios y Roles (CRUD protegidos)

- Usuarios: `/api/v1/users`
  - Crear: body JSON con `name`, `email`, `password`, `roles` (array de IDs opcional)
  - Listar/Ver/Actualizar/Eliminar soportados
  - Devuelve el usuario con relación `roles`

- Roles: `/api/v1/roles`
  - Campos: `name`
  - Listar/Ver/Crear/Actualizar/Eliminar

### Roles y permisos (definición funcional)

- admin: acceso total a la API.
- coordinator: puede ver información, registrar usuarios con rol `student` y `teacher`, y crear cronograma.
- teacher: puede crear cronograma y ver información.
- student: solo puede ver información.

Nota: La tabla `schedule` (cronograma) está excluida del CRUD genérico. Su creación/gestión se expone por endpoints específicos (pueden agregarse con reglas de autorización por rol).

### Usuarios de prueba (seed)

Se crearon roles y usuarios iniciales:

- admin@example.com / username: `admin` / password: `password` → rol: `admin`
- coordinator@example.com / username: `coordinator` / password: `password` → rol: `coordinator`
- teacher@example.com / username: `teacher` / password: `password` → rol: `teacher`
- student@example.com / username: `student` / password: `password` → rol: `student`

Para ejecutar el seed:

```bash
php artisan db:seed
```

### Asignación de roles (solo admin)

Rutas protegidas que requieren `Authorization: Bearer {TOKEN}` de un usuario con rol `admin`:

- Reemplazar todos los roles del usuario:
```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/1/roles/sync \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"roles":[1,2]}'
```

- Agregar un rol adicional (sin quitar los existentes):
```bash
curl -X POST http://127.0.0.1:8000/api/v1/users/1/roles/attach \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"role_id":2}'
```

- Quitar un rol específico:
```bash
curl -X DELETE http://127.0.0.1:8000/api/v1/users/1/roles/2 \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### Ejemplos de uso (curl)

- Listar usuarios (protegido):
```bash
curl -X GET http://127.0.0.1:8000/api/v1/users \
  -H "Authorization: Bearer TOKEN_AQUI"
```

- Crear un subject (protegido):
```bash
curl -X POST http://127.0.0.1:8000/api/v1/subjects \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"name":"Matemáticas","description":"Básica"}'
```

### Ejemplos de autenticación (curl)

- Login (usuario seed admin):
```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

- Registrar usuario (campos requeridos: first_name, last_name, email, username, password):
```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name":"Ana",
    "last_name":"Pérez",
    "email":"ana@example.com",
    "username":"anap",
    "password":"password"
  }'
```

- Usar token para consultar perfil (reemplaza TOKEN_AQUI por el token devuelto por login/register):
```bash
curl http://127.0.0.1:8000/api/v1/auth/me \
  -H "Authorization: Bearer TOKEN_AQUI"
```

- Logout:
```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/logout \
  -H "Authorization: Bearer TOKEN_AQUI"
```

