# Modulo Dentistas Y Agenda: Backend Y Base De Datos

Documento de referencia del estado backend/base del modulo de dentistas y agenda semanal.

## Funcionalidad Cubierta

**Gestion de dentistas**
- Listar: `GET /dentistas`.
- Crear: `POST /dentistas`.
- Editar: `PUT /dentistas/{id}`.
- Eliminar: `GET /dentistas/{id}/eliminar`.

**Agenda semanal**
- Actualizar agenda del doctor: `POST /perfil/agenda`.
- La agenda se guarda en `dentist_schedules`.
- Cada doctor puede tener 7 registros, uno por dia de semana.

## Relaciones Usadas

**dentists.user_id**
- Vincula un dentista con un usuario doctor.
- Permite que citas y perfil usen el doctor real de la sesion.

**dentists -> appointments**
- Un dentista tiene muchas citas.
- No se permite eliminar un dentista con citas registradas.

**dentists -> dentist_schedules**
- Un dentista tiene una agenda semanal.
- La agenda se usa para validar disponibilidad al crear o editar citas.

**dentists -> clinical_records**
- Un dentista puede quedar asociado a notas clinicas.

## Reglas De Integridad

- `dentists.nombre` es unico porque el front actual selecciona dentistas por nombre.
- `dentists.telefono` es obligatorio para mantener contacto operativo.
- Los dentistas creados desde backend reciben agenda semanal base.
- Lunes a jueves: 09:00 a 17:00.
- Viernes: 09:00 a 15:00.
- Sabado y domingo: deshabilitados.
- La agenda solo acepta weekdays del 1 al 7.
- Si un horario esta activo, `end_time` debe ser mayor que `start_time`.

## Base De Datos

Migracion nueva:
- `2026_06_30_000004_harden_dentists_and_schedules.php`

Cambios:
- Indice unico en `dentists.nombre`.
- `dentists.telefono` obligatorio.
- Indice operativo en `dentist_schedules(dentist_id, weekday, enabled)`.
- Limpieza de datos antiguos sin telefono a `No registrado`.

## Permisos

- El rol `admin` puede acceder a rutas `dentistas*`.
- La agenda propia del doctor se actualiza desde perfil.

## Pruebas

Archivo:
- `tests/Feature/DentistBackendTest.php`

Casos cubiertos:
- Crear dentista genera agenda semanal por defecto.
- Nombre de dentista debe ser unico.
- Telefono de dentista es obligatorio.
- No se elimina dentista con citas registradas.

## Pendientes Posibles

- Agregar una vista/formulario admin para editar agenda de cualquier dentista.
- Convertir eliminacion de dentistas de GET a DELETE.
- Agregar auditoria de altas, cambios y bajas.
