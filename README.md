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
| PDF | `barryvdh/laravel-dompdf` |
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
# 4. Migrar y sembrar datos demo
php artisan migrate:fresh --seed
```

> **Nota:** Si ya existen schemas de tenant de una ejecución anterior, primero ejecutar:
> ```
> php artisan tenants:drop-schemas
> php artisan migrate:fresh --seed
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
├── schema: public              ← BD central: institutions, groups, users, tenants
├── schema: tenant_cc1023456789 ← Empresa de Ana García
├── schema: tenant_cc1098765432 ← Empresa de Luis Pérez
└── schema: tenant_cc1055544433 ← Empresa de María Rodríguez
```

Cada empresa estudiantil vive en su propio schema PostgreSQL. La identificación del tenant se hace por **cédula del estudiante** (no por subdominio), lo que simplifica el deploy en Railway.

---

## Módulos implementados

| Fase | Módulo |
|---|---|
| 1 | Fundación: login por rol, multitenancy, guards separados |
| 2 | Maestros contables: configuración empresa, PUC colombiano, terceros, productos |
| 3 | Ventas: facturas, notas crédito, recibos de caja + asientos automáticos |
| 4 | Compras: órdenes, facturas de compra, pagos a proveedores + asientos automáticos |
| 5 | Reportes: libro diario, mayor, balance de comprobación, estado de resultados, balance general, cartera, CxP, exportación PDF |
| 6 | Panel docente: dashboard grupo, modo auditoría (solo lectura), comparativo, rúbrica de calificación |

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

1. Crear un proyecto con un servicio **Laravel** y un servicio **PostgreSQL**
2. Configurar variables de entorno: `DB_CONNECTION=pgsql`, `APP_KEY`, `APP_URL`
3. Comando de inicio: `php artisan serve --host=0.0.0.0 --port=$PORT`
4. En cada deploy ejecutar: `php artisan migrate --force` y `php artisan tenants:migrate --force`

---

Ver [PLATAFORMA.md](PLATAFORMA.md) para la guía completa de uso de la plataforma.
