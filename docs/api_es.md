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
Actualmente no requiere autenticación. Puede añadirse middleware si se necesita.

### Paginación
Parámetro opcional `per_page` (1–100). Ejemplo: `GET /api/v1/schools?per_page=20`.

### Endpoints comunes

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

