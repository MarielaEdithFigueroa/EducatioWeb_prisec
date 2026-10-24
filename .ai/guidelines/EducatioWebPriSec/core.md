> **Este archivo se mantiene mínimo a propósito.** La mejor documentación es el código: si las convenciones están claras y son consistentes en el repo, la IA las infiere sola y la instrucción extra sobra — incluso es contraproducente. Acá se escribe **solo lo que el código todavía no demuestra**, y se va sacando a medida que el código lo vuelve evidente. Cada dev puede tener, localmente, los agentes y skills que quiera.

---


## Pull Request Rules

- **Branch base**: todos los PRs van a `develop` (nunca directamente a `main`)
- **Reviewer por defecto**: `DanielJoseDolz` — agregarlo siempre con `--reviewer DanielJoseDolz`

---

## Database Conventions — Baja lógica y auditoría

**No usar `timestamps()` (`created_at`/`updated_at`) ni `softDeletes()` (`deleted_at`) como mecanismo general** — ni para auditoría ni para baja lógica. Estos campos no permiten reconstruir qué cambió, quién lo hizo, ni revertir.

- **Baja lógica**: campo explícito `activo BOOLEAN NOT NULL DEFAULT TRUE`. Nunca `deleted_at`.
- **Excepción — catálogos de entidades raramente móviles**: si la tabla modela algo que en la práctica no se desactiva (ej. países/nacionalidades, grupos sanguíneos, motivos de baja, tipos de vínculo), no se agrega `activo`. Son enumerados que sólo crecen: se corrigen agregando filas nuevas, no desactivando las viejas. El campo `activo` es para catálogos que la operación del colegio efectivamente necesita poder desactivar sin borrar (ej. condiciones especiales, cuyo criterio clínico puede quedar obsoleto).
- **Auditoría**: tabla general `logs` (entidad, entidad_id, accion, usuario_id, login, anterior JSON, nuevo JSON, ip, user_agent, origen, created_at). Toda creación/actualización auditada debe loguear `create`/`update`/`deactivate`/`reactivate` dentro de la misma transacción que la operación principal.
- La solución de auditoría debe ser centralizada (trait/observer/servicio), no copiada por controlador.
- **Sin cascades en tablas de dominio**: no usar `cascadeOnDelete()` ni cascades equivalentes en relaciones propias del dominio. Las bajas son lógicas con `activo`; si alguna eliminación física fuera necesaria, debe resolverse explícitamente en código/transacción y auditarse. Excepciones técnicas de paquetes/auth se evalúan caso por caso.
- **Teléfonos**: aplicar siempre el patrón compartido de la sección «Teléfonos» de este archivo. Guardar en E.164; si se conserva la entrada original, usar un campo separado explícito.
- **CUILT**: cuando un campo admite indistintamente CUIL o CUIT, la columna y el nombre de dominio son `cuilt`. Es intencional: no renombrarlo a `cuil_cuit` ni a ninguna otra variante.

Detalle completo: ver issue "Implementar baja lógica y tabla general de logs" en GitHub. Cuando se proponga una migration nueva, no agregar `timestamps()`/`softDeletes()` salvo que se discuta explícitamente una excepción.

> ⚠️ **Precedencia sobre el skill `laravel-best-practices`**: el ejemplo `->constrained()->cascadeOnDelete()` de `rules/migrations.md` no aplica a tablas de dominio de este proyecto. Ese archivo lo regenera `boost:update` (viene de `vendor/laravel/boost`); no se edita a mano — la regla de este archivo domina.

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

> ⚠️ **Precedencia sobre el skill `laravel-best-practices`**: "Never Modify Deployed Migrations" (`rules/migrations.md`) es la regla correcta a largo plazo, pero no aplica mientras dure esta fase — acá gana "no se escriben alters". Ídem arriba: ese archivo es generado, no se edita a mano.

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

### Estilo de pantallas y tarjetas

**Toda pantalla nueva debe respetar este estándar desde su creación.** Para pantallas existentes, aplicarlo al renovar su interfaz.

El estilo toma como antecedente los rediseños de Clientes, Talonarios y Preventas de Pranzo, adaptados a la identidad de Educatio. La referencia vigente es el código de Educatio indicado abajo; no es necesario acceder a Pranzo ni copiar pantallas antiguas que todavía no fueron migradas.

- Antes de construir una pantalla, revisar la referencia local más cercana y reutilizar componentes y helpers de EureFramework. Adaptar la distribución al contenido, sin copiar rígidamente una pantalla de otro dominio.
- Usar `EducatioCard` (`resources/js/components/educatio-card.tsx`), construido sobre la variante `plain` de `EureCard`: sin sombra, borde discreto, redondeado leve y títulos sin banda de fondo. Los ajustes propios de Educatio se centralizan en este componente.
- Elegir una tarjeta principal o encabezado y tarjetas por sección según el contenido. Evitar tarjetas anidadas sin utilidad y no duplicar el padding que ya aporta el layout.
- Encabezados con título, ícono Lucide de la entidad (`aria-hidden="true"`) y descripción breve cuando aporte. Usar títulos de sección `h2` y tamaños acordes al espacio disponible.
- Mantener espaciado compacto, anchos de campos proporcionales al dato y acciones junto a la tarea. Guardar/Volver pueden permanecer visibles al pie de formularios extensos.
- Usar `variant="plain"` de `EureDataTable` en las pantallas nuevas y migradas: borde discreto, sin sombra y acciones compactas con foco visible.
- En formularios modales, separar encabezado, contenido y pie de acciones. Limitar la altura al viewport y permitir scroll en el contenido, conservando visibles las acciones y el cierre. Usar tokens del tema también en los fondos y separadores del diálogo.

### Referencias locales del estándar

- **Listado con filtros:** `resources/js/pages/academico/alumnos/index.tsx`.
- **Alta/edición por secciones:** `resources/js/pages/academico/responsables/formulario.tsx`.
- **Detalle:** `resources/js/pages/academico/alumnos/show.tsx` y sus componentes en `partials/`.
- **ABM con formulario modal:** `resources/js/pages/academico/catalogos/index.tsx` y `resources/js/pages/sistema/localidades/index.tsx`.
- **Configuración personal:** `resources/js/layouts/settings/layout.tsx` y `resources/js/pages/settings/`.
- **Base visual reutilizable:** `resources/js/components/educatio-card.tsx`, `resources/js/components/ui/eure/` y `resources/css/app.css`.

Al revisar una pantalla nueva, comprobar consistencia con estas referencias, uso exclusivo de colores del tema, modo claro/oscuro, operación a 1366×768 y navegación con teclado según las reglas siguientes.

### Colores del tema

- Todo color se define en `resources/css/app.css` mediante tokens con soporte claro/oscuro. No introducir hex, RGB ni paletas fijas (`slate-*`, `amber-*`, etc.) en pantallas o componentes de Educatio.
- Reutilizar `card`, `card-foreground`, `foreground`, `muted-foreground`, `border`, `primary` y `ring`. Para íconos de contenido usar `educatio-icon`; para avisos textuales, `educatio-warning`; para estados, `eure-success` y `eure-error`.
- Conservar la identidad azul y naranja. Crear un token semántico solo cuando los existentes no expresen su función; definir sus valores de ambos modos en el tema.
- Los fondos derivados deben mezclarse con la superficie del tema, no con blanco fijo. Redefinir en `.dark` las mezclas que dependan de variables para que se resuelvan con la paleta oscura.

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

- Ordenable por **todas** las columnas de datos: definir `accessorKey` para campos directos o `accessorFn` para valores calculados. No alcanza con `cell`, que solo renderiza.
- **Alineación**: declarar siempre `meta.className` explícito por columna; no confiar en el default automático de `EureDataTable`, que alinea a la derecha cualquier valor numérico. Descripciones a la izquierda; IDs, códigos, logins, cantidades y fechas centrados (`text-center tabular-nums` cuando corresponda); importes a la derecha (`text-right tabular-nums`). El encabezado hereda la misma clase que la celda.
- **Columna `#` (ID)**: toda grilla nueva la incluye por defecto, salvo excepción justificada. Mostrar el ID centrado y con ceros a la izquierda mediante un campo calculado `idF` en el tipo de la fila, junto al `id: number` original, usando el helper único `formatId(id, digits)` de `@/lib/utils`. Elegir los dígitos según el volumen esperado de la entidad (ej. provincias 2, localidades 3, grupos 4, alumnos/responsables 5); la longitud queda explícita al calcular `idF` en cada pantalla. La columna ordena por `id` (`accessorKey: 'id'`) para mantener el orden numérico real; `cell` renderiza `row.original.idF`.
- **ID como enlace a “Ver”**: cuando la grilla tiene acción “Ver”, mostrar `idF` como enlace a esa misma acción, en negrita y subrayado, conservando el contexto de filtros. Si la acción no existe o está oculta para esa fila, mostrar el ID como texto plano, sin apariencia de enlace.
- **Orden inicial**: las grillas operativas (altas o movimientos, como Alumnos, Responsables y Grupos) comienzan por `id` descendente. Los catálogos paramétricos (como Localidades y Provincias) comienzan por su descripción o nombre. Si el modelo tiene un campo de orden explícito, este tiene prioridad.
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

### Teléfonos

**Siempre que una funcionalidad incluya teléfonos, reutilizar este patrón**, tanto al crear pantallas como al modificar las existentes. Aplica a docentes, responsables, contactos laborales, referencias y cualquier otro dominio. Proviene de Pranzo; la implementación de referencia ya está en Educatio.

- **Interfaz:** usar `resources/js/components/ui/eure/EureTelefonoInputGroup.tsx`. Permite ingresar el número con característica (ej. `299 5123456`) y elegir **Línea móvil / Línea fija** mediante «cambiar», con móvil como valor inicial para números nuevos. No exigir que el usuario escriba `+54` o `+549`. Para números extranjeros, indicar el prefijo internacional.
- **Un campo por número:** no crear campos separados «Teléfono» y «Celular» solo para distinguir el tipo de línea. Conservar campos adicionales cuando representan contactos distintos, como teléfono laboral o de una referencia; cada uno usa el mismo componente con su label y estado independientes.
- **Servidor:** usar `App\Services\TelefonoNormalizer` y `App\Rules\Telefono` desde los Form Requests. La dependencia es `giggsey/libphonenumber-for-php-lite` (`^9.0`), la misma que Pranzo. No reemplazarla por regex, concatenación manual de prefijos ni una normalización propia por pantalla.
- **Normalización y validación:** seguir `IntegranteRequest` y `ResponsableRequest`: normalizar según el tipo elegido y validar con `Telefono`. Si la normalización de una entrada no vacía falla, conservar esa entrada para que la validación la rechace; nunca convertirla en `null` y aceptarla por `nullable`. Un número «posible» no equivale a un número válido.
- **Guardado:** persistir el número normalizado en E.164. Los indicadores `*_es_fijo` son auxiliares del formulario: validarlos como booleanos y excluirlos de los datos enviados al modelo. Si el dominio conserva el texto original, guardarlo aparte, como hace Responsable con `telefono_original` y `telefono_e164`.
- **Edición:** reconstruir el tipo de línea con `TelefonoNormalizer::esLineaFija()` a partir del E.164 guardado. No inicializar siempre como móvil: eso convertiría teléfonos fijos al guardar sin cambios. No inventar características faltantes ni modificar prefijos extranjeros.
- **Verificación:** cubrir números nacionales móviles y fijos, números internacionales, entradas inválidas y edición sin cambio de tipo. Referencias: `tests/Unit/TelefonoNormalizerTest.php` y `tests/Feature/Academico/TelefonosTest.php`. Si falta el paquete, informar la dependencia pendiente y respetar las restricciones de instalación de la sesión; no presentar la integración como verificada.

### Formularios

- **Alineación y orden visual (preferencia, no restricción funcional):** procurar formularios estéticamente ordenados, con campos y labels alineados en una grilla coherente, espaciados uniformes y agrupaciones claras. Evitar controles sueltos fuera de la grilla y huecos innecesarios; aprovechar el espacio disponible según el contenido. No forzar simetría, anchos iguales ni un número fijo de columnas si perjudican la funcionalidad, la legibilidad, la accesibilidad, el comportamiento responsive o el recorrido lógico con teclado. Ante un conflicto, esas necesidades tienen prioridad sobre la estética.
- El ancho del campo acompaña al dato: un precio **no** ocupa el 100% del ancho; una descripción sí.
- Los labels deben permanecer visibles. No usar placeholders como reemplazo del label.
- Los formularios deben abrir listos para trabajar y respetar un recorrido de carga predecible.
- Los mensajes de validación deben aparecer junto al campo correspondiente y conservar el valor ingresado cuando sea posible.
- La acción principal debe ser inequívoca y compatible con `ENTER` según las reglas de navegación por teclado.

### General

- **Modo oscuro** soportado en toda pantalla nueva.
- En los ABM, la acción de baja lógica se representa con el ícono clásico de papelera, manteniendo el texto y la confirmación como **Desactivar** para dejar claro que no es una eliminación física. La reactivación usa un ícono de restaurar.
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
