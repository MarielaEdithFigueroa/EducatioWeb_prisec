# 03 · Dominio académico

Referencia funcional del área **Académica** (gestión escolar), derivada del módulo académico del legacy (proyecto `Escuelas.vbp`, carpeta `SecundarioV4`). Describe **qué hace** el dominio, no cómo se modela en la base.

> Alcance de la reescritura: **Primaria y Secundaria**. Lo del legacy que sea propio de Terciario (correlatividades, mesas de examen, inscripción a finales, pasantías) queda fuera.

## Alumnos y responsables

- Alta y ABM de **alumnos** con su **legajo** y documentación.
- **Responsables** (padre/madre/tutor) y **responsable pedagógico** asociados al alumno. Un alumno puede tener hermanos en la institución (vínculo entre legajos).
- Datos complementarios: **teléfonos**, **direcciones**, **ficha médica**, **datos religiosos**, **deportes**.
- **Matriculación** por año lectivo y **rematriculación** (reinscribir alumnos del año anterior).
- Gestión de **documentación** del legajo (qué se presentó y qué falta).

## Estructura académica

- **Institución / establecimiento** y sus parámetros.
- **Año lectivo**: apertura del nuevo año y **pasaje de año** (promover grupos y alumnos al ciclo siguiente).
- **Grupos / cursos** (divisiones) y **grupos especiales** (agrupaciones no estándar).
- **Planes de estudio**: materias que componen cada plan; carga de **materias por plan**; **materias especiales** y **materias compartidas** entre planes.
- **Cambio de grupo** de un alumno; **desasignar materias**.

## Docentes

- ABM de **docentes/profesores**.
- **Asignar materias a docentes** (y a grupos).
- **Faltas**, **licencias** y **sanciones** de docentes; **evaluaciones docentes**.

## Calificaciones

- Carga de **notas por materia** (por docente/grupo).
- **Notas especiales**, **notas extra**, marca de **recuperatorio**.
- **Nota definitiva**, con posibilidad de **cálculo automático** a partir de las parciales.
- **Promedios** y **posibles calificaciones** (escala válida configurable por la institución: numérica o conceptual).
- Reportes: **alumnos desaprobados**, control de notas.

## Asistencia

- Toma de asistencia **diaria**, **masiva** (por grupo), **por código de barras** y **presencia por jornada/turno**.
- Consolidación **mensual** de asistencia.
- Cómputo de **días hábiles** para el boletín.

## Boletines, certificados e informes

- **Boletines** por período (notas + asistencia + conceptos).
- **Certificados escolares** (alumno regular, analítico, etc.) y su emisión/impresión.
- **Conceptos** del profesor, **conceptos internos** e **informe actitudinal**.

## Convivencia y comunicación

- **Sanciones**, **faltas** y **acciones** disciplinarias sobre alumnos.
- **Cuaderno de comunicaciones** (mensajes escuela ↔ familia) con búsqueda y alta de comunicaciones.
- Envío de **mails** (genéricos, de cuota, etc.).

## Egreso e ingreso

- **Egresados**: alumnos que completaron el nivel.
- **Vacantes** y **solicitudes** de inscripción de aspirantes.

---

> El detalle de cada regla (cómo se calcula una nota definitiva, qué escala aplica, qué entra en el boletín) se define con el PO al implementar cada feature, y queda plasmado en el código, los tests y el issue correspondiente — no en este doc.
