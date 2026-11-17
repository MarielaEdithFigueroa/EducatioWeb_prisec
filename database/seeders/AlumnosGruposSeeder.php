<?php

namespace Database\Seeders;

use App\Actions\Academico\GestionarGrupoAlumno;
use App\Models\Alumno;
use App\Models\Ciudad;
use App\Models\Grupo;
use App\Models\Nacionalidad;
use App\Models\TipoDocumento;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class AlumnosGruposSeeder extends Seeder
{
    use RegistraAuditoria;

    /** @var array<string, array<int, string>> */
    private const EJEMPLOS_EXISTENTES = [
        '5.º grado|A' => [1 => 'PRI-2026-0001', 2 => 'PRI-2026-0002'],
        '6.º grado|A' => [1 => 'PRI-2026-0003'],
        '7.º grado|B' => [1 => 'PRI-2026-0004'],
        '3.º grado|A' => [1 => 'PRI-2026-0007', 2 => 'PRI-2026-0008'],
    ];

    public function run(GestionarGrupoAlumno $gestionar): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $dniId = TipoDocumento::query()->where('nombre', 'DNI')->sole()->id;
        $nacionalidadId = Nacionalidad::query()->where('codigo', 'ARG')->sole()->id;
        $localidades = Ciudad::query()->whereIn('nombre', ['Neuquén', 'Plottier', 'Cipolletti', 'Allen'])
            ->whereHas('provincia', fn (Builder $query): Builder => $query->whereIn('codigo', ['NQN', 'RN']))
            ->orderBy('nombre')->get()->map(fn (Ciudad $ciudad): array => [
                'id' => $ciudad->id,
                'cpa' => match ($ciudad->nombre) {
                    'Cipolletti' => 'R8324',
                    'Allen' => 'R8328',
                    'Plottier' => 'Q8316',
                    default => 'Q8300',
                },
            ])->all();
        if (count($localidades) !== 4) {
            throw new LogicException('Cargá los catálogos de provincias y localidades antes del plantel de ejemplo.');
        }
        $grupos = Grupo::query()->where('activo', true)->where('ciclo_lectivo', now()->year)
            ->whereHas('nivel', fn (Builder $query): Builder => $query->where('codigo', 'PRIMARIA'))
            ->with(['curso', 'division', 'turno'])->orderBy('id')->get();

        DB::transaction(function () use ($gestionar, $grupos, $dniId, $nacionalidadId, $localidades): void {
            foreach ($grupos as $grupo) {
                $grado = array_search($grupo->curso->descripcion, array_map(fn (int $numero): string => $numero.'.º grado', range(1, 7)), true);
                $division = $grupo->division->descripcion;
                if ($grado === false || ! in_array($division, ['A', 'B'], true)
                    || $grupo->turno->descripcion !== ($division === 'A' ? 'Mañana' : 'Tarde')) {
                    continue;
                }
                $grado++;
                $cantidad = 12 + (($grado - 1 + ($division === 'B' ? 3 : 0)) % 7);
                $ejemplos = self::EJEMPLOS_EXISTENTES[$grupo->curso->descripcion.'|'.$division] ?? [];

                for ($posicion = 1; $posicion <= $cantidad; $posicion++) {
                    $alumno = isset($ejemplos[$posicion])
                        ? Alumno::query()->where('legajo', $ejemplos[$posicion])->first()
                        : $this->crearAlumno($grupo, $grado, $division, $posicion, $dniId, $nacionalidadId, $localidades);

                    if ($alumno === null || ! $alumno->activo || $alumno->grupo_id !== null || $alumno->historialGrupos()->exists()) {
                        continue;
                    }

                    $gestionar->cambiar($alumno, $grupo->id, '0', 'Asignación inicial de datos de ejemplo');
                }
            }
        }, attempts: 3);
    }

    /** @param array<int, array{id: int, cpa: string}> $localidades */
    private function crearAlumno(Grupo $grupo, int $grado, string $division, int $posicion, int $dniId, int $nacionalidadId, array $localidades): Alumno
    {
        $nombres = ['Lucía', 'Tomás', 'Juana', 'Benjamín', 'Catalina', 'Santiago', 'Alma', 'Francisco', 'Olivia', 'Julián', 'Renata', 'Lorenzo', 'Emma', 'Nicolás', 'Clara', 'Pedro', 'Delfina', 'Simón', 'Isabella', 'Agustín', 'Mora', 'Ignacio', 'Victoria', 'Bruno', 'Pilar', 'Valentín', 'Amparo', 'Facundo', 'Josefina', 'Manuel', 'Abril', 'Lautaro', 'Malena', 'Ramiro', 'Bianca', 'León', 'Elena', 'Juan Ignacio', 'Ana Paula', 'Lucas', 'María Emilia', 'Franco', 'Lola', 'Salvador', 'Julieta', 'Ciro', 'Aitana', 'Lisandro'];
        $apellidos = ['Álvarez', 'Bustos', 'Castro', 'Domínguez', 'Escobar', 'Figueroa', 'Giménez', 'Hernández', 'Ibáñez', 'Ledesma', 'Medina', 'Navarro', 'Ortega', 'Pereyra', 'Quiroga', 'Ramírez', 'Sosa', 'Torres', 'Vega', 'Zárate', 'Aguilar', 'Barrios', 'Cabrera', 'Delgado', 'Espinoza', 'Flores', 'Godoy', 'Herrera', 'Iglesias', 'Jara', 'Luna', 'Mansilla', 'Núñez', 'Ojeda', 'Paz', 'Riquelme', 'Suárez', 'Tapia', 'Vargas', 'Zúñiga', 'Acosta', 'Bravo', 'Carrizo', 'Duarte', 'Estévez', 'Ferreyra', 'Gallardo', 'Hidalgo', 'Lagos', 'Muñoz', 'Ocampo', 'Ponce', 'Ríos', 'Salinas', 'Toledo', 'Varela', 'Villalba', 'Zamora', 'Benítez', 'Méndez'];
        $calles = ['Belgrano', 'San Martín', 'Sarmiento', 'Roca', 'Mitre', 'Brown', 'Los Arrayanes', 'Rivadavia', 'Los Álamos', 'Las Heras', 'Alberdi', 'San Juan'];
        $indice = ($grado - 1) * 36 + ($division === 'B' ? 18 : 0) + $posicion - 1;
        $cohorte = $grupo->ciclo_lectivo - $grado - 5;
        $mes = 1 + ($posicion % 12);
        $nacimiento = sprintf('%04d-%02d-%02d', $cohorte - ($mes > 6 ? 1 : 0), $mes, 1 + ($indice % 28));
        $localidad = $localidades[$indice % count($localidades)];
        $datos = [
            'apellido' => $apellidos[(($indice % count($nombres)) * 7 + intdiv($indice, count($nombres))) % count($apellidos)],
            'nombre' => $nombres[$indice % count($nombres)],
            'tipo_documento_id' => $dniId,
            'numero_documento' => (string) (48000000 + ($cohorte - 2010) * 750000 + $indice * 137),
            'sexo_registral' => $indice % 2 === 0 ? 'F' : 'M',
            'genero' => $indice % 2 === 0 ? 'Mujer' : 'Varón',
            'nacionalidad_id' => $nacionalidadId,
            'fecha_nacimiento' => $nacimiento,
            'ciudad_nacimiento_id' => $localidad['id'],
            'ciudad_id' => $localidad['id'],
            'cpa' => $localidad['cpa'],
            'domicilio' => $calles[$indice % count($calles)].' '.(120 + ($indice * 37) % 2900),
            'fecha_ingreso' => $grupo->ciclo_lectivo.'-02-23',
            'fecha_inicio_cursado' => $grupo->ciclo_lectivo.'-02-23',
            'autoriza_uso_imagen' => $indice % 5 !== 0,
            'observaciones' => 'Alumno ficticio generado para probar los grupos de Primaria.',
        ];
        $alumno = Alumno::query()->lockForUpdate()->firstOrNew(
            ['legajo' => sprintf('DEMO-PRI-%d-%d%s-%02d', $grupo->ciclo_lectivo, $grado, $division, $posicion)],
        );
        $anterior = $alumno->exists ? $alumno->attributesToArray() : null;

        if (! $alumno->exists) {
            $alumno->fill(['activo' => true, ...$datos]);
        } elseif ($alumno->numero_documento === null && $alumno->tipo_documento_id === null
            && $alumno->observaciones === $datos['observaciones']) {
            $nombresAnteriores = array_slice($nombres, 0, 18);
            $apellidosAnteriores = array_slice($apellidos, 0, 20);
            $identidadOriginal = $alumno->nombre === $nombresAnteriores[($posicion + $grado - 2) % count($nombresAnteriores)]
                && $alumno->apellido === $apellidosAnteriores[$indice % count($apellidosAnteriores)];
            $domicilioVacio = $alumno->domicilio === null && $alumno->ciudad_id === null && $alumno->cpa === null;
            if ($identidadOriginal) {
                $alumno->fill(['nombre' => $datos['nombre'], 'apellido' => $datos['apellido']]);
            }
            foreach ($datos as $campo => $valor) {
                if ((! $identidadOriginal && in_array($campo, ['sexo_registral', 'genero'], true))
                    || (! $domicilioVacio && in_array($campo, ['domicilio', 'ciudad_id', 'cpa'], true))) {
                    continue;
                }
                if ($alumno->getAttribute($campo) === null) {
                    $alumno->setAttribute($campo, $valor);
                }
            }
        }

        if ($alumno->isDirty()) {
            $alumno->save();
            $alumno->refresh();
            $this->auditar($anterior === null ? 'create' : 'update', $alumno, $anterior, $alumno->attributesToArray());
        }

        return $alumno;
    }
}
