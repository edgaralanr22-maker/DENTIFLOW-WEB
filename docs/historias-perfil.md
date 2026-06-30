# Modulo Perfil: Backend Y Base De Datos

Documento de referencia del estado backend/base del perfil de usuario.

## Funcionalidad Cubierta

**Perfil**
- Ver perfil: `GET /perfil`.
- Editar perfil: `GET /perfil/editar`.
- Guardar perfil: `POST /perfil`.
- Cambiar contrasena: `POST /perfil/password`.
- Actualizar agenda del doctor: `POST /perfil/agenda`.

## Datos Principales

Tablas usadas:
- `users`
- `dentists`
- `dentist_schedules`
- `appointments`
- `patients`
- `inventory_items`

## Reglas Por Rol

**Administrador**
- Edita solo su cuenta tecnica en `users`.
- No modifica perfiles de doctores ni asistentes.

**Doctor**
- Actualiza `users.name` y `users.email`.
- Actualiza su perfil clinico en `dentists` mediante `dentists.user_id`.
- El nombre de dentista no puede duplicar otro registro.
- El telefono clinico nunca queda nulo; si viene vacio se guarda `No registrado`.
- Puede actualizar su agenda semanal en `dentist_schedules`.

**Asistente**
- Actualiza `users.name`, `users.email`, `users.telefono` y `users.puesto`.
- No crea ni modifica registros en `dentists`.

## Seguridad

- El cambio de contrasena exige `current_password`.
- La nueva contrasena se guarda con hash mediante el cast `hashed` del modelo `User`.
- Al actualizar nombre o correo se sincroniza la sesion activa.

## Actividad

- El perfil de doctor muestra actividad reciente desde citas reales del dentista.
- El perfil de asistente muestra actividad reciente desde citas reales del sistema.
- El ultimo acceso visible se basa en `users.updated_at`.
- Si no existe actividad, se muestra un estado vacio controlado desde backend.

## Archivos

Controlador:
- `app/Http/Controllers/ProfileController.php`

Modelo principal:
- `app/Models/User.php`

Relaciones:
- `User::dentist()`
- `Dentist::schedules()`

Vistas conectadas:
- `resources/views/admin/profile.blade.php`
- `resources/views/admin/profile-edit.blade.php`
- `resources/views/perfil/index.blade.php`
- `resources/views/perfil/asistente.blade.php`
- `resources/views/perfil/editar.blade.php`

## Pruebas

Archivos:
- `tests/Feature/ProfileBackendTest.php`
- `tests/Feature/AssistantProfileTest.php`

Casos cubiertos:
- Doctor actualiza perfil sin dejar telefono nulo.
- Doctor no puede duplicar el nombre de otro dentista.
- Usuario cambia contrasena validando contrasena actual.
- Doctor actualiza agenda semanal.
- Asistente ve y actualiza su propio perfil.

## Pendientes Posibles

- Agregar formulario visible para cambio de contrasena en las vistas si el front lo requiere.
- Registrar auditoria de cambios de perfil.
- Guardar ultimo inicio de sesion real en una columna dedicada.
- Separar datos laborales del asistente en una tabla propia si crece el modulo.
