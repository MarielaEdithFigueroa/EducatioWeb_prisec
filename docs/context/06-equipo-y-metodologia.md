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
composer lint:check    # estilo PHP (Pint). Si falla: composer lint
npm run types:check    # tipos TypeScript
composer test          # Pint + Pest completo
```

Si `types:check` falla por bindings de Wayfinder desactualizados, correr `php artisan wayfinder:generate` y repetir.

## Definición de "terminado"

- La feature tiene al menos un **test Pest** que la cubre.
- Los quality gates pasan.
- La pantalla es **accesible desde el menú** (aunque sea un menú "dev") y se probó en el navegador. Una pantalla a la que no se puede llegar no está terminada.
- El PR está abierto contra `develop` con reviewer.

## Convenciones

Las convenciones técnicas (baja lógica con `activo`, auditoría en `logs`, UI, fechas, dinero, formularios) están en `CLAUDE.md` / `.ai/guidelines/EducatioWebPriSec/core.md`. Ese archivo es la fuente única de convenciones — no se duplican acá.

## Dónde mirar el dominio

- **Propósito y stack**: `docs/context/01-proposito.md`
- **Terminología**: `docs/context/02-glosario.md`
- **Reglas académicas**: `docs/context/03-academico.md`
- **Reglas administrativas**: `docs/context/04-administrativo.md`
- **Referencia del legacy**: `docs/context/05-legacy-educatio.md`
