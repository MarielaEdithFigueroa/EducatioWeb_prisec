# Propuesta de migraciones: alumnos, responsables y ubicación

> Borrador de diseño generado con Codex. No es una migración ejecutable ni la fuente de verdad del esquema.

## Orden propuesto de las migraciones

1. `create_provincias_table.php`
2. `create_ciudades_table.php`
3. `create_nacionalidades_table.php`
4. `create_grupos_sanguineos_table.php`
5. `create_motivos_baja_table.php`
6. `create_alumnos_table.php`
7. `create_responsables_table.php`
8. `create_alumnos_responsables_table.php`

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
            $table->char('codigo', 2);
            $table->string('nombre', 100);

            $table->unique('codigo', 'provincias_codigo_unique');
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
            $table->string('codigo', 10);
            $table->string('nombre', 120);
            $table->string('codigo_postal', 10)->nullable();

            $table->unique(
                ['provincia_id', 'codigo'],
                'ciudades_provincia_codigo_unique'
            );
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

## `create_nacionalidades_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nacionalidades', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->boolean('activo')->default(true);
            $table->char('codigo', 3);
            $table->string('nombre', 100);

            $table->unique('codigo', 'nacionalidades_codigo_unique');
            $table->unique('nombre', 'nacionalidades_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nacionalidades');
    }
};
```

## `create_grupos_sanguineos_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_sanguineos', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('codigo', 5);
            $table->string('nombre', 30);

            $table->unique('codigo', 'grupos_sanguineos_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_sanguineos');
    }
};
```

## `create_motivos_baja_table.php`

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
            $table->string('codigo', 20);
            $table->string('nombre', 100);

            $table->unique('codigo', 'motivos_baja_codigo_unique');
            $table->unique('nombre', 'motivos_baja_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivos_baja');
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
            $table->unsignedBigInteger('ciudad_nacimiento_id')->nullable();
            $table->unsignedSmallInteger('nacionalidad_id')->nullable();
            $table->unsignedTinyInteger('grupo_sanguineo_id')->nullable();

            // Sexo e identidad de género
            $table->char('sexo_registral', 1)->nullable();
            $table->string('genero', 30)->nullable();
            $table->string('genero_autodescripcion', 100)->nullable();

            // Contacto
            $table->string('email', 254)->nullable();

            // Domicilio
            $table->string('domicilio', 200)->nullable();
            $table->unsignedBigInteger('ciudad_id')->nullable();

            // Información académica general
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_inicio_cursado')->nullable();
            $table->string('libro', 30)->nullable();
            $table->string('folio', 30)->nullable();
            $table->date('fecha_baja')->nullable();
            $table->unsignedBigInteger('motivo_baja_id')->nullable();
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
            $table->index(
                'ciudad_nacimiento_id',
                'alumnos_ciudad_nacimiento_idx'
            );
            $table->index('nacionalidad_id', 'alumnos_nacionalidad_idx');
            $table->index(
                'grupo_sanguineo_id',
                'alumnos_grupo_sanguineo_idx'
            );
            $table->index('motivo_baja_id', 'alumnos_motivo_baja_idx');

            $table->foreign('ciudad_id', 'alumnos_ciudad_fk')
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign(
                'ciudad_nacimiento_id',
                'alumnos_ciudad_nacimiento_fk'
            )
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('nacionalidad_id', 'alumnos_nacionalidad_fk')
                ->references('id')
                ->on('nacionalidades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign(
                'grupo_sanguineo_id',
                'alumnos_grupo_sanguineo_fk'
            )
                ->references('id')
                ->on('grupos_sanguineos')
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

## Seeder propuesto de provincias y ciudades (no implementado)

Este bloque es una propuesta no operativa: no crea el archivo del seeder ni modifica `DatabaseSeeder`.

Fuente: catálogo oficial de localidades de [Georef Argentina](https://www.argentina.gob.ar/georef/descarga-de-la-base-completa), consultado el 12/08/2026. El recorte incluye:

- Las 24 jurisdicciones argentinas.
- Todas las localidades oficiales de la provincia del Neuquén.
- Las localidades de otras provincias cuyo centroide se encuentra hasta 200 km en línea recta del centroide de Neuquén capital (`-38.9518288206176`, `-68.0591813073308`).
- La Ciudad Autónoma de Buenos Aires.
- La capital de cada provincia.

Cuando Georef devuelve más de un registro con el mismo nombre dentro de una provincia, el borrador conserva una sola ciudad y prioriza el código oficial más corto. El catálogo incluye barrios y parajes cuando Georef los clasifica como localidades.

```php
<?php

namespace Database\Seeders;

use App\Models\Provincia;
use Illuminate\Database\Seeder;

class ProvinciasYCiudadesSeeder extends Seeder
{
    public function run(): void
    {
        $provincias = [
            '02' => 'Ciudad Autónoma de Buenos Aires',
            '06' => 'Buenos Aires',
            '10' => 'Catamarca',
            '14' => 'Córdoba',
            '18' => 'Corrientes',
            '22' => 'Chaco',
            '26' => 'Chubut',
            '30' => 'Entre Ríos',
            '34' => 'Formosa',
            '38' => 'Jujuy',
            '42' => 'La Pampa',
            '46' => 'La Rioja',
            '50' => 'Mendoza',
            '54' => 'Misiones',
            '58' => 'Neuquén',
            '62' => 'Río Negro',
            '66' => 'Salta',
            '70' => 'San Juan',
            '74' => 'San Luis',
            '78' => 'Santa Cruz',
            '82' => 'Santa Fe',
            '86' => 'Santiago del Estero',
            '90' => 'Tucumán',
            '94' => 'Tierra del Fuego, Antártida e Islas del Atlántico Sur',
        ];

        $ciudadesPorProvincia = [
            '02' => [
                '02000010' => 'Ciudad Autónoma de Buenos Aires',
            ],
            '06' => [
                '06441030' => 'La Plata',
            ],
            '10' => [
                '10049030' => 'San Fernando del Valle de Catamarca',
            ],
            '14' => [
                '14014010' => 'Córdoba',
            ],
            '18' => [
                '18021020' => 'Corrientes',
            ],
            '22' => [
                '22140060' => 'Resistencia',
            ],
            '26' => [
                '26077030' => 'Rawson',
            ],
            '30' => [
                '30084160' => 'Paraná',
            ],
            '34' => [
                '34014020' => 'Formosa',
            ],
            '38' => [
                '38021060' => 'San Salvador de Jujuy',
            ],
            '42' => [
                '42112020' => '25 de Mayo',
                '42112005' => 'Casa de Piedra',
                '42042010' => 'Gobernador Duval',
                '42112010' => 'Puelén',
                '42021020' => 'Santa Rosa',
            ],
            '46' => [
                '46014010' => 'La Rioja',
            ],
            '50' => [
                '50007010' => 'Mendoza',
            ],
            '54' => [
                '54028030' => 'Posadas',
            ],
            '58' => [
                '58014005' => 'Aguada San Roque',
                '58007010' => 'Aluminé',
                '58077010' => 'Andacollo',
                '58014010' => 'Añelo',
                '58035010' => 'Arroyito',
                '58105010' => 'Bajada del Agrio',
                '58091010' => 'Barrancas',
                '58091020' => 'Buta Ranquil',
                '58084010' => 'Caviahue',
                '58035030' => 'Centenario',
                '58063010' => 'Chorriaca',
                '58042010' => 'Chos Malal',
                '58084020' => 'Copahue',
                '5811202001' => 'Covunco Centro',
                '58035040' => 'Cutral Có',
                '58084030' => 'El Cholar',
                '58084040' => 'El Huecú',
                '58098005' => 'El Sauce',
                '58077020' => 'Huinganco',
                '58049010' => 'Junín de los Andes',
                '58105020' => 'La Buitrera',
                '58021010' => 'Las Coloradas',
                '58105030' => 'Las Lajas',
                '58077030' => 'Las Ovejas',
                '58063020' => 'Loncopué',
                '58112010' => 'Los Catutos',
                '58077040' => 'Los Miches',
                '58077050' => 'Manzano Amargo',
                '58035060' => 'Mari Menuco',
                '58112020' => 'Mariano Moreno',
                '58007015' => 'Moquehue',
                '58035070' => 'Neuquén',
                '58091030' => 'Octavio Pico',
                '58098010' => 'Paso Aguerre',
                '58098020' => 'Picún Leufú',
                '58028010' => 'Piedra del Águila',
                '58035090' => 'Plaza Huincul',
                '58035100' => 'Plottier',
                '58105040' => 'Quili Malal',
                '58112030' => 'Ramón M. Castro',
                '58091040' => 'Rincón de los Sauces',
                '58056010' => 'San Martín de los Andes',
                '58014020' => 'San Patricio del Chañar',
                '58028020' => 'Santo Tomás',
                '58035110' => 'Senillosa',
                '58084050' => 'Taquimilán',
                '58042020' => 'Tricao Malal',
                '58077060' => 'Varvarco',
                '58042030' => 'Villa del Curi Leuvú',
                '58077070' => 'Villa del Nahueve',
                '58035120' => 'Villa El Chocón',
                '58070010' => 'Villa La Angostura',
                '58056020' => 'Villa Lago Meliquina',
                '58007020' => 'Villa Pehuenia',
                '58070020' => 'Villa Traful',
                '58035130' => 'Vista Alegre Norte',
                '58035140' => 'Vista Alegre Sur',
                '58112040' => 'Zapala',
            ],
            '62' => [
                '62035010' => 'Aguada Guzmán',
                '62042010' => 'Allen',
                '62042030' => 'Barda del Medio',
                '62042040' => 'Barrio Blanco',
                '62042050' => 'Barrio Calle Ciega Nº 10',
                '62042060' => 'Barrio Calle Ciega Nº 6',
                '62042070' => 'Barrio Canale',
                '62042080' => 'Barrio Chacra Monte',
                '62042090' => 'Barrio Costa Este',
                '62042110' => 'Barrio Costa Oeste',
                '62042115' => 'Barrio Destacamento',
                '62042120' => 'Barrio El Labrador',
                '62042130' => 'Barrio El Maruchito',
                '62042140' => 'Barrio El Petróleo',
                '62042143' => 'Barrio Emergente',
                '62042147' => 'Barrio Fátima',
                '62042150' => 'Barrio Frontera',
                '62042160' => 'Barrio Guerrico',
                '62042170' => 'Barrio Isla 10',
                '62042180' => 'Barrio La Barda',
                '62042200' => 'Barrio La Costa',
                '62042210' => 'Barrio La Defensa',
                '62042215' => 'Barrio La Herradura',
                '6204240001' => 'Barrio La Lor',
                '62042245' => 'Barrio Luisillo',
                '62042250' => 'Barrio Mar del Plata',
                '62042260' => 'Barrio María Elvira',
                '62042265' => 'Barrio Moño Azul',
                '62042280' => 'Barrio Norte',
                '62042297' => 'Barrio Pinar',
                '6204245001' => 'Barrio Pino Azul',
                '62042310' => 'Barrio Porvenir',
                '6204239001' => 'Barrio Presidente Perón',
                '62042335' => 'Barrio Santa Lucia',
                '62042340' => 'Barrio Santa Rita',
                '62014010' => 'Barrio Unión',
                '62042360' => 'Catriel',
                '62035020' => 'Cerro Policía',
                '62042370' => 'Cervantes',
                '62014020' => 'Chelforó',
                '62042380' => 'Chichinales',
                '62014030' => 'Chimpay',
                '62042390' => 'Cinco Saltos',
                '62042400' => 'Cipolletti',
                '62042410' => 'Contralmirante Cordero',
                '62014050' => 'Coronel Belisle',
                '62035030' => 'El Cuy',
                '62042420' => 'Ferri',
                '62042430' => 'General Enrique Godoy',
                '62042440' => 'General Fernández Oro',
                '62042450' => 'General Roca',
                '62042460' => 'Ingeniero Luis A. Huergo',
                '62042470' => 'Ingeniero Otto Krause',
                '62035040' => 'Las Perlas',
                '62042480' => 'Mainqué',
                '62049030' => 'Ministro Ramos Mexía',
                '62035060' => 'Naupa Huen',
                '62042020' => 'Paraje Arroyón (Bajo San Cayetano)',
                '62035070' => 'Paso Córdova',
                '62042500' => 'Península Ruca Co',
                '62042240' => 'Puente Cero',
                '62042520' => 'Sargento Vidal',
                '62049050' => 'Sierra Colorada',
                '62035080' => 'Valle Azul',
                '62007090' => 'Viedma',
                '62042530' => 'Villa Alberdi',
                '62042540' => 'Villa del Parque',
                '62042550' => 'Villa Manzano',
                '62042560' => 'Villa Regina',
                '62042570' => 'Villa San Isidro',
            ],
            '66' => [
                '66028050' => 'Salta',
            ],
            '70' => [
                '70028010' => 'San Juan',
            ],
            '74' => [
                '74056150' => 'San Luis',
            ],
            '78' => [
                '78021040' => 'Río Gallegos',
            ],
            '82' => [
                '82063170' => 'Santa Fe',
            ],
            '86' => [
                '86049110' => 'Santiago del Estero',
            ],
            '90' => [
                '90084010' => 'San Miguel de Tucumán',
            ],
            '94' => [
                '94015020' => 'Ushuaia',
            ],
        ];

        foreach ($provincias as $codigo => $nombre) {
            Provincia::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'activo' => true,
                ],
            );
        }

        foreach ($ciudadesPorProvincia as $codigoProvincia => $ciudades) {
            $provincia = Provincia::query()
                ->where('codigo', $codigoProvincia)
                ->sole();

            foreach ($ciudades as $codigo => $nombre) {
                $provincia->ciudades()->updateOrCreate(
                    ['codigo' => $codigo],
                    [
                        'nombre' => $nombre,
                        'activo' => true,
                    ],
                );
            }
        }
    }
}
```

La integración futura en `DatabaseSeeder` sería:

```php
$this->call([
    ProvinciasYCiudadesSeeder::class,
    NivelesSeeder::class,
    CursosSeeder::class,
    DivisionesSeeder::class,
    TurnosSeeder::class,
    PlanesEstudioSeeder::class,
]);
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
- Se usa el código postal numérico de `ciudades`; no se almacena CPA ni se
  duplica el código postal en alumnos o responsables.
- Confirmar contra el legacy la unicidad de legajo, documento y CUIT/CUIL antes de importar datos.
- `genero_autodescripcion` se completa cuando `genero = otro`.
- `vinculo_descripcion` se completa cuando `vinculo = otro`.
- `autoriza_uso_imagen = null` significa que el dato todavía no fue relevado.
- Una relación alumno-responsable desactivada se reactiva en lugar de crear otra fila.
- Documento, CUIT/CUIL y teléfonos de responsables deben normalizarse mediante
  validaciones del servidor.
- No se almacena el teléfono privado del alumno. La consulta de teléfonos de
  contacto se resolverá con una vista basada en sus responsables.
- No se incluyen `timestamps`, `SoftDeletes`, enums ni eliminaciones en cascada.
