# Modulo Citas: Backend Y Base De Datos

Documento de referencia del estado backend/base del modulo de citas.

## Funcionalidad Cubierta

**Crear cita**
- Ruta: `POST /citas`.
- Controlador: `AppointmentController::store()`.
- Modelo: `Appointment`.
- Tabla: `appointments`.

**Editar cita**
- Ruta: `POST /citas/{id}`.
- Controlador: `AppointmentController::update()`.

**Cambiar estado**
- Confirmar: `GET /citas/{id}/confirmar`.
- Cancelar: `GET /citas/{id}/cancelar`.
- Marcar asistida: `GET /citas/{id}/asistida`.
- Estados permitidos: `Pendiente`, `Confirmada`, `Cancelada`, `Asistida`.

**Reprogramar**
- Ruta: `GET /citas/{id}/reprogramar`.
- Backend redirige al formulario de edicion existente para conservar el front actual.

## Relaciones Usadas

**appointments.patient_id**
- Relaciona la cita con `patients`.
- La cita solo acepta pacientes existentes para evitar registros incompletos.

**appointments.dentist_id**
- Relaciona la cita con `dentists`.
- Si el usuario activo es doctor, el dentista se toma desde la sesion y `dentists.user_id`.
- Si el usuario no es doctor, el dentista debe existir en base de datos.

**appointments.tipo**
- Se valida contra `treatments.tratamiento` para usar tratamientos del catalogo existente.

## Reglas De Integridad

- No se crean pacientes incompletos desde citas.
- No se crean dentistas incompletos desde citas, salvo el caso controlado de una sesion doctor sin perfil clinico vinculado.
- No se permiten citas en fecha/hora pasada.
- No se permiten choques de horario para el mismo dentista.
- Cada cita ocupa un bloque operativo de 60 minutos.
- Si el dentista tiene agenda semanal registrada, la cita debe caer dentro de su horario.
- Si el dia de agenda esta deshabilitado, no se permite agendar.

## Base De Datos

Migracion nueva:
- `2026_06_30_000003_add_appointment_operational_indexes.php`

Indices agregados:
- `appointments(dentist_id, date, time)`
- `appointments(patient_id, date)`
- `appointments(estado)`

Estos indices ayudan a consultar agenda, validar choques y filtrar por estado sin modificar el front.

## Pruebas

Archivo:
- `tests/Feature/AppointmentDentistSessionTest.php`

Casos cubiertos:
- La cita usa el dentista de la sesion activa del doctor.
- Si falta perfil clinico para un doctor, se crea y vincula.
- No se permiten citas sobrepuestas.
- No se permite agendar en dias deshabilitados.
- No se permite agendar fuera del horario del dentista.
- Reprogramar redirige a edicion.

## Pendientes Posibles

- Agregar duracion configurable por tipo de tratamiento.
- Agregar auditoria: quien creo, actualizo o cancelo la cita.
- Convertir acciones de estado de GET a POST/PATCH para mayor seguridad.
- Agregar recordatorios reales si se habilita correo o notificaciones.
