# ContaEdu — Plataforma Contable Educativa

Sistema web educativo tipo SaaS multi-tenant que simula un software contable colombiano (similar a Siigo), diseñado para que estudiantes de administración y contabilidad practiquen el **ciclo contable completo** en un entorno seguro y aislado.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Framework | Laravel 12 |
| Frontend | Livewire 4 + Alpine.js + Blade |
| Estilos | Tailwind CSS v4 |
| Base de datos | PostgreSQL (schemas por tenant) |
| Multitenancy | `stancl/tenancy` v3 — modo schemas |
| PDF | `barryvdh/laravel-dompdf` ^3.1 |
| Notificaciones JS | SweetAlert2 v11 (CDN) |
| Autenticación | Laravel Breeze + guard `student` |
| Testing | Pest 4 |
| Entorno local | Laravel Herd (Windows) |

---

## Requisitos

- PHP 8.3+
- PostgreSQL 14+
- Node.js 18+
- Composer 2+
- Laravel Herd (o servidor equivalente)

---

## Instalación local

```bash
# 1. Clonar el repositorio
git clone <url-del-repo> contaedu
cd contaedu

# 2. Instalar dependencias PHP y JS
composer install
npm install && npm run build

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate
```

Editar `.env` con las credenciales de PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=contaedu
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

```bash
# 4. Migrar base de datos central
php artisan migrate

# 5. Sembrar datos centrales (CIIU, usuarios demo, instituciones)
php artisan db:seed --class=CiiuSeeder

# 6. Crear y migrar tenants de prueba
php artisan tenants:migrate

# 7. Sembrar PUC colombiano en cada tenant
php artisan tenants:seed --class=PucSeeder
```

> **Nota:** Si ya existen schemas de tenant de una ejecución anterior, primero ejecutar:
> ```bash
> php artisan migrate:fresh --seed
> php artisan tenants:migrate --fresh
> php artisan tenants:seed --class=PucSeeder
> ```

---

## Credenciales de acceso

| Rol | URL de login | Email / Cédula | Contraseña |
|---|---|---|---|
| Superadministrador | `/login` | admin@contaedu.test | password |
| Docente | `/login` | docente@contaedu.test | password |
| Estudiante — Ana García | `/estudiante/login` | cc1023456789 | password |
| Estudiante — Luis Pérez | `/estudiante/login` | cc1098765432 | password |
| Estudiante — María Rodríguez | `/estudiante/login` | cc1055544433 | password |

---

## Arquitectura multi-tenant

```
PostgreSQL (una sola instancia)
├── schema: public                  ← BD central
│   ├── users, institutions, groups
│   ├── tenants (empresas estudiantes + demos)
│   ├── ciiu_codes (508 actividades económicas DIAN 2020)
│   ├── student_scores, announcements
│   └── transfer_requests, reference_access_logs
│
├── schema: tenant_cc1023456789     ← Empresa de Ana García
├── schema: tenant_cc1098765432     ← Empresa de Luis Pérez
└── schema: tenant_cc1055544433     ← Empresa de María Rodríguez
    └── (cada schema contiene: accounts, journal_entries, invoices,
        products, thirds, fixed_assets, fe_facturas, bank_reconciliations, …)
```

Cada empresa estudiantil vive en su propio schema PostgreSQL. La identificación del tenant se hace por **cédula del estudiante** (no por subdominio), lo que simplifica el deploy en Railway.

---

## Módulos implementados

| Fase | Módulo | Descripción |
|---|---|---|
| 1 | Fundación | Login por rol, multitenancy, guards separados (`web` / `student`) |
| 2 | Maestros contables | Configuración empresa (CIIU, NIT, sector), PUC colombiano completo (408+ cuentas), terceros, productos |
| 3 | Ventas | Facturas, notas crédito, recibos de caja + asientos automáticos |
| 4 | Compras | Órdenes de compra, facturas de compra, pagos a proveedores + asientos automáticos |
| 5 | Reportes contables | Libro diario, mayor, balance de comprobación, estado de resultados, balance general, cartera (CxC), cuentas por pagar (CxP), liquidación IVA |
| 6 | Panel docente | Dashboard de grupo, modo auditoría (solo lectura), comparativo entre estudiantes, rúbrica de calificación |
| 7 | Activos fijos | Registro de propiedades/planta/equipo, depreciación línea recta automática, normas colombianas de vida útil |
| 8 | Conciliación bancaria | Comparación extracto vs. libros, partidas bancarias, cuadre automático, finalización y bloqueo |
| 9 | Facturación electrónica | Resoluciones DIAN, emisión al simulador DIAN, CUFE (SHA-384), representación gráfica, notas crédito FE |

---

## Exportación PDF

Todos los módulos cuentan con exportación PDF usando DomPDF con la paleta de colores ContaEdu:

| Documento | Ruta |
|---|---|
| Reportes contables (8 tipos) | `/empresa/reportes` → botón PDF |
| Conciliación bancaria | `/empresa/conciliacion-bancaria` → botón PDF |
| Activos fijos | `/empresa/activos-fijos` → botón PDF |
| Factura electrónica | `/empresa/facturacion-electronica/{id}` → botón PDF / representación gráfica |

> **Nota DomPDF:** Los márgenes se aplican con `padding` en el div contenedor (`.page`), no con `@page { margin }` ni `setOption`, ya que DomPDF ignora ambos.

---

## Paleta de colores ContaEdu

| Token | Hex | Uso |
|---|---|---|
| `forest-800` | `#10472a` | Color principal, encabezados |
| `forest-700` | `#165e36` | Thead de tablas PDF |
| `forest-50` | `#edf8f2` | Fondos de cards y celdas |
| `forest-100` | `#d4f0e1` | Bordes suaves |
| `gold-500` | `#d4a017` | Botones de acción primaria |

---

## PUC colombiano

El Plan Único de Cuentas se siembra automáticamente en cada tenant con `PucSeeder`. Incluye cuentas de los niveles 1 al 4 (clases 1 a 6 y cuentas de orden clase 9). El seeder usa `insertOrIgnore` para ser idempotente.

Los 508 códigos CIIU de actividades económicas DIAN 2020 se cargan en la base de datos central desde `database/data/ciiu_codes.json`.

---

## Confirmaciones interactivas

Todos los diálogos de confirmación (`wire:confirm` de Livewire y formularios HTML) usan **SweetAlert2** en lugar del `window.confirm()` nativo del navegador. La integración está en `resources/views/layouts/tenant.blade.php`.

---

## Tests

```bash
# Correr toda la suite
php artisan test --compact

# Filtrar por nombre
php artisan test --compact --filter=AdminDashboard
```

---

## Deploy en Railway

### Pre-deploy step (migraciones y seeders)

En Railway usar el campo **"Add pre-deploy step"** (NO el Start Command):

```
php artisan migrate --force && php artisan db:seed --class=CiiuSeeder --force && php artisan tenants:seed --class=PucSeeder --force
```

> **Importante:** Dejar el Start Command **vacío**. Nixpacks detecta automáticamente PHP y levanta PHP-FPM + Nginx. Poner el comando de migraciones en Start Command reemplaza el servidor web y deja la app caída.

### Variables de entorno requeridas

```env
APP_ENV=production
APP_KEY=<generar con php artisan key:generate>
APP_URL=https://tu-app.railway.app
DB_CONNECTION=pgsql
DB_HOST=<host de Railway PostgreSQL>
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=<password de Railway>
```

---

Ver [PLATAFORMA.md](PLATAFORMA.md) para la guía completa de uso de la plataforma.
