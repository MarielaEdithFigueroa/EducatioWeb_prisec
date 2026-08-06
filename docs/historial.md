# Historial técnico de Educatio

Este documento registra hitos, decisiones y cambios relevantes del proyecto. Complementa el historial de Git y no reemplaza `docs/decisiones-arquitectura.md` ni `docs/roadmap.md`.

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
