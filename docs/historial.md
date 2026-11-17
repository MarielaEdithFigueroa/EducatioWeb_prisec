# Historial técnico de Educatio

Este documento registra hitos, decisiones y cambios relevantes del proyecto. Complementa el historial de Git y no reemplaza `docs/decisiones-arquitectura.md` ni `docs/roadmap.md`.

Las entradas describen lo decidido o verificado en su fecha. En particular, las claves personalizadas y los timestamps generales de la etapa inicial fueron reemplazados. Consultar [arquitectura](decisiones-arquitectura.md) para las reglas vigentes.

## 2026-08-06  Fundación del nuevo Educatio

### Contexto

Se inició la migración del sistema Educatio existente, desarrollado en VB6, hacia una nueva aplicación basada en Laravel 13, React, Inertia y MariaDB.

### Decisiones principales

- Utilizar tablas en español y en plural.
- Utilizar modelos Eloquent en singular.
- Utilizar columnas en `snake_case`.
- Utilizar claves primarias personalizadas como `id_curso`, `id_nivel` e `id_grupo`.
- Declarar explícitamente `$table` y `$primaryKey` en los modelos.
- Mantener `id_nivel` en `cursos`, `planes_estudio` y `grupos`.
- Garantizar la correspondencia curso–nivel mediante una clave foránea compuesta.
- Garantizar la correspondencia plan–nivel mediante una clave foránea compuesta.
- Almacenar `ciclo_lectivo` directamente como año en la tabla `grupos`.
- Utilizar `RESTRICT` en las claves foráneas.
- No utilizar eliminaciones en cascada.
- Utilizar baja lógica mediante el campo `activo`.
- No utilizar `softDeletes` en estas tablas.
- Utilizar timestamps en las tablas modificables y omitirlos en `niveles`.
- Mantener índices mínimos, justificados por integridad o consultas conocidas.
- No debilitar el modelo nuevo para aceptar errores del sistema legacy.

### Implementación completada

- Migración y modelo de `niveles`.
- Migración y modelo de `cursos`.
- Migración y modelo de `divisiones`.
- Migración y modelo de `turnos`.
- Migración y modelo de `planes_estudio`.
- Migración y modelo de `grupos`.
- Seeder de niveles.
- Mejora de idempotencia del seeder de usuarios.
- Documentación inicial de arquitectura.
- Roadmap técnico.
- Validación de migraciones y restricciones contra MariaDB.
- Ejecución inicial de migraciones y seeders en la base de desarrollo.

### Validaciones

- Sintaxis PHP validada.
- Pint aprobado.
- Suite inicial aprobada con 9 tests y 23 assertions.
- Claves foráneas simples y compuestas verificadas.
- Restricciones únicas e índices verificados.

### Próximo objetivo

Implementar el ABM de cursos como primer módulo funcional completo.

## 2026-08-10  Normalización de identificadores

Las claves primarias de las tablas académicas se normalizaron a `id` y las claves foráneas al formato `entidad_id` convencional de Laravel. Los tipos, las restricciones de integridad y las decisiones de dominio existentes se conservaron sin cambios.

## 17/11/2026 · Relevamiento y actualización documental

Se contrastaron documentación, rutas, modelos, migraciones, seeders, pantallas y pruebas del checkout local. También se consultaron formularios del legacy académico y administrativo. Esta fecha corresponde al relevamiento; no a la implementación de todos los circuitos siguientes.

### Implementación constatada

- ABM de estructura académica, incluidos niveles y grupos por ciclo lectivo.
- Alumnos, responsables y vínculos, condiciones especiales y documentación adjunta.
- Aulas con fotos y equipo institucional con funciones, contactos, referencias, documentación, adjuntos e impresión de ficha.
- Localidades y seeders de catálogos/datos de ejemplo.
- Baja lógica y auditoría centralizada en `logs`, sin timestamps generales en los modelos de dominio revisados.
- Normalización compartida de teléfonos y escape de búsquedas `LIKE`.

Se distinguieron de lo anterior los circuitos todavía pendientes: matrícula/trayectoria, materias y asignaciones docentes, calificaciones, asistencia, boletines y Administrativo. El detalle está en [el roadmap](roadmap.md).

### Verificaciones del relevamiento

- `php vendor/bin/pest --compact`: 346 pruebas aprobadas y 3.888 aserciones sobre SQLite en memoria.
- `npm run types:check`: aprobado.
- `npm run lint:check`: aprobado.
- No se ejecutaron build, Pint, pruebas visuales ni reconstrucción de MariaDB en este relevamiento. No se consultó el backlog remoto.

### Documentación reconciliada

Se actualizó la presentación de Educatio en el README, el estado funcional y las referencias de arquitectura. Los borradores de kickoff se conservaron como antecedentes. Se corrigieron referencias desactualizadas a multitenancy, funcionalidades de autenticación no habilitadas y deuda de escape `LIKE` ya resuelta. La presencia de Inicial en el catálogo quedó explícita sin ampliar por inferencia el alcance funcional acordado.

## 17/11/2026 · Grupo actual e historial de permanencias

Se acordó conservar `alumnos.grupo_id` junto a `alumnos_grupos`, con cero o una permanencia vigente y auditoría completa en `logs`. Una acción transaccional mantiene ambas representaciones; cada regreso a un grupo crea una fila nueva. Se incorporaron protección contra fichas obsoletas, índice único de vigencia, cierre al dar de baja al alumno y restricciones para modificar/desactivar grupos utilizados.

La ficha de alumno tiene una pestaña «Grupos e historial» y el detalle de grupo muestra sus alumnos actuales. Los movimientos tienen efecto inmediato; matrícula anual, cargas retroactivas y preasignaciones futuras quedan pendientes.

Validación: 369 pruebas aprobadas (4.124 aserciones), TypeScript y ESLint aprobados; Pint aplicado y Wayfinder regenerado. El build se intentó y falló durante la carga de configuración de Vite con `spawn EPERM` y un error del binario nativo de Tailwind. No se instalaron dependencias ni se modificó la base de desarrollo. Quedan pendientes reconstrucción/verificación en MariaDB y revisión visual en navegador.

## 17/11/2026 · Asignación masiva desde el grupo

Se agregó «Asignar alumnos» al detalle de grupo. La pantalla permite buscar y filtrar alumnos por grupo actual, conservar una selección múltiple entre filtros y revisar ingresos/traslados antes de confirmar con un motivo común. Se procesan hasta 200 alumnos por lote, reutilizando el historial y la auditoría individual. Los alumnos que ya pertenecen al destino no generan movimientos nuevos.

El lote se confirma o revierte completo. Se bloquean primero todos los alumnos en orden de ID; se revalidan destino, alumnos, permisos y versiones antes de completar la operación. Las pruebas cubren versiones obsoletas, estados inactivos, inconsistencias y fallos de auditoría que deben revertir también los primeros movimientos.

Verificación de los recursos afectados: 82 pruebas aprobadas y 1.112 aserciones sobre SQLite en memoria; TypeScript, ESLint, Prettier y Pint aprobados, Wayfinder regenerado. No hubo nuevos cambios de esquema para la carga masiva. La inspección posterior de solo lectura confirmó que `alumnos_grupos` ya existe en MariaDB local. Siguen pendientes las pruebas de concurrencia en MariaDB y la revisión visual: el navegador devolvió acceso denegado. El build volvió a fallar con `spawn EPERM` y el error de carga del binario nativo de Tailwind.

Se ampliaron los filtros con año lectivo del grupo vigente y año de nacimiento. Se agregó una indicación explícita de filtrado, etiquetas para quitar cada filtro, conteo de coincidencias y limpieza independiente de la selección. La verificación posterior de asignación masiva pasó 18 pruebas y 153 aserciones, incluidos años conocidos/desconocidos y ausencia de ciclo tras cerrar una permanencia; TypeScript, ESLint y Pint aprobados.

Tras revisar el criterio escolar con el usuario, se reemplazó el año calendario de nacimiento por la cohorte con corte al 30/06, conforme al antecedente del legacy y la referencia oficial citada en las decisiones de arquitectura. La cohorte 2020 abarca 01/07/2019–30/06/2020; el selector muestra el intervalo explícito. Se conservó el filtro de ciclo lectivo. Verificación: 54 pruebas afectadas aprobadas y 661 aserciones (asignación masiva, individual y CRUD de alumnos), con cobertura de los días de corte, cambio de año, año bisiesto y fecha desconocida; TypeScript, ESLint y Pint aprobados. Continúan pendientes build y revisión visual por las limitaciones del entorno ya registradas.

## 17/11/2026 · Vista de alumnos por grupo

Se incorporó una vista accesible desde Grupos, con selector de año lectivo y lista de grupos a la izquierda. Al elegir un grupo se consulta su alumnado actual en el panel derecho sin recargar la página. La selección queda resaltada; se conservan la consulta de ficha y el acceso a asignación masiva. La tabla de alumnos se comparte con el detalle del grupo.

La consulta devuelve solo ID, legajo, apellido y nombre, con Gates y sin caché. Cada petición tiene su propio `AbortController` y número de secuencia: un cambio de grupo, año o filtro cancela la anterior y descarta respuestas obsoletas. La pantalla maneja carga, grupo vacío, errores, reintento y redirección al login por sesión vencida. Los grupos inactivos son consultables y no ofrecen asignación.

Verificación: 92 pruebas afectadas aprobadas, 971 aserciones (panel, CRUD de grupos, asignación individual y masiva); TypeScript, ESLint, Prettier y Pint aprobados, Wayfinder regenerado. No hubo cambios de esquema. Siguen pendientes la revisión visual y el build por las limitaciones del entorno ya registradas.

## 17/11/2026 · Alumnos de ejemplo con grupo y lista compacta

Se integró `AlumnosGruposSeeder` después de crear los grupos: asigna los 11 alumnos activos de ejemplo a grupos del año actual mediante la acción transaccional de dominio. Crea historial y logs con origen `consola`, sin atribuir usuario o sesión web inexistentes. Omite alumnos con grupo o historial previo y destinos inactivos; el seed general conserva el estado de baja/reactivación de alumnos con historia. Puede ejecutarse por separado sobre los ejemplos existentes, sin reconstruir la base.

La lista lateral muestra cada grupo en dos líneas, con menor espaciado y una marca de selección que no aumenta la altura de la fila.

Verificación: suite completa aprobada, 409 pruebas y 4.680 aserciones; TypeScript, ESLint y Pint aprobados, Prettier aplicado al panel. No se ejecutó el seed sobre la base de desarrollo. Continúan pendientes build y revisión visual por las limitaciones del entorno ya registradas.

## 17/11/2026 · Planteles de ejemplo de Primaria

Se redujo el seed de grupos a 14 del año actual: 1.º a 7.º grado, divisiones A/Mañana y B/Tarde. Una carga limpia asigna 210 alumnos, distribuidos entre 12 y 18 por grupo. Incluye los seis ejemplos originales de Primaria y 204 alumnos ficticios nuevos, con legajos deterministas y nacimientos acordes a la cohorte escolar. Los ejemplos originales de Secundaria se conservan sin grupo inicial.

La creación de alumnos y sus asignaciones se auditan dentro de la misma transacción. Repetir el seed no duplica alumnos ni movimientos, no revierte correcciones o bajas y no genera reemplazos por traslados o cierres. La reducción de grupos se aplica a bases nuevas o reconstruidas; no se eliminan grupos anteriores de una base existente.

Verificación: 411 pruebas aprobadas y 10.119 aserciones, Pint aprobado. Se cubre también la ejecución parcial del plantel antes de cargar los ejemplos originales. No hubo cambios de esquema ni de interfaz en este ajuste. No se ejecutó el seed sobre la base de desarrollo.

## 17/11/2026 · Datos de alumnos más realistas

Se ampliaron las combinaciones de nombres y apellidos del plantel sintético: la carga inicial no repite nombres completos. Todos los alumnos tienen DNI ficticios únicos, incluidos los generados, con estabilidad entre ejecuciones y sin colisiones al crear el ciclo siguiente. Se incorporaron sexo registral, género, nacionalidad, domicilios y localidades de Neuquén y Río Negro; se mantienen los nacimientos por cohorte escolar.

Al repetir `AlumnosGruposSeeder` se pueden enriquecer los ejemplos antiguos identificados por su legajo y observación de origen, siempre que no tengan tipo ni número de documento. Solo se reemplazan nombres que coinciden con la versión anterior. Se preservan correcciones manuales, domicilios parciales, bajas, grupos e historial. Los cambios se comparan bajo bloqueo y se auditan en la misma transacción; una nueva ejecución sin cambios no genera logs adicionales.

Verificación: 414 pruebas aprobadas y 11.226 aserciones; Pint aprobado. El plantel requiere los catálogos de documentos, nacionalidades y localidades para evitar crear ejemplos incompletos en ejecuciones parciales. Sin cambios de esquema ni de interfaz. No se ejecutó el seed sobre la base de desarrollo.

## 17/11/2026 · Filtro de grupo en Alumnos

Se incorporó «Grupo actual» al panel de filtros de Alumnos, con búsqueda, «Todos» y «Sin grupo». Las opciones identifican año, nivel, curso/división y turno e incluyen grupos inactivos. El filtro se valida en servidor y consulta la pertenencia vigente; los grupos anteriores no producen coincidencias. Se integra con la URL, el resumen de filtros aplicados, la indicación de cambios pendientes, los conteos y la limpieza de filtros.

Verificación: 33 pruebas afectadas y 491 aserciones aprobadas; TypeScript, ESLint y Pint aprobados. El build volvió a fallar al cargar la configuración de Vite por `spawn EPERM` y el binario nativo de Tailwind. Sin cambios de rutas ni esquema; revisión visual pendiente.
