# Modulo Dashboard: Backend Y Base De Datos

Documento de referencia del estado backend/base del dashboard.

## Funcionalidad Cubierta

**Dashboard principal**
- Ver inicio: `GET /inicio`.
- Redireccion historica: `GET /dashboard` redirige a `/inicio`.

**Calendario**
- Ver calendario: `GET /calendario`.

## Datos Principales

Tablas consultadas:
- `appointments`
- `patients`
- `dentists`
- `treatments`
- `inventory_items`
- `users`
- `clinic_settings`

## Reglas Por Rol

**Administrador**
- Ve indicadores globales del consultorio.
- Ve calendario mensual filtrable por dentista.
- Ve conteos generales de usuarios, doctores, citas, pacientes y tratamientos.
- El estado del sistema se toma de `clinic_settings.maintenance_mode_enabled`.

**Doctor**
- Ve solo citas asociadas a su dentista vinculado.
- La agenda de hoy, proximas citas, calendario, semana y estados de cita quedan filtrados por `dentist_id`.
- Los tratamientos del dashboard se limitan a tratamientos relacionados con citas de ese dentista.

**Asistente**
- Ve la operacion general de citas, pacientes, inventario y agenda.
- Mantiene vista global porque su rol atiende recepcion y operacion diaria.

## Reglas De Calculo

- Citas de hoy: `appointments.date = hoy`.
- Proximas citas: citas futuras que no esten `Cancelada` ni `Asistida`.
- Estado de citas: conteo por `Confirmada`, `Pendiente`, `Asistida` y `Cancelada`.
- Actividad semanal: citas no canceladas agrupadas por dia de la semana actual.
- Alertas de inventario: materiales con `stock <= reposicion`.
- Ingresos: suma de tratamientos en estado `Realizado`.
- Ultima actividad del sistema: fecha mas reciente de actualizacion entre usuarios, citas, pacientes, dentistas, tratamientos, inventario y configuracion.

## Archivos

Controlador:
- `app/Http/Controllers/DashboardController.php`

Vistas conectadas:
- `resources/views/admin/dashboard.blade.php`
- `resources/views/dashboard/index.blade.php`

## Pruebas

Archivo:
- `tests/Feature/AdminDashboardTest.php`

Casos cubiertos:
- El admin ve indicadores, calendario y estadisticas.
- El dashboard admin toma el estado del sistema desde configuracion.
- El dashboard de doctor solo muestra su propia agenda.

## Pendientes Posibles

- Aplicar el modo mantenimiento como bloqueo real de rutas operativas.
- Agregar metricas financieras por periodo en dashboard.
- Crear auditoria visible de ultimos cambios administrativos.
- Optimizar consultas agregadas con indices o vistas si crece el volumen de datos.
