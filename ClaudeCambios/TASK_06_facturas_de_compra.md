# TASK 06 — Facturas de Compra en Módulo Facturas

> ⚠️ Prerequisito: TASK 05 completado y verificado.

## Objetivo
Agregar la pestaña **"Facturas de compra"** dentro del módulo Facturas (`/empresa/facturas`). Cuando la empresa recibe una factura de un proveedor, el estudiante la registra aquí y el sistema genera automáticamente el asiento contable correspondiente.

## Contexto pedagógico
El ciclo contable completo requiere registrar tanto las ventas como las compras. Actualmente el módulo de Compras maneja órdenes de compra — las facturas de compra son el documento que formaliza la deuda con el proveedor y genera el pasivo en el balance. Es uno de los documentos más usados en la contabilidad diaria de cualquier empresa colombiana.

---

## Asiento contable generado automáticamente

```
Al registrar una factura de compra:

DÉBITO:
  6135 (o cuenta de gasto/costo según el producto)   $xxx
  240810 IVA descontable                              $xxx  (si aplica)

CRÉDITO:
  2205 Proveedores — Nacionales                       $xxx
  2365 Retención en la fuente practicada              $xxx  (si aplica)
  2367 Retención IVA (Reteiva)                        $xxx  (si aplica)
  2368 Retención ICA (Reteica)                        $xxx  (si aplica)
```

---

## Cambios requeridos

### 1. Pestaña nueva en Facturas
Agregar "Facturas de compra" como segunda pestaña:

```
Facturas de venta | Facturas de compra | Recibos de caja | Notas de crédito | Notas débito
```

---

### 2. Tabla: `purchase_invoices` (schema del tenant)

```php
Schema::create('purchase_invoices', function (Blueprint $table) {
    $table->id();
    $table->string('numero_factura_proveedor');        // número de la factura del proveedor
    $table->foreignId('third_id')->constrained('thirds'); // proveedor (tipo proveedor)
    $table->date('fecha_factura');
    $table->date('fecha_vencimiento')->nullable();
    $table->decimal('subtotal', 15, 2)->default(0);
    $table->decimal('iva', 15, 2)->default(0);
    $table->decimal('retencion_fuente', 15, 2)->default(0);
    $table->decimal('retencion_iva', 15, 2)->default(0);
    $table->decimal('retencion_ica', 15, 2)->default(0);
    $table->decimal('total', 15, 2)->default(0);
    $table->enum('estado', ['pendiente', 'pagada', 'anulada'])->default('pendiente');
    $table->text('observaciones')->nullable();
    $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
    $table->timestamps();
    $table->softDeletes();
});
```

### 3. Tabla: `purchase_invoice_items` (schema del tenant)

```php
Schema::create('purchase_invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
    $table->string('descripcion');
    $table->decimal('cantidad', 10, 2);
    $table->decimal('precio_unitario', 15, 2);
    $table->decimal('subtotal', 15, 2);
    $table->enum('iva_porcentaje', ['19', '5', '0'])->default('19');
    $table->decimal('iva_valor', 15, 2)->default(0);
    $table->string('cuenta_gasto_codigo');    // PUC: 5105, 5135, 6135...
    $table->string('cuenta_gasto_nombre');
    $table->timestamps();
});
```

---

### 4. Formulario — Nueva factura de compra

```
Nueva factura de compra
────────────────────────────────────────────────
Proveedor *                     Nro. factura proveedor *
[ Seleccionar proveedor... ]    [________________]

Fecha factura *                 Fecha vencimiento
[dd/mm/aaaa]                    [dd/mm/aaaa]

Ítems
─────────────────────────────────────────────────────────────────
Descripción | Cant. | Precio unit. | %IVA | Cta. gasto | Total
[_________]   [1]    [_________]   [19%]  [_________]   $0    [x]
                                                    [+ Agregar ítem]

                                         Subtotal:  $0
                                         IVA:       $0

Retenciones practicadas al proveedor:
☐ Retención en la fuente (tarifa según tipo de servicio/compra)
☐ Reteiva (15% del IVA)
☐ Reteica (tarifa mínima 0.4‰)

                                         Total a pagar: $0

[ Cancelar ]  [ Registrar factura ]
```

**Regla:** Solo pueden seleccionarse terceros con tipo `proveedor` o `ambos` en el selector.

---

### 5. Contabilización automática
Al registrar la factura → crear asiento en `journal_entries` con los débitos y créditos correspondientes usando las cuentas del PUC ya sembradas.

El asiento debe crearse en `DB::transaction()` — si falla, no se guarda la factura.

---

### 6. Listado de facturas de compra

```
NRO.    FECHA       PROVEEDOR        TOTAL        SALDO        ESTADO
001     10/04/26    Distribuidora X  $500.000     $500.000     Pendiente
002     08/04/26    Papelería Y      $120.000     $0           Pagada
```

Filtros: buscar por proveedor, filtrar por estado (Todas, Pendiente, Pagada, Anulada).

---

## Lo que NO se toca en esta tarea
- Módulo de Órdenes de compra (ya existe en Compras — son documentos diferentes)
- Módulo de Negocios
- Facturas de venta existentes
- Notas crédito y débito existentes

---

## Verificación
1. Ir a Facturas → ver pestaña "Facturas de compra"
2. Crear una factura de compra con proveedor, ítems e IVA
3. Verificar que el asiento contable se generó correctamente:
   - Db cuenta de gasto + IVA descontable
   - Cr 2205 Proveedores
   - Cr retenciones (si se marcaron)
4. El balance del proveedor debe reflejar la deuda
5. Filtrar por estado — funciona correctamente
6. Anular una factura → asiento se reversa automáticamente
