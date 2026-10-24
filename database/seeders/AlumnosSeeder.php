<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\AlumnoResponsable;
use App\Models\GrupoSanguineo;
use App\Models\MotivoBaja;
use App\Models\Nacionalidad;
use App\Models\Provincia;
use App\Models\Responsable;
use App\Models\TipoDocumento;
use App\Models\TipoVinculo;
use Illuminate\Database\Seeder;

class AlumnosSeeder extends Seeder
{
    public function run(): void
    {
        $dni = TipoDocumento::query()
            ->where('nombre', 'DNI')
            ->sole();
        $motivoCambioEstablecimiento = MotivoBaja::query()
            ->where('nombre', 'Cambio de establecimiento')
            ->sole();
        $nacionalidades = Nacionalidad::query()
            ->whereIn('codigo', ['ARG', 'BOL', 'CHL', 'COL'])
            ->get()
            ->keyBy('codigo');
        $gruposSanguineos = GrupoSanguineo::query()
            ->get()
            ->keyBy('codigo');
        $ciudades = [
            'allen' => $this->ciudadId('RN', 'Allen'),
            'centenario' => $this->ciudadId('NQN', 'Centenario'),
            'cipolletti' => $this->ciudadId('RN', 'Cipolletti'),
            'exterior' => $this->ciudadId('EXT', 'Otra localidad del exterior'),
            'general_roca' => $this->ciudadId('RN', 'General Roca'),
            'neuquen' => $this->ciudadId('NQN', 'Neuquén'),
            'plottier' => $this->ciudadId('NQN', 'Plottier'),
            'senillosa' => $this->ciudadId('NQN', 'Senillosa'),
            'zapala' => $this->ciudadId('NQN', 'Zapala'),
        ];

        /** @var array<int, array<string, string|null>> $alumnos */
        $alumnos = [
            [
                'legajo' => 'PRI-2026-0001', 'apellido' => 'Acuña', 'nombre' => 'Sofía',
                'nombre_elegido' => null, 'documento' => '50000001', 'cuilt' => '27500000010',
                'nacimiento' => '2016-03-14', 'ciudad_nacimiento' => 'neuquen', 'nacionalidad' => 'ARG',
                'grupo' => 'O+', 'sexo' => 'F', 'genero' => 'Mujer', 'genero_autodescripcion' => null,
                'ciudad' => 'neuquen', 'cpa' => 'Q8300', 'domicilio' => 'Belgrano 245',
            ],
            [
                'legajo' => 'PRI-2026-0002', 'apellido' => 'Benítez', 'nombre' => 'Mateo',
                'nombre_elegido' => null, 'documento' => '50000002', 'cuilt' => '20500000024',
                'nacimiento' => '2015-07-22', 'ciudad_nacimiento' => 'plottier', 'nacionalidad' => 'ARG',
                'grupo' => 'A+', 'sexo' => 'M', 'genero' => 'Varón', 'genero_autodescripcion' => null,
                'ciudad' => 'plottier', 'cpa' => 'Q8316', 'domicilio' => 'Los Álamos 810',
            ],
            [
                'legajo' => 'PRI-2026-0003', 'apellido' => 'Cárdenas', 'nombre' => 'Valentina',
                'nombre_elegido' => null, 'documento' => '50000003', 'cuilt' => '27500000037',
                'nacimiento' => '2014-11-05', 'ciudad_nacimiento' => 'centenario', 'nacionalidad' => 'ARG',
                'grupo' => 'B+', 'sexo' => 'F', 'genero' => 'Mujer', 'genero_autodescripcion' => null,
                'ciudad' => 'centenario', 'cpa' => 'Q8309', 'domicilio' => 'Canadá 117',
            ],
            [
                'legajo' => 'PRI-2026-0004', 'apellido' => 'Díaz', 'nombre' => 'Bautista',
                'nombre_elegido' => null, 'documento' => '50000004', 'cuilt' => '20500000040',
                'nacimiento' => '2013-01-30', 'ciudad_nacimiento' => 'cipolletti', 'nacionalidad' => 'ARG',
                'grupo' => 'O-', 'sexo' => 'M', 'genero' => 'Varón', 'genero_autodescripcion' => null,
                'ciudad' => 'cipolletti', 'cpa' => 'R8324', 'domicilio' => 'Río Negro 935',
            ],
            [
                'legajo' => 'SEC-2026-0005', 'apellido' => 'Fernández', 'nombre' => 'Alejandra',
                'nombre_elegido' => 'Alex', 'documento' => '50000005', 'cuilt' => '24500000054',
                'nacimiento' => '2012-09-18', 'ciudad_nacimiento' => 'general_roca', 'nacionalidad' => 'ARG',
                'grupo' => 'AB+', 'sexo' => 'X', 'genero' => 'No binario', 'genero_autodescripcion' => null,
                'ciudad' => 'neuquen', 'cpa' => 'Q8300', 'domicilio' => 'Mitre 1520',
            ],
            [
                'legajo' => 'SEC-2026-0006', 'apellido' => 'González', 'nombre' => 'Martina',
                'nombre_elegido' => null, 'documento' => '50000006', 'cuilt' => '27500000061',
                'nacimiento' => '2011-04-09', 'ciudad_nacimiento' => 'neuquen', 'nacionalidad' => 'ARG',
                'grupo' => 'A-', 'sexo' => 'F', 'genero' => 'Prefiere no informar', 'genero_autodescripcion' => null,
                'ciudad' => 'neuquen', 'cpa' => 'Q8300', 'domicilio' => 'Lago Traful 443',
            ],
            [
                'legajo' => 'PRI-2026-0007', 'apellido' => 'Herrera', 'nombre' => 'Thiago',
                'nombre_elegido' => null, 'documento' => '50000007', 'cuilt' => '20500000075',
                'nacimiento' => '2017-08-12', 'ciudad_nacimiento' => 'exterior', 'nacionalidad' => 'CHL',
                'grupo' => 'B-', 'sexo' => 'M', 'genero' => 'Varón', 'genero_autodescripcion' => null,
                'ciudad' => 'neuquen', 'cpa' => 'Q8300', 'domicilio' => 'Las Glicinas 88',
            ],
            [
                'legajo' => 'PRI-2026-0008', 'apellido' => 'Ibarra', 'nombre' => 'Emilia',
                'nombre_elegido' => null, 'documento' => '50000008', 'cuilt' => '27500000088',
                'nacimiento' => '2018-02-27', 'ciudad_nacimiento' => 'exterior', 'nacionalidad' => 'BOL',
                'grupo' => 'O+', 'sexo' => 'F', 'genero' => 'Mujer', 'genero_autodescripcion' => null,
                'ciudad' => 'neuquen', 'cpa' => 'Q8300', 'domicilio' => 'Chocón 1284',
            ],
            [
                'legajo' => 'SEC-2026-0009', 'apellido' => 'Juárez', 'nombre' => 'Joaquín',
                'nombre_elegido' => null, 'documento' => '50000009', 'cuilt' => '20500000091',
                'nacimiento' => '2010-12-03', 'ciudad_nacimiento' => 'exterior', 'nacionalidad' => 'COL',
                'grupo' => 'A+', 'sexo' => 'M', 'genero' => 'Varón', 'genero_autodescripcion' => null,
                'ciudad' => 'plottier', 'cpa' => 'Q8316', 'domicilio' => 'San Martín 341',
            ],
            [
                'legajo' => 'SEC-2026-0010', 'apellido' => 'López', 'nombre' => 'Camila',
                'nombre_elegido' => null, 'documento' => '50000010', 'cuilt' => '23500000104',
                'nacimiento' => '2009-06-16', 'ciudad_nacimiento' => 'senillosa', 'nacionalidad' => 'ARG',
                'grupo' => 'AB-', 'sexo' => 'F', 'genero' => 'Mujer', 'genero_autodescripcion' => null,
                'ciudad' => 'senillosa', 'cpa' => 'Q8316', 'domicilio' => 'Primeros Pobladores 612',
            ],
            [
                'legajo' => 'SEC-2026-0011', 'apellido' => 'Martínez', 'nombre' => 'Felipe',
                'nombre_elegido' => null, 'documento' => '50000011', 'cuilt' => '20500000113',
                'nacimiento' => '2011-10-25', 'ciudad_nacimiento' => 'zapala', 'nacionalidad' => 'ARG',
                'grupo' => 'O+', 'sexo' => 'M', 'genero' => 'Varón', 'genero_autodescripcion' => null,
                'ciudad' => 'neuquen', 'cpa' => 'Q8300', 'domicilio' => 'Olascoaga 2040',
            ],
            [
                'legajo' => 'SEC-2026-0012', 'apellido' => 'Molina', 'nombre' => 'Samantha',
                'nombre_elegido' => 'Sam', 'documento' => '50000012', 'cuilt' => '24500000127',
                'nacimiento' => '2010-05-07', 'ciudad_nacimiento' => 'neuquen', 'nacionalidad' => 'ARG',
                'grupo' => 'B+', 'sexo' => 'X', 'genero' => 'Otra identidad', 'genero_autodescripcion' => 'Género fluido',
                'ciudad' => 'neuquen', 'cpa' => 'Q8300', 'domicilio' => 'Antártida Argentina 780',
            ],
        ];

        foreach ($alumnos as $indice => $alumno) {
            $inactivo = $alumno['legajo'] === 'SEC-2026-0010';
            $nombreParaEmail = $alumno['nombre_elegido'] ?? $alumno['nombre'];

            Alumno::query()->updateOrCreate(
                ['legajo' => $alumno['legajo']],
                [
                    'activo' => ! $inactivo,
                    'apellido' => $alumno['apellido'],
                    'nombre' => $alumno['nombre'],
                    'nombre_elegido' => $alumno['nombre_elegido'],
                    'tipo_documento_id' => $dni->id,
                    'numero_documento' => $alumno['documento'],
                    'cuilt' => $alumno['cuilt'],
                    'fecha_nacimiento' => $alumno['nacimiento'],
                    'ciudad_nacimiento_id' => $ciudades[$alumno['ciudad_nacimiento']],
                    'nacionalidad_id' => $nacionalidades[$alumno['nacionalidad']]->id,
                    'grupo_sanguineo_id' => $gruposSanguineos[$alumno['grupo']]->id,
                    'sexo_registral' => $alumno['sexo'],
                    'genero' => $alumno['genero'],
                    'genero_autodescripcion' => $alumno['genero_autodescripcion'],
                    'email' => ($indice + 1) % 3 === 0
                        ? str($nombreParaEmail.'.'.$alumno['apellido'])
                            ->ascii()
                            ->lower()
                            ->replace(' ', '.')
                            ->append('@example.test')
                            ->toString()
                        : null,
                    'domicilio' => $alumno['domicilio'],
                    'ciudad_id' => $ciudades[$alumno['ciudad']],
                    'cpa' => $alumno['cpa'],
                    'fecha_ingreso' => '2024-02-26',
                    'fecha_inicio_cursado' => '2026-02-23',
                    'libro' => str_starts_with($alumno['legajo'], 'PRI') ? '1' : '2',
                    'folio' => str_pad((string) ($indice + 1), 3, '0', STR_PAD_LEFT),
                    'fecha_baja' => $inactivo ? '2026-07-10' : null,
                    'motivo_baja_id' => $inactivo ? $motivoCambioEstablecimiento->id : null,
                    'autoriza_uso_imagen' => match ($indice % 3) {
                        0 => true,
                        1 => false,
                        default => null,
                    },
                    'observaciones' => match ($alumno['legajo']) {
                        'SEC-2026-0005', 'SEC-2026-0012' => 'Usar el nombre elegido en toda comunicación institucional.',
                        'SEC-2026-0010' => 'Pase solicitado por la familia.',
                        default => null,
                    },
                ],
            );
        }

        $this->sembrarResponsables($dni, $ciudades);
    }

    /** @param array<string, int> $ciudades */
    private function sembrarResponsables(TipoDocumento $dni, array $ciudades): void
    {
        $tiposVinculo = TipoVinculo::query()
            ->get()
            ->keyBy('nombre');

        /** @var array<int, array<string, mixed>> $responsables */
        $responsables = [
            [
                'apellido' => 'Acuña', 'nombre' => 'Mariana', 'documento' => '30000001',
                'cuilt' => '27300000016', 'nacimiento' => '1985-06-18', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'mariana.acuna@example.test', 'telefono' => '+5492994100001',
                'telefono_original' => '299 410-0001', 'domicilio' => 'Belgrano 245',
                'ciudad' => 'neuquen', 'profesion' => 'Contadora pública',
                'vinculos' => [
                    ['legajo' => 'PRI-2026-0001', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Rojas', 'nombre' => 'Sebastián', 'documento' => '30000004',
                'cuilt' => '20300000046', 'nacimiento' => '1983-09-02', 'sexo' => 'M', 'genero' => 'Varón',
                'email' => 'sebastian.rojas@example.test', 'telefono' => '+5492994100002',
                'telefono_original' => '299 410-0002', 'domicilio' => 'Rivadavia 610',
                'ciudad' => 'neuquen', 'profesion' => 'Técnico electromecánico',
                'vinculos' => [
                    ['legajo' => 'PRI-2026-0001', 'tipo' => 'Padre', 'orden' => 2, 'principal' => false, 'convive' => false, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Benítez', 'nombre' => 'Laura', 'documento' => '30000003',
                'cuilt' => '27300000032', 'nacimiento' => '1987-02-11', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'laura.benitez@example.test', 'telefono' => '+5492994100003',
                'telefono_original' => '299 410-0003', 'domicilio' => 'Los Álamos 810',
                'ciudad' => 'plottier', 'profesion' => 'Enfermera',
                'vinculos' => [
                    ['legajo' => 'PRI-2026-0002', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Cárdenas', 'nombre' => 'Rodrigo', 'documento' => '30000005',
                'cuilt' => '20300000054', 'nacimiento' => '1982-11-29', 'sexo' => 'M', 'genero' => 'Varón',
                'email' => 'rodrigo.cardenas@example.test', 'telefono' => '+5492994100004',
                'telefono_original' => '299 410-0004', 'domicilio' => 'Canadá 117',
                'ciudad' => 'centenario', 'profesion' => 'Comerciante',
                'vinculos' => [
                    ['legajo' => 'PRI-2026-0003', 'tipo' => 'Padre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Díaz', 'nombre' => 'Laura', 'documento' => '30000006',
                'cuilt' => '27300000067', 'nacimiento' => '1984-04-07', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'laura.diaz@example.test', 'telefono' => '+5492994100005',
                'telefono_original' => '299 410-0005', 'domicilio' => 'Río Negro 935',
                'ciudad' => 'cipolletti', 'profesion' => 'Docente',
                'vinculos' => [
                    ['legajo' => 'PRI-2026-0004', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Fernández', 'nombre' => 'Paula', 'documento' => '30000007',
                'cuilt' => '27300000075', 'nacimiento' => '1981-12-20', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'paula.fernandez@example.test', 'telefono' => '+5492994100006',
                'telefono_original' => '299 410-0006', 'domicilio' => 'Mitre 1520',
                'ciudad' => 'neuquen', 'profesion' => 'Arquitecta',
                'vinculos' => [
                    ['legajo' => 'SEC-2026-0005', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'González', 'nombre' => 'Hernán', 'documento' => '30000008',
                'cuilt' => '20300000089', 'nacimiento' => '1979-07-15', 'sexo' => 'M', 'genero' => 'Varón',
                'email' => 'hernan.gonzalez@example.test', 'telefono' => '+5492994100007',
                'telefono_original' => '299 410-0007', 'domicilio' => 'Lago Traful 443',
                'ciudad' => 'neuquen', 'profesion' => 'Geólogo',
                'vinculos' => [
                    ['legajo' => 'SEC-2026-0006', 'tipo' => 'Padre', 'orden' => 1, 'economico' => false],
                ],
            ],
            [
                'apellido' => 'Herrera', 'nombre' => 'Camila', 'documento' => '30000009',
                'cuilt' => '27300000091', 'nacimiento' => '1988-01-26', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'camila.herrera@example.test', 'telefono' => '+5492994100008',
                'telefono_original' => '299 410-0008', 'domicilio' => 'Las Glicinas 88',
                'ciudad' => 'neuquen', 'profesion' => 'Administrativa',
                'vinculos' => [
                    ['legajo' => 'PRI-2026-0007', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Ibarra', 'nombre' => 'Rosa', 'documento' => '30000010',
                'cuilt' => '27300000105', 'nacimiento' => '1986-05-09', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'rosa.ibarra@example.test', 'telefono' => '+5492994100009',
                'telefono_original' => '299 410-0009', 'domicilio' => 'Chocón 1284',
                'ciudad' => 'neuquen', 'profesion' => 'Pediatra',
                'vinculos' => [
                    ['legajo' => 'PRI-2026-0008', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Juárez', 'nombre' => 'Diana', 'documento' => '30000011',
                'cuilt' => '27300000113', 'nacimiento' => '1980-10-03', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'diana.juarez@example.test', 'telefono' => '+5492994100010',
                'telefono_original' => '299 410-0010', 'domicilio' => 'San Martín 341',
                'ciudad' => 'plottier', 'profesion' => 'Abogada',
                'vinculos' => [
                    ['legajo' => 'SEC-2026-0009', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'López', 'nombre' => 'Gabriela', 'documento' => '30000012',
                'cuilt' => '27300000121', 'nacimiento' => '1982-08-24', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'gabriela.lopez@example.test', 'telefono' => '+5492994100011',
                'telefono_original' => '299 410-0011', 'domicilio' => 'Primeros Pobladores 612',
                'ciudad' => 'senillosa', 'profesion' => 'Farmacéutica',
                'vinculos' => [
                    ['legajo' => 'SEC-2026-0010', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                    ['legajo' => 'PRI-2026-0004', 'tipo' => 'Nueva Mamá', 'orden' => 2, 'principal' => false, 'convive' => false, 'economico' => false],
                ],
            ],
            [
                'apellido' => 'Martínez', 'nombre' => 'Andrea', 'documento' => '30000014',
                'cuilt' => '27300000148', 'nacimiento' => '1983-03-31', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'andrea.martinez@example.test', 'telefono' => '+5492994100012',
                'telefono_original' => '299 410-0012', 'domicilio' => 'Olascoaga 2040',
                'ciudad' => 'neuquen', 'profesion' => 'Kinesióloga',
                'vinculos' => [
                    ['legajo' => 'SEC-2026-0011', 'tipo' => 'Madre', 'orden' => 1, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Molina', 'nombre' => 'Patricia', 'documento' => '30000015',
                'cuilt' => '27300000156', 'nacimiento' => '1981-01-17', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'patricia.molina@example.test', 'telefono' => '+5492994100013',
                'telefono_original' => '299 410-0013', 'domicilio' => 'Antártida Argentina 780',
                'ciudad' => 'neuquen', 'profesion' => 'Psicóloga',
                'vinculos' => [
                    ['legajo' => 'SEC-2026-0012', 'tipo' => 'Madre', 'orden' => 1, 'economico' => false],
                ],
            ],
            [
                'apellido' => 'Molina', 'nombre' => 'Alejandro', 'documento' => '30000018',
                'cuilt' => '20300000186', 'nacimiento' => '1979-05-22', 'sexo' => 'M', 'genero' => 'Varón',
                'email' => 'alejandro.molina@example.test', 'telefono' => '+5492994100014',
                'telefono_original' => '299 410-0014', 'domicilio' => 'Antártida Argentina 780',
                'ciudad' => 'neuquen', 'profesion' => 'Maestro mayor de obras',
                'vinculos' => [
                    ['legajo' => 'SEC-2026-0012', 'tipo' => 'Padre', 'orden' => 2, 'principal' => false, 'economico' => true],
                ],
            ],
            [
                'apellido' => 'Quiroga', 'nombre' => 'Marta', 'documento' => '30000019',
                'cuilt' => '27300000199', 'nacimiento' => '1966-09-12', 'sexo' => 'F', 'genero' => 'Mujer',
                'email' => 'marta.quiroga@example.test', 'telefono' => '+5492994100015',
                'telefono_original' => '299 410-0015', 'domicilio' => 'Los Maitenes 91',
                'ciudad' => 'senillosa', 'profesion' => 'Jubilada',
                'vinculos' => [
                    ['legajo' => 'SEC-2026-0010', 'tipo' => 'Tutor/a', 'orden' => 2, 'activo' => false, 'principal' => false, 'convive' => false, 'economico' => false, 'hasta' => '2025-12-19'],
                ],
            ],
        ];

        foreach ($responsables as $datos) {
            $responsable = Responsable::query()->updateOrCreate(
                [
                    'tipo_documento_id' => $dni->id,
                    'numero_documento' => $datos['documento'],
                ],
                [
                    'activo' => true,
                    'apellido' => $datos['apellido'],
                    'nombre' => $datos['nombre'],
                    'cuilt' => $datos['cuilt'],
                    'fecha_nacimiento' => $datos['nacimiento'],
                    'sexo_registral' => $datos['sexo'],
                    'genero' => $datos['genero'],
                    'email' => $datos['email'],
                    'telefono_e164' => $datos['telefono'],
                    'telefono_original' => $datos['telefono_original'],
                    'domicilio' => $datos['domicilio'],
                    'ciudad_id' => $ciudades[$datos['ciudad']],
                    'cpa' => match ($datos['ciudad']) {
                        'centenario' => 'Q8309',
                        'plottier', 'senillosa' => 'Q8316',
                        'cipolletti' => 'R8324',
                        default => 'Q8300',
                    },
                    'profesion' => $datos['profesion'],
                ],
            );

            foreach ($datos['vinculos'] as $datosVinculo) {
                $alumno = Alumno::query()
                    ->where('legajo', $datosVinculo['legajo'])
                    ->sole();
                $tipoVinculo = $tiposVinculo[$datosVinculo['tipo']];

                AlumnoResponsable::query()->updateOrCreate(
                    [
                        'alumno_id' => $alumno->id,
                        'responsable_id' => $responsable->id,
                    ],
                    [
                        'activo' => $datosVinculo['activo'] ?? true,
                        'vinculo_id' => $tipoVinculo->id,
                        'orden_contacto' => $datosVinculo['orden'],
                        'fecha_desde' => '2024-02-26',
                        'fecha_hasta' => $datosVinculo['hasta'] ?? null,
                        'es_responsable_legal' => true,
                        'es_responsable_pedagogico' => true,
                        'es_responsable_economico' => $datosVinculo['economico'],
                        'recibe_comunicaciones' => true,
                        'recibe_informes_academicos' => true,
                        'recibe_facturacion' => $datosVinculo['economico'],
                        'es_contacto_principal' => $datosVinculo['principal'] ?? true,
                        'es_contacto_emergencia' => true,
                        'convive_con_alumno' => $datosVinculo['convive'] ?? true,
                        'autorizado_retirar' => true,
                        'habilitado_portal' => ($datosVinculo['activo'] ?? true),
                        'observaciones' => ($datosVinculo['activo'] ?? true)
                            ? null
                            : 'Vínculo histórico conservado para consulta.',
                    ],
                );
            }
        }
    }

    private function ciudadId(string $codigoProvincia, string $nombre): int
    {
        $provincia = Provincia::query()
            ->where('codigo', $codigoProvincia)
            ->sole();

        return (int) $provincia->ciudades()
            ->where('nombre', $nombre)
            ->sole()
            ->id;
    }
}
