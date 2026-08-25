# EureFramework

Base técnica para aplicaciones argentinas construidas con Laravel 13, React 19, Inertia v3, Tailwind CSS v4 y TypeScript. No incluye dominio funcional.

## Incluye

- autenticación cerrada con `login` y contraseña;
- perfil, cambio de contraseña, sesiones, cache, queues, logs y health endpoint;
- layout administrativo responsive, modo claro/oscuro y componentes Eure;
- auditoría general preparada en la tabla `logs`;
- formatos regionales argentinos y utilidades CUIT/CUIL.

## Inicio

Configurá MariaDB en `.env` y definí `SEED_ADMIN_PASSWORD`. Luego:

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
```

El seeder crea el usuario indicado por `SEED_ADMIN_LOGIN`, `SEED_ADMIN_NOMBRE` y `SEED_ADMIN_APELLIDO`. La contraseña nunca se incluye en el repositorio.

## Después de cada `pull`

Estamos en `migrate:fresh`, así que después de traer cambios hay que rehacer la base y levantar el entorno de nuevo:

```powershell
php artisan migrate:fresh --seed
composer run dev
```

## Calidad

```powershell
php artisan test
vendor\bin\pint
npm run lint
npm run types:check
npm run build
```
