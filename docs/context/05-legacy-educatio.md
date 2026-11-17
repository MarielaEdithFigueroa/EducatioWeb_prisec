# 05 · Sistema legacy (Educatio VB6)

Referencia del sistema que esta reescritura reemplaza. Sirve para entender el alcance funcional y como fuente para el dominio — **no** como modelo a copiar tal cual.

## Qué es

Educatio es una aplicación de **escritorio en Visual Basic 6** (formularios `.frm`/`.frx`, módulos `.bas`, clases `.cls`), de Eureka Soluciones Informáticas, en producción desde ~2004. Se distribuye como ejecutable por PC, con actualizaciones vía un control ActiveX de auto-update (ArBytes AutoUpdate). Hay logs de eventos y de cambios por instalación.

## Dónde está

En la **carpeta hermana del repo** — relativa a la raíz del proyecto, `../Legacy/EducatioSecVB` (fuera del repo y no versionado; hay que darle acceso al agente para que pueda leerla). Está organizada por módulos:

| Carpeta | Proyecto VB | Qué es |
|---|---|---|
| `Administrativo` | `EducatioAdm.vbp` | Módulo administrativo (facturación/cobranzas) — versión base |
| `AdministrativoConIva` | `EducatioAdm.vbp` | Variante con tratamiento de IVA |
| `AdministrativoV4` | `EducatioAdm.vbp` | Módulo administrativo — **versión más reciente (referencia)** |
| `SecundarioV4` | `Escuelas.vbp` | Módulo académico de Secundaria — **referencia académica** |
| `Terciario` | `Escuelas.vbp` | Módulo académico de Terciario — **fuera de alcance** |
| `FormsCompartidos` | — | Formularios comunes (alumno, cta cte, ficha, clientes, proveedores, etc.) |

## Cómo leer los formularios

Los nombres de los `.frm` son muy descriptivos y son la mejor pista del dominio. Convenciones:

- Prefijo `F` / `Frm` / `frm` = formulario (pantalla).
- Prefijo `rpt` = reporte (ej. `rptBoletines`, `rptListadosAlumnos`).
- Ejemplos administrativos: `FrmFacturacion`, `FCobroFacturaElectronica`, `FCierreCaja`, `FMorosos`, `FrmPlandeCuotas`, `FrmContaLibroMayor`.
- Ejemplos académicos: `frmAlumnos`, `FCargarNotasPorMateria`, `FIngresarAsistencia`, `FrmPlanEstudios`, `FrmCertificadoEscolar`, `frmCuadernoComunicaciones`, `FrmNotaDefinitivaAutomatica`.

Para entender una regla puntual, se abre el `.frm` correspondiente y se lee el código VB del formulario. Los `.frx` son binarios (recursos visuales) y no aportan lógica.

Los `.vbp` identifican los formularios y módulos realmente incluidos en cada ejecutable, incluso cuando provienen de otra carpeta. Revisarlos antes de elegir entre archivos de nombre parecido. Si los acentos aparecen dañados, comprobar la codificación del archivo; no reescribir el original para leerlo.

## Referencias para los próximos circuitos

Rutas relativas a `../Legacy/EducatioSecVB`:

| Circuito | Archivo | Comportamiento observado |
| --- | --- | --- |
| Legajo y responsables | `SecundarioV4/frmAlumnos.frm` | Datos del alumno, responsables y accesos a otros módulos; distingue grupo actual y futuro |
| Cambio de grupo | `SecundarioV4/FrmCambiarGrupo.frm` | Actualiza `cod_grupo`/`cod_grupo2`; cierra el vínculo previo de `Alumno-Grupo` y crea o reactiva el correspondiente al grupo elegido |
| Rematriculación | `SecundarioV4/frmAlumnosRematriculados.frm` | Edita datos de `AlumnosRematriculacion` y la fecha de rematriculación del alumno |
| Grupos y planes | `SecundarioV4/frmNuevosGrupos.frm`, `SecundarioV4/FrmPlanEstudios.frm`, `SecundarioV4/FrmCargarMateriasPorPlan.frm` | Referencias para composición de grupos y carga de materias por plan |
| Asignación docente | `SecundarioV4/frmAsignarMateriasaDocente.frm` | Asigna materia, docente y grupo con condición; impide un segundo titular activo para la misma materia y grupo |
| Cargos por familia | `AdministrativoV4/frmGeneraCtaCtexFamilia.frm` | Genera `ItemsAFacturar` para un alumno seleccionado por familia y comprueba cargos activos del mismo plan y mes |
| Facturación electrónica | `AdministrativoV4/FrmFacturacionDetalle.frm` | Registra solicitudes y resultados de CAE |

Estas observaciones describen código existente, no decisiones aprobadas para el modelo web. Los SQL de formularios tampoco reemplazan un relevamiento del esquema y de los datos reales. Los borradores de [kickoff](../kickoff) conservan las hipótesis del análisis inicial.

## Qué se toma y qué no

- **Se toma**: el alcance funcional y las reglas de negocio (qué campos, qué validaciones, qué flujos). Ver `03-academico.md` y `04-administrativo.md`.
- **No se toma**: la arquitectura VB6, la estructura de pantallas, ni el modelo de datos literal. El proyecto web define su propio modelo (código + migraciones) siguiendo las convenciones de EureFramework.
- **Fuera de alcance**: todo lo propio de **Terciario** (correlatividades, mesas de examen, inscripción a finales, equivalencias, pasantías) y del **nivel superior** en general.

## Integraciones del legacy a tener en cuenta

- **AFIP** — factura electrónica y CAE.
- **Controlador fiscal Hasar** — cobros/comprobantes fiscales.
- **Bancos/medios de pago** — débito automático y devoluciones (PagoMisCuentas, First Data, Banco Itaú, MercadoPago, Cambridge, etc.).

Cuáles de estas integraciones entran en la reescritura, y con qué prioridad, lo define el PO.
