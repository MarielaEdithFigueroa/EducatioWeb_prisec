# EducatioWeb PriSec

Reescritura web del sistema de gestión escolar Educatio de Eureka Soluciones Informáticas, construida sobre **EureFramework**. Usa Laravel 13, React 19, Inertia v3, Tailwind CSS v4, TypeScript y MariaDB.

El alcance funcional acordado es Primaria y Secundaria, para una institución por despliegue. El catálogo también contiene Inicial; Terciario queda fuera del proyecto.

## Estado del proyecto

Ya existen circuitos de alumnos, responsables y sus vínculos, estructura académica y grupos, aulas, equipo institucional, documentación/adjuntos y localidades. Comparten validaciones de servidor, baja lógica, auditoría y componentes Eure/Educatio.

La ficha del alumno incluye grupo actual e historial de permanencias, con traslados y cierres auditados. El proceso completo de matrícula anual/rematriculación, materias y asignaciones docentes, calificaciones, asistencia, boletines y los módulos administrativos siguen pendientes. El [estado y roadmap](docs/roadmap.md) detalla el alcance implementado y sus límites.

## Documentación

- [Propósito y alcance](docs/context/01-proposito.md).
- [Glosario](docs/context/02-glosario.md), [dominio académico](docs/context/03-academico.md) y [administrativo](docs/context/04-administrativo.md).
- [Cómo consultar el legacy VB6](docs/context/05-legacy-educatio.md).
- [Equipo y metodología](docs/context/06-equipo-y-metodologia.md).
- [Decisiones de arquitectura](docs/decisiones-arquitectura.md) e [historial técnico](docs/historial.md).
- [Convenciones del proyecto](.ai/guidelines/EducatioWebPriSec/core.md), incorporadas en las instrucciones para agentes.

El código y las migraciones definen el modelo vigente; los issues de GitHub contienen el backlog. Los documentos de `docs/kickoff` se conservan como antecedentes, con hipótesis que no deben tomarse como decisiones actuales.

## Preparación local

Se necesitan PHP compatible con `composer.json`, Composer, Node/npm y MariaDB. La CI usa Node 22; las versiones de dependencias están fijadas en los archivos lock.

Para una instalación nueva, copiá `.env.example` a `.env`, configurá MariaDB y definí `SEED_ADMIN_PASSWORD`. Los siguientes comandos son para la preparación manual del entorno:

```powershell
composer install
npm ci
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
```

`migrate:fresh --seed` elimina y reconstruye las tablas: se usa durante desarrollo sobre una base descartable. Los seeders cargan catálogos, ejemplos y el usuario indicado por `SEED_ADMIN_LOGIN`, `SEED_ADMIN_NOMBRE` y `SEED_ADMIN_APELLIDO`; la contraseña se obtiene del entorno.

Para trabajar:

```powershell
composer run dev
```

Este comando inicia Laravel, el worker de cola y Vite. Si cambian migraciones durante esta fase, se modifica la migración original y se reconstruye la base de desarrollo. No hace falta reconstruirla por cambios únicamente documentales o de interfaz. Ver [las reglas de esquema](docs/decisiones-arquitectura.md).

## Acceso

El acceso es cerrado mediante `login` y contraseña con Fortify. Hay edición de perfil, cambio de contraseña y apariencia. No están habilitados registro público, recuperación de contraseña, verificación de email, 2FA ni passkeys (`config/fortify.php`).

Todos los usuarios autenticados pueden operar los recursos actuales; roles y permisos están diferidos.

## Calidad

```powershell
php vendor/bin/pest --compact
composer lint:check
npm run lint:check
npm run format:check
npm run types:check
npm run build
```

Pest utiliza SQLite en memoria según `phpunit.xml`. `composer test` limpia la configuración, verifica Pint y ejecuta las pruebas. `composer ci:check` reúne lint, formato, tipos y `composer test`; el build se ejecuta aparte.

Después de modificar rutas, regenerar los bindings con `php artisan wayfinder:generate`. No editar manualmente `resources/js/actions` ni `resources/js/routes`.
