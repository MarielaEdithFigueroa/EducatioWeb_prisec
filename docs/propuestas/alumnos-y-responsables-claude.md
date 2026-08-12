# Alumnos y Responsables — migraciones propuestas

> Propuesta, no aplicada. Orden de ejecución: provincias → ciudades → tipos_documento → motivos_baja → alumnos → responsables → alumno_responsable.

## `2026_08_12_000001_create_provincias_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provincias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 60);

            $table->unique('descripcion', 'provincias_descripcion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provincias');
    }
};
```

## `2026_08_12_000002_create_ciudades_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciudades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 100);
            $table->unsignedBigInteger('provincia_id');

            $table->unique(['provincia_id', 'descripcion'], 'ciudades_provincia_descripcion_unique');
            $table->foreign('provincia_id', 'ciudades_provincia_fk')
                ->references('id')
                ->on('provincias')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudades');
    }
};
```

## `2026_08_12_000003_create_tipos_documento_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_documento', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 30);

            $table->unique('descripcion', 'tipos_documento_descripcion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_documento');
    }
};
```

## `2026_08_12_000004_create_motivos_baja_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivos_baja', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 100);

            $table->unique('descripcion', 'motivos_baja_descripcion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivos_baja');
    }
};
```

## `2026_08_12_000005_create_alumnos_table.php`

`genero`: string libre en vez del binario Femenino/Masculino fijo del legacy. Valores esperados a nivel aplicación: `femenino`, `masculino`, `no_binario`, `prefiere_no_decir`, `otro` — con `genero_especificar` como texto libre solo cuando se elige `otro`. No es un catálogo aparte (evita una tabla más para algo que no se filtra ni se reporta hoy); si más adelante hace falta administrar los valores desde una pantalla, se convierte a catálogo.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('legajo', 20);
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->string('genero', 20)->nullable();
            $table->string('genero_especificar', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->unsignedBigInteger('tipo_documento_id')->nullable();
            $table->string('dni', 20)->nullable();
            $table->string('cuit', 20)->nullable();
            $table->string('domicilio', 150)->nullable();
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->unsignedBigInteger('provincia_id')->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_inicio_cursado')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->unsignedBigInteger('motivo_baja_id')->nullable();
            $table->boolean('no_permite_foto')->default(false);
            $table->text('observaciones')->nullable();

            $table->unique('legajo', 'alumnos_legajo_unique');
            $table->index(['activo', 'apellido', 'nombre'], 'alumnos_activo_apellido_nombre_idx');
            $table->index('ciudad_id', 'alumnos_ciudad_idx');
            $table->index('provincia_id', 'alumnos_provincia_idx');

            $table->foreign('tipo_documento_id', 'alumnos_tipo_documento_fk')
                ->references('id')
                ->on('tipos_documento')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('ciudad_id', 'alumnos_ciudad_fk')
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('provincia_id', 'alumnos_provincia_fk')
                ->references('id')
                ->on('provincias')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('motivo_baja_id', 'alumnos_motivo_baja_fk')
                ->references('id')
                ->on('motivos_baja')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
```

## `2026_08_12_000006_create_responsables_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->unsignedBigInteger('tipo_documento_id')->nullable();
            $table->string('dni', 20)->nullable();
            $table->string('cuit', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('domicilio', 150)->nullable();
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->unsignedBigInteger('provincia_id')->nullable();
            $table->string('profesion', 100)->nullable();
            $table->string('domicilio_laboral', 150)->nullable();
            $table->unsignedBigInteger('ciudad_laboral_id')->nullable();
            $table->string('telefono_laboral', 30)->nullable();
            $table->text('observaciones')->nullable();

            $table->index(['activo', 'apellido', 'nombre'], 'responsables_activo_apellido_nombre_idx');
            $table->index('ciudad_id', 'responsables_ciudad_idx');
            $table->index('provincia_id', 'responsables_provincia_idx');
            $table->index('ciudad_laboral_id', 'responsables_ciudad_laboral_idx');

            $table->foreign('tipo_documento_id', 'responsables_tipo_documento_fk')
                ->references('id')
                ->on('tipos_documento')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('ciudad_id', 'responsables_ciudad_fk')
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('provincia_id', 'responsables_provincia_fk')
                ->references('id')
                ->on('provincias')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('ciudad_laboral_id', 'responsables_ciudad_laboral_fk')
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsables');
    }
};
```

## `2026_08_12_000007_create_alumno_responsable_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_responsable', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('responsable_id');
            $table->boolean('es_economico')->default(false);
            $table->boolean('es_pedagogico')->default(false);
            $table->boolean('es_informado')->default(false);
            $table->unsignedSmallInteger('orden')->default(0);

            $table->unique(['alumno_id', 'responsable_id'], 'alumno_responsable_alumno_responsable_unique');
            $table->index('responsable_id', 'alumno_responsable_responsable_idx');

            $table->foreign('alumno_id', 'alumno_responsable_alumno_fk')
                ->references('id')
                ->on('alumnos')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('responsable_id', 'alumno_responsable_responsable_fk')
                ->references('id')
                ->on('responsables')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_responsable');
    }
};
```

## Seeders — provincias y ciudades

> **No es un seeder de producción.** Cubre las 23 provincias + CABA, sus capitales, y una selección **no exhaustiva** de localidades de Neuquén y su zona de influencia (Alto Valle / Confluencia, ~200 km), armada a mano a partir de conocimiento general, **sin validar contra una fuente geográfica oficial**. Antes de usarlo en serio hay que contrastarlo contra algo como la [API Georef de datos.gob.ar](https://datos.gob.ar) (INDEC/IGN) y completar lo que falte — puede haber localidades cercanas a Neuquén que quedaron afuera, o algún error de encuadre provincial. Asume que existen `App\Models\Provincia` y `App\Models\Ciudad` (con `Provincia::ciudades()` como `hasMany`), acompañando a las migraciones de arriba.

### `ProvinciasSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Provincia;
use Illuminate\Database\Seeder;

class ProvinciasSeeder extends Seeder
{
    public function run(): void
    {
        $provincias = [
            'Buenos Aires',
            'Catamarca',
            'Chaco',
            'Chubut',
            'Ciudad Autónoma de Buenos Aires',
            'Córdoba',
            'Corrientes',
            'Entre Ríos',
            'Formosa',
            'Jujuy',
            'La Pampa',
            'La Rioja',
            'Mendoza',
            'Misiones',
            'Neuquén',
            'Río Negro',
            'Salta',
            'San Juan',
            'San Luis',
            'Santa Cruz',
            'Santa Fe',
            'Santiago del Estero',
            'Tierra del Fuego, Antártida e Islas del Atlántico Sur',
            'Tucumán',
        ];

        foreach ($provincias as $descripcion) {
            Provincia::query()->updateOrCreate(
                ['descripcion' => $descripcion],
                ['activo' => true],
            );
        }
    }
}
```

### `CiudadesSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Provincia;
use Illuminate\Database\Seeder;

class CiudadesSeeder extends Seeder
{
    public function run(): void
    {
        $ciudadesPorProvincia = [
            // Capitales provinciales
            'Buenos Aires' => ['La Plata'],
            'Catamarca' => ['San Fernando del Valle de Catamarca'],
            'Chaco' => ['Resistencia'],
            'Chubut' => ['Rawson'],
            'Ciudad Autónoma de Buenos Aires' => ['Ciudad Autónoma de Buenos Aires'],
            'Córdoba' => ['Córdoba'],
            'Corrientes' => ['Corrientes'],
            'Entre Ríos' => ['Paraná'],
            'Formosa' => ['Formosa'],
            'Jujuy' => ['San Salvador de Jujuy'],
            'La Pampa' => ['Santa Rosa'],
            'La Rioja' => ['La Rioja'],
            'Mendoza' => ['Mendoza'],
            'Misiones' => ['Posadas'],
            'Salta' => ['Salta'],
            'San Juan' => ['San Juan'],
            'San Luis' => ['San Luis'],
            'Santa Cruz' => ['Río Gallegos'],
            'Santa Fe' => ['Santa Fe'],
            'Santiago del Estero' => ['Santiago del Estero'],
            'Tierra del Fuego, Antártida e Islas del Atlántico Sur' => ['Ushuaia'],
            'Tucumán' => ['San Miguel de Tucumán'],

            // Neuquén y zona de influencia (~200 km) — no exhaustivo, a revisar
            'Neuquén' => [
                'Neuquén',
                'Plottier',
                'Centenario',
                'Senillosa',
                'Vista Alegre',
                'San Patricio del Chañar',
                'Añelo',
                'Cutral Có',
                'Plaza Huincul',
                'Picún Leufú',
                'Piedra del Águila',
                'Zapala',
                'Las Lajas',
            ],
            'Río Negro' => [
                'Viedma',
                'Cipolletti',
                'General Roca',
                'Cinco Saltos',
                'Allen',
                'Fernández Oro',
                'Villa Regina',
                'Catriel',
            ],
        ];

        foreach ($ciudadesPorProvincia as $descripcionProvincia => $ciudades) {
            $provincia = Provincia::query()
                ->where('descripcion', $descripcionProvincia)
                ->sole();

            foreach ($ciudades as $descripcionCiudad) {
                $provincia->ciudades()->updateOrCreate(
                    ['descripcion' => $descripcionCiudad],
                    ['activo' => true],
                );
            }
        }
    }
}
```
