# Modulo Tratamientos: Backend Y Base De Datos

Documento de referencia del estado backend/base del catalogo de tratamientos.

## Funcionalidad Cubierta

**Catalogo de tratamientos**
- Listar: `GET /pacientes/tratamientos`.
- Crear: `POST /pacientes/tratamientos`.
- Editar: `PUT /pacientes/tratamientos/{id}`.
- Eliminar: `GET /pacientes/tratamientos/{id}/eliminar`.

## Relaciones Usadas

**treatments.patient_id**
- Permite que un tratamiento se relacione con un paciente cuando se usa como registro clinico o financiero.

**treatments.appointment_id**
- Permite relacionar un tratamiento con una cita.

**appointments.tipo -> treatments.tratamiento**
- El front actual selecciona tratamientos por nombre al crear citas.
- Por eso `tratamiento` queda como nombre unico en el catalogo.

## Reglas De Integridad

- El nombre del tratamiento es unico.
- El tipo debe ser uno de los tipos usados por el formulario actual:
  `Preventivo`, `Correctivo`, `Estético`, `Quirúrgico`, `Diagnóstico`, `Restaurativo`.
- El costo no puede ser negativo.
- Los tratamientos nuevos del catalogo quedan con estado `Activo`.
- No se elimina un tratamiento si ya fue usado por una cita, paciente o reporte.

## Base De Datos

Migracion nueva:
- `2026_06_30_000005_harden_treatments_catalog.php`

Cambios:
- Indice unico en `treatments.tratamiento`.
- Indice para filtros por `tipo` y `tratamiento`.
- Indice para reportes por `estado` y `fecha`.
- Indice para historial por `patient_id` y `fecha`.
- Indice para relacion por `appointment_id`.

## Pruebas

Archivo:
- `tests/Feature/TreatmentBackendTest.php`

Casos cubiertos:
- Crear tratamiento valido.
- Evitar nombres duplicados.
- Bloquear eliminacion si el tratamiento esta usado en una cita.
- Permitir actualizar sin fallar por su propio nombre unico.

## Pendientes Posibles

- Separar catalogo de tratamientos y tratamientos realizados en tablas diferentes.
- Agregar duracion sugerida por tratamiento para que citas no use siempre 60 minutos.
- Agregar auditoria de cambios de costo.
