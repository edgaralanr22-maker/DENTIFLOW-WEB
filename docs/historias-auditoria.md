# Modulo Auditoria: Backend Y Base De Datos

Documento de referencia del estado backend/base de auditoria administrativa.

## Funcionalidad Cubierta

**Auditoria administrativa**
- Registrar cambios sensibles cuando `clinic_settings.administrative_audit_enabled` esta activo.
- No registra acciones nuevas cuando la auditoria esta desactivada.

## Datos Principales

Tabla:
- `audit_logs`

Campos:
- `user_id`
- `actor_name`
- `action`
- `entity_type`
- `entity_id`
- `metadata`
- `ip_address`

## Acciones Auditadas

**Configuracion**
- `admin.settings.updated`

## Reglas De Integridad

- `user_id` es nullable para permitir sesiones antiguas o acciones sin usuario enlazado.
- `entity_type` guarda la clase del modelo afectado.
- `entity_id` guarda el id cuando existe una entidad concreta.
- `metadata` guarda datos de contexto como antes/despues o datos del usuario eliminado.
- Si se cambia configuracion, el registro se guarda cuando la auditoria estaba activa antes o queda activa despues del cambio.

## Archivos

Modelo:
- `app/Models/AuditLog.php`

Migracion:
- `2026_06_30_000008_create_audit_logs_table.php`

Controladores conectados:
- `app/Http/Controllers/AdminSettingsController.php`

## Pruebas

Archivo:
- `tests/Feature/AuditLogBackendTest.php`

Casos cubiertos:
- Actualizar configuracion genera auditoria si queda habilitada.
- Actualizar configuracion no genera auditoria si esta deshabilitada.

## Pendientes Posibles

- Crear una vista administrativa para consultar auditoria.
- Registrar auditoria de perfil, inventario, citas y tratamientos si se requiere trazabilidad completa.
- Agregar filtros por fecha, usuario, accion y entidad.
- Definir politica de retencion de logs.
