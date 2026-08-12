# Propuesta de migraciones: alumnos, responsables y ubicación

> Borrador de diseño generado con Codex. No es una migración ejecutable ni la fuente de verdad del esquema.

## Orden propuesto de las migraciones

1. `create_provincias_table.php`
2. `create_ciudades_table.php`
3. `create_alumnos_table.php`
4. `create_responsables_table.php`
5. `create_alumnos_responsables_table.php`

## `create_provincias_table.php`

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
            $table->tinyIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('nombre', 100);

            $table->unique('nombre', 'provincias_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provincias');
    }
};
```

## `create_ciudades_table.php`

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
            $table->unsignedTinyInteger('provincia_id');
            $table->string('nombre', 120);

            $table->unique(
                ['provincia_id', 'nombre'],
                'ciudades_provincia_nombre_unique'
            );
            $table->index(
                ['provincia_id', 'activo', 'nombre'],
                'ciudades_prov_activo_nombre_idx'
            );

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

## `create_alumnos_table.php`

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

            // Identificación
            $table->string('legajo', 30);
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->string('nombre_elegido', 100)->nullable();
            $table->string('tipo_documento', 20)->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->char('cuit_cuil', 11)->nullable();
            $table->date('fecha_nacimiento')->nullable();

            // Sexo e identidad de género
            $table->char('sexo_registral', 1)->nullable();
            $table->string('genero', 30)->nullable();
            $table->string('genero_autodescripcion', 100)->nullable();

            // Contacto
            $table->string('email', 254)->nullable();
            $table->string('telefono_e164', 20)->nullable();
            $table->string('telefono_original', 50)->nullable();

            // Domicilio
            $table->string('domicilio', 200)->nullable();
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->string('codigo_postal', 10)->nullable();

            // Información académica general
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_inicio_cursado')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja', 255)->nullable();
            $table->boolean('autoriza_uso_imagen')->nullable();
            $table->text('observaciones')->nullable();

            $table->unique('legajo', 'alumnos_legajo_unique');
            $table->unique(
                ['tipo_documento', 'numero_documento'],
                'alumnos_documento_unique'
            );
            $table->unique('cuit_cuil', 'alumnos_cuit_cuil_unique');
            $table->index(
                ['activo', 'apellido', 'nombre'],
                'alumnos_activo_nombre_idx'
            );
            $table->index('ciudad_id', 'alumnos_ciudad_idx');

            $table->foreign('ciudad_id', 'alumnos_ciudad_fk')
                ->references('id')
                ->on('ciudades')
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

## `create_responsables_table.php`

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

            // Identificación
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->string('nombre_elegido', 100)->nullable();
            $table->string('tipo_documento', 20)->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->char('cuit_cuil', 11)->nullable();
            $table->date('fecha_nacimiento')->nullable();

            // Sexo e identidad de género
            $table->char('sexo_registral', 1)->nullable();
            $table->string('genero', 30)->nullable();
            $table->string('genero_autodescripcion', 100)->nullable();

            // Contacto personal
            $table->string('email', 254)->nullable();
            $table->string('telefono_e164', 20)->nullable();
            $table->string('telefono_original', 50)->nullable();

            // Domicilio particular
            $table->string('domicilio', 200)->nullable();
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->string('codigo_postal', 10)->nullable();

            // Información laboral
            $table->string('profesion', 100)->nullable();
            $table->string('domicilio_laboral', 200)->nullable();
            $table->unsignedBigInteger('ciudad_laboral_id')->nullable();
            $table->string('telefono_laboral_e164', 20)->nullable();
            $table->string('telefono_laboral_original', 50)->nullable();

            $table->text('observaciones')->nullable();

            $table->unique(
                ['tipo_documento', 'numero_documento'],
                'responsables_documento_unique'
            );
            $table->unique('cuit_cuil', 'responsables_cuit_cuil_unique');
            $table->index(
                ['activo', 'apellido', 'nombre'],
                'responsables_activo_nombre_idx'
            );
            $table->index('ciudad_id', 'responsables_ciudad_idx');
            $table->index(
                'ciudad_laboral_id',
                'responsables_ciudad_lab_idx'
            );

            $table->foreign('ciudad_id', 'responsables_ciudad_fk')
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign(
                'ciudad_laboral_id',
                'responsables_ciudad_lab_fk'
            )
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

## `create_alumnos_responsables_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos_responsables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);

            // Relación
            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('responsable_id');

            // Vínculo
            $table->string('vinculo', 30)->nullable();
            $table->string('vinculo_descripcion', 100)->nullable();
            $table->unsignedTinyInteger('orden_contacto')->default(0);

            // Responsabilidades
            $table->boolean('es_responsable_legal')->default(false);
            $table->boolean('es_responsable_pedagogico')->default(false);
            $table->boolean('es_responsable_economico')->default(false);

            // Comunicaciones
            $table->boolean('recibe_comunicaciones')->default(false);
            $table->boolean('recibe_informes_academicos')->default(false);
            $table->boolean('recibe_facturacion')->default(false);

            // Contacto y convivencia
            $table->boolean('es_contacto_principal')->default(false);
            $table->boolean('es_contacto_emergencia')->default(false);
            $table->boolean('convive_con_alumno')->default(false);
            $table->boolean('autorizado_retirar')->default(false);

            // Acceso digital
            $table->boolean('habilitado_portal')->default(false);

            $table->text('observaciones')->nullable();

            $table->unique(
                ['alumno_id', 'responsable_id'],
                'alum_resp_alumno_resp_unique'
            );
            $table->index(
                ['alumno_id', 'activo', 'orden_contacto'],
                'alum_resp_alum_act_ord_idx'
            );
            $table->index(
                ['responsable_id', 'activo'],
                'alum_resp_resp_activo_idx'
            );

            $table->foreign('alumno_id', 'alum_resp_alumno_fk')
                ->references('id')
                ->on('alumnos')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('responsable_id', 'alum_resp_responsable_fk')
                ->references('id')
                ->on('responsables')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos_responsables');
    }
};
```

## Valores controlados propuestos

```text
sexo_registral: F | M | X

genero:
- mujer
- varon
- no_binario
- otro
- prefiere_no_informar

vinculo:
- madre
- padre
- tutor
- hermano
- abuelo
- familiar
- referente_afectivo
- otro
```

## Consideraciones pendientes

- `alumnos` y `responsables` referencian solamente `ciudad_id`; la provincia se obtiene mediante la ciudad para evitar datos contradictorios.
- `responsables.ciudad_laboral_id` representa la ciudad del domicilio laboral.
- Las ciudades y provincias inactivas deben conservarse para mostrar datos históricos.
- El código postal permanece en el domicilio porque una ciudad puede tener más de un código postal.
- Confirmar contra el legacy la unicidad de legajo, documento y CUIT/CUIL antes de importar datos.
- `genero_autodescripcion` se completa cuando `genero = otro`.
- `vinculo_descripcion` se completa cuando `vinculo = otro`.
- `autoriza_uso_imagen = null` significa que el dato todavía no fue relevado.
- Una relación alumno-responsable desactivada se reactiva en lugar de crear otra fila.
- Documento, CUIT/CUIL y teléfonos deben normalizarse mediante validaciones del servidor.
- No se incluyen `timestamps`, `SoftDeletes`, enums ni eliminaciones en cascada.
