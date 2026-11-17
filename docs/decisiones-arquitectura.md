# Decisiones de arquitectura

## Stack tecnológico

EducatioWeb PriSec utiliza Laravel 13 en el backend, React en la interfaz, Inertia como puente entre ambos y MariaDB como base de datos de desarrollo y producción.

Estas decisiones describen el diseño vigente. Para el avance funcional y las verificaciones fechadas, consultar [el roadmap](roadmap.md) y [el historial](historial.md). El código y las migraciones son la fuente de verdad del modelo.

## Convenciones de nombres

El dominio se expresa en castellano. Las tablas usan nombres en español, en plural y con `snake_case`; los modelos usan el nombre de la entidad en singular y PascalCase. Las columnas y claves también usan `snake_case`.

Las claves primarias usan el nombre convencional `id`. Las claves foráneas usan el nombre singular de la relación seguido por `_id`, por ejemplo `nivel_id`, `curso_id` y `plan_estudio_id`, y conservan el tipo de la clave primaria referenciada.

En los modelos se declara `$table` explícitamente para no depender del pluralizador inglés con nombres de tablas en castellano. Las claves primarias y foráneas siguen las convenciones de Eloquent, por lo que no requieren configuración manual en los modelos ni en sus relaciones.

## Baja lógica y auditoría

Los maestros del dominio no se eliminan durante la operación normal. Se dan de baja mediante `activo`, un booleano obligatorio cuyo valor predeterminado es `true`. El campo se ubica inmediatamente después de la clave primaria.

No se utiliza `softDeletes` ni una columna `deleted_at`: una baja es una decisión explícita del dominio y se representa mediante `activo`. Esto permite distinguir claramente una desactivación de una eliminación física y se integra con la auditoría general de `logs`.

Los catálogos que no requieren desactivación operativa, como nacionalidades, grupos sanguíneos, motivos de baja o tipos de vínculo, pueden omitir `activo`. No se agregan `timestamps()` como mecanismo general de auditoría a las tablas de dominio.

La auditoría está centralizada en [RegistraAuditoria](../app/Support/Auditoria/RegistraAuditoria.php). Controladores y acciones la invocan explícitamente dentro de la misma transacción que la operación principal. Registra `create`, `update`, `deactivate` o `reactivate`, los valores anteriores/nuevos y el contexto de usuario, sesión y petición. El modelo `Log` y su tabla permanecen desacoplados de cada dominio.

## Cambios de esquema durante desarrollo

Mientras la base sea descartable, los cambios van en las migraciones originales y los datos iniciales en seeders. No se agregan alters ni backfills. La verificación completa del esquema requiere `php artisan migrate:fresh --seed` en una base de desarrollo descartable de MariaDB, además de las pruebas compatibles en SQLite.

Cuando existan datos reales en producción, esta política deberá reemplazarse por migraciones incrementales; no se modificarán migraciones ya aplicadas ni se reconstruirá esa base con `migrate:fresh`.

## Claves foráneas y eliminaciones

Todas las claves foráneas del dominio usan `RESTRICT` tanto al actualizar como al eliminar la clave referenciada. No se utilizan borrados en cascada: eliminar un maestro nunca debe eliminar grupos ni información histórica como efecto secundario. La operación normal es desactivar mediante `activo`; cualquier eliminación física excepcional deberá ser explícita, transaccional y auditada.

## Tipos de identificadores

Las entidades de crecimiento normal usan `BIGINT UNSIGNED` autoincremental. Los identificadores de `niveles` y todas sus referencias usan `TINYINT UNSIGNED`, por tratarse de un catálogo pequeño. Los niveles tienen ABM e IDs autoincrementales: el seeder identifica los registros por `codigo`, no por IDs fijos. Los campos `orden` y `ciclo_lectivo` usan `SMALLINT UNSIGNED`.

No se usa `foreignId()` para `nivel_id`, ya que produciría un `BIGINT UNSIGNED` incompatible con la PK `TINYINT UNSIGNED` de `niveles`.

## Ciclo lectivo

`ciclo_lectivo` se almacena directamente como año en `grupos`, por ejemplo `2026`. Se usa `SMALLINT UNSIGNED` en lugar de una tabla independiente porque el ciclo siempre coincide con el año calendario, del 1 de enero al 31 de diciembre, y no tiene atributos propios. También se evita la semántica especial del tipo SQL `YEAR` y se mantiene compatibilidad con los tests en SQLite.

## Nivel, curso, plan y grupo

Un `Curso` pertenece a un `Nivel`. `nivel_id` se mantiene deliberadamente en `cursos`, `planes_estudio` y `grupos` porque el nivel es una dimensión central del dominio y de las consultas. En `grupos` también permite crear temporalmente un grupo sin plan de estudio.

La consistencia redundante se garantiza en la base mediante claves foráneas compuestas:

- `(grupos.curso_id, grupos.nivel_id)` referencia `(cursos.id, cursos.nivel_id)`;
- `(grupos.plan_estudio_id, grupos.nivel_id)` referencia `(planes_estudio.id, planes_estudio.nivel_id)`.

`grupos.plan_estudio_id` admite `NULL`. Cuando no hay plan, la segunda FK compuesta no exige una coincidencia; cuando existe un plan, MariaDB garantiza que pertenece al mismo nivel del grupo.

La identidad funcional de un grupo se define mediante la restricción única:

`(ciclo_lectivo, nivel_id, curso_id, division_id, turno_id)`.

El plan y el estado `activo` no forman parte de esa identidad: cambiar el plan o desactivar el registro no crea un grupo distinto.

La composición de materias de los planes todavía requiere su propio diseño. La pertenencia actual e histórica de alumnos se resuelve con el circuito siguiente; no constituye por sí sola el proceso completo de matrícula anual o rematriculación.

## Grupo actual e historial del alumno

Se conserva deliberadamente `alumnos.grupo_id` (nullable) junto a las permanencias en `alumnos_grupos`. El alumno puede tener cero o un grupo vigente en total, no uno por ciclo. Ambos accesos deben coincidir.

- Cada permanencia guarda alumno, grupo, descripción histórica del grupo, inicio, finalización opcional y motivos de ingreso/egreso. Las fechas de permanencia conservan microsegundos.
- La fila sin finalización es la vigente. Una columna calculada `alumno_vigente_id`, con índice único, impide dos permanencias abiertas para el mismo alumno. No se usa `activo` para representar el cierre: estas filas se conservan y se cierran mediante `fecha_hasta`.
- Volver a un grupo crea otra fila; no se reabre una permanencia anterior. Los modelos impiden reescribir o borrar permanencias cerradas mediante operaciones Eloquent individuales.
- `GestionarGrupoAlumno` bloquea al alumno, verifica la coincidencia entre ambas representaciones, cierra/abre permanencias y actualiza `grupo_id` dentro de una transacción. Para una asignación, bloquea también el grupo destino y verifica que esté activo.
- La versión enviada por la ficha combina la última permanencia y su estado abierta/cerrada. Una ficha anterior a cualquier movimiento se rechaza, incluso ante una asignación seguida de cierre.
- El formulario general no admite modificar `grupo_id`; el modelo también comprueba su coincidencia con la permanencia vigente al guardar ese atributo. Los cambios directos mediante SQL o actualizaciones masivas que omitan eventos de Eloquent quedan fuera de este circuito y no deben usarse para asignaciones.
- La baja del alumno cierra su permanencia y deja `grupo_id` vacío, en la misma transacción que la baja. La reactivación no restaura automáticamente la asignación anterior.
- No se desactiva un grupo con alumnos vigentes. Una vez que tiene historia, no se cambia su ciclo, nivel, curso, división, turno ni plan; se crea otro grupo. La descripción guardada en la permanencia conserva la denominación previa aunque se corrijan nombres de los catálogos.

La auditoría completa de cada creación y cierre de `alumnos_grupos`, y del cambio de `alumnos.grupo_id`, usa `RegistraAuditoria` y `logs`: usuario, login, fecha/hora, sesión, IP, user-agent, origen y snapshots anteriores/nuevos. Todo se confirma o revierte junto. La tabla `logs` permanece desacoplada; la relación `AlumnoGrupo::auditoria()` permite consultar los eventos de una permanencia. La ficha muestra los usuarios de ingreso/egreso y no expone identificadores de sesión.

Desde el detalle del grupo, «Asignar alumnos» permite llenar el grupo mediante selección múltiple, búsqueda por alumno y filtro por grupo actual (incluidos grupos inactivos cuando hay referencias). La selección se conserva al filtrar y se revisan ingresos y traslados antes de confirmar, con un motivo común. Se admiten hasta 200 alumnos por lote; los que ya pertenecen al destino no generan movimientos.

Los filtros de grupo actual, año lectivo y cohorte de nacimiento se combinan por intersección. El año lectivo corresponde exclusivamente al grupo vigente: un alumno sin permanencia abierta no tiene ciclo actual, aunque conserve historia. La cohorte usa el corte escolar al **30 de junio**, no el año calendario: la cohorte 2020 reúne nacimientos del **01/07/2019 al 30/06/2020**, ambos incluidos. `Alumno::cohorteNacimiento()` calcula el año de cierre del intervalo desde la fecha personal, sin persistir otro dato ni alterar esa fecha. La interfaz muestra el rango y mantiene una opción para fechas desconocidas, además de los filtros activos y el conteo de coincidencias; limpiar filtros no elimina seleccionados ni el motivo.

El corte coincide con el listado «Alumnos por edad al 30/06» del legacy (`SecundarioV4/Declaraciones.bas`, caso 16) y con la referencia de [Educación Neuquén para ingreso a Primaria](https://www.neuquen.edu.ar/ciclo-lectivo-2027-llego-el-turno-de-preinscripcion-para-ninas-y-ninos-ingresantes-a-nivel-primario/), consultada el 17/11/2026. Se aplica como criterio de búsqueda, sin determinar automáticamente el grado ni sustituir las reglas de matrícula o las excepciones de trayectoria.

La asignación masiva reutiliza `GestionarGrupoAlumno` dentro de una transacción para todo el lote. Primero bloquea todos los alumnos por ID ascendente y luego el destino, antes de aplicar movimientos individuales. Comprueba las versiones observadas al seleccionar, el estado activo y la policy de cada alumno; cualquier conflicto o fallo de auditoría revierte el lote completo. No utiliza actualizaciones masivas que eludan el historial o los logs. El límite de lote es operativo, no una capacidad máxima del grupo.

Este circuito registra movimientos con efecto inmediato. La fecha declarada de baja del alumno puede ser anterior, pero la permanencia se cierra al registrar la baja, sin introducir intervalos negativos. Cargas retroactivas, correcciones históricas y preasignaciones futuras requieren un circuito específico, todavía pendiente.

## Autorización y navegación

El sistema gestiona una institución y requiere usuario autenticado. Los roles/permisos están diferidos: las Policies/Gates y el `authorize()` de los Form Requests conservan el punto de extensión sin imponer roles inventados.

El menú se centraliza en [admin-general-layout.tsx](../resources/js/layouts/admin-general-layout.tsx). Las rutas se conectan al frontend mediante Wayfinder y las pantallas reutilizan `EducatioCard` y componentes Eure. Las reglas visuales y de teclado se mantienen en la guía del proyecto.

## Servicios y helpers compartidos

- Teléfonos: `TelefonoNormalizer` y la regla `Telefono`, utilizados desde Form Requests; persistencia E.164 y `EureTelefonoInputGroup` en la interfaz.
- Búsquedas: `App\Support\Database\LikePattern::contains()` escapa `!`, `%` y `_`. Las consultas que lo usan declaran `ESCAPE '!'`.
- Adjuntos: `GuardarAdjunto`, modelo `Adjunto` y almacenamiento privado, con descarga a través de rutas autenticadas.
- Fechas, moneda e identificadores: helpers en `resources/js/lib`, con regionalización argentina.

## Política de índices

Los índices iniciales se limitan a los requeridos por las claves foráneas, las restricciones únicas y las consultas operativas ya identificadas. No se crean índices aislados sobre booleanos ni índices preventivos sin un patrón de consulta concreto. Los índices se revisarán usando consultas reales y sus planes de ejecución a medida que crezca el sistema.

Todos los índices y constraints tienen nombres explícitos y breves para respetar el límite de identificadores de MariaDB y facilitar el diagnóstico.

## Migración del sistema legacy

El modelo nuevo no se debilitará para aceptar inconsistencias históricas. Los datos anteriores serán perfilados, saneados y transformados antes de su importación.
