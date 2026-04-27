# Restablecer Contraseña — ContaEdu

## Contexto del proyecto

ContaEdu es una plataforma contable educativa multi-tenant desplegada en Railway.
URL de producción: `https://contaedu-production-03ff.up.railway.app`

Stack: Laravel + Breeze, PostgreSQL, arquitectura multi-tenant schema-per-tenant.
Un solo dominio sirve a todos los tenants (sin subdominio por tenant).

Roles del sistema:
- **Coordinador / Docente** → se autentican con correo + contraseña → ruta `/login`
- **Estudiante** → se autentican con número de documento + contraseña → ruta `/estudiante/login`

Hasta ahora el reset de contraseña lo hacía el admin manualmente. No existe flujo
autoservicio implementado. Breeze tiene el mecanismo por debajo pero nunca se ha
activado en producción.

---

## Alcance de esta implementación

**Solo rol Docente en esta fase.** Una vez validado, se propaga a coordinador y estudiante.

El flujo de docente es el más limpio: usa correo como identificador, que es exactamente
el supuesto sobre el que Breeze está construido. Sirve para validar la infraestructura
(SMTP, tokens, tenant en rutas públicas) antes de tocar lógica más compleja.

---

## Antes de escribir cualquier código — diagnóstico obligatorio

Estas tres preguntas bloquean todo lo demás si no se responden primero.

### 1. ¿Cómo resuelve el tenant en rutas públicas?

El reset vive en rutas sin sesión activa (`/forgot-password`, `/reset-password/{token}`).
Con un solo dominio para todos los tenants, el sistema no sabe en qué schema buscar
el correo del docente.

**Revisar:**
- Cómo funciona el middleware de tenant actualmente
- Si el login (`/login`) resuelve el tenant antes de buscar el usuario, y cómo lo hace
- Si `password_reset_tokens` está en el schema central (`public`) o en cada tenant

Si el tenant no se resuelve en rutas públicas, el flujo de Breeze rompe silenciosamente:
no encuentra al usuario, no envía el correo, y no da error claro.

### 2. ¿Las vistas de Breeze están publicadas y modificadas?

El formulario de login de docentes no muestra el enlace "¿Olvidaste tu contraseña?",
lo que indica que la vista fue personalizada. Verificar si los controladores de auth
de Breeze también fueron modificados o si están intactos.

### 3. ¿Están configuradas las variables SMTP en Railway?

El correo de reset nunca se ha enviado en producción. Verificar que las variables
`MAIL_*` estén en Railway y no solo en `.env` local.

---

## Plan de tareas

### Fase 1 — Diagnóstico (no tocar código aún)

- [ ] Revisar el middleware de tenant: identificar cómo se activa y si funciona sin sesión
- [ ] Verificar en qué schema vive `password_reset_tokens`
- [ ] Verificar si los controladores de Breeze están publicados y modificados
- [ ] Confirmar variables SMTP en Railway (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`)
- [ ] Confirmar que la ruta `/forgot-password` de Breeze existe y responde

### Fase 2 — Resolver tenant en rutas públicas

Este es el riesgo de bloqueo principal. Dependiendo del resultado del diagnóstico:

**Si el tenant se resuelve por correo (el login ya lo hace así):**
Reutilizar ese mismo mecanismo en el flujo de reset. El docente ingresa su correo,
el sistema busca en qué tenant existe y activa ese contexto antes de continuar.

**Si el tenant requiere contexto previo que no existe en rutas públicas:**
Agregar el `tenant_id` como parámetro en el enlace de reset que se envía por correo,
de forma que cuando el usuario llega al formulario de nueva contraseña, el tenant
ya está identificado en la URL. Evaluar implicaciones de seguridad de exponer el
`tenant_id` en la URL.

**Cualquiera que sea la solución, debe garantizar:**
- Que la búsqueda del usuario en `forgot-password` ocurra en el schema correcto
- Que la validación del token en `reset-password` ocurra en el schema correcto
- Que un docente de un tenant no pueda (ni siquiera accidentalmente) resetear
  la contraseña de un usuario de otro tenant

### Fase 3 — Configuración SMTP

- [ ] Definir proveedor SMTP (Gmail con contraseña de aplicación es la opción sin costo adicional)
- [ ] Configurar variables en Railway
- [ ] Hacer prueba de envío aislada antes de conectarla con el flujo de reset
  (un tinker o un comando artisan simple que envíe un correo de prueba)
- [ ] Verificar que el correo llega y no cae en spam

### Fase 4 — Activar y personalizar el flujo de Breeze

- [ ] Agregar enlace "¿Olvidaste tu contraseña?" en la vista de login de docentes (`/login`)
- [ ] Personalizar la notificación de correo que Breeze envía por defecto
  (está en inglés y es genérica — adaptarla a ContaEdu en español)
- [ ] Personalizar la vista `forgot-password` con la identidad visual de ContaEdu
- [ ] Personalizar la vista `reset-password` con la identidad visual de ContaEdu
- [ ] Verificar que el texto del correo no incluya la URL de Railway en crudo
  (usar una variable de entorno `APP_URL` correctamente configurada)

### Fase 5 — Auditoría

Todos los eventos del flujo deben registrarse en la tabla `audit_logs` del schema
central (`public`), accesible desde el módulo de logs del superadmin.

Eventos a registrar:

| Evento | Acción en audit_logs | Severidad |
|---|---|---|
| Docente solicita reset (éxito) | `password_reset_requested` | info |
| Docente solicita reset (correo no encontrado) | `password_reset_failed` | warning |
| Contraseña actualizada | `password_reset_completed` | info |
| Token inválido o expirado | `password_reset_invalid_token` | warning |

**Importante sobre la respuesta al usuario:**
Siempre responder con el mismo mensaje genérico independientemente de si el correo
existe o no. Nunca revelar si un correo está o no registrado en el sistema.
Esto aplica tanto en el mensaje visible como en el tiempo de respuesta
(evitar diferencias de timing entre usuario encontrado y no encontrado).

Si la tabla `audit_logs` no existe aún en el schema central, crearla con:
- `id`, `action`, `description`, `user_id` (nullable), `tenant_id` (nullable),
  `ip`, `user_agent`, `meta` (json), `severity` (default: 'info'), `created_at`
- Índices en `action + created_at`, `tenant_id`, `user_id`
- Conexión explícita a la base de datos central, no al schema del tenant

### Fase 6 — Pruebas

- [ ] Flujo completo en local con usuario docente real
- [ ] Flujo completo en Railway con SMTP de producción
- [ ] Caso de correo inexistente → verificar respuesta genérica
- [ ] Caso de token expirado → verificar mensaje de error claro
- [ ] Caso de token ya usado → verificar que no permite reutilizarlo
- [ ] Verificar que los registros de auditoría aparecen en el módulo del superadmin
- [ ] Verificar que el enlace en el correo usa `APP_URL` correcto (no localhost)

---

## Decisiones de diseño ya analizadas

**¿Por qué no PHPMailer?**
Breeze usa el sistema de notificaciones de Laravel que trabaja con el driver de mail
configurado en `.env`. Laravel maneja el SMTP directamente sin necesidad de PHPMailer.
PHPMailer tiene sentido para correos construidos manualmente fuera de las notificaciones
de Laravel (avisos al coordinador, alertas del superadmin), pero no para el flujo de
reset que Breeze gestiona.

**¿Por qué empezar con docentes y no con todos los roles?**
El estudiante usa número de documento como identificador de login, lo que rompe el
supuesto de Breeze (correo como identificador). El flujo de reset del estudiante
requiere lógica adicional: verificar documento + correo juntos, y contemplar el caso
de correo desactualizado con aprobación del coordinador. Ese flujo es más complejo y
debe construirse una vez validada la infraestructura con el caso simple del docente.

**¿Por qué no usar datos documentales como llave de acceso directo para estudiantes?**
En contexto SENA los datos documentales (número de documento, fecha de nacimiento)
circulan en listas de clase y formatos institucionales. No son secretos reales en ese
entorno. Usarlos como único factor para cambiar contraseña directamente equivale a
dejar acceso a cualquier compañero de clase con acceso a una lista. Su rol correcto
es verificación para iniciar una solicitud supervisada por el coordinador, no para
ejecutar el cambio de forma autónoma.

---

## Contexto adicional relevante

- Estudiantes pueden tener 17 años (minoría de edad). La Ley 1581 de 2012 requiere
  autorización del acudiente para tratamiento de datos de menores. El correo registrado
  al momento de la matrícula de un estudiante de 17 años puede ser del acudiente, no
  del estudiante. Esto es relevante para la fase de estudiantes, no para esta fase.

- El coordinador debe poder resetear contraseñas manualmente desde el panel como
  canal de emergencia, independientemente del flujo autoservicio. Esto ya existe
  (era el único flujo hasta ahora) y debe mantenerse.

- El módulo de logs del superadmin debe poder filtrar por: acción, tenant_id,
  severidad, y rango de fechas. Si no está implementado aún, esta feature es
  el momento de construirlo junto con la auditoría del reset.
