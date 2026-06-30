# Modulo Configuracion: Backend Y Base De Datos

Documento de referencia del estado backend/base de la configuracion general.

## Funcionalidad Cubierta

**Configuracion de clinica**
- Ver configuracion: `GET /administracion/configuracion`.
- Guardar configuracion: `POST /administracion/configuracion`.

## Datos Principales

Tabla:
- `clinic_settings`

Campos:
- `clinic_name`
- `support_email`
- `appointment_duration`
- `schedule_interval`
- `opening_time`
- `closing_time`
- `default_appointment_status`
- `automatic_reminders_enabled`
- `inventory_alerts_enabled`
- `maintenance_mode_enabled`
- `administrative_audit_enabled`

## Reglas De Integridad

- El nombre de clinica es obligatorio y tiene maximo 100 caracteres.
- El correo de soporte debe tener formato de email.
- La duracion por cita solo permite `30`, `45`, `60` o `90` minutos.
- El intervalo de agenda solo permite `15`, `30` o `60` minutos.
- La hora de cierre debe ser posterior a la hora de apertura.
- El estado inicial de nuevas citas solo permite `Pendiente` o `Confirmada`.
- Los checks de modulos se guardan como banderas booleanas explicitas.

## Conexion Con Otros Modulos

**Citas**
- Al crear una cita, `appointments.estado` toma el valor de `default_appointment_status`.
- La deteccion de choque horario usa `appointment_duration` para calcular el bloque ocupado.
- Si no existe configuracion guardada, el modelo usa valores por defecto para no romper el flujo.

**Modo mantenimiento**
- Si `maintenance_mode_enabled` esta activo, el middleware bloquea rutas operativas para doctor y asistente.
- Admin puede seguir entrando a inicio, configuracion, usuarios, dentistas, perfil y logout.
- Doctor y asistente conservan acceso a `inicio`, `perfil*` y `logout` para no quedar atrapados.
- Las rutas publicas de login, registro y recuperacion de contrasena siguen disponibles.

## Archivos

Controlador:
- `app/Http/Controllers/AdminSettingsController.php`

Middleware:
- `app/Http/Middleware/EnsureRoleAccess.php`

Modelo:
- `app/Models/ClinicSetting.php`

Migracion:
- `2026_06_30_000007_create_clinic_settings_table.php`

Vista conectada:
- `resources/views/admin/settings.blade.php`

## Pruebas

Archivo:
- `tests/Feature/AdminSettingsTest.php`

Casos cubiertos:
- Mostrar la pantalla de configuracion.
- Guardar configuracion en base de datos.
- Cargar valores guardados en el formulario.
- Crear citas usando el estado inicial configurado.
- Bloquear rutas operativas para no administradores durante mantenimiento.
- Permitir que admin entre a configuracion durante mantenimiento.

## Pendientes Posibles

- Aplicar `opening_time` y `closing_time` como horario global si se desea limitar toda la clinica.
- Activar comportamiento real de modo mantenimiento.
- Registrar auditoria de cambios administrativos.
- Usar `schedule_interval` para generar slots disponibles en calendario.
