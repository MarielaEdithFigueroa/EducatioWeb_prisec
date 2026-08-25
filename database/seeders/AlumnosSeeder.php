<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\GrupoSanguineo;
use App\Models\MotivoBaja;
use App\Models\Nacionalidad;
use App\Models\Provincia;
use App\Models\TipoDocumento;
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
