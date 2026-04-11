# TASK 09 — Rediseño del Sidebar: Dos Zonas Pedagógicas

> ⚠️ Prerequisito: TASK 08 completado y verificado (módulo Banco creado).
> ⚠️ Este TASK es exclusivamente de UI — no toca ninguna lógica de negocio,
>     ninguna migración, ningún modelo ni ningún controlador.
>     Solo modifica el layout del sidebar.

---

## Objetivo

Reorganizar el sidebar de ContaEdu en dos zonas visualmente diferenciadas que reflejan el modelo pedagógico de la plataforma:

- **Zona superior — Módulo de Aprendizaje:** donde el profesor enseña y el estudiante aprende los conceptos contables
- **Zona inferior — Mi Empresa:** donde el estudiante aplica todo lo aprendido operando su empresa simulada

---

## Contexto pedagógico

El modelo de aprendizaje de ContaEdu sigue la misma lógica del SENA:

```
ZONA SUPERIOR (el aula)
  El profesor demuestra → el estudiante aprende el PUC,
  registra asientos, entiende los reportes, practica
  ejercicios guiados paso a paso.

ZONA INFERIOR (la empresa)
  El estudiante ejecuta → aplica todo lo aprendido
  en su propia empresa, toma decisiones, negocia,
  mueve dinero, gestiona su banco. Los resultados
  se reflejan automáticamente en la zona superior
  (los asientos de Negocios y Banco aparecen en el
  Libro Diario, el Balance y los Reportes).
```

Esto cierra el ciclo completamente — las decisiones empresariales de la zona inferior tienen consecuencias contables visibles en la zona superior.

---

## Estructura final del sidebar

```
─── APRENDIZAJE CONTABLE ─────────────────
  🏠 Inicio
  🧾 Facturas
  🛒 Compras
  👥 Terceros
  📦 Productos
  📊 Plan de cuentas
  📈 Reportes
  📅 Calendario
  🏗️ Activos fijos
  ⚖️ Conciliación
  ⚡ F. Electrónica
  ⚙️ Configuración
  🏢 Empresas Maestras

─── MI EMPRESA ───────────────────────────
  🤝 Negocios
  🏦 Banco
```

---

## Cambios específicos en el sidebar

### 1. Separador visual "APRENDIZAJE CONTABLE"
Agregar antes del primer ítem (Inicio) una etiqueta separadora sutil:

```html
<div class="sidebar-section-label">
    APRENDIZAJE CONTABLE
</div>
```

Estilo: texto muy pequeño, mayúsculas, color gris claro (no intrusivo), sin fondo destacado. Debe sentirse como una guía, no como un encabezado dominante.

### 2. Separador visual "MI EMPRESA"
Agregar antes de "Empresas Maestras" una línea divisoria + etiqueta:

```html
<div class="sidebar-divider"></div>
<div class="sidebar-section-label">
    MI EMPRESA
</div>
```

La línea divisoria es sutil — un borde de 1px en color verde oscuro con opacidad reducida, coherente con el diseño forest green/gold existente.

### 3. Orden de ítems — zona inferior
El orden en "Mi Empresa" debe ser exactamente:

```
1. Negocios   (ya existe)
2. Banco      (agregado en TASK 08)
```

### 4. Orden de ítems — zona superior
Empresas Maestras se ubica al final de la zona de aprendizaje, después de Configuración:

```
...
11. Conciliación
12. F. Electrónica
13. Configuración
14. Empresas Maestras
```

### 4. Ítem "Banco" en el sidebar
Agregar el ítem Banco con su ícono correspondiente (🏦 o ícono de banco del set de íconos que usa ContaEdu), coherente con el estilo visual de los demás ítems.

---

## Estilos CSS a agregar

Mantener coherencia total con el sistema de diseño forest green/gold existente. No inventar colores nuevos.

```css
/* Etiqueta de sección */
.sidebar-section-label {
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.4);  /* blanco con opacidad sobre fondo verde oscuro */
    padding: 0.5rem 1rem 0.25rem;
    margin-top: 0.5rem;
}

/* Línea divisoria entre zonas */
.sidebar-divider {
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    margin: 0.75rem 1rem;
}
```

---

## Lo que NO se toca en esta tarea

- Ninguna lógica de negocio
- Ninguna migración ni modelo
- Ningún controlador ni componente Livewire
- El orden de los ítems dentro de "Aprendizaje Contable" — quedan exactamente como están
- Los íconos existentes de cada ítem
- El comportamiento del ítem activo (highlight verde)
- El comportamiento en mobile si existe

---

## Verificación

1. Abrir ContaEdu como estudiante
2. Ver el sidebar con dos zonas claramente diferenciadas
3. La etiqueta "APRENDIZAJE CONTABLE" aparece sutil antes de Inicio
4. La línea divisoria + etiqueta "MI EMPRESA" aparece antes de Empresas Maestras
5. El ítem "Banco" aparece como último ítem de la zona inferior
6. Al hacer clic en cualquier ítem — funciona exactamente igual que antes
7. El ítem activo mantiene su highlight verde correctamente en ambas zonas
8. En mobile (si aplica) — el sidebar se comporta igual que antes
9. No hay ningún cambio visual en la zona superior más allá de la etiqueta
