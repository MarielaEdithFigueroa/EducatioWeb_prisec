<?php

namespace App\Http\Requests\Academico;

use App\Models\ContactoIntegrante;
use App\Models\Integrante;
use App\Rules\Cuit;
use App\Rules\Telefono;
use App\Services\TelefonoNormalizer;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class IntegranteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $integrante = $this->route('integrante');

        return $integrante instanceof Integrante
            ? $this->user()?->can('update', $integrante) === true
            : $this->user()?->can('create', Integrante::class) === true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $integrante = $this->route('integrante');
        $actual = $integrante instanceof Integrante ? $integrante : null;
        $tiposActuales = $actual?->documentacion()->pluck('tipo_documentacion_integrante_id')->all() ?? [];

        return [
            ...$this->reglasRelaciones(),
            'legajo' => ['required', 'string', 'max:30', Rule::unique('integrantes', 'legajo')->ignore($actual)],
            'apellido' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:100'],
            'estado_civil' => ['nullable', 'string', Rule::in(Integrante::ESTADOS_CIVILES)],
            'domicilio' => ['nullable', 'string', 'max:200'],
            'titulo_habilitante' => ['nullable', 'string', 'max:200'],
            'numero_registro' => ['nullable', 'string', 'max:50'],
            'art' => ['nullable', 'string', 'max:100'],
            'obra_social' => ['nullable', 'string', 'max:100'],
            'cuenta_bancaria' => ['nullable', 'string', 'max:100'],
            'numero_documento' => ['nullable', 'required_with:tipo_documento_id', 'string', 'max:20', 'regex:/^[A-Z0-9][A-Z0-9 .-]*$/'],
            'cuilt' => ['nullable', 'string', new Cuit],
            'sexo_registral' => ['nullable', Rule::in(['F', 'M', 'X'])],
            'cpa' => ['nullable', 'string', 'regex:/^(?:[0-9]{4}|[A-Z][0-9]{4}[A-Z]{3})$/'],
            'telefono' => ['nullable', 'string', 'max:40', new Telefono($this->esLineaFija('telefono'))],
            'celular' => ['nullable', 'string', 'max:40', new Telefono],
            ...array_fill_keys(array_map(fn (string $campo): string => $campo.'_es_fijo', Integrante::TELEFONOS_CON_TIPO), ['sometimes', 'boolean']),
            'email' => ['nullable', 'email', 'max:254'],
            'fecha_nacimiento' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'fecha_ingreso' => ['nullable', 'date_format:Y-m-d', ...($this->filled('fecha_nacimiento') ? ['after_or_equal:fecha_nacimiento'] : [])],
            'fecha_residencia_provincia' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'tipo_documento_id' => ['nullable', 'required_with:numero_documento', 'integer', 'exists:tipos_documento,id'],
            'ciudad_id' => ['nullable', 'integer', $this->referencia('ciudades', $actual?->ciudad_id)],
            'ciudad_nacimiento_id' => ['nullable', 'integer', $this->referencia('ciudades', $actual?->ciudad_nacimiento_id)],
            'nacionalidad_id' => ['nullable', 'integer', $this->referencia('nacionalidades', $actual?->nacionalidad_id)],
            'funciones_ids' => ['sometimes', 'array'],
            'funciones_ids.*' => ['required', 'integer', 'distinct', 'exists:funciones_equipo,id'],
            'tiene_aportes' => ['sometimes', 'boolean'],
            'horario' => ['nullable', 'string', 'max:2000'],
            'documentacion' => ['sometimes', 'array'],
            'documentacion.*' => ['array:tipo_documentacion_integrante_id,presentado,archivo,eliminar_archivo,adjunto_id'],
            'documentacion.*.archivo' => ['nullable', 'prohibited_if:documentacion.*.eliminar_archivo,true', ...DocumentoIntegranteRequest::reglasPdf()],
            'documentacion.*.eliminar_archivo' => ['sometimes', 'boolean'],
            'documentacion.*.adjunto_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'documentacion.*.tipo_documentacion_integrante_id' => [
                'required', 'integer', 'distinct',
                Rule::exists('tipos_documentacion_integrante', 'id')->where(fn (Builder $query) => $query->where(
                    fn (Builder $query) => $query->where('activo', true)->orWhereIn('id', $tiposActuales)
                )),
            ],
            'documentacion.*.presentado' => ['required', 'boolean'],
            'foto' => ['nullable', Rule::prohibitedIf($this->boolean('eliminar_foto')), 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:'.config('adjuntos.max_kb'), 'dimensions:ratio=1,max_width=6000,max_height=6000'],
            'eliminar_foto' => ['sometimes', 'boolean'],
            'continuar' => ['sometimes', 'boolean'],
            'seccion' => ['nullable', Rule::in(IndexIntegranteRequest::SECCIONES)],
            'filtros' => ['sometimes', 'array:buscar,estado,funcion_id'],
            ...IndexIntegranteRequest::reglasFiltros('filtros.'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'legajo.unique' => 'El legajo ya existe, incluso entre integrantes dados de baja.',
            'cpa.regex' => 'Ingresá un código postal de 4 dígitos o un CPA válido.',
            '*.regex' => 'Revisá el formato ingresado.',
            'foto.dimensions' => 'La foto debe ser cuadrada y no superar los 6000 × 6000 píxeles.',
            'foto.max' => 'La foto supera el tamaño máximo permitido.',
            'foto.image' => 'Seleccioná una imagen JPG, PNG o WebP.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'cuilt' => 'CUIL/CUIT',
            'telefono' => 'teléfono',
            'celular' => 'celular',
            'contactos.*.nombre' => 'nombre y apellido',
            'contactos.*.telefono' => 'teléfono',
            'contactos.*.vinculo' => 'vínculo',
            'contactos.*.observaciones' => 'observaciones',
            'referencias_laborales.*.lugar' => 'lugar',
            'referencias_laborales.*.referencia_1_telefono' => 'teléfono de la referencia 1',
            'referencias_laborales.*.referencia_2_telefono' => 'teléfono de la referencia 2',
            'referencias_laborales.*.observaciones' => 'observaciones',
            'funciones_ids' => 'funciones del equipo',
            'funciones_ids.*' => 'función del equipo',
            'tipo_documento_id' => 'tipo de documento',
            'ciudad_id' => 'localidad',
            'ciudad_nacimiento_id' => 'ciudad de nacimiento',
            'nacionalidad_id' => 'nacionalidad',
            'fecha_ingreso' => 'fecha de ingreso',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'titulo_habilitante' => 'título habilitante',
            'numero_registro' => 'número de registro',
            'documentacion.*.tipo_documentacion_integrante_id' => 'tipo de documentación',
            'documentacion.*.presentado' => 'documentación presentada',
            'documentacion.*.archivo' => 'PDF',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('funciones_ids') && $this->input('funciones_ids') === null) {
            $this->merge(['funciones_ids' => []]);
        }
        $datos = [];
        foreach (['legajo', 'apellido', 'nombre', 'estado_civil', 'numero_documento', 'cuilt', 'sexo_registral', 'domicilio', 'cpa', 'telefono', 'celular', 'email', 'titulo_habilitante', 'numero_registro', 'art', 'obra_social', 'cuenta_bancaria', 'horario'] as $campo) {
            $valor = $this->input($campo);
            if (! is_string($valor)) {
                continue;
            }
            $valor = $campo === 'horario' ? trim($valor) : Str::squish($valor);
            if (in_array($campo, ['email'], true)) {
                $valor = Str::lower($valor);
            }
            if (in_array($campo, ['numero_documento', 'cpa', 'sexo_registral'], true)) {
                $valor = Str::upper($valor);
            }
            if ($campo === 'cuilt') {
                $valor = preg_replace('/[\\s.-]+/', '', $valor);
            }
            if (in_array($campo, ['telefono', 'celular'], true)) {
                $valor = TelefonoNormalizer::normalizarArgentina($valor, $campo !== 'celular' && $this->esLineaFija($campo)) ?? $valor;
            }
            $datos[$campo] = $valor === '' ? null : $valor;
        }
        $this->merge($datos);
        $this->normalizarRelaciones();
    }

    /** @var array<string, Collection> */
    private array $filasActuales = [];

    /** @return array<string, array{relacion: string, tabla: string, obligatorio: string, textos: list<string>, telefonos: list<string>}> */
    private function colecciones(): array
    {
        return [
            'referencias_laborales' => ['relacion' => 'referenciasLaborales', 'tabla' => 'integrantes_referencias_laborales', 'obligatorio' => 'lugar', 'textos' => ['lugar', 'cargo', 'tiempo', 'referencia_1_nombre', 'referencia_2_nombre'], 'telefonos' => ['referencia_1_telefono', 'referencia_2_telefono']],
            'contactos' => ['relacion' => 'contactos', 'tabla' => 'integrantes_contactos', 'obligatorio' => 'nombre', 'textos' => ['nombre'], 'telefonos' => ['telefono']],
        ];
    }

    private function fijoRelacionado(string $coleccion, int|string $indice, string $campo): bool
    {
        $prefijo = "$coleccion.$indice";
        if ($this->has("$prefijo.{$campo}_es_fijo")) {
            return $this->boolean("$prefijo.{$campo}_es_fijo");
        }
        if (! isset($this->filasActuales[$coleccion])) {
            $integrante = $this->route('integrante');
            $relacion = $this->colecciones()[$coleccion]['relacion'];
            $this->filasActuales[$coleccion] = $integrante instanceof Integrante ? $integrante->{$relacion}()->where('activo', true)->get()->keyBy('id') : collect();
        }
        $id = $this->input("$prefijo.id");
        $registro = is_scalar($id) ? $this->filasActuales[$coleccion]->get($id) : null;

        return TelefonoNormalizer::esLineaFija($registro?->{$campo});
    }

    /** @return array<string, list<mixed>> */
    private function reglasRelaciones(): array
    {
        $reglas = [];
        $integrante = $this->route('integrante');
        foreach ($this->colecciones() as $coleccion => $config) {
            $campos = ['id', ...$config['textos'], ...$config['telefonos'], ...array_map(fn (string $campo): string => $campo.'_es_fijo', $config['telefonos']), 'observaciones'];
            if ($coleccion === 'contactos') {
                $campos[] = 'vinculo';
                $reglas['contactos.*.vinculo'] = ['nullable', Rule::in(ContactoIntegrante::VINCULOS)];
            }
            $existe = Rule::exists($config['tabla'], 'id')->where(fn (Builder $query) => $query->where('integrante_id', $integrante instanceof Integrante ? $integrante->id : 0)->where('activo', true));
            $reglas[$coleccion] = ['sometimes', 'array', 'list'];
            $reglas[$coleccion.'.*'] = ['array:'.implode(',', $campos)];
            $reglas[$coleccion.'.*.id'] = ['nullable', 'integer', 'distinct', $existe];
            $reglas[$coleccion.'_bajas'] = ['sometimes', 'array', 'list'];
            $reglas[$coleccion.'_bajas.*'] = ['required', 'integer', 'distinct', $existe];
            $reglas[$coleccion.'.*.observaciones'] = ['nullable', 'string', 'max:2000'];
            foreach ($config['textos'] as $campo) {
                $reglas["$coleccion.*.$campo"] = [$campo === $config['obligatorio'] ? 'required' : 'nullable', 'string', 'max:150'];
            }
            foreach ($config['telefonos'] as $campo) {
                $reglas["$coleccion.*.{$campo}_es_fijo"] = ['sometimes', 'boolean'];
            }
            $filas = $this->input($coleccion, []);
            if (is_array($filas)) {
                foreach ($filas as $indice => $fila) {
                    foreach ($config['telefonos'] as $campo) {
                        $reglas["$coleccion.$indice.$campo"] = ['nullable', 'string', 'max:40', new Telefono($this->fijoRelacionado($coleccion, $indice, $campo))];
                    }
                }
            }
        }

        return $reglas;
    }

    private function normalizarRelaciones(): void
    {
        foreach ($this->colecciones() as $coleccion => $config) {
            foreach ([$coleccion, $coleccion.'_bajas'] as $campo) {
                if ($this->has($campo) && $this->input($campo) === null) {
                    $this->merge([$campo => []]);
                }
            }
            $filas = $this->input($coleccion);
            if (! is_array($filas)) {
                continue;
            }
            foreach ($filas as $indice => &$fila) {
                if (! is_array($fila)) {
                    continue;
                }
                foreach ([...$config['textos'], 'observaciones', 'vinculo', ...$config['telefonos']] as $campo) {
                    if (! isset($fila[$campo]) || ! is_string($fila[$campo])) {
                        continue;
                    }
                    $valor = $campo === 'observaciones' ? trim($fila[$campo]) : Str::squish($fila[$campo]);
                    if (in_array($campo, $config['telefonos'], true)) {
                        $valor = TelefonoNormalizer::normalizarArgentina($valor, $this->fijoRelacionado($coleccion, $indice, $campo)) ?? $valor;
                    }
                    $fila[$campo] = $valor === '' ? null : $valor;
                }
            }
            unset($fila);
            $this->merge([$coleccion => $filas]);
        }
    }

    private function esLineaFija(string $campo): bool
    {
        if ($this->has($campo.'_es_fijo')) {
            return $this->boolean($campo.'_es_fijo');
        }
        $integrante = $this->route('integrante');

        return $integrante instanceof Integrante && TelefonoNormalizer::esLineaFija($integrante->{$campo});
    }

    private function referencia(string $tabla, ?int $actual): Exists
    {
        return Rule::exists($tabla, 'id')->where(fn (Builder $query) => $query->where(
            fn (Builder $query) => $query->where('activo', true)->when($actual, fn (Builder $query) => $query->orWhere('id', $actual))
        ));
    }
}
