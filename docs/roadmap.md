# Estado y roadmap técnico de Educatio

Último relevamiento local: **17/11/2026**.

Este documento resume lo implementado y los principales circuitos pendientes de la reescritura VB6. Se contrasta con código, migraciones y pruebas; el detalle, prioridad y asignación del backlog viven en los issues de GitHub. Este relevamiento no incluyó los issues remotos ni una validación funcional completa en navegador.

## Alcance y criterios

- Una institución por despliegue, sin multitenancy.
- Alcance funcional acordado: Primaria y Secundaria. El catálogo y los seeders también incluyen Inicial; su presencia técnica no define por sí sola el alcance funcional. Terciario queda fuera.
- Roles y permisos diferidos: se requiere autenticación y se conservan Policies/Gates y Form Requests como puntos de extensión.
- Modelo nuevo con integridad referencial, baja lógica y auditoría; los datos legacy deberán sanearse antes de importarlos.
- Convenciones en [la guía del proyecto](../.ai/guidelines/EducatioWebPriSec/core.md) y decisiones en [arquitectura](decisiones-arquitectura.md).

## Implementación disponible

«Implementado» indica que existe código del circuito y pruebas asociadas. No implica aceptación funcional de todo el módulo ni equivalencia completa con el legacy.

| Área | Disponible | Límite actual |
| --- | --- | --- |
| Estructura académica | ABM de niveles, cursos, divisiones, turnos y planes; grupos por ciclo lectivo con filtros, baja/reactivación y coherencia de nivel | Los planes son catálogos: aún no contienen materias |
| Alumnos | Listado con filtros por texto, ubicación, grupo actual (incluido «Sin grupo») y estado; alta, edición, ficha por secciones, baja/reactivación, condiciones especiales y documentación adjunta | El legajo personal no constituye una matrícula anual ni una trayectoria escolar |
| Pertenencia a grupos | Grupo actual e historial de permanencias, cambios/cierres inmediatos, auditoría completa, alumnos actuales en la ficha del grupo y asignación masiva con revisión de ingresos/traslados | Pendientes matrícula anual, rematriculación, movimientos retroactivos y preasignación futura |
| Responsables | Listado, detalle, edición, alta desde un alumno y vinculación de responsables existentes; funciones legales, pedagógicas y económicas por vínculo | No hay alta general independiente ni circuito de facturación/cobranzas |
| Equipo institucional | Integrantes con funciones, datos personales y laborales, foto, contactos, referencias, checklist documental, adjuntos, baja/reactivación e impresión de ficha | Las funciones del equipo no son asignaciones de docentes a materias y grupos |
| Aulas | ABM, capacidad, especialidades y fotos | No hay asignación de aulas a grupos ni gestión de horarios |
| Geografía | ABM de localidades y catálogos geográficos | No implica una pantalla de mantenimiento para cada catálogo auxiliar |
| Acceso y perfil | Login/contraseña con Fortify, edición de perfil, cambio de contraseña y apariencia | Sin gestión general de usuarios, roles, registro público, recuperación de contraseña, verificación de email, 2FA ni passkeys habilitados |
| Infraestructura compartida | Auditoría en `logs`, adjuntos privados, teléfonos E.164, formatos argentinos y componentes Eure/Educatio | La consulta de auditoría desde el menú sigue pendiente |

Referencias para contrastar el estado:

- [Rutas web](../routes/web.php) y [configuración personal](../routes/settings.php).
- [Menú centralizado](../resources/js/layouts/admin-general-layout.tsx): diferencia accesos disponibles y entradas deshabilitadas.
- [Migraciones](../database/migrations), [modelos](../app/Models) y [Form Requests](../app/Http/Requests).
- [Pruebas académicas](../tests/Feature/Academico) y [pruebas de localidades](../tests/Feature/Sistema/LocalidadesCrudTest.php).

El inicio (`dashboard`) todavía contiene placeholders. Tener un nombre en el menú no significa que el módulo esté implementado: Materias, Calificaciones, Asistencia, Boletines y el área Administrativa permanecen deshabilitados.

## Datos iniciales

[DatabaseSeeder](../database/seeders/DatabaseSeeder.php) ya incluye:

- Niveles, cursos, divisiones, turnos, planes de estudio y grupos.
- Provincias/localidades, nacionalidades, tipos de documento, vínculos, motivos de baja, grupos sanguíneos y condiciones especiales.
- Datos de ejemplo de alumnos, aulas e integrantes; funciones y tipos de documentación del equipo.
- En desarrollo y tests se crean 14 grupos de Primaria del año actual (1.º a 7.º, divisiones A/Mañana y B/Tarde). `AlumnosGruposSeeder` prepara entre 12 y 18 alumnos por grupo: 210 asignados en una carga limpia, incluidos los seis ejemplos originales de Primaria. Los seis ejemplos de Secundaria quedan sin asignación inicial. Los alumnos generados tienen legajos estables y nacimientos acordes a la cohorte escolar; las asignaciones conservan historial y auditoría de consola.
- Los alumnos generados incluyen nombres completos sin duplicados en la carga inicial, DNI ficticios únicos, domicilio, localidad y datos personales coherentes. El seed puede completar los antiguos ejemplos `DEMO-PRI` sin documento: solo reemplaza nombres que aún coinciden con el generador anterior y respeta datos corregidos manualmente. Cada actualización se audita.
- Repetir el seed conserva bajas, cierres y traslados, sin crear reemplazos para recuperar el cupo inicial. Puede ejecutarse por separado con `php artisan db:seed --class=AlumnosGruposSeeder` cuando los catálogos y grupos de ejemplo ya existen. No elimina grupos creados por versiones anteriores: la reducción a 14 grupos corresponde a una base reconstruida.
- Usuario inicial, con contraseña obligatoria desde `SEED_ADMIN_PASSWORD`.

La existencia de estos seeders no acredita una importación de datos reales del legacy.

## Circuitos pendientes

### Matrícula y trayectoria académica

Ya se puede asignar un alumno a un grupo, trasladarlo, cerrar su permanencia y consultar todos sus períodos anteriores desde «Grupos e historial». Se conservan `alumnos.grupo_id` y `alumnos_grupos` mediante una operación transaccional auditada. La baja cierra la asignación; la reactivación del alumno no la reabre.

Para llenar un grupo: **Grupos → Ver → Asignar alumnos**. Permite seleccionar hasta 200 alumnos por operación, buscar por nombre/legajo/documento y combinar filtros por grupo actual, año lectivo de ese grupo y cohorte de nacimiento con corte al 30/06 (incluida fecha desconocida). Cada cohorte muestra su rango: por ejemplo, 2020 corresponde al 01/07/2019–30/06/2020. Una franja indica filtros activos y cantidad de resultados; se pueden quitar individualmente o limpiar todos sin perder la selección. Antes de confirmar se revisan los movimientos y se aplica un motivo común. Un conflicto cancela todo el lote; no hay guardados parciales.

La vista **Grupos → Alumnos por grupo** ofrece dos columnas: año lectivo, búsqueda y lista compacta de grupos a la izquierda; alumnos actuales del grupo seleccionado a la derecha. Cada grupo ocupa dos líneas y la selección se indica con borde, fondo y una marca. Cambiar de grupo consulta únicamente sus alumnos sin recargar la página. Incluye grupos inactivos, estados de carga/error, actualización y acceso a la ficha o asignación masiva. La lista de alumnos es la vigente, no una reconstrucción histórica del ciclo elegido.

Falta definir el proceso de matrícula anual, pases, repitencia, egreso y rematriculación, además de movimientos retroactivos y grupos futuros. La pertenencia vigente no sustituye los estados de una inscripción anual. Ver [las reglas implementadas](decisiones-arquitectura.md#grupo-actual-e-historial-del-alumno).

El legacy conserva grupo actual, grupo futuro y registros en `Alumno-Grupo`. Su comportamiento sirve como referencia para definir el circuito nuevo, no como esquema a copiar. Ver [guía del legacy](context/05-legacy-educatio.md).

### Materias y asignaciones docentes

Faltan materias/áreas, composición de planes, carga horaria y asignación de docentes a materias y grupos, incluyendo las condiciones y vigencias que se acuerden. El catálogo `planes_estudio` y el legajo de integrantes son las bases existentes.

### Evaluación y asistencia

Faltan escalas de calificación, períodos, notas parciales/definitivas, recuperatorios, conceptos, promoción, asistencia, tardanzas y justificaciones. Las reglas por nivel y su impacto en boletines requieren definición funcional. No se incorporan automáticamente mesas/finales u otros circuitos propios de Terciario.

### Informes, convivencia y comunicaciones

Faltan boletines, certificados, actas, informes académicos, sanciones, cuaderno de comunicaciones y envíos institucionales. Existe impresión de la ficha de integrantes y exportación reutilizable en `EureDataTable`; eso no constituye un módulo completo de informes ni reemplaza Crystal Reports.

### Administrativo

Faltan planes de cuotas, becas/descuentos, generación de cargos, cuenta corriente, comprobantes, cobranzas, caja, morosidad, cheques, compras/egresos y contabilidad. Las integraciones fiscales y bancarias requieren análisis propio. [El mapa administrativo](context/04-administrativo.md) describe el legacy; no compromete todo ese alcance para el MVP.

### Configuración y accesos

Faltan datos y parámetros institucionales, ciclo lectivo activo, numeraciones, configuración de informes, gestión de usuarios y consulta de auditoría. Roles/permisos siguen diferidos. No se planifican restricciones por tenant ni administración de varias instituciones.

### Migración e integraciones

Existe un relevamiento funcional inicial en `docs/context` y antecedentes en `docs/kickoff`. Quedan pendientes el inventario exhaustivo de tablas, perfilado de datos reales, mapeos, saneamiento, importadores, conciliación y estrategia de transición/reversión.

Moodle, aplicaciones móviles, correo, notificaciones y otras integraciones mencionadas en el legacy o en documentos iniciales requieren confirmación de alcance y prioridad. No se consideran implementadas.

### Operación

Hay configuración local, logs de aplicación y workflows de CI en [`.github/workflows`](../.github/workflows). Quedan por definir/verificar los ambientes de despliegue, backups y restauración, monitoreo, seguridad operativa y transición a migraciones incrementales cuando existan datos reales de producción. Este relevamiento no acredita el estado de servicios remotos.

## Calidad verificada

En el relevamiento del **17/11/2026** se ejecutaron:

| Comando | Resultado |
| --- | --- |
| `php vendor/bin/pest --compact` | 369 pruebas aprobadas, 4.124 aserciones; SQLite en memoria, incluida la vinculación alumno–grupo |
| Pruebas afectadas tras incorporar asignación masiva | 82 aprobadas, 1.112 aserciones: asignación masiva, vínculo individual, CRUD de grupos/alumnos e invariantes de arquitectura |
| `npm run types:check` | Aprobado |
| `npm run lint:check` | Aprobado |
| `php vendor/bin/pint --dirty --format agent` | Aplicado a los cambios PHP |
| `npm run build` | No completado: `spawn EPERM` y error al cargar el binario nativo de Tailwind durante la carga de configuración de Vite |

La suite cubre los recursos implementados, autenticación, configuración personal, validaciones, auditoría, adjuntos, teléfonos, seeders e invariantes de arquitectura. No demuestra cobertura de los procesos todavía pendientes.

No se ejecutó `migrate:fresh --seed` sobre MariaDB durante este trabajo. La inspección posterior de solo lectura constató que la tabla `alumnos_grupos` ya existe en la base local. La unicidad se probó en SQLite; los bloqueos y la concurrencia real requieren verificación en MariaDB. El intento de revisión visual quedó bloqueado por acceso denegado al navegador. La validación inicial en MariaDB está registrada como antecedente en [el historial](historial.md); no equivale a verificar hoy todas las migraciones.

## Secuencia propuesta para continuar

Esta secuencia es una propuesta técnica, pendiente de priorización funcional con Daniel y Mariela:

1. Completar la definición de matrícula anual y trayectoria sobre la pertenencia actual e histórica ya implementada.
2. Incorporar materias, composición de planes y asignaciones docentes.
3. Definir e implementar asistencia y calificaciones según las reglas de cada nivel.
4. Construir boletines/certificados sobre esos datos y acordar el puente con Administrativo.

Cada circuito debe cerrar reglas de negocio, validaciones de servidor, pruebas, acceso desde el menú y revisión de uso antes de darse por terminado.
