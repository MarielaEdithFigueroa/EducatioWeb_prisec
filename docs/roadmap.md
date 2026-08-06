# Roadmap técnico de Educatio

Educatio está siendo migrado desde una aplicación existente desarrollada en VB6. La nueva versión utiliza Laravel 13, React, Inertia y MariaDB. Este documento registra el avance técnico general del proyecto, pero no reemplaza el análisis funcional detallado de cada módulo. El orden de las etapas puede ajustarse a medida que se conozcan nuevas dependencias.

# Principios del proyecto

Las siguientes reglas deberán respetarse durante todo el desarrollo:

- Mantener una arquitectura consistente por encima de la velocidad de implementación.
- Priorizar la claridad y mantenibilidad del código.
- No debilitar el modelo nuevo para aceptar inconsistencias del sistema VB6.
- Perfilar, sanear y transformar los datos legacy antes de importarlos.
- Documentar toda decisión funcional o técnica importante.
- Preservar la integridad referencial en las migraciones y operaciones de datos.
- Acompañar cada módulo importante con pruebas apropiadas.
- Respetar las convenciones definidas en `docs/decisiones-arquitectura.md`.
- Revisar el diseño funcional y técnico antes de implementar un módulo nuevo.
- Evitar agregar abstracciones, tablas, índices o funcionalidades sin una necesidad concreta.
- Utilizar baja lógica mediante `activo` cuando corresponda.
- No ejecutar operaciones destructivas sobre bases con información que deba conservarse sin autorización explícita.

## Estados

- [x] Completado
- [ ] Pendiente
- [~] En progreso
- [!] Bloqueado o requiere definición

`[~]` identifica una implementación parcial o una tarea actualmente en curso. `[!]` identifica una tarea que no puede continuar hasta resolver una definición o dependencia.

## 1. Base técnica del proyecto

- [x] Configuración inicial de Laravel 13.
- [x] Integración con React e Inertia.
- [x] Configuración de MariaDB.
- [x] Repositorio Git.
- [x] Entorno de trabajo con Visual Studio Code y Codex.
- [x] Migraciones de infraestructura existentes.
- [x] Modelos y estructura inicial del proyecto.
- [x] Documento `docs/decisiones-arquitectura.md`.

## 2. Núcleo académico inicial

- [x] Definición de convenciones de tablas y claves.
- [x] Migración `niveles`.
- [x] Migración `cursos`.
- [x] Migración `divisiones`.
- [x] Migración `turnos`.
- [x] Migración `planes_estudio`.
- [x] Migración `grupos`.
- [x] Modelos Eloquent correspondientes.
- [x] Relaciones Eloquent básicas.
- [x] Seeder de niveles.
- [x] Restricciones simples y compuestas.
- [x] Restricción única de identidad de grupos.
- [x] Verificación de migraciones en MariaDB.
- [x] Pruebas iniciales aprobadas.
- [ ] Seeders de cursos iniciales.
- [ ] Seeders de divisiones iniciales.
- [ ] Seeders de turnos iniciales.
- [ ] Seeders de planes de estudio, si corresponde.
- [ ] Datos iniciales de prueba para grupos.

## 3. ABM del núcleo académico

- [ ] Consulta de niveles en modo solo lectura.
- [ ] ABM de cursos.
- [ ] ABM de divisiones.
- [ ] ABM de turnos.
- [ ] ABM de planes de estudio.
- [ ] ABM de grupos.
- [ ] Filtros por nivel.
- [ ] Filtros por ciclo lectivo.
- [ ] Ordenamiento mediante el campo `orden`.
- [ ] Baja lógica mediante `activo`.
- [ ] Validaciones de coherencia entre nivel, curso y plan.
- [ ] Pruebas funcionales del módulo.

## 4. Seguridad, usuarios y permisos

- [x] Revisión del sistema actual de autenticación: existe autenticación cerrada mediante Laravel Fortify.
- [ ] Roles.
- [ ] Permisos.
- [~] Perfiles de usuario: existe edición básica del perfil, pero el alcance institucional está pendiente.
- [~] Usuarios administradores: existe un administrador inicial creado por seeder; falta la gestión de usuarios.
- [ ] Usuarios institucionales.
- [ ] Restricciones por institución.
- [x] Protección de rutas que requieren autenticación.
- [ ] Auditoría de accesos.
- [~] Recuperación y cambio de contraseña: el cambio de contraseña está implementado; la recuperación completa continúa pendiente de revisión.

No se definen roles ni permisos hasta realizar el análisis funcional correspondiente.

## 5. Instituciones y configuración general

- [ ] Instituciones.
- [ ] Datos institucionales.
- [ ] Configuración académica.
- [ ] Niveles habilitados por institución.
- [ ] Ciclo lectivo activo.
- [ ] Parámetros generales.
- [ ] Configuración de logos y datos para informes.
- [ ] Configuración de numeraciones y formatos.

## 6. Personas

- [ ] Alumnos.
- [ ] Responsables.
- [ ] Docentes.
- [ ] Personal.
- [ ] Datos personales.
- [ ] Contactos.
- [ ] Domicilios.
- [ ] Documentación.
- [ ] Relaciones familiares.
- [ ] Estado activo o histórico.

## 7. Matrícula y trayectoria académica

- [ ] Inscripción de alumnos.
- [ ] Asignación a grupos.
- [ ] Historial de grupos por ciclo lectivo.
- [ ] Pases.
- [ ] Altas y bajas.
- [ ] Repitencia.
- [ ] Egresos.
- [ ] Cambios de curso o división.
- [ ] Trayectoria académica.
- [ ] Importación de datos históricos desde VB6.

## 8. Planes de estudio y espacios curriculares

- [ ] Materias o espacios curriculares.
- [ ] Asociación de materias con planes.
- [ ] Asociación de materias con cursos.
- [ ] Carga horaria.
- [ ] Orientaciones.
- [ ] Equivalencias.
- [ ] Planes históricos.
- [ ] Vigencia de planes.
- [ ] Docentes por materia y grupo.

## 9. Calificaciones

- [ ] Tipos de examen.
- [ ] Períodos de evaluación.
- [ ] Calificaciones.
- [ ] Cursados.
- [ ] Aprobaciones y desaprobaciones.
- [ ] Mesas de examen.
- [ ] Exámenes finales.
- [ ] Historial académico.
- [ ] Promedios.
- [ ] Reglas de promoción.
- [ ] Boletines.
- [ ] Certificados.
- [ ] Actas.
- [ ] Importación del historial desde VB6.

## 10. Asistencia

- [ ] Asistencia diaria.
- [ ] Inasistencias.
- [ ] Tardanzas.
- [ ] Justificaciones.
- [ ] Asistencia por materia.
- [ ] Resúmenes mensuales.
- [ ] Alertas.
- [ ] Informes.

## 11. Facturación y cuenta corriente

- [ ] Conceptos de facturación.
- [ ] Planes de cuotas.
- [ ] Generación de cuotas.
- [ ] Cuenta corriente.
- [ ] Becas.
- [ ] Descuentos.
- [ ] Recargos.
- [ ] Pagos.
- [ ] Comprobantes.
- [ ] Deudas.
- [ ] Actualización de valores.
- [ ] Informes financieros.
- [ ] Migración de saldos históricos.

## 12. Comunicaciones

- [ ] Integración con el Cuaderno de Comunicaciones.
- [ ] Mensajes.
- [ ] Destinatarios.
- [ ] Respuestas.
- [ ] Archivos adjuntos.
- [ ] Notificaciones.
- [ ] Comunicaciones institucionales.
- [ ] Certificados y boletines enviados.
- [ ] Integración con aplicaciones móviles.

## 13. Informes y exportaciones

- [ ] Informes PDF.
- [ ] Exportaciones a Excel.
- [ ] Listados académicos.
- [ ] Listados administrativos.
- [ ] Boletines.
- [ ] Actas.
- [ ] Certificados.
- [ ] Informes de asistencia.
- [ ] Informes financieros.
- [ ] Reemplazo progresivo de Crystal Reports.
- [ ] Plantillas institucionales.

## 14. Integraciones

- [ ] Moodle.
- [ ] Aplicaciones móviles.
- [ ] APIs.
- [ ] Servicios externos.
- [ ] Importaciones.
- [ ] Exportaciones.
- [ ] Procesos programados.
- [ ] Correo electrónico.
- [ ] Notificaciones push.

## 15. Migración desde VB6

- [ ] Inventario de tablas existentes.
- [ ] Inventario de módulos actuales.
- [ ] Mapeo de tablas legacy a tablas nuevas.
- [ ] Perfilado de datos.
- [ ] Identificación de inconsistencias.
- [ ] Reglas de saneamiento.
- [ ] Scripts de transformación.
- [ ] Importaciones de prueba.
- [ ] Validación de cantidades y totales.
- [ ] Validación funcional con usuarios.
- [ ] Estrategia de transición.
- [ ] Estrategia de reversión.
- [ ] Migración definitiva.

> El modelo nuevo no se debilitará para aceptar inconsistencias históricas. Los datos legacy deberán ser perfilados, saneados y transformados antes de su importación.

## 16. Calidad y pruebas

- [ ] Tests unitarios.
- [ ] Tests de integración.
- [ ] Tests de migraciones.
- [ ] Tests de restricciones de base de datos.
- [ ] Tests de permisos.
- [ ] Tests de formularios.
- [ ] Tests de procesos académicos.
- [ ] Tests de procesos financieros.
- [ ] Pruebas de rendimiento.
- [ ] Revisión con Pint.
- [ ] Análisis estático.
- [ ] Pruebas de regresión.
- [ ] Pruebas de migración legacy.
- [x] Suite inicial: 9 tests y 23 assertions aprobados.
- [x] Validación sintáctica de los archivos PHP iniciales.
- [x] Validación inicial de migraciones contra MariaDB.

## 17. Despliegue y operación

- [ ] Definición de ambientes.
- [ ] Desarrollo.
- [ ] Pruebas.
- [ ] Producción.
- [ ] Variables de entorno.
- [ ] Copias de seguridad.
- [ ] Restauración.
- [ ] Despliegue automatizado.
- [ ] Logs.
- [ ] Monitoreo.
- [ ] Manejo de errores.
- [ ] Actualizaciones.
- [ ] Seguridad del servidor.
- [ ] Política de migraciones en producción.

## 18. Documentación

- [x] `docs/decisiones-arquitectura.md`.
- [ ] Documentación funcional.
- [ ] Diccionario de datos.
- [ ] Diagramas del modelo.
- [ ] Documentación de APIs.
- [ ] Manual técnico.
- [ ] Manual de instalación.
- [ ] Manual de despliegue.
- [ ] Manual de usuario.
- [ ] Registro de decisiones futuras.
- [ ] Registro de cambios.

## Próximos pasos inmediatos

1. Revisar y confirmar el commit de la estructura académica inicial.
2. Crear los seeders de cursos, divisiones y turnos.
3. Definir el alcance visual y funcional de los primeros ABM.
4. Implementar el ABM de cursos.
5. Implementar el ABM de divisiones.
6. Implementar el ABM de turnos.
7. Implementar el ABM de planes de estudio.
8. Implementar el ABM de grupos.
9. Agregar pruebas funcionales de este núcleo.
10. Comenzar el relevamiento del módulo de alumnos.

## Estado actual del proyecto

### Arquitectura

- Convenciones iniciales definidas.
- Decisiones técnicas documentadas.
- Integridad referencial del núcleo académico validada.
- Roadmap técnico creado.

### Base de datos

- Migraciones académicas iniciales implementadas y ejecutadas.
- Tablas `niveles`, `cursos`, `divisiones`, `turnos`, `planes_estudio` y `grupos` operativas.
- Claves foráneas simples y compuestas verificadas.
- Restricciones únicas e índices iniciales verificados.
- Seeders iniciales ejecutados correctamente.

### Backend

- Laravel 13 configurado.
- Modelos Eloquent del núcleo académico creados.
- Relaciones básicas configuradas.
- Autenticación inicial disponible.
- Primeros ABM todavía pendientes.

### Frontend

- React e Inertia configurados.
- Primer ABM académico pendiente de implementación.

### Migración desde VB6

- Relevamiento y perfilado de datos todavía pendientes.
- No se iniciaron importaciones de datos históricos.

### Estado general

Se completó la etapa inicial de arquitectura, modelo de datos y documentación. El próximo objetivo funcional será implementar el ABM de cursos como primer circuito completo entre Laravel, React, Inertia y MariaDB.
