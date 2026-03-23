# ContaEdu — Módulo de Facturación Electrónica Simulada
## Prompt para Claude Code | Laravel 11 + PostgreSQL

---

## 1. CONTEXTO DEL PROYECTO

**ContaEdu** es un software de contabilidad académica desarrollado con **Laravel 11** y **PostgreSQL**, diseñado para entornos educativos. Ya existen los módulos de **Facturación/Ventas**, **Contabilidad/PUC** e **Inventario/Productos**.

Este documento contiene las instrucciones completas para implementar el **Módulo de Facturación Electrónica Simulada**, cuyo propósito es replicar fielmente el proceso real de facturación electrónica colombiana regulado por la DIAN, sin conexión a servidores externos. Todo el proceso es interno al sistema.

---

## 2. MARCO NORMATIVO DE REFERENCIA

El módulo simula el sistema real basado en:

- **Resolución 000042 del 05 de mayo de 2020** — Define el sistema de facturación electrónica con validación previa, el CUFE, el CUDE, y el Anexo Técnico UBL 2.1.
- **Resolución 000165 de 2023** — Actualiza y unifica las disposiciones del Sistema de Facturación Electrónica. Establece obligatoriedad escalonada completada en 2024.
- **Resolución 000015 de 2021** — Define el sistema RADIAN para factura como título valor.
- **Estándar UBL V2.1** — Formato XML requerido para todos los documentos electrónicos.

**Modelo colombiano: Validación Previa (Clearance)**
El XML debe ser transmitido y autorizado ANTES de ser entregado al cliente. No existe factura válida sin validación DIAN previa.

---

## 3. GLOSARIO TÉCNICO (para comentarios en código)

| Término | Definición |
|---|---|
| **CUFE** | Código Único de Factura Electrónica. Hash SHA-384 de 96 caracteres hexadecimales que identifica inequívocamente cada factura. Requerido en facturas de venta. |
| **CUDE** | Código Único de Documento Electrónico. Equivalente al CUFE para notas crédito, notas débito y demás documentos derivados. |
| **Clave técnica** | Identificador único asignado por la DIAN al autorizar el rango de numeración. Se incluye en el cálculo del CUFE. |
| **UBL 2.1** | Universal Business Language versión 2.1. Estándar XML que estructura el documento electrónico. |
| **OFE** | Obligado a Facturar Electrónicamente. El emisor de la factura. |
| **Adquirente** | El comprador. También tiene obligaciones: debe emitir eventos de recepción. |
| **Resolución de autorización** | Documento DIAN que autoriza un prefijo y rango de numeración para un período determinado. |
| **Validación previa** | El proceso en que la DIAN valida el XML antes de que la factura se expida al adquirente. |
| **RADIAN** | Plataforma DIAN para registrar la factura electrónica como título valor negociable. |
| **ApplicationResponse** | Respuesta XML que emite la DIAN (o el simulador) tras validar un documento. Contiene el CUFE y el estado. |
| **Eventos del receptor** | Actos jurídicos del adquirente: Acuse de Recibo, Recibo del bien/servicio, Aceptación expresa o Reclamo. |

---

## 4. ARQUITECTURA DEL MÓDULO

### 4.1 Estructura de carpetas a crear

```
app/
├── Enums/
│   ├── EstadoFacturaEnum.php
│   ├── TipoDocumentoEnum.php
│   ├── TipoOperacionEnum.php
│   ├── TipoImpuestoEnum.php
│   └── EventoReceptorEnum.php
│
├── Models/
│   ├── FeResolucion.php
│   ├── FeFactura.php
│   ├── FeDetalleFactura.php
│   ├── FeEvento.php
│   ├── FeEventoReceptor.php
│   └── FeNotaCredito.php
│
├── Services/
│   └── FacturacionElectronica/
│       ├── CufeService.php
│       ├── CudeService.php
│       ├── XmlUblService.php
│       ├── ValidadorFacturaService.php
│       ├── DiанSimuladorService.php
│       └── FacturaService.php          ← Orquestador principal
│
├── Http/
│   └── Controllers/
│       └── FacturacionElectronicaController.php
│
└── Observers/
    └── FeFacturaObserver.php

database/
└── migrations/
    ├── create_fe_resoluciones_table.php
    ├── create_fe_facturas_table.php
    ├── create_fe_detalles_factura_table.php
    ├── create_fe_eventos_table.php
    ├── create_fe_eventos_receptor_table.php
    └── create_fe_notas_credito_table.php

resources/
└── views/
    └── facturacion-electronica/
        ├── index.blade.php
        ├── crear.blade.php
        ├── detalle.blade.php
        ├── resoluciones/
        │   ├── index.blade.php
        │   └── crear.blade.php
        └── partials/
            ├── xml-viewer.blade.php
            └── application-response.blade.php
```

---

## 5. MIGRACIONES — BASE DE DATOS PostgreSQL

### 5.1 `fe_resoluciones`

```php
Schema::create('fe_resoluciones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
    $table->string('numero_resolucion', 50);       // Ej: "18760000001"
    $table->string('prefijo', 4)->nullable();       // Ej: "SENA", "FE", "FV"
    $table->unsignedInteger('numero_desde');        // Inicio del rango autorizado
    $table->unsignedInteger('numero_hasta');        // Fin del rango autorizado
    $table->unsignedInteger('numero_actual');       // Contador interno (inicia en numero_desde)
    $table->date('fecha_desde');                   // Inicio vigencia resolución
    $table->date('fecha_hasta');                   // Vencimiento resolución
    $table->string('clave_tecnica', 255);          // Asignada por DIAN al crear resolución
    // En simulación: generar UUID aleatorio como clave técnica
    $table->string('ambiente', 2)->default('02');  // 01=Producción, 02=Pruebas (siempre 02 en sim.)
    $table->boolean('activa')->default(true);
    $table->text('notas')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 5.2 `fe_facturas`

```php
Schema::create('fe_facturas', function (Blueprint $table) {
    $table->id();

    // Referencia a resolución
    $table->foreignId('resolucion_id')->constrained('fe_resoluciones');
    $table->unsignedInteger('numero');                      // Consecutivo dentro del rango
    $table->string('numero_completo', 20);                  // Ej: "FE-000001"
    $table->string('cufe', 96)->nullable()->unique();       // SHA-384 = 96 chars hex

    // Clasificación del documento
    $table->string('tipo_operacion', 10)->default('10');
    // 10=Estandar, 09=Mandato, 11=Trasporte, 12=Cambiaria, 20=Exportación, 32=Contingencia

    // Fechas y horas (críticas para el cálculo del CUFE)
    $table->date('fecha_emision');
    $table->time('hora_emision');

    // Estado del documento — ver máquina de estados en sección 8
    $table->string('estado', 20)->default('borrador');
    // borrador | generada | enviada | validada | rechazada_dian | anulada

    // Datos del EMISOR (empresa configurada)
    $table->string('nit_emisor', 20);
    $table->unsignedTinyInteger('dv_emisor');               // Dígito de verificación NIT
    $table->string('razon_social_emisor', 255);
    $table->string('regimen_fiscal_emisor', 2)->default('48'); // 48=Responsable de IVA

    // Datos del ADQUIRENTE
    $table->string('tipo_doc_adquirente', 2)->default('31');
    // 11=CC, 12=CE, 13=Pasaporte, 31=NIT, 22=Tarjeta extranjería
    $table->string('num_doc_adquirente', 20);
    $table->string('nombre_adquirente', 255);
    $table->string('email_adquirente', 255)->nullable();
    $table->string('telefono_adquirente', 20)->nullable();
    $table->string('direccion_adquirente', 255)->nullable();
    $table->string('municipio_adquirente', 5)->nullable();  // Código DANE
    $table->foreignId('cliente_id')->nullable()->constrained('clients');

    // TOTALES — todos en pesos colombianos (COP)
    $table->decimal('subtotal', 18, 2);                     // Antes de impuestos y descuentos
    $table->decimal('total_descuentos', 18, 2)->default(0);
    $table->decimal('base_iva', 18, 2)->default(0);
    $table->decimal('valor_iva', 18, 2)->default(0);        // Impuesto 01
    $table->decimal('base_ica', 18, 2)->default(0);
    $table->decimal('valor_ica', 18, 2)->default(0);        // Impuesto 03
    $table->decimal('base_inc', 18, 2)->default(0);
    $table->decimal('valor_inc', 18, 2)->default(0);        // Impuesto 04 - Inc Nacional Consumo
    $table->decimal('total_retenciones', 18, 2)->default(0);
    $table->decimal('total', 18, 2);                        // Valor final a pagar

    // Condiciones de pago
    $table->string('medio_pago', 2)->default('10');         // 10=Efectivo, 42=Débito, 48=Tarjeta
    $table->string('forma_pago', 2)->default('1');          // 1=Contado, 2=Crédito
    $table->date('fecha_vencimiento_pago')->nullable();      // Solo si forma_pago=2

    // Documentos XML generados por el simulador
    $table->longText('xml_factura')->nullable();            // XML UBL 2.1 de la factura
    $table->longText('xml_application_response')->nullable(); // Respuesta simulada DIAN

    // Respuesta simulada de la DIAN
    $table->timestamp('fecha_validacion_dian')->nullable();
    $table->string('codigo_respuesta_dian', 10)->nullable(); // "00"=ok, ver códigos en sección 9
    $table->text('mensaje_dian')->nullable();

    // QR (representación gráfica)
    $table->text('qr_data')->nullable();                    // URL+CUFE para generar QR

    // Trazabilidad
    $table->text('notas')->nullable();
    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();
    $table->softDeletes();

    // Índices
    $table->unique(['resolucion_id', 'numero']);
    $table->index(['estado', 'fecha_emision']);
    $table->index('nit_emisor');
    $table->index('num_doc_adquirente');
});
```

### 5.3 `fe_detalles_factura`

```php
Schema::create('fe_detalles_factura', function (Blueprint $table) {
    $table->id();
    $table->foreignId('factura_id')->constrained('fe_facturas')->cascadeOnDelete();
    $table->foreignId('producto_id')->nullable()->constrained('products');

    $table->unsignedSmallInteger('orden');                  // Número de línea (1, 2, 3...)
    $table->string('codigo_producto', 50)->nullable();      // Código interno
    $table->string('codigo_estandar', 50)->nullable();      // Código UNSPSC (estándar ONU)
    $table->string('descripcion', 500);
    $table->string('unidad_medida', 10)->default('94');
    // 94=Unidad, 50=Kilogramo, 22=Litro, 01=Hora — tabla UNECE

    $table->decimal('cantidad', 12, 4);
    $table->decimal('precio_unitario', 18, 4);              // Precio antes de descuento e impuestos
    $table->decimal('precio_referencia', 18, 4)->nullable(); // Precio de lista (opcional)

    $table->decimal('porcentaje_descuento', 5, 2)->default(0);
    $table->decimal('valor_descuento', 18, 2)->default(0);

    // Impuestos de la línea
    $table->decimal('porcentaje_iva', 5, 2)->default(19);   // 0, 5, 19
    $table->decimal('valor_iva', 18, 2)->default(0);
    $table->decimal('porcentaje_ica', 5, 4)->default(0);    // Variable por municipio
    $table->decimal('valor_ica', 18, 2)->default(0);
    $table->decimal('porcentaje_inc', 5, 2)->default(0);    // Inc Nacional al Consumo
    $table->decimal('valor_inc', 18, 2)->default(0);

    $table->decimal('subtotal_linea', 18, 2);               // cantidad * precio - descuento (sin IVA)
    $table->decimal('total_linea', 18, 2);                  // subtotal + todos los impuestos

    $table->timestamps();
});
```

### 5.4 `fe_eventos`

```php
// Auditoría interna de cambios de estado del documento
Schema::create('fe_eventos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('factura_id')->constrained('fe_facturas')->cascadeOnDelete();
    $table->string('estado_anterior', 20)->nullable();
    $table->string('estado_nuevo', 20);
    $table->string('origen', 50);                           // "sistema"|"simulador_dian"|"usuario"
    $table->text('descripcion')->nullable();
    $table->json('metadata')->nullable();                   // Datos adicionales del evento
    $table->foreignId('user_id')->nullable()->constrained('users');
    $table->timestamp('created_at')->useCurrent();
});
```

### 5.5 `fe_eventos_receptor`

```php
// Eventos que el ADQUIRENTE debe emitir — proceso RADIAN simplificado
Schema::create('fe_eventos_receptor', function (Blueprint $table) {
    $table->id();
    $table->foreignId('factura_id')->constrained('fe_facturas')->cascadeOnDelete();
    $table->string('tipo_evento', 30);
    // 030=AcuseRecibo | 032=ReciboBienServicio | 033=AceptacionExpresa | 031=Reclamo

    $table->string('cude_evento', 96)->nullable();          // Hash del evento (como el CUFE)
    $table->timestamp('fecha_evento');
    $table->text('observaciones')->nullable();
    $table->string('estado', 20)->default('registrado');    // registrado | procesado | rechazado
    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();

    // Una factura no puede tener dos eventos del mismo tipo
    $table->unique(['factura_id', 'tipo_evento']);
});
```

### 5.6 `fe_notas_credito`

```php
Schema::create('fe_notas_credito', function (Blueprint $table) {
    $table->id();
    $table->foreignId('factura_origen_id')->constrained('fe_facturas');
    $table->foreignId('resolucion_id')->constrained('fe_resoluciones');

    $table->string('numero_completo', 20);
    $table->string('cude', 96)->nullable()->unique();       // CUDE (no CUFE — es nota, no factura)
    $table->date('fecha_emision');
    $table->time('hora_emision');

    $table->string('codigo_concepto', 2);
    // 1=Devolución parcial bienes, 2=Anulación factura, 3=Rebaja precio,
    // 4=Ajuste por error en NIT, 5=Otros

    $table->text('descripcion_concepto');

    // Valores ajustados
    $table->decimal('subtotal', 18, 2);
    $table->decimal('valor_iva', 18, 2)->default(0);
    $table->decimal('total', 18, 2);

    $table->string('estado', 20)->default('generada');      // generada | validada | rechazada
    $table->longText('xml_nota')->nullable();
    $table->longText('xml_application_response')->nullable();

    $table->timestamp('fecha_validacion_dian')->nullable();
    $table->text('mensaje_dian')->nullable();

    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
```

---

## 6. ENUMS

### `EstadoFacturaEnum.php`

```php
enum EstadoFacturaEnum: string
{
    case BORRADOR    = 'borrador';      // En construcción, no enviada
    case GENERADA    = 'generada';      // XML y CUFE generados, pendiente envío a DIAN
    case ENVIADA     = 'enviada';       // Transmitida al simulador DIAN
    case VALIDADA    = 'validada';      // Aceptada por la DIAN — factura legalmente válida
    case RECHAZADA   = 'rechazada';     // La DIAN rechazó el documento
    case ANULADA     = 'anulada';       // Nota crédito de anulación emitida y validada

    public function label(): string { /* etiquetas en español */ }
    public function color(): string { /* colores para UI: verde, rojo, amarillo, etc. */ }
    public function esTerminal(): bool
    {
        return in_array($this, [self::VALIDADA, self::ANULADA]);
    }
    public function puedeTransicionarA(self $nuevo): bool
    {
        return match($this) {
            self::BORRADOR  => in_array($nuevo, [self::GENERADA]),
            self::GENERADA  => in_array($nuevo, [self::ENVIADA, self::ANULADA]),
            self::ENVIADA   => in_array($nuevo, [self::VALIDADA, self::RECHAZADA]),
            self::RECHAZADA => in_array($nuevo, [self::GENERADA]),   // Se puede corregir y reenviar
            self::VALIDADA  => in_array($nuevo, [self::ANULADA]),    // Solo con nota crédito
            self::ANULADA   => false,
        };
    }
}
```

### `EventoReceptorEnum.php`

```php
enum EventoReceptorEnum: string
{
    case ACUSE_RECIBO        = '030';   // Obligatorio — el adquirente confirma haber recibido
    case RECIBO_BIEN         = '032';   // Confirma recepción del bien/servicio
    case ACEPTACION_EXPRESA  = '033';   // Acepta la factura — a partir de aquí no puede reclamar
    case RECLAMO             = '031';   // Rechaza parcial o totalmente — plazo 3 días hábiles
}
```

### `TipoDocumentoEnum.php`

```php
enum TipoDocumentoEnum: string
{
    case NIT        = '31';
    case CC         = '13';
    case CE         = '22';
    case PASAPORTE  = '91';
    case NIT_OTRO   = '42';
}
```

---

## 7. SERVICIOS — LÓGICA DE NEGOCIO

### 7.1 `CufeService.php` — Cálculo real del CUFE

**Fuente normativa:** Resolución 000042/2020, Sección 10.1.1, Algoritmo SHA-384.

El CUFE se calcula con la concatenación exacta de los siguientes campos **sin separadores**, con los valores monetarios formateados con exactamente dos decimales (punto como separador decimal, sin comas de miles):

```
NumFac + FecFac + HorFac + ValFac + CodImp1 + ValImp1 + CodImp2 + ValImp2 + CodImp3 + ValImp3 + ValTot + NitOFE + NumAdq + ClTec + TipoAmb
```

Donde:
- `NumFac` = número completo de la factura (Ej: `SENA-000001`)
- `FecFac` = fecha formato `YYYY-MM-DD`
- `HorFac` = hora formato `HH:MM:SS` (hora de Colombia, sin zona horaria en la cadena)
- `ValFac` = subtotal sin impuestos, 2 decimales (Ej: `1000000.00`)
- `CodImp1` = `"01"` (IVA), `ValImp1` = valor IVA 2 decimales
- `CodImp2` = `"04"` (INC), `ValImp2` = valor INC 2 decimales
- `CodImp3` = `"03"` (ICA), `ValImp3` = valor ICA 2 decimales
- `ValTot` = total factura 2 decimales
- `NitOFE` = NIT del emisor SIN dígito de verificación
- `NumAdq` = número de identificación del adquirente
- `ClTec` = clave técnica de la resolución
- `TipoAmb` = `"2"` (pruebas/simulación) o `"1"` (producción)

El resultado es `hash('sha384', $cadena)` — 96 caracteres hexadecimales en minúscula.

```php
class CufeService
{
    public function calcular(FeFactura $factura, FeResolucion $resolucion): string
    {
        $cadena = implode('', [
            $factura->numero_completo,
            $factura->fecha_emision->format('Y-m-d'),
            $factura->hora_emision->format('H:i:s'),
            number_format($factura->subtotal, 2, '.', ''),
            '01',
            number_format($factura->valor_iva, 2, '.', ''),
            '04',
            number_format($factura->valor_inc, 2, '.', ''),
            '03',
            number_format($factura->valor_ica, 2, '.', ''),
            number_format($factura->total, 2, '.', ''),
            $factura->nit_emisor,
            $factura->num_doc_adquirente,
            $resolucion->clave_tecnica,
            $resolucion->ambiente,  // "2" para pruebas
        ]);

        return hash('sha384', $cadena); // 96 chars hex
    }

    public function validar(string $cufe, FeFactura $factura, FeResolucion $resolucion): bool
    {
        return $this->calcular($factura, $resolucion) === $cufe;
    }
}
```

### 7.2 `CudeService.php` — Cálculo del CUDE para notas crédito

El CUDE usa la misma lógica que el CUFE pero aplicada a la nota crédito y con referencia al CUFE de la factura origen. Implementar siguiendo el mismo patrón de `CufeService`.

### 7.3 `ValidadorFacturaService.php` — Validaciones previas al envío

Este servicio valida la factura ANTES de enviarla al simulador DIAN. Si falla cualquier regla, se lanza una excepción con el código de error y el mensaje específico. La factura NO avanza de estado.

```php
class ValidadorFacturaService
{
    // Reglas a implementar — TODAS deben pasar:

    public function validar(FeFactura $factura): ValidationResult
    {
        $errores = [];

        // REGLA 1 — Resolución vigente
        // La fecha de hoy debe estar entre resolucion.fecha_desde y resolucion.fecha_hasta
        // Error: "La resolución {numero} venció el {fecha_hasta}"

        // REGLA 2 — Consecutivo dentro del rango
        // factura.numero debe estar entre resolucion.numero_desde y resolucion.numero_hasta
        // Error: "Consecutivo {numero} fuera del rango autorizado [{desde}-{hasta}]"

        // REGLA 3 — NIT emisor válido
        // Debe tener entre 9 y 10 dígitos numéricos
        // El dígito de verificación debe ser correcto (algoritmo módulo 11 DIAN)
        // Error: "El NIT del emisor no es válido"

        // REGLA 4 — Identificación del adquirente
        // num_doc_adquirente no puede estar vacío
        // Si tipo_doc = NIT (31), validar módulo 11
        // Error: "La identificación del adquirente es requerida"

        // REGLA 5 — Al menos un detalle
        // La factura debe tener mínimo 1 línea en fe_detalles_factura
        // Error: "La factura debe tener al menos un ítem"

        // REGLA 6 — Total cuadrado
        // sum(detalles.total_linea) debe ser igual a factura.total (tolerancia: $0.01)
        // Error: "El total declarado ({total}) no coincide con la suma de líneas ({suma_lineas})"

        // REGLA 7 — IVA coherente por línea
        // Verificar que valor_iva de cada línea = (subtotal_linea * porcentaje_iva / 100)
        // Error: "Error de IVA en la línea {orden}: {descripcion}"

        // REGLA 8 — Campos obligatorios del adquirente
        // nombre_adquirente y email_adquirente son requeridos
        // Error: "El email del adquirente es obligatorio para factura electrónica"

        // REGLA 9 — Total mayor a cero
        // factura.total > 0
        // Error: "El valor total de la factura debe ser mayor a cero"

        // REGLA 10 — Fecha de emisión no futura
        // fecha_emision <= hoy
        // Error: "La fecha de emisión no puede ser futura"

        return new ValidationResult($errores);
    }

    private function validarDigitoVerificacion(string $nit): bool
    {
        // Algoritmo oficial DIAN para dígito de verificación del NIT
        // Pesos: 3,7,13,17,19,23,29,37,41,43,47,53,59,67,71
        $pesos = [3,7,13,17,19,23,29,37,41,43,47,53,59,67,71];
        $nit = str_pad($nit, 15, '0', STR_PAD_LEFT);
        $suma = 0;
        for ($i = 0; $i < 15; $i++) {
            $suma += (int)$nit[$i] * $pesos[$i];
        }
        $residuo = $suma % 11;
        $dv = $residuo > 1 ? 11 - $residuo : $residuo;
        return $dv === (int)substr($nit, -1);
    }
}
```

### 7.4 `DiанSimuladorService.php` — Motor de simulación DIAN

Este servicio actúa como si fuera el servidor de la DIAN. Recibe la factura ya validada internamente, aplica sus propias reglas y devuelve un `ApplicationResponse` simulado.

**Lógica del simulador:**

```php
class DiаnSimuladorService
{
    // Tasa de aceptación base: 92% de las facturas se aceptan automáticamente
    // El 8% restante se rechaza con errores realistas para fines pedagógicos

    public function validar(FeFactura $factura, FeResolucion $resolucion): SimuladorResponse
    {
        // PASO 1: Verificar CUFE
        // Si el CUFE no tiene exactamente 96 caracteres → RECHAZAR
        // Código error DIAN: "FAD09"
        // Mensaje: "El UUID de la factura no corresponde al algoritmo CUFE-SHA384"

        // PASO 2: Verificar XML mínimo (si fue generado)
        // El XML debe contener los nodos UBL obligatorios

        // PASO 3: Reglas de negocio del simulador
        // - Si rango tiene menos de 5 facturas disponibles → ADVERTENCIA (no rechazo)
        // - Si el adquirente tiene el mismo NIT que el emisor → RECHAZAR
        //   Código: "FAJ07" — "El adquirente no puede ser el mismo emisor"
        // - 3% aleatorio: rechazar con "Error técnico en transmisión, intente nuevamente"
        //   Esto enseña el manejo de errores transitorios

        // PASO 4: Construir ApplicationResponse
        return new SimuladorResponse(
            aceptada: true/false,
            cufe: $factura->cufe,
            codigoRespuesta: '00',  // '00'=ok, ver tabla de códigos
            mensaje: 'Documento validado correctamente',
            fechaValidacion: now(),
            xmlResponse: $this->generarXmlApplicationResponse(...)
        );
    }

    // TABLA DE CÓDIGOS DE RESPUESTA SIMULADOS (basada en códigos reales DIAN):
    // "00" — Documento procesado correctamente
    // "FAD01" — El número de factura ya existe en el sistema
    // "FAD09" — CUFE incorrecto o mal calculado
    // "FAJ07" — Adquirente igual al emisor
    // "FAJ71" — Email del adquirente no registrado
    // "FAK01" — Consecutivo fuera del rango autorizado
    // "FAK24" — Resolución vencida
    // "ZZZ" — Error técnico transitorio (reintentar)
}
```

### 7.5 `XmlUblService.php` — Generador de XML UBL 2.1

Genera el XML de la factura según el estándar UBL 2.1. El XML incluye:

```php
class XmlUblService
{
    public function generarFactura(FeFactura $factura): string
    {
        // Estructura mínima del XML UBL 2.1 (Invoice):
        // Nota: En el simulador NO se incluye firma digital XAdES-EPES
        // pero la estructura de nodos debe ser correcta

        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
                 xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                 xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                 xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
            <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>
            <cbc:CustomizationID>10</cbc:CustomizationID>   <!-- Tipo operación -->
            <cbc:ProfileID>DIAN 2.1</cbc:ProfileID>
            <cbc:ProfileExecutionID>2</cbc:ProfileExecutionID>  <!-- 2=Pruebas, 1=Producción -->
            <cbc:ID>{numero_completo}</cbc:ID>
            <cbc:UUID schemeName="CUFE-SHA384">{cufe}</cbc:UUID>
            <cbc:IssueDate>{fecha_emision}</cbc:IssueDate>
            <cbc:IssueTime>{hora_emision}</cbc:IssueTime>
            <cbc:InvoiceTypeCode>01</cbc:InvoiceTypeCode>   <!-- 01=Factura venta -->
            <cbc:Note>{notas}</cbc:Note>
            <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>
            <cbc:LineCountNumeric>{cantidad_lineas}</cbc:LineCountNumeric>

            <!-- Referencia a la resolución -->
            <cac:InvoicePeriod>
                <cbc:StartDate>{resolucion.fecha_desde}</cbc:StartDate>
                <cbc:EndDate>{resolucion.fecha_hasta}</cbc:EndDate>
                <cbc:Description>Resolución DIAN: {resolucion.numero_resolucion}</cbc:Description>
            </cac:InvoicePeriod>

            <!-- Datos del emisor (AccountingSupplierParty) -->
            <cac:AccountingSupplierParty>...</cac:AccountingSupplierParty>

            <!-- Datos del adquirente (AccountingCustomerParty) -->
            <cac:AccountingCustomerParty>...</cac:AccountingCustomerParty>

            <!-- Métodos de pago -->
            <cac:PaymentMeans>...</cac:PaymentMeans>

            <!-- Impuestos totales -->
            <cac:TaxTotal>...</cac:TaxTotal>

            <!-- Totales del documento -->
            <cac:LegalMonetaryTotal>
                <cbc:LineExtensionAmount currencyID="COP">{subtotal}</cbc:LineExtensionAmount>
                <cbc:TaxExclusiveAmount currencyID="COP">{base_gravable}</cbc:TaxExclusiveAmount>
                <cbc:TaxInclusiveAmount currencyID="COP">{total}</cbc:TaxInclusiveAmount>
                <cbc:AllowanceTotalAmount currencyID="COP">{total_descuentos}</cbc:AllowanceTotalAmount>
                <cbc:PayableAmount currencyID="COP">{total}</cbc:PayableAmount>
            </cac:LegalMonetaryTotal>

            <!-- Líneas de la factura -->
            {lineas}
        </Invoice>
        XML;
    }
}
```

### 7.6 `FacturaService.php` — Orquestador principal

Este es el servicio que llaman los controladores. Coordina todos los demás servicios.

```php
class FacturaService
{
    public function __construct(
        private ValidadorFacturaService $validador,
        private CufeService $cufeService,
        private XmlUblService $xmlService,
        private DiаnSimuladorService $simulador,
    ) {}

    // FLUJO COMPLETO — llamar desde el controlador:

    public function emitir(FeFactura $factura): FeFactura
    {
        // 1. Verificar que la factura esté en estado 'borrador'
        // 2. Ejecutar ValidadorFacturaService::validar() — lanzar excepción si falla
        // 3. Asignar número consecutivo (bloquear fila con lockForUpdate())
        // 4. Calcular CUFE con CufeService::calcular()
        // 5. Generar XML con XmlUblService::generarFactura()
        // 6. Cambiar estado a 'generada' — registrar en fe_eventos
        // 7. Llamar DiаnSimuladorService::validar()
        // 8. Si aceptada: estado → 'validada', guardar xml_application_response
        // 9. Si rechazada: estado → 'rechazada', guardar codigo y mensaje DIAN
        // 10. Registrar evento en fe_eventos
        // 11. Si validada: disparar evento Laravel FacturaValidada (para contabilidad)
        // Todo dentro de DB::transaction()
    }

    public function reenviar(FeFactura $factura): FeFactura
    {
        // Solo permitido si estado === 'rechazada'
        // Permite corregir y reenviar al simulador
    }

    public function anular(FeFactura $factura, array $datosNota): FeNotaCredito
    {
        // Solo si estado === 'validada'
        // Verifica que NO exista aceptación expresa del receptor (fe_eventos_receptor tipo 033)
        // Si existe aceptación expresa → lanzar excepción: no se puede anular
        // Crear FeNotaCredito con codigo_concepto = '2' (anulación)
        // Calcular CUDE
        // Enviar al simulador
        // Si validada: cambiar factura a estado 'anulada'
    }

    public function registrarEventoReceptor(FeFactura $factura, EventoReceptorEnum $tipoEvento, string $observaciones = ''): FeEventoReceptor
    {
        // Validar que la factura esté 'validada'
        // Validar secuencia de eventos (030 antes de 032, 032 antes de 033 o 031)
        // Si tipo === 031 (Reclamo): verificar que han pasado menos de 3 días hábiles desde emisión
        // Si tipo === 033 (Aceptación Expresa): bloquear posibilidad de anulación futura
        // Calcular CUDE del evento
        // Guardar en fe_eventos_receptor
    }
}
```

---

## 8. MÁQUINA DE ESTADOS COMPLETA

```
[borrador]
    → emitir()          → [generada]

[generada]
    → simulador acepta  → [validada]    ✅ Factura legalmente válida
    → simulador rechaza → [rechazada]   ❌ Ver mensaje DIAN
    → anular()          → [anulada]     (cancelación antes de envío)

[rechazada]
    → reenviar()        → [generada]    (corregir datos y reintentar)

[validada]
    → anular() +
      nota crédito validada → [anulada]
    ⚠️  NO se puede anular si existe evento receptor tipo 033 (Aceptación Expresa)

[anulada]
    → ESTADO TERMINAL — no permite ninguna transición
```

**Eventos del receptor (independientes del estado de la factura):**
```
[validada] → registrar evento 030 (Acuse de Recibo)    — obligatorio
          → registrar evento 032 (Recibo del Bien)     — después del 030
          → registrar evento 033 (Aceptación Expresa)  — cierra posibilidad de reclamo
          → registrar evento 031 (Reclamo)             — dentro de 3 días hábiles, alternativo al 033
```

---

## 9. INTEGRACIÓN CON MÓDULOS EXISTENTES

### Con el módulo de Ventas
- Cuando una venta se confirma, crear automáticamente un `FeFactura` en estado `borrador` con los datos del cliente y los ítems de la venta.
- Agregar botón "Emitir Factura Electrónica" en la vista de detalle de venta.

### Con el módulo de Inventario
- Al validarse una factura (`estado → validada`), disparar el descuento de inventario correspondiente a través del evento `FacturaValidada`.
- Si la factura es anulada, revertir el movimiento de inventario.

### Con el módulo de Contabilidad/PUC
- Al emitirse una factura validada, generar automáticamente el asiento contable:
  - **Débito** cuenta `1305` (Clientes) por el valor total
  - **Crédito** cuenta `4135` (Ingresos por ventas) por el subtotal
  - **Crédito** cuenta `2408` (IVA generado) por el valor IVA
- Al emitirse una nota crédito validada, generar el asiento de reversión.

---

## 10. RUTAS Y CONTROLADOR

```php
// routes/web.php — grupo con middleware auth y prefix 'facturacion-electronica'

Route::prefix('facturacion-electronica')->name('fe.')->middleware('auth')->group(function () {
    // Facturas
    Route::get('/', [FacturacionElectronicaController::class, 'index'])->name('index');
    Route::get('/crear', [FacturacionElectronicaController::class, 'crear'])->name('crear');
    Route::post('/', [FacturacionElectronicaController::class, 'store'])->name('store');
    Route::get('/{factura}', [FacturacionElectronicaController::class, 'show'])->name('show');
    Route::post('/{factura}/emitir', [FacturacionElectronicaController::class, 'emitir'])->name('emitir');
    Route::post('/{factura}/reenviar', [FacturacionElectronicaController::class, 'reenviar'])->name('reenviar');
    Route::post('/{factura}/anular', [FacturacionElectronicaController::class, 'anular'])->name('anular');
    Route::get('/{factura}/xml', [FacturacionElectronicaController::class, 'verXml'])->name('xml');
    Route::get('/{factura}/representacion', [FacturacionElectronicaController::class, 'representacion'])->name('representacion');

    // Eventos del receptor
    Route::post('/{factura}/eventos', [FacturacionElectronicaController::class, 'registrarEvento'])->name('eventos.store');

    // Resoluciones
    Route::resource('resoluciones', FeResolucionController::class)->except('destroy');
});
```

---

## 11. VISTAS — INDICACIONES GENERALES

### `index.blade.php`
- Tabla con: número, adquirente, fecha, total, estado (badge de color), acciones
- Filtros por: estado, fecha desde/hasta, adquirente
- Botón "Nueva Factura Electrónica"

### `crear.blade.php`
- Selector de resolución activa (con contador de facturas disponibles en el rango)
- Sección datos del adquirente: tipo documento + número (con botón buscar en clientes existentes)
- Tabla dinámica de ítems (agregar/eliminar líneas con JS)
- Cálculo en tiempo real de subtotales, IVA, total
- Vista previa del CUFE (mostrar "Se generará al emitir")

### `detalle.blade.php`
- Mostrar todos los datos de la factura
- Panel de estado actual con historial de eventos (`fe_eventos`)
- Si estado=`validada`: mostrar ApplicationResponse y CUFE con botón copiar
- Visor del XML generado (coloreado)
- Sección "Eventos del Receptor" con los 4 botones de eventos y su estado
- Botón "Descargar representación gráfica (PDF)" — puede ser una vista Blade imprimible

---

## 12. SEEDERS — DATOS DE PRUEBA

Crear `FeResolucionSeeder` que genere:

```php
FeResolucion::create([
    'empresa_id'          => 1,
    'numero_resolucion'   => '18760000001',
    'prefijo'             => 'SEDU',
    'numero_desde'        => 1,
    'numero_hasta'        => 1000,
    'numero_actual'       => 1,
    'fecha_desde'         => '2024-01-01',
    'fecha_hasta'         => '2025-12-31',
    'clave_tecnica'       => Str::uuid(),   // Simulado
    'ambiente'            => '2',           // Siempre pruebas
    'activa'              => true,
]);
```

Crear `FeFacturaSeeder` con al menos 10 facturas en diferentes estados para demostración.

---

## 13. MANUAL DEL ESTUDIANTE — ESTRUCTURA Y CONTENIDO

Crear el archivo `resources/views/facturacion-electronica/manual.blade.php` con el siguiente contenido organizado. Este manual debe ser accesible desde la interfaz antes de usar el módulo.

### Capítulo 1 — ¿Qué es la Factura Electrónica y por qué existe?

**Contenido a desarrollar:**
- En Colombia, la facturación en papel permitía evasión de impuestos y falsificación de documentos. La DIAN implementó la factura electrónica para cerrar esas brechas.
- Una factura electrónica NO es simplemente un PDF. Es un documento XML estructurado, firmado digitalmente, que debe ser validado por la DIAN ANTES de entregarse al comprador.
- Obligatoria para todos los contribuyentes en Colombia desde 2024 (Resolución 000165 de 2023).
- Regulada principalmente por la Resolución 000042 del 5 de mayo de 2020.

### Capítulo 2 — Los Actores del Sistema

**Contenido a desarrollar:**
- **El Emisor (OFE — Obligado a Facturar Electrónicamente):** la empresa que vende y genera la factura. Tiene un NIT con dígito de verificación y una resolución de autorización de la DIAN.
- **El Adquirente:** quien compra. No es un espectador pasivo — también tiene obligaciones legales de responder con eventos dentro de plazos definidos.
- **La DIAN:** valida el documento ANTES de que sea válido. Sin validación DIAN, la factura no existe legalmente.
- **El Proveedor Tecnológico:** software certificado por la DIAN. En ContaEdu, este rol lo cumple el **simulador interno**.
- **RADIAN:** plataforma DIAN para convertir la factura en un título valor negociable.

### Capítulo 3 — Anatomía de una Factura Electrónica

**Contenido a desarrollar:**
- **El XML:** es el corazón del documento. Sigue el estándar UBL 2.1. Contiene: datos del emisor, datos del adquirente, cada ítem con su precio y código de impuesto, y los totales. El PDF que recibe el cliente es solo la representación visual del XML.
- **El CUFE:** Código Único de Factura Electrónica. Se calcula con SHA-384 usando 15 campos específicos de la factura + la clave técnica de la resolución. Tiene exactamente 96 caracteres. Garantiza que nadie puede alterar una factura sin invalidar el CUFE. La DIAN verifica este cálculo.
- **La Resolución de Autorización:** la DIAN autoriza un prefijo (Ej: "FE") y un rango de números (Ej: 1 al 1000) por un período de tiempo. Cuando se agota el rango o vence el período, hay que solicitar una nueva resolución. Sin resolución vigente no se puede emitir ninguna factura.
- **El QR:** código bidimensional que incluye la URL de verificación en el portal DIAN + el CUFE. Cualquier persona puede escanear el QR de una factura para verificar su autenticidad.
- **La Clave Técnica:** código secreto asignado por la DIAN al crear la resolución. Se incluye en el cálculo del CUFE. Garantiza que solo el titular del NIT puede generar facturas con ese CUFE válido.

### Capítulo 4 — El Flujo Completo de una Factura

**Contenido a desarrollar con diagrama:**
```
1. REGISTRAR LA VENTA en el sistema (datos del cliente, ítems, cantidades, precios)
2. VALIDAR INTERNAMENTE (¿resolución vigente? ¿totales cuadran? ¿NIT válido?)
   → Si falla: el sistema muestra el error. La factura queda en borrador.
3. GENERAR CUFE Y XML (el sistema calcula el hash y construye el documento)
4. TRANSMITIR A LA DIAN (en producción: servidor DIAN. En ContaEdu: simulador)
5. ESPERAR RESPUESTA (en producción: milisegundos. En ContaEdu: inmediata)
   → VALIDADA: la DIAN aceptó. El documento es legalmente válido. Se entrega al cliente.
   → RECHAZADA: la DIAN rechazó. Se muestra el código y mensaje de error. Hay que corregir y reintentar.
6. ENTREGAR AL CLIENTE el PDF (representación gráfica) con el QR y el CUFE impreso.
```

### Capítulo 5 — Los Eventos del Receptor

**Contenido a desarrollar:**
- Una vez que el adquirente recibe la factura, tiene obligaciones. Estos eventos se llaman **eventos del receptor** y se transmiten también a la DIAN.
- **030 — Acuse de Recibo:** confirma que recibió la factura electrónica. Obligatorio para poder deducir el IVA.
- **032 — Recibo del Bien o Servicio:** confirma que recibió físicamente la mercancía o el servicio fue prestado.
- **033 — Aceptación Expresa:** acepta la factura sin objeciones. A partir de este momento NO puede emitir un reclamo ni el emisor puede emitir nota crédito de anulación.
- **031 — Reclamo:** rechaza parcial o totalmente la factura. Debe hacerse dentro de los **3 días hábiles** siguientes al recibo del bien/servicio. Si no reclama en ese plazo, la aceptación se vuelve **tácita**.
- **Importancia para el adquirente:** sin el Acuse de Recibo (030) no tiene derecho a la deducción del IVA ni al impuesto descontable.

### Capítulo 6 — Notas Crédito y Débito

**Contenido a desarrollar:**
- **Nota Crédito:** disminuye el valor de una factura. Casos: devolución de mercancía, descuento posterior a la factura, anulación de la operación, corrección de datos del adquirente.
- **Nota Débito:** aumenta el valor de una factura. Caso: intereses de mora, mayor valor acordado después de la factura.
- **El CUDE:** las notas usan el CUDE (Código Único de Documento Electrónico) en lugar del CUFE. También es SHA-384 de 96 caracteres y hace referencia al CUFE de la factura origen.
- **Restricción crítica:** si el adquirente ya emitió la Aceptación Expresa (evento 033), NO se puede emitir nota crédito de anulación. La operación quedó firme jurídicamente.
- Una nota crédito NO borra la factura original — ambas documentos quedan en el sistema y se compensan.

### Capítulo 7 — Contingencia

**Contenido a desarrollar:**
- Si hay falla de internet o del sistema del proveedor tecnológico, el emisor puede usar **facturación de contingencia**.
- En contingencia se emite un documento en papel o PDF temporal con un número de documento de contingencia.
- Cuando se restablece la conexión, tiene **48 horas** para transmitir el documento a la DIAN usando el "Instrumento Electrónico de Transmisión".
- En ContaEdu: el simulador puede activar un modo de "contingencia" donde el estudiante aprende este flujo alternativo.

### Capítulo 8 — Cómo Usar el Simulador ContaEdu

**Contenido a desarrollar:**
- **¿Qué simula y qué no simula?**
  - ✅ Simula: cálculo real del CUFE, estructura XML UBL 2.1, validación de campos, respuesta DIAN (aceptación/rechazo), eventos del receptor, notas crédito/débito.
  - ❌ No simula: firma digital XAdES-EPES, conexión a servidores reales DIAN, habilitación de NIT, proveedores tecnológicos certificados.
- **El ambiente de pruebas:** ContaEdu siempre opera en ambiente `02` (Pruebas). En producción real se usa `01`.
- **Escenarios de práctica guiada:**
  1. Emitir una factura exitosa completa
  2. Provocar un rechazo por CUFE incorrecto (modificar un campo después de generado)
  3. Registrar los eventos del receptor completos
  4. Emitir una nota crédito por devolución parcial
  5. Intentar anular una factura con aceptación expresa (ver el error)
  6. Agotar un rango de resolución (ver qué ocurre)

---

## 14. CONSIDERACIONES TÉCNICAS ADICIONALES

### Concurrencia — Asignación de consecutivos
Usar `DB::transaction()` con `lockForUpdate()` al asignar el número consecutivo para evitar duplicados en entornos concurrentes:

```php
DB::transaction(function () use ($factura) {
    $resolucion = FeResolucion::lockForUpdate()->find($factura->resolucion_id);
    if ($resolucion->numero_actual > $resolucion->numero_hasta) {
        throw new RangoAgotadoException();
    }
    $factura->numero = $resolucion->numero_actual;
    $factura->numero_completo = $resolucion->prefijo . '-' . str_pad($resolucion->numero_actual, 6, '0', STR_PAD_LEFT);
    $resolucion->increment('numero_actual');
    $factura->save();
});
```

### Soft Deletes
Usar `SoftDeletes` en `FeFactura` y `FeNotaCredito`. Las facturas NUNCA se eliminan permanentemente — deben conservarse para auditoría.

### Eventos Laravel
Crear y disparar estos eventos para desacoplar módulos:
- `FacturaValidada` — escuchado por Contabilidad e Inventario
- `FacturaAnulada` — escuchado por Contabilidad e Inventario
- `EventoReceptorRegistrado` — para notificaciones

### Testing
Crear al menos los siguientes Feature Tests:
- `test_factura_pasa_validacion_completa`
- `test_cufe_se_calcula_correctamente`
- `test_factura_rechazada_por_nit_invalido`
- `test_no_se_puede_anular_con_aceptacion_expresa`
- `test_rango_agotado_lanza_excepcion`

---

## 15. ORDEN DE IMPLEMENTACIÓN SUGERIDO

1. Crear los Enums
2. Ejecutar las migraciones en el orden listado
3. Crear los Models con sus relaciones Eloquent
4. Implementar `ValidadorFacturaService` con todos sus métodos de validación
5. Implementar `CufeService` y verificar con valores de prueba conocidos
6. Implementar `XmlUblService` (estructura básica funcional)
7. Implementar `DiаnSimuladorService`
8. Implementar `FacturaService` como orquestador
9. Implementar `FacturacionElectronicaController`
10. Crear las vistas (index → crear → detalle)
11. Crear el manual del estudiante
12. Crear seeders
13. Escribir los Feature Tests
14. Integrar con Ventas, Inventario y Contabilidad vía eventos Laravel

---

*Documento generado para ContaEdu — Módulo de Facturación Electrónica Simulada*
*Basado en: Resolución DIAN 000042/2020, Resolución 000165/2023, Resolución 000015/2021, Estándar UBL 2.1*
*Stack: Laravel 11 + PostgreSQL | Ambiente: Pruebas (código 02)*
