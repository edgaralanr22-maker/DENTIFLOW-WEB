# Modulo Inventario: Backend Y Base De Datos

Documento de referencia del estado backend/base del inventario.

## Funcionalidad Cubierta

**Inventario**
- Listar: `GET /inventario`.
- Crear: `POST /inventario`.
- Editar: `POST /inventario/{id}`.
- Eliminar: `GET /inventario/{id}/eliminar`.

## Datos Principales

Tabla:
- `inventory_items`

Campos:
- `material`
- `stock`
- `reposicion`
- `costo`
- `proveedor`

## Reglas De Integridad

- El material es unico para evitar duplicar stock del mismo insumo.
- `stock` no puede ser negativo.
- `reposicion` no puede ser negativo.
- `costo` no puede ser negativo.
- Si `stock <= reposicion`, el material aparece como alerta de reposicion.
- Si proveedor queda vacio al crear o editar, se guarda como `Sin proveedor`.
- El listado muestra estado visual: `Sin proveedor`, `Stock bajo` o `Con proveedor`.
- Proveedores nulos antiguos se normalizan a `Sin proveedor`.

## Base De Datos

Migracion nueva:
- `2026_06_30_000006_harden_inventory_items.php`

Cambios:
- Indice unico en `inventory_items.material`.
- Indice para alertas por `stock` y `reposicion`.
- Indice para reportes/agrupacion por `proveedor`.

## Modelo

Modelo:
- `InventoryItem`

Metodos agregados:
- `needsRestock()`: indica si el material requiere reposicion.
- `inventoryValue()`: calcula valor estimado `stock * costo`.

## Pruebas

Archivo:
- `tests/Feature/InventoryBackendTest.php`

Casos cubiertos:
- Crear material valido.
- Crear material sin proveedor y mostrarlo como `Sin proveedor`.
- Evitar materiales duplicados.
- Actualizar material sin fallar por su propio nombre unico.
- Limpiar proveedor al editar y guardarlo como `Sin proveedor`.
- Calcular alertas de reposicion.

## Pendientes Posibles

- Crear tabla de movimientos de inventario para entradas y salidas.
- Evitar borrado fisico y usar baja logica si se requiere historial.
- Registrar usuario responsable de cada ajuste de stock.
