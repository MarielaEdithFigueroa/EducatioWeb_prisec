# Alumnos — borrador desde el legacy (para validar con Mariela)

> **Encuadre.** Reconstrucción del módulo **Alumnos** a partir del formulario real del legacy (`SecundarioV4/frmAlumnos.frm`, mantenido hasta 2025). **Es un borrador para que Mariela lo corrija**, no el modelo final. Los campos y validaciones exactos se cierran con las **capturas de Mariela** + la **DB del legacy**. Sirve para ir a ella con algo hecho y no con una hoja en blanco.

## Alumno — campos (del form)

**Identificación**
- Legajo (`cod_alumno`) — identificador del alumno.
- Apellido, Nombre.
- Sexo (Femenino / Masculino).
- DNI + tipo de documento (`tipodni`), CUIT.

**Contacto**
- Domicilio, Ciudad, Provincia.
- Celular, Email.

**Escolar / grupo**
- Grupo actual (`cod_grupo`) → curso + turno.
- **Grupo a futuro** (grupo previsto para el ciclo siguiente).
- Fecha de ingreso, Fecha de inicio de cursado.

**Situación / baja**
- Fecha de baja + Motivo de baja (baja lógica del alumno).
- Vínculos a sanciones (`Cod_TotalSanciones`) y faltas (`Cod_TotalFaltas`).

**Pase / documentación escolar**
- Datos de pase: Nombre (institución), Fecha de pase, "Guardados en".
- Libro 1er ciclo + Folio, Libro 2do ciclo + Folio.

**Financiero / beca** (nexo con administrativo)
- % de Beca, Forma de pago.
- Accesos: Cuenta corriente, Pagos realizados, Ficha de morosidad, Plan de cuotas.

**Permisos / web**
- Autorización de imagen (`NoPermiteFoto` — "NO autorizado para fotografiar o publicar imágenes del alumno").
- Password web / usuario de autoinscripción (`usuarioautoinscripcion`).

**Otros datos**
- Familia (`Cod_Familia`) → **hermanos** en la institución.
- Deportes que practica.
- Datos religiosos.
- Foto / álbum de fotos.

## Responsables (el form maneja Responsable 1 y Responsable 2)

Por responsable: Apellido, Nombre, Celular, Email, Domicilio / Ciudad / Provincia, Domicilio laboral / Ciudad laboral / Teléfono laboral, Profesión, CUIT, DNI, **CBU** (Clave Bancaria), Forma de pago.

> El legacy distingue roles de responsable (de **cobros**, **pedagógico**, **informado**). A confirmar cómo se modela (¿1..N responsables por alumno con un rol cada uno?).

## Entidades vinculadas (no van todas en el ABM de alumno)

Grupo (curso/división/turno) · Familia/hermanos · Sanciones · Faltas · Cuenta corriente · Plan de cuotas · Rematriculación (`alumnosrematriculacion`, `alumnosreincriptos`) · Deportes · Datos religiosos · Teléfonos · Direcciones.

## Reglas inferidas (a confirmar)

- El alumno **no se borra**: se da de baja con fecha + motivo (baja lógica). Encaja con la convención `activo` del proyecto nuevo.
- Legajo (`cod_alumno`) es la clave del alumno.
- El alumno pertenece a un grupo por ciclo, y puede tener un "grupo a futuro" para el pasaje de año.
- Hay datos que son puente con el administrativo (beca, forma de pago, cuenta corriente): **decidir si entran al MVP académico o quedan del lado administrativo**.

## Preguntas para Mariela (las que el código no responde)

1. Legajo: ¿es único y acompaña al alumno de primaria a secundaria, o cambia por nivel?
2. Responsables: ¿cuántos por alumno y con qué roles (cobro / pedagógico / informado)? ¿Qué datos son obligatorios?
3. ¿Qué campos del alumno son **obligatorios hoy** y cuáles quedaron **obsoletos** del viejo (ej. libros/folios de pase, autoinscripción web, forma de pago a nivel alumno)?
4. "Grupo a futuro" y el **pasaje de año**: ¿cómo se usa hoy en la práctica?
5. ¿La foto / autorización de imagen sigue vigente como dato a cargar?

---

> Próximo paso: Mariela manda las capturas y responde estas 5; con eso el borrador pasa a issue de diseño validado, y la implementación va aparte (primer CRUD de referencia).
