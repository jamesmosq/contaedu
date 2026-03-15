# Guía de la Plataforma ContaEdu

## ¿Qué es ContaEdu?

ContaEdu es una plataforma educativa que simula un software contable tipo Siigo. Cada estudiante opera su propia **empresa virtual completamente aislada** y practica el ciclo contable completo: desde la configuración inicial hasta los reportes financieros. El docente puede supervisar todas las empresas del grupo y calificar el trabajo de cada estudiante.

---

## Roles del sistema

| Rol | Descripción |
|---|---|
| **Superadministrador** | Gestiona las instituciones educativas y los docentes |
| **Docente** | Administra el grupo de estudiantes, audita empresas y califica |
| **Estudiante** | Trabaja en su empresa virtual de forma aislada |

---

## Flujo completo de uso

### 1. El superadmin configura la institución

1. Ingresar a `/login` con las credenciales de superadmin
2. En el panel se gestionan las **instituciones** y los **docentes**
3. Al crear un docente se asigna automáticamente a una institución con su grupo

### 2. El docente crea las empresas estudiantiles

1. El docente ingresa a `/login` con sus credenciales
2. En el panel del docente, sección **"Crear empresa"**, ingresa:
   - Cédula del estudiante (se convierte en el ID del tenant)
   - Nombre del estudiante
   - Nombre de la empresa
   - NIT de la empresa
3. Al guardar, el sistema crea automáticamente:
   - Un schema PostgreSQL exclusivo (`tenant_cc...`)
   - Todas las tablas contables migradas
   - El **Plan Único de Cuentas (PUC) colombiano** con 57 cuentas predefinidas

### 3. El estudiante trabaja en su empresa

El estudiante ingresa a `/estudiante/login` con su cédula y contraseña. Tiene acceso a todos los módulos:

---

## Módulos del estudiante

### Configuración de empresa
Ruta: `/empresa/configuracion`

El estudiante configura los datos de su empresa:
- Razón social, NIT, régimen tributario
- Dirección, teléfono, correo
- Prefijo de facturación y resolución DIAN (educativa)

### Plan de cuentas
Ruta: `/empresa/cuentas`

Visualiza el PUC colombiano. Puede agregar **subcuentas auxiliares** (nivel 3 y 4) para mayor detalle contable. Las cuentas ya sembradas cubren las 6 clases del PUC:

| Clase | Descripción |
|---|---|
| 1 | Activo (Disponible, Deudores, Inventarios, PPE) |
| 2 | Pasivo (Obligaciones, Proveedores, CxP, IVA) |
| 3 | Patrimonio (Capital, Reservas, Resultados) |
| 4 | Ingresos operacionales |
| 5 | Gastos operacionales |
| 6 | Costos de venta |

### Terceros
Ruta: `/empresa/terceros`

CRUD de clientes y proveedores con:
- Tipo de documento (CC, NIT, CE)
- Tipo: cliente, proveedor o ambos
- Régimen tributario (simplificado / común)

### Productos
Ruta: `/empresa/productos`

CRUD de productos/servicios con:
- Precio de venta y precio de costo
- Unidad de medida
- Tasa de IVA (0%, 5%, 19%)
- Vinculación de cuentas contables (inventario, ingresos, costo de ventas)

### Facturas de venta
Ruta: `/empresa/facturas`

Ciclo completo de venta:
1. Crear factura en estado **borrador** → agregar líneas de producto
2. **Confirmar** → la factura pasa a "emitida" y se genera automáticamente el asiento:
   ```
   Débito  1305 Cuentas por cobrar     $total_con_IVA
   Crédito 4135 Ingresos por ventas    $subtotal
   Crédito 2408 IVA por pagar          $IVA
   ```
   Si el producto tiene costo:
   ```
   Débito  6135 Costo de ventas        $costo
   Crédito 1435 Inventario             $costo
   ```
3. **Anular** → genera asiento de reverso automático
4. **Recibo de caja** → registra el cobro al cliente:
   ```
   Débito  1105 Caja                   $monto_cobrado
   Crédito 1305 Cuentas por cobrar     $monto_cobrado
   ```

### Compras
Ruta: `/empresa/compras`

Ciclo completo de compra:
1. Crear factura de compra con líneas de productos
2. **Confirmar** → genera asiento automático:
   ```
   Débito  1435 Inventario             $subtotal
   Débito  2408 IVA descontable        $IVA
   Crédito 2205 Proveedores nacionales $total
   ```
3. **Registrar pago** → salda la cuenta del proveedor:
   ```
   Débito  2205 Proveedores nacionales $monto_pagado
   Crédito 1105 Caja                   $monto_pagado
   ```

### Reportes
Ruta: `/empresa/reportes`

Siete reportes con filtros por fecha, todos exportables a **PDF**:

| Reporte | Descripción |
|---|---|
| **Libro diario** | Todos los asientos en orden cronológico |
| **Libro mayor** | Movimientos de una cuenta con saldo acumulado |
| **Balance de comprobación** | Saldos de todas las cuentas activas. Débitos = Créditos siempre |
| **Estado de resultados** | Ingresos (4) − Costos (6) − Gastos (5) = Utilidad/Pérdida |
| **Balance general** | Activos (1) = Pasivos (2) + Patrimonio (3) |
| **Cartera por cobrar** | Facturas de venta emitidas con días vencidos (aging) |
| **Cuentas por pagar** | Facturas de compra pendientes con días vencidos |

---

## Módulos del docente

### Dashboard del grupo
Ruta: `/docente/dashboard`

Tabla con todos los estudiantes del grupo y métricas en tiempo real:
- Número de facturas emitidas y total facturado
- Botón para crear nueva empresa estudiantil
- Acceso directo al modo auditoría

### Modo auditoría
El docente puede navegar **toda la empresa de un estudiante en modo solo lectura**:

1. Hacer clic en **"Auditar"** junto a la empresa del estudiante
2. El sistema inicializa el tenant del estudiante en la sesión del docente
3. Aparece un **banner naranja** en la parte superior: _"Modo auditoría — empresa de [nombre] — Solo lectura"_
4. El docente puede navegar todos los módulos: facturas, compras, reportes, cuentas, etc.
5. **Todos los botones de guardar/modificar están deshabilitados** — solo lectura estricta
6. Para salir: botón **"Salir de auditoría"** en el banner

### Panel comparativo
Ruta: `/docente/comparativo`

Tabla con métricas de todos los estudiantes lado a lado:
- Facturas de venta (cantidad y total)
- Facturas de compra (cantidad y total)
- Estado del balance (activos = pasivos + patrimonio)

### Rúbrica de calificación
Ruta: `/docente/rubrica/{tenantId}`

El docente ingresa notas de 1.0 a 5.0 por módulo:
- Maestros contables (terceros, productos, configuración)
- Facturación y cobro
- Compras y pagos
- Cierre contable (reportes cuadrados)

La plataforma calcula automáticamente el **promedio ponderado** y lo persiste en la BD.

---

## Regla fundamental de contabilidad

Toda operación que genera dinero está protegida por una transacción de BD. Si al generar un asiento los **débitos no son iguales a los créditos** (diferencia ≥ 0.01), el sistema lanza `AccountingImbalanceException` y **revierte toda la operación** — nunca queda un documento sin su asiento completo.

---

## Datos demo incluidos

Al ejecutar `php artisan migrate:fresh --seed` se crean 3 empresas con datos de prueba:

| Empresa | Sector | Régimen |
|---|---|---|
| García Distribuciones S.A.S. | Distribución de alimentos | Común |
| Pérez Comercial E.U. | Materiales de construcción | Simplificado |
| Rodríguez & Asociados S.A.S. | Consultoría contable | Común |

Cada empresa tiene: configuración completa, 5 terceros, 4 productos, 3 facturas de venta confirmadas, 1 recibo de caja, 2 facturas de compra y 1 pago a proveedor — totalizando **7 asientos contables** listos para explorar.

---

## Arquitectura técnica resumida

```
app/
├── Http/Middleware/
│   ├── InitializeTenancyByStudent.php  ← detecta estudiante o modo auditoría
│   └── CheckRole.php                   ← verifica superadmin / teacher
├── Livewire/
│   ├── Admin/Dashboard.php             ← CRUD instituciones y docentes
│   ├── Teacher/Dashboard.php           ← panel del grupo con métricas
│   ├── Teacher/Comparativo.php         ← comparativa entre estudiantes
│   ├── Teacher/Rubrica.php             ← calificación por módulo
│   └── Tenant/                         ← todos los módulos del estudiante
├── Models/
│   ├── Central/                        ← User, Tenant, Group, Institution
│   └── Tenant/                         ← Account, Third, Product, Invoice, ...
└── Services/
    ├── AccountingService.php           ← genera y valida asientos contables
    ├── InvoiceService.php              ← confirma/anula facturas de venta
    ├── PurchaseService.php             ← confirma compras y aplica pagos
    ├── TenantProvisionService.php      ← crea schema + migra + siembra PUC
    └── ReportService.php               ← genera los 7 reportes financieros
```
