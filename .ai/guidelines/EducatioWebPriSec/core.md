> **Este archivo se mantiene mínimo a propósito.** La mejor documentación es el código: si las convenciones están claras y son consistentes en el repo, la IA las infiere sola y la instrucción extra sobra — incluso es contraproducente. Acá se escribe **solo lo que el código todavía no demuestra**, y se va sacando a medida que el código lo vuelve evidente. Cada dev puede tener, localmente, los agentes y skills que quiera.

---


## Pull Request Rules

- **Branch base**: todos los PRs van a `develop` (nunca directamente a `main`)
- **Reviewer por defecto**: `DanielJoseDolz` — agregarlo siempre con `--reviewer DanielJoseDolz`

---

## Database Conventions — Baja lógica y auditoría

**No usar `timestamps()` (`created_at`/`updated_at`) ni `softDeletes()` (`deleted_at`) como mecanismo general** — ni para auditoría ni para baja lógica. Estos campos no permiten reconstruir qué cambió, quién lo hizo, ni revertir.

- **Baja lógica**: campo explícito `activo BOOLEAN NOT NULL DEFAULT TRUE`. Nunca `deleted_at`.
- **Auditoría**: tabla general `logs` (entidad, entidad_id, accion, usuario_id, login, anterior JSON, nuevo JSON, ip, user_agent, origen, created_at). Toda creación/actualización auditada debe loguear `create`/`update`/`deactivate`/`reactivate` dentro de la misma transacción que la operación principal.
- La solución de auditoría debe ser centralizada (trait/observer/servicio), no copiada por controlador.
- **Sin cascades en tablas de dominio**: no usar `cascadeOnDelete()` ni cascades equivalentes en relaciones propias del dominio. Las bajas son lógicas con `activo`; si alguna eliminación física fuera necesaria, debe resolverse explícitamente en código/transacción y auditarse. Excepciones técnicas de paquetes/auth se evalúan caso por caso.
- **Teléfonos**: guardar normalizados en formato E.164 siempre que sea posible (`+549...`). Si se conserva el texto original ingresado por el usuario, guardarlo en un campo separado explícito.

Detalle completo: ver issue "Implementar baja lógica y tabla general de logs" en GitHub. Cuando se proponga una migration nueva, no agregar `timestamps()`/`softDeletes()` salvo que se discuta explícitamente una excepción.

### Orden de campos

**`activo` va siempre inmediatamente después de `id`.** No al final, no entre los datos.

```php
$table->id();
$table->boolean('activo')->default(true);
// ...el resto de los campos
```

El orden de columnas no tiene efecto funcional: es para el que lee la tabla. Por eso es regla y no preferencia.

Verificado por `tests/Feature/ArchitectureTest.php`.

### Migraciones — mientras dure el desarrollo

Se trabaja con `php artisan migrate:fresh --seed` ante cada cambio de diseño.

- **No se escriben alters** (`Schema::table(...)`). El cambio va en la migration original.
- **No se escriben backfills ni migraciones de datos.** Los datos salen del seeder.

### Nombres de índices y constraints

Nombrar explícitamente, de forma corta, todos los índices y constraints creados en migrations (incluidas FK y restricciones `unique`). MariaDB limita esos identificadores a 64 caracteres y los nombres automáticos de Laravel pueden excederlo con tablas o columnas largas. El error a evitar es:

```
SQLSTATE[42000]: Syntax error or access violation: 1059 Identifier name 'locales_plataformas_delivery_local_id_plataforma_delivery_id_unique' is too long
```

SQLite no impone este límite, por lo que una migration puede pasar los tests con SQLite en memoria y fallar luego en MariaDB.

```php
$table->unique(['local_id', 'plataforma_delivery_id'], 'lpd_local_plataforma_unique');
$table->foreign('local_id', 'lpd_local_fk')->references('id')->on('locales');
```

> ⏱ **Esta regla vence cuando haya datos reales en producción.** Desde ese momento toda modificación de esquema va por alter y no se toca una migration ya aplicada. Revisar esta sección —y borrar el test correspondiente— cuando eso ocurra.

---

## Autorización — diferida

El sistema requiere **usuario autenticado**, pero por ahora **no hay reglas de autorización**: todos los usuarios pueden hacer **todo**. Los roles/permisos se agregan más adelante. No inventar roles ni chequeos de permisos mientras tanto.

Para que ese agregado después sea un **relleno y no una reescritura**, dejar las costuras desde el primer CRUD:

- Autorizar vía **Policies/Gates de Laravel** y `authorize()` en los Form Requests, aunque hoy devuelvan `true`. **No** dispersar lógica de permisos inline por controlador/pantalla.
- **Navegación/menú centralizado** en un solo lugar (config-driven), no links de nav por pantalla. La visibilidad por rol, cuando exista, debe ser un solo punto de cambio.
- Al menos **un test por recurso**: es la red que hace verificable el barrido cuando se agreguen los permisos (un endpoint sin cubrir es un agujero silencioso).
- El **alcance de datos por usuario** (ej. un docente ve solo sus materias/grupos) **no** es parte del barrido final: se decide al modelar esa entidad, porque afecta las queries. Si una pantalla es intrínsecamente "por usuario", modelar ese scoping cuando se construye.

> No es multitenant (decisión de dominio). No modelar "scope"/tenant ni layouts por tenant.

---

## Convenciones de UI  y experiencia de uso

## Principios de diseño de Educatio

Educatio es una herramienta interna de trabajo diario para personal administrativo y docente. No es una aplicación de marketing ni una app destinada a vender el producto.

- Priorizar productividad, claridad, consistencia y velocidad de carga por encima de efectos visuales o elementos decorativos.
- Mantener una estética moderna, limpia, sobria y profesional, sin colores llamativos innecesarios.
- La información principal, los filtros y las acciones frecuentes deben estar visibles y accesibles con la menor cantidad razonable de clics.
- La interfaz puede ser densa cuando el trabajo lo requiera, pero nunca confusa.
- Una acción frecuente debe requerir la menor cantidad posible de pasos sin sacrificar claridad, validación o seguridad.

## Navegación con teclado

Educatio debe poder operarse eficientemente con teclado. Sus usuarios realizan carga intensiva de datos durante gran parte de la jornada.

- `TAB` define el recorrido natural entre controles.
- `ENTER` ejecuta la acción principal de la pantalla o formulario, como Aceptar, Guardar o Buscar, siempre que el foco no esté en un campo multilínea ni en un componente donde Enter tenga otra función esperable.
- El orden de tabulación debe coincidir con el flujo lógico de carga.
- El primer campo editable debe recibir foco cuando corresponda.
- Los campos `readonly` o deshabilitados no deben interrumpir innecesariamente el recorrido con `TAB`.
- No aceptar componentes que rompan la navegación por teclado o requieran el mouse para una operación habitual.
- Todo componente personalizado debe conservar estados de foco visibles y accesibles.


### Tablas (`EureDataTable`)

- Ordenable por **todas** las columnas: definir `accessorFn` (no alcanza con `cell`, que solo renderiza — sin `accessorFn` la columna no ordena por el valor real).
- Alineación: **precios a la derecha** (`text-right tabular-nums`); **logins, cantidades y fechas centrados**.
- Los IDs se muestran centrados con `#` como título de columna. Según el caso (evaluar) formatear con 0s a la izquierda (ej: `000123`) con longitud que tenga que ver con la entidad.
- Las pantallas deben ser operativas en 1366x768. Se puede aprovechar ancho completo en monitores grandes, pero la información principal, filtros y acciones frecuentes no deben depender de tener más espacio.
- Las grillas deben degradar bien en tamaños chicos. Evaluar columnas prescindibles que desaparezcan en monitores chicos; para entidades con `codigo` + `descripcion`, evaluar un componente tipo `ResponsiveText` que muestre descripción en ancho cómodo y código en ancho reducido.

### Filtros y búsquedas

- Toda búsqueda backend con `LIKE` debe escapar `%` y `_` para tratarlos como texto literal. Deuda técnica: centralizar este escape en un helper común para no repetirlo por pantalla/controlador.
- En filtros sobre entidades relacionadas, incluir opciones inactivas cuando existan datos históricos que puedan apuntar a ellas; mostrarlas con sufijo `(inactivo)`.
- Las URLs con query string no hacen self-healing: si el usuario edita manualmente una combinación válida pero sin resultados, se muestra vacío. Sólo corregir/rechazar cuando haya un problema de integridad o autorización.
- Si una pantalla combina filtros server-side con búsqueda client-side de `EureDataTable`, diferenciar el placeholder de la búsqueda client-side, por ejemplo `Buscar en resultados...`.

### Fechas

- Siempre `dd/MM/yyyy`. Con hora: `dd/MM/yyyy HH:mm` (24h).
- Usar los helpers de `@/lib/date` — nunca formatear inline.
- Donde aporte, usar `RelativeDateToggle`. Aplicarlo solo donde tiene sentido, y probarlo.

### Dinero

- Usar `EureInputMoney` (formatea al perder el foco).
- Al escribir, **coma y punto** valen ambos como separador decimal.

### Formularios

- El ancho del campo acompaña al dato: un precio **no** ocupa el 100% del ancho; una descripción sí.
- Los labels deben permanecer visibles. No usar placeholders como reemplazo del label.
- Los formularios deben abrir listos para trabajar y respetar un recorrido de carga predecible.
- Los mensajes de validación deben aparecer junto al campo correspondiente y conservar el valor ingresado cuando sea posible.
- La acción principal debe ser inequívoca y compatible con `ENTER` según las reglas de navegación por teclado.

### General

- **Modo oscuro** soportado en toda pantalla nueva.
- Todo select (`EureSelect`, `EureSelectGroup`, `EureMultiSelect`) debe permitir **buscar**.
- **Helpers y formatos unificados**: si una función de formato ya existe en `@/lib` (`date.ts`, `money.ts`), se reusa. **No escribir funciones de formato por pantalla** — es un patrón que la IA tiende a repetir; no aceptarlo en review.
- Toda funcionalidad nueva debe tener **entrada en el menú** para poder probarla, aunque sea un menú "dev". Una pantalla a la que no se puede llegar no está terminada.

---

## Educatio — Project Context

EducatioWeb PriSec es la reescritura web del sistema de gestión escolar **Educatio** de Eureka Soluciones Informáticas. El legacy es una aplicación de escritorio VB6 en producción desde ~2004, con dos grandes áreas: **Académico** (alumnos, responsables, materias y planes de estudio, calificaciones, asistencia, boletines, certificados, docentes, sanciones, cuaderno de comunicaciones) y **Administrativo** (facturación electrónica AFIP, cobranzas, cuenta corriente de alumnos y empresas, caja, cheques, morosos, planes de cuotas, débito automático y contabilidad). El alcance de esta reescritura es **Primaria y Secundaria** (PriSec); el nivel Terciario del legacy queda fuera.

El proyecto se construye sobre **EureFramework** (base técnica Laravel + Inertia + React, sin dominio). Al arrancar todavía no hay dominio implementado: se parte de la base y se va incorporando el dominio de Educatio.

### El código legacy (referencia)

El sistema viejo (VB6) es la referencia funcional para reconstruir el dominio. Si está disponible localmente, vive en la **carpeta hermana del repo**: `../Legacy/EducatioSecVB` (fuera del repo y **no versionado**). El mapa de módulos y cómo leer los `.frm` está en `docs/context/05-legacy-educatio.md`.

> Al estar **fuera del repo**, conocer la ruta no alcanza: hay que **darle acceso a esa carpeta al agente** de forma explícita. No hardcodear rutas absolutas de una máquina (usar la relativa `../Legacy/EducatioSecVB`).

### Domain context (docs — leer antes de trabajar en lógica de negocio)

- `docs/context/01-proposito.md` — propósito, stack, contexto de la reescritura
- `docs/context/02-glosario.md` — terminología clave (Educatio, legajo, rematriculación, boletín, cuenta corriente, CAE, etc.)
- `docs/context/03-academico.md` — dominio académico: alumnos, responsables, planes, calificaciones, asistencia, boletines
- `docs/context/04-administrativo.md` — facturación electrónica, cobranzas, cuenta corriente, caja, morosos
- `docs/context/05-legacy-educatio.md` — alcance y referencia funcional del sistema legacy VB6
- `docs/context/06-equipo-y-metodologia.md` — cómo trabajamos (git flow, PRs, revisión)

### Sobre estos docs

Son **contexto de dominio durable** (propósito, glosario, referencia funcional del legacy), no planning volátil. No se cargan acá sprint plans, user stories ni modelos de datos detallados: eso envejece frente al código.

Fuente de verdad para el modelo de dominio: el **código + las migraciones**. Para el backlog: los **issues de GitHub**. Para las convenciones: este archivo.

## Commands

```bash
# Start dev server (Laravel + queue + Vite, concurrently)
composer dev

# Run all tests (clears config, checks Pint, runs Pest)
composer test

# Run a single test
php artisan test --filter=TestName
./vendor/bin/pest --filter=TestName

# PHP linting (Laravel Pint)
composer lint          # auto-fix
composer lint:check    # check only

# JS/TS linting and formatting
npm run lint           # ESLint auto-fix
npm run lint:check     # ESLint check only
npm run format         # Prettier auto-fix
npm run format:check   # Prettier check only
npm run types:check    # TypeScript check (no emit)

# Full CI check
composer ci:check

# Regenerate Wayfinder bindings (run after adding/changing routes)
php artisan wayfinder:generate
```

## Architecture

### Request lifecycle

A request hits Laravel → Inertia middleware → Controller returns `Inertia::render('page-name', $props)` → Vite serves the React SPA → Inertia hydrates the matching page component in `resources/js/pages/` with the props.

### Layout assignment

`resources/js/app.tsx` assigns layouts by page name convention:
- `welcome` → no layout
- `auth/*` → `AuthLayout`
- `settings/*` → `[AppLayout, SettingsLayout]` (nested)
- everything else → `AppLayout`

New pages must follow this naming convention or the layout won't apply.

### Shared Inertia data

`app/Http/Middleware/HandleInertiaRequests.php` shares global props (auth user, flash messages, etc.) with every Inertia response. Flash toasts are consumed client-side via `hooks/use-flash-toast.ts`.

### Wayfinder (auto-generated — never edit manually)

`resources/js/actions/` and `resources/js/routes/` are fully generated by `php artisan wayfinder:generate`. They provide type-safe TypeScript bindings for every Laravel route and controller action.

### Authentication

Laravel Fortify handles all auth flows (login, register, password reset, email verification, 2FA, passkeys). Views are wired in `FortifyServiceProvider::configureViews()` to render Inertia pages under `auth/`. Passkey support comes from `@laravel/passkeys`.

### UI components

`resources/js/components/ui/` contains shadcn/ui primitives (Radix UI-based). Application-specific components live in `resources/js/components/`. The `@/*` alias maps to `resources/js/`.

**Never use `npx shadcn@latest add <component>`** — the CLI auto-detects pnpm when it's installed globally and runs `pnpm add` instead of `npm`, breaking the project. Instead: install the Radix primitive with `npm install @radix-ui/react-<name>` and create the `resources/js/components/ui/<component>.tsx` file manually, following the existing shadcn v4 pattern (function component with `data-slot`, no forwardRef — see `checkbox.tsx` as reference).

### Testing

Tests use SQLite in-memory (`DB_DATABASE=:memory:`). Feature tests cover auth flows and settings. Use `tests/Pest.php` to add global helpers or dataset definitions. Test files use Pest's functional syntax, not class-based PHPUnit.

### CSS

Tailwind v4 uses CSS-first configuration — there is no `tailwind.config.js`. Custom tokens and theme overrides go in `resources/css/app.css`.
