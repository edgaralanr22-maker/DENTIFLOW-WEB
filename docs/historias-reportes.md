# Modulo Reportes: Backend Y Base De Datos

Documento de referencia del estado backend/base de reportes.

## Funcionalidad Cubierta

**Consulta de reportes**
- Ver reporte: `GET /reportes`.
- Exportar reporte: `GET /reportes/export`.

## Datos Principales

Tablas consultadas:
- `appointments`
- `treatments`
- `patients`
- `dentists`

## Relaciones Usadas

**appointments.dentist_id**
- Permite calcular el top de dentistas por cantidad de citas.

**treatments.patient_id**
- Permite contar pacientes atendidos cuando el movimiento viene desde tratamientos.

**treatments.appointment_id**
- Permite conectar ganancias de tratamientos con la cita y el dentista que atendio.

## Reglas De Calculo

- El reporte usa datos reales de citas, tratamientos, pacientes y dentistas.
- Las ganancias se calculan solo con tratamientos en estado `Realizado`.
- Si el usuario activo es doctor, reportes y exportacion se filtran a tratamientos vinculados a sus citas.
- Los filtros de periodo usan fecha inicial y final, incluyendo el ultimo dia completo.
- La distribucion de tratamientos se calcula por `treatments.tipo`.
- Los costos por tratamiento realizado se agrupan por nombre de tratamiento y suman `treatments.costo`.
- El top de dentistas se calcula con citas reales del periodo.
- Los valores de lectura rapida se calculan desde base de datos, sin valores fijos.
- Si no hay datos en el periodo, el reporte no muestra tratamientos de ejemplo.

## Exportacion

- La ruta `reportes.export` genera un archivo CSV real.
- El archivo incluye: fecha, paciente, tratamiento, tipo, estado, costo y dentista.
- El nombre del archivo usa el rango consultado:
  `reporte-dentiflow-{desde}-{hasta}.csv`.

## Controlador

Archivo:
- `app/Http/Controllers/ReportController.php`

Responsabilidades:
- Resolver el periodo consultado.
- Consultar citas y tratamientos del rango.
- Calcular tarjetas principales, tendencias, grafico mensual, distribucion y top dentistas.
- Exportar los registros del periodo en CSV.

## Pruebas

Archivo:
- `tests/Feature/ReportBackendTest.php`

Casos cubiertos:
- El reporte usa datos reales del periodo.
- El reporte no muestra distribucion simulada cuando no hay datos.
- La exportacion CSV devuelve filas reales.

## Pendientes Posibles

- Generar PDF real si el proyecto lo requiere.
- Generar XLSX real en lugar de CSV cuando se necesite compatibilidad completa con Excel.
- Agregar filtro por dentista, paciente o tipo de tratamiento.
- Registrar auditoria de quien exporta reportes.
