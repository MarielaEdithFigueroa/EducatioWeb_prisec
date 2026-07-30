# 02 · Glosario

Terminología del dominio Educatio. Los términos salen del sistema legacy y del vocabulario habitual de la gestión escolar argentina. Se usan **en castellano** en código y UI.

## General

- **Educatio** — sistema de gestión escolar de Eureka. El legacy (VB6) tiene un módulo **Académico** (escuela) y uno **Administrativo** (facturación/cobranzas).
- **EureFramework** — base técnica sobre la que se construye este proyecto (Laravel + Inertia + React). No es dominio.
- **Institución / Establecimiento** — la escuela. Un despliegue puede gestionar una o varias.
- **Año lectivo / Ciclo lectivo** — el año escolar. La operación se organiza por año lectivo; al cerrar el año se hace el pasaje al siguiente ("Nuevo Año").
- **Baja lógica** — las entidades no se borran: se marcan `activo = false`. Nunca `deleted_at`.

## Académico

- **Alumno** — estudiante. Tiene **legajo** (número identificatorio) y matrícula.
- **Legajo** — número/expediente del alumno; también el conjunto de su documentación.
- **Matrícula / Matriculación** — alta del alumno en el año lectivo. **Rematriculación** es reinscribir un alumno del año anterior al nuevo año.
- **Responsable** — adulto a cargo del alumno (padre/madre/tutor), receptor de facturación y comunicaciones. **Responsable pedagógico** es quien responde por lo académico.
- **Grupo / Curso / División** — la agrupación de alumnos (ej: "2º B"). **Grupo especial** es una agrupación no estándar (ej: para una materia optativa o taller).
- **Materia** — asignatura. Puede ser común, **especial** (optativa/taller) o **compartida** entre planes.
- **Plan de estudios** — conjunto de materias por año/nivel que cursa un alumno. Las materias se cargan "por plan".
- **Docente / Profesor** — quien dicta materias. Se le **asignan materias**; se registran sus **faltas**, **licencias** y **sanciones**.
- **Calificación / Nota** — evaluación del alumno en una materia. Hay **notas por materia**, **notas especiales**, **notas extra**, **nota definitiva** (puede calcularse automáticamente), **recuperatorio** y **promedios**. Las **posibles calificaciones** son la escala válida (numérica o conceptual) que define la institución.
- **Asistencia** — presencia del alumno. Se toma **diaria**, **masiva** (por grupo), **por código de barras** o **por jornada** (presencia por turno), y se consolida **mensual**.
- **Boletín** — reporte de calificaciones y asistencia del alumno por período.
- **Certificado escolar** — constancia oficial (alumno regular, analítico, etc.).
- **Concepto** — valoración cualitativa. **Concepto del profesor** (sobre el alumno), **conceptos internos**, **informe actitudinal**.
- **Sanción** — medida disciplinaria sobre el alumno (o sobre el docente). Relacionada con **faltas** y **acciones**.
- **Cuaderno de comunicaciones** — canal de mensajes escuela ↔ familia.
- **Ficha médica / Datos religiosos** — datos complementarios del alumno.
- **Beca** — beneficio que reduce o exime el arancel del alumno.
- **Egresado** — alumno que completó el nivel.
- **Vacante / Solicitud** — cupo e inscripción de un aspirante.

## Administrativo (facturación y cobranzas)

- **Cuota / Arancel** — importe periódico que paga el responsable por el alumno. Se organiza en **planes de cuotas**; hay **actualización de cuotas**.
- **Cuenta corriente (Cta Cte)** — saldo del alumno/responsable o de una **empresa**. Puede ser **de alumnos**, **de empresas**, **unificada** o **adicional**.
- **Empresa** — entidad que puede tener cuenta corriente propia y **planes de empresas** (ej: convenios, obras sociales, empleadores que cubren aranceles).
- **Factura / Facturación** — comprobante de venta. **Factura electrónica** requiere autorización de AFIP.
- **CAE** — Código de Autorización Electrónico que AFIP otorga a cada comprobante electrónico. Sin CAE la factura electrónica no es válida.
- **Nota de crédito / Nota de débito** — comprobantes que ajustan una factura.
- **Recibo** — comprobante de cobro.
- **Cobro** — registro de un pago recibido. Puede ser en efectivo, cheque, débito automático, transferencia/banco, **pronto pago**, o vía **controlador fiscal** (Hasar).
- **Caja** — el dinero operativo del día. Se hace **apertura**, **movimientos**, **ajustes** y **cierre de caja**.
- **Cheque** — valor recibido o emitido. Se gestionan **chequeras**, **cheques emitidos**, **cambio** e **historial**.
- **Egreso** — pago/salida de dinero (a proveedores, gastos). Puede ser **con IVA** o **por cuenta**; incluye **órdenes de compra** y **proveedores**.
- **Morosos** — alumnos/responsables con deuda vencida. Se gestionan **acciones**, **estadísticas** y **parámetros** de mora.
- **Débito automático** — cobro directo por convenio bancario/tarjeta (ej: PagoMisCuentas, First Data, Banco Itaú, MercadoPago). Incluye archivos de envío y de devolución.
- **Contabilidad** — **asientos**, **plan de cuentas**, **libro diario**, **libro mayor**, **ejercicio fiscal**, **retenciones** y **cuentas de IVA**.

> Este glosario es una guía de dominio, no el esquema de datos. Los nombres exactos de tablas/columnas se definen en las migraciones.
