# 06 · Equipo y metodología

## Equipo

- **Mariela** — referente del dominio (negocio/funcional) de Educatio. Fuente de verdad para las reglas y el alcance funcional; ante una duda de dominio, se define con ella.
- **Daniel Dolz** — Eureka Soluciones Informáticas. PO y reviewer por defecto de los PRs (`DanielJoseDolz`).

> El resto de los roles del equipo se completan acá cuando estén definidos.

## Git flow

- **Branch base**: todo PR va a `develop`. Nunca directo a `main`.
- **Branch por tarea**: `tipo/descripcion-corta` (tipos: `feat`, `fix`, `chore`, `refactor`, `test`, `docs`). Nunca trabajar directo en `develop` ni `main`.
- **PR**: apunta a `develop`, con título convencional (ej. `feat(alumnos): ABM de alumnos (#123)`) y **reviewer `DanielJoseDolz`** (`--reviewer DanielJoseDolz`).
- El backlog y la asignación de tareas viven en los **issues de GitHub**, no en estos docs.

## Antes de abrir el PR — quality gates

Correr, y que pasen, antes de hacer push:

```bash
composer ci:check      # ESLint, Prettier, TypeScript, Pint y Pest
npm run build         # compilación del frontend
```

Durante una tarea, ejecutar las pruebas afectadas y los chequeos correspondientes al cambio. Para PHP modificado, ejecutar `vendor/bin/pint --dirty --format agent`. Los scripts disponibles están definidos en `composer.json` y `package.json`; las pruebas usan SQLite en memoria según `phpunit.xml`.

Si `types:check` falla por bindings de Wayfinder desactualizados, correr `php artisan wayfinder:generate` y repetir.

## Definición de "terminado"

- La feature tiene al menos un **test Pest** que la cubre.
- Los quality gates pasan.
- La pantalla es **accesible desde el menú** (aunque sea un menú "dev") y se probó en el navegador. Una pantalla a la que no se puede llegar no está terminada.
- El PR está abierto contra `develop` con reviewer.

## Convenciones

Las convenciones técnicas (baja lógica con `activo`, auditoría en `logs`, UI, fechas, dinero, formularios) se mantienen en [`.ai/guidelines/EducatioWebPriSec/core.md`](../../.ai/guidelines/EducatioWebPriSec/core.md) y se incorporan a las instrucciones de los agentes. Las decisiones de diseño están en [arquitectura](../decisiones-arquitectura.md).

## Mantenimiento de la documentación

- `README.md`: entrada al proyecto, preparación local y enlaces.
- `docs/context`: contexto durable y referencia funcional del legacy.
- `docs/roadmap.md`: estado contrastado con el código y pendientes generales, con fecha y límites del relevamiento.
- `docs/historial.md`: hitos y verificaciones fechadas; las decisiones reemplazadas se conservan como antecedentes y se señalan como tales.
- `docs/kickoff`: borradores históricos. Sus preguntas e hipótesis se contrastan con el modelo implementado antes de retomarlas.
- Issues de GitHub: backlog, prioridad y seguimiento de tareas.

Al completar un circuito, revisar el estado documentado y sus límites. No marcar una funcionalidad como terminada por tener solamente una tabla, un enlace de menú o una prueba aislada.

## Dónde mirar el dominio

- **Propósito y stack**: `docs/context/01-proposito.md`
- **Terminología**: `docs/context/02-glosario.md`
- **Reglas académicas**: `docs/context/03-academico.md`
- **Reglas administrativas**: `docs/context/04-administrativo.md`
- **Referencia del legacy**: `docs/context/05-legacy-educatio.md`
