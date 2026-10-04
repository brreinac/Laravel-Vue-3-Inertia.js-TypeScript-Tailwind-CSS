# QVOX Task Manager

Aplicación de gestión de tareas para equipos desarrollada para la prueba técnica Full-Stack de QVOX. Combina Laravel como API protegida por JWT con una SPA en Vue 3, Inertia, TypeScript y Tailwind CSS.

## Arquitectura

- **Backend:** Laravel 13, Eloquent, Form Requests, Policies, JWT, Events, Listener en cola y Notifications.
- **Frontend:** Vue 3 con Composition API y `<script setup lang="ts">`, Inertia, Pinia y componentes reutilizables.
- **Base de datos:** MySQL 8 con `utf8mb4`, claves foráneas e índices para los filtros principales.
- **Tiempo real:** Laravel Reverb publica cambios de estado en canales privados de proyecto; Laravel Echo actualiza el Kanban sin recarga.

La API concentra la validación y autorización. El frontend ofrece una buena experiencia, pero no sustituye ninguna regla de permisos del backend.

## Requisitos

- PHP 8.3 o superior con extensiones `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer` y `xml`.
- Composer 2.
- Node.js 20 o superior.
- pnpm 11 o superior.
- MySQL 8.0 o superior. La aplicación usa explícitamente el driver `mysql`; no requiere MariaDB, Oracle ni SQL Server.

## Instalación

```bash
git clone <url-del-repositorio>
cd Laravel-Vue-3-Inertia.js-TypeScript-Tailwind-CSS
copy .env.example .env
composer install
pnpm install --frozen-lockfile
php artisan key:generate
php artisan jwt:secret
```

Cree una base de datos MySQL con `utf8mb4` y configure en `.env` `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. Después ejecute:

```bash
php artisan migrate --seed
```

Para desarrollo local, use terminales separadas:

```bash
php artisan serve
pnpm dev
php artisan queue:work
php artisan reverb:start
```

`QUEUE_CONNECTION=database`, `MAIL_MAILER=log` y `BROADCAST_CONNECTION=reverb` ya están definidos en `.env.example`. El worker procesa el registro de actividad y la notificación por correo usando el log local. Reverb usa el protocolo compatible con Pusher, pero no requiere una cuenta ni servicio de Pusher.

## Credenciales de prueba

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Admin | `admin@qvox.local` | `password` |
| Member | `mateo@qvox.local` | `password` |
| Member | `sofia@qvox.local` | `password` |

## API principal

| Método | Ruta | Descripción |
| --- | --- | --- |
| `POST` | `/login` | Genera el token JWT. |
| `POST` | `/api/logout` | Invalida el token actual. |
| `GET` | `/api/auth/me` | Devuelve el usuario autenticado. |
| `GET` | `/api/projects` | Lista proyectos con búsqueda, estado y paginación. |
| `POST/PATCH/DELETE` | `/api/projects` | Administración de proyectos (solo admin). El `DELETE` archiva. |
| `GET` | `/api/tasks` | Lista tareas con filtros `status`, `priority`, `project_id`, `assigned_to`, `search` y paginación. |
| `POST/PATCH/DELETE` | `/api/tasks` | CRUD de tareas; crear y eliminar son acciones de admin. |
| `PATCH` | `/api/tasks/{task}/status` | Cambio de estado con reglas de transición. |
| `POST` | `/api/tasks/{task}/comments` | Agrega un comentario autorizado. |
| `GET` | `/api/tasks/export` | Exporta CSV respetando los filtros. |
| `GET` | `/api/search?q=...` | Búsqueda global de proyectos, tareas y usuarios. |

Las transiciones permitidas se centralizan en `ChangeTaskStatus`:

```text
pendiente → en_progreso
en_progreso → pendiente | en_revision
en_revision → en_progreso | completada
completada → en_revision
```

Al completar una tarea se fija `completed_at`; al volver a un estado abierto se limpia. Solo se publica el evento cuando el estado cambia realmente.

## Autorización

- **Admin:** administra proyectos, crea/asigna/elimina tareas y consulta todos los registros.
- **Member:** solo visualiza, actualiza el contenido, cambia el estado y comenta sus tareas asignadas.
- Las Policies protegen los recursos; `role:admin` protege también el directorio de usuarios. Las comprobaciones no dependen del cliente.

## Frontend y funcionalidades extra

- Dashboard con totales, tareas próximas a vencer y gráfica de distribución por estado con Chart.js.
- Búsqueda global con debounce de 300 ms.
- Exportación streaming a CSV, sin cargar todo el resultado en memoria.
- Tema oscuro persistente en `localStorage`.
- Kanban con HTML5 Drag and Drop. El tablero usa el mismo endpoint de transición, por lo que no puede eludir reglas del backend.
- Reverb + Echo mediante canales privados `projects.{projectId}` para sincronizar cambios de estado entre sesiones.

## Estructura relevante

```text
app/
  Actions/            reglas de negocio reutilizables
  Events/Listeners/   cambio de estado, bitácora y notificación en cola
  Http/               controladores, requests, resources y middleware
  Models/Policies/    entidades, relaciones y autorización
database/             migraciones, factories y seeder realista
resources/js/         páginas Inertia, componentes, store, tipos y composables
routes/               API JWT, páginas Inertia y canales de broadcasting
tests/Feature/        pruebas de tareas y autorización
```

## Tests

Se crearon seis pruebas Feature para creación de tareas, validación, transición válida e inválida, autorización administrativa y comentarios autorizados.

**Los tests NO fueron ejecutados durante esta implementación.**

Para ejecutarlos en un entorno con PHP, Composer y una base de datos MySQL de pruebas configurada:

```bash
php artisan test
```

## Decisiones técnicas

- Se eligió `php-open-source-saver/jwt-auth`, una implementación JWT mantenida y compatible con Laravel 13, en lugar de construir tokens manualmente.
- Se usa una acción única para cambios de estado para evitar reglas duplicadas entre controladores, Kanban y eventos.
- Los listados se paginan y se cargan con relaciones explícitas para evitar N+1.
- Se utiliza CSV nativo con `cursor()` para cumplir la exportación sin introducir una dependencia XLSX pesada.
- La cola y el correo usan drivers locales (`database` y `log`) para no depender de credenciales externas.

## Limitación de esta entrega

El equipo de trabajo usado para preparar esta implementación no tiene PHP ni Composer instalados y la consigna prohíbe instalar software del sistema. Por ello no se generó `composer.lock`, no se instaló `vendor` y no se ejecutó Laravel, las migraciones, el worker, Reverb, el build ni los tests. El lockfile de pnpm sí fue resuelto una sola vez; no se ejecutó ningún build ni test de frontend.
