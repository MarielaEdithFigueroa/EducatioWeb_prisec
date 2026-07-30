# 01 · Propósito

## Qué es EducatioWeb PriSec

EducatioWeb PriSec es la **reescritura web** del sistema de gestión escolar **Educatio**, de Eureka Soluciones Informáticas (eurekasoluciones.com.ar). El sistema actual (legacy) es una aplicación de escritorio hecha en Visual Basic 6, en producción desde ~2004, que administra la operación académica y administrativa de instituciones educativas.

Esta reescritura acota el alcance a los niveles **Primaria y Secundaria** (de ahí "PriSec"). El nivel **Terciario**, presente en el legacy, queda **fuera** de este proyecto.

## Decisiones de alcance

- **No es multitenant.** Decisión de **Mariela** (referente del dominio). El sistema gestiona **una institución**, no una red de establecimientos con aislamiento de datos por tenant. No se modela "scope"/franquicia ni layouts por tenant. Cualquier scaffolding multitenant heredado de la base técnica se descarta o se reaprovecha solo como marco visual genérico.
- Alcance de niveles: **Primaria y Secundaria**. Terciario queda fuera.
- **Roles diferidos.** Por ahora **no hay autorización**: se requiere usuario autenticado, pero **todos los usuarios pueden hacer todo**. Los roles/permisos se agregan más adelante. No es anarquía: se dejan las costuras desde el inicio (ver "Autorización — diferida" en las convenciones) para que ese agregado sea un relleno y no una reescritura.

## Por qué se reescribe

El legacy es un ejecutable de escritorio que se instala por PC, con actualizaciones vía un control ActiveX de auto-update. Migrarlo a web permite acceso multiusuario sin instalación, mantenimiento centralizado, y una base técnica moderna y sostenible.

## Sobre qué se construye

El proyecto parte de **EureFramework**: una base técnica de Eureka (Laravel 13 + Inertia v3 + React 19 + Tailwind v4 + TypeScript), **sin dominio funcional propio**. EureFramework ya trae:

- autenticación cerrada con `login` y contraseña (Fortify, con 2FA y passkeys);
- perfil, cambio de contraseña, sesiones, cache, queues, logs, health endpoint;
- layout administrativo responsive, modo claro/oscuro y componentes `Eure*`;
- auditoría general preparada en la tabla `logs`;
- formatos regionales argentinos y utilidades CUIT/CUIL.

Al arrancar **todavía no hay dominio de Educatio implementado**. El trabajo consiste en incorporar, sobre esa base, el dominio académico y administrativo descripto en estos docs.

## Stack

- **Backend**: PHP 8.3 / Laravel 13
- **Frontend**: React 19 + Inertia.js v3 (SPA sin complejidad de SPA)
- **CSS**: Tailwind v4 (configuración en CSS, sin `tailwind.config.js`)
- **Rutas type-safe**: Wayfinder
- **Testing**: Pest v4 (SQLite en memoria)
- **DB**: MariaDB en desarrollo y producción; SQLite en tests
- **Regionalización**: `es_AR`, fechas `dd/MM/yyyy`, moneda argentina

## Idioma

Todo el dominio, textos, nombres de entidades y de columnas van en **castellano**. El código sigue las convenciones del repo (ver `CLAUDE.md` / `.ai/guidelines/EducatioWebPriSec/core.md`).

## Fuente de verdad

Estos docs son **contexto de dominio durable** (propósito, glosario, referencia funcional del legacy). Para el modelo de datos concreto, la verdad es el **código + las migraciones**; para el backlog, los **issues de GitHub**.
