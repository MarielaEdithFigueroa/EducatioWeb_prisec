# Decisiones de arquitectura

## Stack tecnológico

EducatioWeb PriSec utiliza Laravel 13 en el backend, React en la interfaz, Inertia como puente entre ambos y MariaDB como base de datos de desarrollo y producción.

## Convenciones de nombres

El dominio se expresa en castellano. Las tablas usan nombres en español, en plural y con `snake_case`; los modelos usan el nombre de la entidad en singular y PascalCase. Las columnas y claves también usan `snake_case`.

Las claves primarias incluyen el nombre de la entidad, por ejemplo `id_curso`, `id_nivel` e `id_grupo`. Las claves foráneas conservan exactamente el nombre y el tipo de la clave primaria referenciada.

En los modelos se declaran `$table` y `$primaryKey` explícitamente. Esto evita depender del pluralizador inglés y de la suposición de Eloquent de que la tabla tendrá una columna `id`. También hace visibles las convenciones del dominio y evita inferencias ambiguas en relaciones, factories, seeders y herramientas externas.

## Baja lógica y auditoría temporal

Los maestros del dominio no se eliminan durante la operación normal. Se dan de baja mediante `activo`, un booleano obligatorio cuyo valor predeterminado es `true`. El campo se ubica inmediatamente después de la clave primaria.

No se utiliza `softDeletes` ni una columna `deleted_at`: una baja es una decisión explícita del dominio y se representa mediante `activo`. Esto permite distinguir claramente una desactivación de una eliminación física y se integra con la auditoría general de `logs`.

Las tablas `cursos`, `divisiones`, `turnos`, `planes_estudio` y `grupos` tienen `created_at` y `updated_at`. En este conjunto inicial se conservarán como metadatos técnicos de creación y última modificación; no reemplazan el registro detallado de auditoría. `niveles` no tiene timestamps porque es un catálogo fijo, interno y cargado por seeder.

## Claves foráneas y eliminaciones

Todas las claves foráneas del dominio usan `RESTRICT` tanto al actualizar como al eliminar la clave referenciada. No se utilizan borrados en cascada: eliminar un maestro nunca debe eliminar grupos ni información histórica como efecto secundario. La operación normal es desactivar mediante `activo`; cualquier eliminación física excepcional deberá ser explícita, transaccional y auditada.

## Tipos de identificadores

Las entidades de crecimiento normal usan `BIGINT UNSIGNED` autoincremental. Los identificadores de `niveles` y todas sus referencias usan `TINYINT UNSIGNED`, porque se trata de un catálogo interno pequeño con IDs fijos. Los campos `orden` y `ciclo_lectivo` usan `SMALLINT UNSIGNED`.

No se usa `foreignId()` para `id_nivel`, ya que produciría un `BIGINT UNSIGNED` incompatible con la PK `TINYINT UNSIGNED` de `niveles`.

## Ciclo lectivo

`ciclo_lectivo` se almacena directamente como año en `grupos`, por ejemplo `2026`. Se usa `SMALLINT UNSIGNED` en lugar de una tabla independiente porque el ciclo siempre coincide con el año calendario, del 1 de enero al 31 de diciembre, y no tiene atributos propios. También se evita la semántica especial del tipo SQL `YEAR` y se mantiene compatibilidad con los tests en SQLite.

## Nivel, curso, plan y grupo

Un `Curso` pertenece a un `Nivel`. `id_nivel` se mantiene deliberadamente en `cursos`, `planes_estudio` y `grupos` porque el nivel es una dimensión central del dominio y de las consultas. En `grupos` también permite crear temporalmente un grupo sin plan de estudio.

La consistencia redundante se garantiza en la base mediante claves foráneas compuestas:

- `(grupos.id_curso, grupos.id_nivel)` referencia `(cursos.id_curso, cursos.id_nivel)`;
- `(grupos.id_plan_estudio, grupos.id_nivel)` referencia `(planes_estudio.id_plan_estudio, planes_estudio.id_nivel)`.

`grupos.id_plan_estudio` admite `NULL`. Cuando no hay plan, la segunda FK compuesta no exige una coincidencia; cuando existe un plan, MariaDB garantiza que pertenece al mismo nivel del grupo.

La identidad funcional de un grupo se define mediante la restricción única:

`(ciclo_lectivo, id_nivel, id_curso, id_division, id_turno)`.

El plan y el estado `activo` no forman parte de esa identidad: cambiar el plan o desactivar el registro no crea un grupo distinto.

## Política de índices

Los índices iniciales se limitan a los requeridos por las claves foráneas, las restricciones únicas y las consultas operativas ya identificadas. No se crean índices aislados sobre booleanos ni índices preventivos sin un patrón de consulta concreto. Los índices se revisarán usando consultas reales y sus planes de ejecución a medida que crezca el sistema.

Todos los índices y constraints tienen nombres explícitos y breves para respetar el límite de identificadores de MariaDB y facilitar el diagnóstico.

## Migración del sistema legacy

El modelo nuevo no se debilitará para aceptar inconsistencias históricas. Los datos anteriores serán perfilados, saneados y transformados antes de su importación.
